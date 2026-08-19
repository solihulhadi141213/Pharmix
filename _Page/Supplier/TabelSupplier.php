<?php
    //koneksi dan session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    //inisiasi Variabel
    $JmlHalaman = 0;
    $page       = 1;
    $html       = "";

    // Validasi Sesi Akses
    if (empty($SessionIdAkses)) {
        echo json_encode([
            "status" => "error",
            "html"   => '
                <tr>
                    <td colspan="8" class="text-center text-danger">
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
        
    // Tangkap parameter dengan default values
    $keyword_by = $_POST['keyword_by'] ?? "";
    $keyword    = $_POST['keyword'] ?? "";
    $batas      = (int)($_POST['batas'] ?? 10);
    $ShortBy    = in_array($_POST['ShortBy'] ?? "DESC", ["ASC", "DESC"]) ? $_POST['ShortBy'] : "DESC";
    $OrderBy    = in_array($_POST['OrderBy'] ?? "id_supplier", ["id_supplier", "nama_supplier"]) ? $_POST['OrderBy'] : "id_supplier";
    $page       = (int)($_POST['page'] ?? 1);
    $posisi     = ($page - 1) * $batas;
    
    // Build WHERE clause berdasarkan filter
    $whereClause = "";
    $params = [];
    $types = "";
    
    if (!empty($keyword)) {
        if (empty($keyword_by)) {
            // Search di semua field
            $whereClause = "WHERE nama_supplier LIKE ? OR alamat_supplier LIKE ? OR email_supplier LIKE ? OR kontak_supplier LIKE ? OR npwp LIKE ? OR pic LIKE ?";
            $searchKeyword = "%$keyword%";
            $params = [$searchKeyword, $searchKeyword, $searchKeyword, $searchKeyword, $searchKeyword, $searchKeyword];
            $types = "ssssss";
        } else {
            // Search di field spesifik (dengan validasi)
            $allowedFields = ["nama_supplier", "alamat_supplier", "email_supplier", "kontak_supplier", "npwp", "pic"];
            if (in_array($keyword_by, $allowedFields)) {
                $whereClause = "WHERE $keyword_by LIKE ?";
                $params = ["%$keyword%"];
                $types = "s";
            }
        }
    }
    
    // Query untuk hitung total data
    $countQuery = "SELECT COUNT(id_supplier) as jml FROM supplier " . $whereClause;
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
            "status" => "error",
            "html"   => '
                <tr>
                    <td colspan="8" class="text-center text-danger">
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
    
    // Query untuk fetch data dengan LIMIT
    $dataQuery = "SELECT * FROM supplier " . $whereClause . " ORDER BY $OrderBy $ShortBy LIMIT ?, ?";
    $stmtData = mysqli_prepare($Conn, $dataQuery);
    $limitParams = array_merge($params, [$posisi, $batas]);
    $limitTypes = $types . "ii";
    mysqli_stmt_bind_param($stmtData, $limitTypes, ...$limitParams);
    mysqli_stmt_execute($stmtData);
    $resultData = mysqli_stmt_get_result($stmtData);
    
    // Ambil semua supplier IDs untuk query transaksi
    $supplierIds = [];
    $resultDataArray = [];
    while ($row = mysqli_fetch_array($resultData)) {
        $supplierIds[] = $row['id_supplier'];
        $resultDataArray[] = $row;
    }
    mysqli_stmt_close($stmtData);
    
    // Query transaksi untuk semua supplier sekaligus (mencegah N+1 problem)
    $transactionData = [];
    if (!empty($supplierIds)) {
        $placeholders = implode(',', array_fill(0, count($supplierIds), '?'));
        $transQuery = "SELECT id_supplier, SUM(total) as total FROM transaksi_jual_beli WHERE id_supplier IN ($placeholders) GROUP BY id_supplier";
        $stmtTrans = mysqli_prepare($Conn, $transQuery);
        mysqli_stmt_bind_param($stmtTrans, str_repeat('s', count($supplierIds)), ...$supplierIds);
        mysqli_stmt_execute($stmtTrans);
        $resultTrans = mysqli_stmt_get_result($stmtTrans);
        
        while ($trans = mysqli_fetch_array($resultTrans)) {
            $transactionData[$trans['id_supplier']] = $trans['total'] ?? 0;
        }
        mysqli_stmt_close($stmtTrans);
    }
    
    // Generate HTML
    foreach ($resultDataArray as $data) {
        $id_supplier     = $data['id_supplier'];
        $nama_supplier   = $data['nama_supplier'];
        $alamat_supplier = $data['alamat_supplier'] ?: "-";
        $email_supplier  = $data['email_supplier'] ?: "-";
        $kontak_supplier = $data['kontak_supplier'] ?: "-";
        $pic             = $data['pic'] ?: "-";
        $npwp            = $data['npwp'] ?: "-";
        
        // Ambil dari cache transaksi
        $jumlah_transaksi = $transactionData[$id_supplier] ?? 0;
        $VolumeTransaksi = "Rp " . number_format($jumlah_transaksi, 0, ',', '.');
        
        $html .= '
            <tr>
                <td><small>'.$no.'</small></td>
                <td>
                    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalDetailSupplier" data-id="'.$id_supplier.'">
                        <small>'.$nama_supplier.'</small>
                    </a>
                </td>
                <td><small class="text-muted">'.$email_supplier.'</small></td>
                <td><small class="text-muted">'.$kontak_supplier.'</small></td>
                <td><small class="text-muted">'.$pic.'</small></td>
                <td><small class="text-muted">'.$npwp.'</small></td>
                <td><small class="text-muted">'.$VolumeTransaksi.'</small></td>
                <td>
                    <button type="button" class="btn btn-sm btn-floating btn-secondary" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow" style="">
                        <li class="dropdown-header text-start">
                            <h6>Option</h6>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalDetailSupplier" data-id="'.$id_supplier.'">
                                <i class="bi bi-info-circle"></i> Detail
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalEditSupplier" data-id="'.$id_supplier.'">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalHapusSupplier" data-id="'.$id_supplier.'">
                                <i class="bi bi-trash"></i> Hapus
                            </a>
                        </li>
                    </ul>
                </td>
            </tr>
        ';
        $no++;
    }
    
    $JmlHalaman = ceil($jml_data / $batas); 

    echo json_encode([
        "status"     => "success",
        "html"       => $html,
        "page"       => $page,
        "total_page" => $JmlHalaman,
        "total_data" => $jml_data
    ]);
?>
