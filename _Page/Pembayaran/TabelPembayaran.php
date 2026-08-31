<?php
    // =====================================================
    // Koneksi dan Session
    // =====================================================
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Zona waktu
    date_default_timezone_set("Asia/Jakarta");

    // Response JSON
    header('Content-Type: application/json; charset=utf-8');

    // =====================================================
    // Fungsi Response Error
    // =====================================================
    function response_error(string $message, int $page = 1, int $total_page = 1, int $total_data = 0) {
        echo json_encode([
            "status"     => "error",
            "html"       => '
                <tr>
                    <td colspan="8" class="text-center text-danger">
                        <small>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</small>
                    </td>
                </tr>
            ',
            "page"       => $page,
            "total_page" => $total_page,
            "total_data" => $total_data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // =====================================================
    // Validasi Session
    // =====================================================
    if (empty($SessionIdAkses)) {
        response_error("Sesi akses sudah berakhir. Silakan login ulang.");
    }

    // =====================================================
    // Ambil Filter
    // =====================================================
    $page       = $_POST['page'] ?? 1;
    $batas      = $_POST['batas'] ?? 10;
    $OrderBy    = $_POST['OrderBy'] ?? 'id_transaksi_pembayaran';
    $ShortBy    = $_POST['ShortBy'] ?? 'DESC';
    $keyword_by = trim($_POST['keyword_by'] ?? '');
    $keyword    = trim($_POST['keyword'] ?? '');

    // =====================================================
    // Validasi Page dan Batas
    // =====================================================
    $page  = (int) $page;
    $batas = (int) $batas;
    if ($page < 1) { $page = 1; }
    if ($batas < 1) { $batas = 10; }
    if ($batas > 100) { $batas = 100; }

    // =====================================================
    // Validasi Sorting
    // =====================================================
    $ShortBy = strtoupper($ShortBy);
    if (!in_array($ShortBy, ['ASC', 'DESC'], true)) {
        $ShortBy = 'DESC';
    }

    // =====================================================
    // Mapping Order By
    // =====================================================
    $allowedOrder = [
        'id_transaksi_pembayaran' => 'tp.id_transaksi_pembayaran',
        'tanggal'                 => 'tp.tanggal',
        'id_transaksi'            => 'tp.id_transaksi',
        'id_transaksi_jual_beli'  => 'tp.id_transaksi_jual_beli',
        'kategori_pembayaran'     => 'tp.kategori_pembayaran',
        'kategori_transaksi'      => 'tp.kategori_transaksi',
        'jumlah'                  => 'tp.jumlah',
        'creat_by_name'           => 'tp.creat_by_name'
    ];
    if (!array_key_exists($OrderBy, $allowedOrder)) {
        $OrderBy = 'id_transaksi_pembayaran';
        $OrderBySql = 'tp.id_transaksi_pembayaran';
    } else {
        $OrderBySql = $allowedOrder[$OrderBy];
    }

    // =====================================================
    // Validasi Keyword By
    // =====================================================
    $allowedKeywordBy = [
        'id_transaksi_pembayaran' => 'tp.id_transaksi_pembayaran',
        'tanggal'                 => 'tp.tanggal',
        'id_transaksi'            => 'tp.id_transaksi',
        'id_transaksi_jual_beli'  => 'tp.id_transaksi_jual_beli',
        'kategori_pembayaran'     => 'tp.kategori_pembayaran',
        'kategori_transaksi'      => 'tp.kategori_transaksi',
        'creat_by_name'           => 'tp.creat_by_name'
    ];
    if (!empty($keyword_by) && !array_key_exists($keyword_by, $allowedKeywordBy)) {
        $keyword_by = '';
    }

    // =====================================================
    // Build WHERE
    // =====================================================
    $where      = [];
    $bindTypes  = '';
    $bindValues = [];

    // =====================================================
    // Filter Keyword
    // =====================================================
    if ($keyword !== '') {
        $keywordLike = '%' . $keyword . '%';
        if ($keyword_by !== '') {
            $where[] = $allowedKeywordBy[$keyword_by] . " LIKE ?";
            $bindTypes .= 's';
            $bindValues[] = $keywordLike;
        } else {
            $where[] = "
                (
                    tp.id_transaksi_pembayaran LIKE ?
                    OR tp.tanggal LIKE ?
                    OR tp.id_transaksi LIKE ?
                    OR tp.id_transaksi_jual_beli LIKE ?
                    OR tp.kategori_pembayaran LIKE ?
                    OR tp.kategori_transaksi LIKE ?
                    OR tp.creat_by_name LIKE ?
                )
            ";
            $bindTypes .= 'sssssss';
            $bindValues[] = $keywordLike;
            $bindValues[] = $keywordLike;
            $bindValues[] = $keywordLike;
            $bindValues[] = $keywordLike;
            $bindValues[] = $keywordLike;
            $bindValues[] = $keywordLike;
            $bindValues[] = $keywordLike;
        }
    }

    // =====================================================
    // WHERE SQL
    // =====================================================
    $whereSql = '';
    if (!empty($where)) {
        $whereSql = 'WHERE ' . implode(' AND ', $where);
    }

    // =====================================================
    // Query Dasar
    // =====================================================
    $fromSql = "
        FROM transaksi_pembayaran AS tp
        LEFT JOIN akses AS a ON a.id_akses = tp.creat_by_id
        $whereSql
    ";

    // =====================================================
    // TOTAL DATA
    // =====================================================
    $sql_count = "
        SELECT COUNT(*) AS total
        $fromSql
    ";
    $stmt_count = $Conn->prepare($sql_count);
    if (!$stmt_count) {
        response_error("Gagal mempersiapkan query jumlah data.");
    }

    if (!empty($bindValues)) {
        $stmt_count->bind_param($bindTypes, ...$bindValues);
    }

    if (!$stmt_count->execute()) {
        $stmt_count->close();
        response_error("Gagal menghitung jumlah data.");
    }

    $result_count = $stmt_count->get_result();
    $data_count   = $result_count->fetch_assoc();
    $total_data   = (int) ($data_count['total'] ?? 0);
    $stmt_count->close();

    // =====================================================
    // Total Page
    // =====================================================
    $total_page = ($total_data > 0) ? (int) ceil($total_data / $batas) : 1;

    if ($page > $total_page) {
        $page = $total_page;
    }

    // =====================================================
    // Posisi Data
    // =====================================================
    $posisi = ($page - 1) * $batas;

    // =====================================================
    // Query Data
    // =====================================================
    $sql = "
        SELECT
            tp.id_transaksi_pembayaran,
            tp.id_transaksi,
            tp.id_transaksi_jual_beli,
            tp.kategori_pembayaran,
            tp.kategori_transaksi,
            tp.tanggal,
            tp.jumlah,
            tp.creat_by_name,
            a.nama_akses
        $fromSql
        ORDER BY $OrderBySql $ShortBy
        LIMIT ?, ?
    ";
    $stmt = $Conn->prepare($sql);
    if (!$stmt) {
        response_error("Gagal mempersiapkan query data.", $page, $total_page, $total_data);
    }

    $bindTypesData      = $bindTypes . 'ii';
    $bindValuesData     = $bindValues;
    $bindValuesData[]   = $posisi;
    $bindValuesData[]   = $batas;
    $stmt->bind_param($bindTypesData, ...$bindValuesData);

    if (!$stmt->execute()) {
        $stmt->close();
        response_error("Terjadi kesalahan saat mengambil data.", $page, $total_page, $total_data);
    }

    $query = $stmt->get_result();

    // =====================================================
    // Build HTML
    // =====================================================
    $html = '';
    $no   = $posisi + 1;

    if ($query->num_rows === 0) {
        $html .= '
            <tr>
                <td colspan="8" class="text-center py-4">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i> Tidak ada data yang ditampilkan.
                    </small>
                </td>
            </tr>
        ';
    } else {
        while ($data = $query->fetch_assoc()) {
            $id_transaksi_pembayaran = $data['id_transaksi_pembayaran'];
            $id_transaksi            = $data['id_transaksi'];
            $id_jual_beli            = $data['id_transaksi_jual_beli'];
            $kat_bayar               = htmlspecialchars($data['kategori_pembayaran'] ?? '-', ENT_QUOTES, 'UTF-8');
            $kat_trans               = htmlspecialchars($data['kategori_transaksi'] ?? '-', ENT_QUOTES, 'UTF-8');
            $tanggal                 = $data['tanggal'] ?? '';
            $jumlah                  = (int) ($data['jumlah'] ?? 0);
            
            // Petugas (Prioritaskan nama dari tabel akses, fallback ke creat_by_name)
            $petugas       = !empty($data['nama_akses']) ? $data['nama_akses'] : ($data['creat_by_name'] ?? '-');
            $petugas       = htmlspecialchars($petugas, ENT_QUOTES, 'UTF-8');

            // Format Nominal
            $NominalFormat = 'Rp ' . number_format($jumlah, 0, ',', '.');

            // Format Tanggal
            $TanggalFormat = '-';
            if (!empty($tanggal)) {
                $strtotime = strtotime($tanggal);
                if ($strtotime !== false) {
                    $TanggalFormat = date('d/m/Y H:i', $strtotime);
                }
            }

            // Tentukan Referensi (Ref)
            $ref_text = '-';
            if (!empty($id_transaksi)) {
                $ref_text = $id_transaksi;
            } elseif (!empty($id_jual_beli)) {
                $ref_text = $id_jual_beli;
            }

            // Routing id_transaksi
            if(!empty($id_transaksi)){
                $database_transaksi = "transaksi";
                $id_ref = $id_transaksi;
            }else{
                $database_transaksi = "transaksi_jual_beli";
                $id_ref = $id_jual_beli;
            }

            // HTML Row
            $html .= '
                <tr>
                    <td>
                        <small class="text-muted">' . $no . '</small>
                    </td>
                    <td>
                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalDetailPembayaran" data-id="' . $id_transaksi_pembayaran . '">
                            '.$id_transaksi_pembayaran.'
                        </a>
                    </td>
                    <td>'.$TanggalFormat.'</td>
                    <td>
                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalDetailTransaksi" data-id="' . $id_ref . '" data-database="' . $database_transaksi . '">
                            ' . $ref_text . '
                        </a>
                    </td>
                    <td>
                        <small class="text-muted">' . $kat_trans . '</small>
                    </td>
                    <td>
                        <small class="text-muted fw-bold">' . $NominalFormat . '</small>
                    </td>
                    <td>
                        <small class="text-muted">' . $petugas . '</small>
                    </td>
                    <td class="">
                        <a class="btn btn-sm btn-secondary btn-floating" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" title="Opsi">
                            <i class="bi bi-three-dots-vertical"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="dropdown-header">
                                <h6 class="mb-0">Opsi</h6>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalDetailPembayaran" data-id="' . $id_transaksi_pembayaran . '">
                                    <i class="bi bi-info-circle me-2"></i>Detail
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalEditPembayaran" data-id="' . $id_transaksi_pembayaran . '">
                                    <i class="bi bi-pencil me-2"></i>Ubah/Edit
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalHapusPembayaran" data-id="' . $id_transaksi_pembayaran . '">
                                    <i class="bi bi-trash me-2"></i>Hapus
                                </a>
                            </li>
                        </ul>
                    </td>
                </tr>
            ';
            $no++;
        }
    }

    // =====================================================
    // Tutup Statement
    // =====================================================
    $stmt->close();

    // =====================================================
    // Response JSON
    // =====================================================
    echo json_encode([
        "status"     => "success",
        "html"       => $html,
        "page"       => $page,
        "total_page" => $total_page,
        "total_data" => $total_data
    ], JSON_UNESCAPED_UNICODE);
    exit;
?>
