<?php
    // Koneksi dan session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Inisiasi Variabel
    $JmlHalaman = 0;
    $page       = 1;
    $html       = "";

    // Validasi Sesi Akses
    if (empty($SessionIdAkses)) {
        echo json_encode([
            "status" => "error",
            "html"   => '
                <tr>
                    <td colspan="9" class="text-center text-danger">
                        <small>Sesi akses sudah berakhir. Silakan login ulang.</small>
                    </td>
                </tr>
            ',
            "page" => 1,
            "total_page" => 1,
            "total_data" => 0
        ]);
        exit;
    }
        
    // Tangkap parameter dari form filter dengan default values
    $keyword_by = $_POST['keyword_by'] ?? "";
    $keyword    = $_POST['keyword'] ?? "";
    $batas      = (int)($_POST['batas'] ?? 10);
    $ShortBy    = in_array($_POST['ShortBy'] ?? "DESC", ["ASC", "DESC"]) ? $_POST['ShortBy'] : "DESC";
    
    // Validasi Kolom Order By agar sesuai dengan tabel kunjungan/anggota
    $allowedOrder = ["id_kunjungan", "id_pasien", "nama_pasien", "tanggal_kunjungan", "priority", "jenis_kunjungan", "status"];
    $OrderBy = in_array($_POST['OrderBy'] ?? "id_kunjungan", $allowedOrder) ? $_POST['OrderBy'] : "id_kunjungan";
    // Sesuaikan mapping nama kolom untuk pengurutan tabel (misal: nama_pasien -> anggota.nama)
    if ($OrderBy === "nama_pasien") {
        $OrderBy = "anggota.nama";
    } elseif ($OrderBy === "id_pasien") {
        $OrderBy = "anggota.id_pasien";
    } else {
        $OrderBy = "kunjungan." . $OrderBy;
    }

    $page       = (int)($_POST['page'] ?? 1);
    $posisi     = ($page - 1) * $batas;
    
    // Build WHERE clause menggunakan JOIN ke tabel anggota
    $whereClause = "";
    $params = [];
    $types = "";
    
    if (!empty($keyword)) {
        if (empty($keyword_by)) {
            // Search di beberapa field utama
            $whereClause = "WHERE anggota.id_pasien LIKE ? OR anggota.nama LIKE ? OR kunjungan.id_encounter LIKE ? OR kunjungan.priority LIKE ? OR kunjungan.jenis_kunjungan LIKE ? OR kunjungan.status LIKE ?";
            $searchKeyword = "%$keyword%";
            $params = [$searchKeyword, $searchKeyword, $searchKeyword, $searchKeyword, $searchKeyword, $searchKeyword];
            $types = "ssssss";
        } else {
            // Search di field spesifik
            if ($keyword_by === "id_pasien") {
                $whereClause = "WHERE anggota.id_pasien LIKE ?";
                $params = ["%$keyword%"];
                $types = "s";
            } elseif ($keyword_by === "nama_pasien") {
                $whereClause = "WHERE anggota.nama LIKE ?";
                $params = ["%$keyword%"];
                $types = "s";
            } else {
                $allowedFieldsKunjungan = ["tanggal_kunjungan", "priority", "jenis_kunjungan", "status"];
                if (in_array($keyword_by, $allowedFieldsKunjungan)) {
                    $whereClause = "WHERE kunjungan.$keyword_by LIKE ?";
                    $params = ["%$keyword%"];
                    $types = "s";
                }
            }
        }
    }
    
    // Query untuk hitung total data dengan JOIN
    $countQuery = "SELECT COUNT(kunjungan.id_kunjungan) as jml FROM kunjungan LEFT JOIN anggota ON kunjungan.id_anggota = anggota.id_anggota " . $whereClause;
    $stmtCount = mysqli_prepare($Conn, $countQuery);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmtCount, $types, ...$params);
    }
    mysqli_stmt_execute($stmtCount);
    $resultCount = mysqli_stmt_get_result($stmtCount);
    $rowCount = mysqli_fetch_array($resultCount);
    $jml_data = $rowCount['jml'];
    mysqli_stmt_close($stmtCount);
    
    if (empty($jml_data)) {
        echo json_encode([
            "status" => "success",
            "html"   => '
                <tr>
                    <td colspan="9" class="text-center text-danger">
                        <small>Tidak ada data yang ditampilkan</small>
                    </td>
                </tr>
            ',
            "page" => 1,
            "total_page" => 1,
            "total_data" => 0
        ]);
        exit;
    }
    
    $no = 1 + $posisi;
    
    // Query untuk fetch data dengan LIMIT, JOIN, dan ORDER
    $dataQuery = "SELECT kunjungan.*, anggota.id_pasien as rm_pasien, anggota.nama as nama_pasien 
                  FROM kunjungan 
                  LEFT JOIN anggota ON kunjungan.id_anggota = anggota.id_anggota " 
                  . $whereClause . " ORDER BY $OrderBy $ShortBy LIMIT ?, ?";
                  
    $stmtData = mysqli_prepare($Conn, $dataQuery);
    $limitParams = array_merge($params, [$posisi, $batas]);
    $limitTypes = $types . "ii";
    mysqli_stmt_bind_param($stmtData, $limitTypes, ...$limitParams);
    mysqli_stmt_execute($stmtData);
    $resultData = mysqli_stmt_get_result($stmtData);
    
    // Generate HTML
    while ($row = mysqli_fetch_array($resultData)) {
        $id_kunjungan      = $row['id_kunjungan'];
        $rm_pasien         = $row['rm_pasien'] ?: "-";
        $nama_pasien       = $row['nama_pasien'] ?: "-";
        $tanggal_kunjungan = $row['tanggal_kunjungan'] ? date('d/m/Y H:i', strtotime($row['tanggal_kunjungan'])) : "-";
        $jenis_kunjungan   = $row['jenis_kunjungan'] ?: "-";
        $priority          = $row['priority'] ?: "-";
        $rawStatus = $row['status'] ?? '-';
        $statusBadge = "-";

        $rawPriority = $row['priority'] ?? '-';
        $priorityBadge = "-";

        switch ($rawPriority) {
            case 'Normal':
                $priorityBadge = '<span class="badge bg-secondary">Normal</span>';
                break;
            case 'Urgent':
                $priorityBadge = '<span class="badge bg-warning text-dark">Urgent</span>';
                break;
            case 'Emergency':
                $priorityBadge = '<span class="badge bg-danger">Emergency</span>';
                break;
            default:
                $priorityBadge = '<span class="badge bg-secondary">-</span>';
                break;
        }

        switch ($rawStatus) {
            case 'planned':
                $statusBadge = '<span class="badge bg-secondary">Planned</span>';
                break;
            case 'arrived':
                $statusBadge = '<span class="badge bg-primary">Arrived</span>';
                break;
            case 'triaged':
                $statusBadge = '<span class="badge bg-info text-dark">Triaged</span>';
                break;
            case 'in-progress':
                $statusBadge = '<span class="badge bg-warning text-dark">In-Progress</span>';
                break;
            case 'onleave':
                $statusBadge = '<span class="badge bg-dark">Onleave</span>';
                break;
            case 'finished':
                $statusBadge = '<span class="badge bg-success">Finished</span>';
                break;
            case 'cancelled':
                $statusBadge = '<span class="badge bg-danger">Cancelled</span>';
                break;
            case 'entered-in-error':
                $statusBadge = '<span class="badge bg-secondary">Entered in Error</span>';
                break;
            case 'unknown':
                $statusBadge = '<span class="badge bg-light text-dark">Unknown</span>';
                break;
            default:
                $statusBadge = '<span class="badge bg-secondary">-</span>';
                break;
        }

        if (empty($row['id_encounter'])) {
            $tombol_encounter = '
                <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalKirimEncounter" data-id="'.$id_kunjungan.'">
                    <small class="text-danger">
                        <i class="bi bi-send"></i> Send Encounter
                    </small>
                </a>
            ';
        } else {
            $id_encounter = $row['id_encounter'];
            $id_encounter_tampil = mb_strimwidth($id_encounter, 0, 20, '...');

            $tombol_encounter = '
                <a href="javascript:void(0);" 
                    class="text-primary"
                    data-bs-toggle="modal" 
                    data-bs-target="#ModalDetailEncounter" 
                    data-id="'.$id_encounter.'"
                    title="'.$id_encounter.'">
                    <i>'.$id_encounter_tampil.'</i>
                </a>
            ';
        }
        
        $html .= '
            <tr>
                <td><small class="text-muted">'.$no.'</small></td>
                <td>
                    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalDetail" data-id="'.$id_kunjungan.'">
                        <small class="text-primary">
                            <b>'.$rm_pasien.'</b>
                        </small>
                    </a>
                </td>
                <td><small class="text-muted">'.$nama_pasien.'</small></td>
                <td><small class="text-muted">'.$tanggal_kunjungan.'</small></td>
                <td><small class="text-muted">'.$jenis_kunjungan.'</small></td>
                <td>'.$priorityBadge.'</td>
                <td>'.$tombol_encounter.'</td>
                <td>'.$statusBadge.'</td>
                <td>
                    <button type="button" class="btn btn-sm btn-floating btn-secondary" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <li class="dropdown-header text-start">
                            <h6>Option</h6>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalDetail" data-id="'.$id_kunjungan.'">
                                <i class="bi bi-info-circle"></i> Detail
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalEdit" data-id="'.$id_kunjungan.'">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalHapus" data-id="'.$id_kunjungan.'">
                                <i class="bi bi-trash"></i> Hapus
                            </a>
                        </li>
                    </ul>
                </td>
            </tr>
        ';
        $no++;
    }
    mysqli_stmt_close($stmtData);
    
    $JmlHalaman = ceil($jml_data / $batas); 

    echo json_encode([
        "status"     => "success",
        "html"       => $html,
        "page"       => $page,
        "total_page" => $JmlHalaman,
        "total_data" => $jml_data
    ]);
?>