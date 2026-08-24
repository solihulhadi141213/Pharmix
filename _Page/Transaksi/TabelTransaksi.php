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
    $OrderBy    = $_POST['OrderBy'] ?? 'id_transaksi';
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
    // Batasi jumlah data per halaman
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
    //
    // Jangan langsung memasukkan $_POST ke SQL.
    // Gunakan whitelist.
    // =====================================================
    $allowedOrder = [
        'id_transaksi'       => 't.id_transaksi',
        'id_transaksi_jenis' => 't.id_transaksi_jenis',
        'nama_transaksi'     => 'tj.nama',
        'tanggal'            => 't.tanggal',
        'kategori'           => 'tj.kategori',
        'jumlah'             => 't.jumlah',
        'pembayaran'         => 't.pembayaran',
        'status'             => 't.status'
    ];
    if (!array_key_exists($OrderBy, $allowedOrder)) {
        $OrderBy = 'id_transaksi';
    }
    $OrderBySql = $allowedOrder[$OrderBy];

    // =====================================================
    // Validasi Keyword By
    // =====================================================
    $allowedKeywordBy = [
        'id_transaksi'       => 't.id_transaksi',
        'id_transaksi_jenis' => 't.id_transaksi_jenis',
        'nama_transaksi'     => 'tj.nama',
        'tanggal'            => 't.tanggal',
        'kategori'           => 'tj.kategori',
        'jumlah'             => 't.jumlah',
        'pembayaran'         => 't.pembayaran',
        'status'             => 't.status'
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
                    t.id_transaksi LIKE ?
                    OR t.tanggal LIKE ?
                    OR tj.nama LIKE ?
                    OR tj.kategori LIKE ?
                    OR t.status LIKE ?
                    OR t.keterangan LIKE ?
                )
            ";
            $bindTypes .= 'ssssss';
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
        FROM transaksi AS t
        INNER JOIN transaksi_jenis AS tj
            ON tj.id_transaksi_jenis = t.id_transaksi_jenis
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

    // =====================================================
    // Bind Parameter Count
    // =====================================================
    if (!empty($bindValues)) {
        $stmt_count->bind_param($bindTypes, ...$bindValues);
    }

    // =====================================================
    // Execute Count
    // =====================================================
    if (!$stmt_count->execute()) {
        $stmt_count->close();
        response_error("Gagal menghitung jumlah data.");
    }

    // =====================================================
    // Ambil Total
    // =====================================================
    $result_count = $stmt_count->get_result();
    $data_count   = $result_count->fetch_assoc();
    $total_data   = (int) ($data_count['total'] ?? 0);
    $stmt_count->close();

    // =====================================================
    // Total Page
    // =====================================================
    $total_page = ($total_data > 0) ? (int) ceil($total_data / $batas) : 1;

    // =====================================================
    // Validasi Page
    // =====================================================
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
            t.id_transaksi,
            t.id_transaksi_jenis,
            t.tanggal,
            t.jumlah,
            t.pembayaran,
            t.keterangan,
            t.status,
            tj.nama AS nama_transaksi,
            tj.kategori AS kategori
        $fromSql
        ORDER BY $OrderBySql $ShortBy
        LIMIT ?, ?
    ";
    $stmt = $Conn->prepare($sql);
    if (!$stmt) {
        response_error("Gagal mempersiapkan query data.", $page, $total_page, $total_data);
    }

    // =====================================================
    // Bind Parameter Data
    // =====================================================
    $bindTypesData      = $bindTypes . 'ii';
    $bindValuesData     = $bindValues;
    $bindValuesData[]   = $posisi;
    $bindValuesData[]   = $batas;
    $stmt->bind_param($bindTypesData, ...$bindValuesData);

    // =====================================================
    // Execute
    // =====================================================
    if (!$stmt->execute()) {
        $stmt->close();
        response_error("Terjadi kesalahan saat mengambil data.", $page, $total_page, $total_data);
    }

    // =====================================================
    // Ambil Result
    // =====================================================
    $query = $stmt->get_result();

    // =====================================================
    // Build HTML
    // =====================================================
    $html = '';
    $no   = $posisi + 1;

    // =====================================================
    // Tidak Ada Data
    // =====================================================
    if ($query->num_rows === 0) {
        $html .= '
            <tr>
                <td colspan="8" class="text-center text-danger py-4">
                    <small>
                        <i class="bi bi-info-circle"></i>
                        Tidak ada data yang ditampilkan.
                    </small>
                </td>
            </tr>
        ';
    } else {
        // =================================================
        // Loop Data
        // =================================================
        while ($data = $query->fetch_assoc()) {
            // =============================================
            // Data
            // =============================================
            $id_transaksi       = (int) $data['id_transaksi'];
            $id_transaksi_jenis = (int) $data['id_transaksi_jenis'];
            $nama_transaksi     = htmlspecialchars($data['nama_transaksi'] ?? '-', ENT_QUOTES, 'UTF-8');
            $kategori           = htmlspecialchars($data['kategori'] ?? '-', ENT_QUOTES, 'UTF-8');
            $tanggal            = $data['tanggal'] ?? '';
            $jumlah             = (int) ($data['jumlah'] ?? 0);
            $pembayaran         = (int) ($data['pembayaran'] ?? 0);
            $status             = $data['status'] ?? '';
            $keterangan         = htmlspecialchars($data['keterangan'] ?? '', ENT_QUOTES, 'UTF-8');

            // =============================================
            // Format Rupiah
            // =============================================
            $JumlahFormat     = 'Rp ' . number_format($jumlah, 0, ',', '.');
            $PembayaranFormat = 'Rp ' . number_format($pembayaran, 0, ',', '.');

            // =============================================
            // Format Tanggal
            // =============================================
            $TanggalFormat = '-';
            if (!empty($tanggal)) {
                $strtotime = strtotime($tanggal);
                if ($strtotime !== false) {
                    $TanggalFormat = date('d/m/Y H:i', $strtotime);
                }
            }

            // =============================================
            // Status Badge
            // =============================================
            switch ($status) {
                case 'Lunas':
                    $status_label = '<span class="badge bg-success">Lunas</span>';
                    break;
                case 'Utang':
                    $status_label = '<span class="badge bg-danger">Utang</span>';
                    break;
                case 'Piutang':
                    $status_label = '<span class="badge bg-warning text-dark">Piutang</span>';
                    break;
                default:
                    $status_label = '<span class="badge bg-secondary">' . htmlspecialchars($status, ENT_QUOTES, 'UTF-8') . '</span>';
                    break;
            }

            // =============================================
            // HTML
            // =============================================
            $html .= '
                <tr>
                    <!-- Nomor -->
                    <td class="text-center">
                        <small class="text-muted">' . $no . '</small>
                    </td>
                    <!-- Tanggal -->
                    <td>
                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalDetail" data-id="' . $id_transaksi . '" class="text-decoration-none">
                            <small>' . $TanggalFormat . '</small>
                        </a>
                    </td>
                    <!-- Nama Transaksi -->
                    <td>
                        <small class="text-muted">' . $nama_transaksi . '</small>
                    </td>
                    <!-- Kategori -->
                    <td>
                        <small class="text-muted">' . $kategori . '</small>
                    </td>
                    <!-- Jumlah -->
                    <td>
                        <small class="text-muted">' . $JumlahFormat . '</small>
                    </td>
                    <!-- Pembayaran -->
                    <td>
                        <small class="text-muted">' . $PembayaranFormat . '</small>
                    </td>
                    <!-- Status -->
                    <td class="text-center">
                        ' . $status_label . '
                    </td>
                    <!-- Opsi -->
                    <td class="text-center">
                        <a class="btn btn-sm btn-secondary btn-floating" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" title="Opsi">
                            <i class="bi bi-three-dots-vertical"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="dropdown-header">
                                <h6 class="mb-0">Opsi</h6>
                            </li>
                            <!-- Detail -->
                            <li>
                                <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalDetail" data-id="' . $id_transaksi . '">
                                    <i class="bi bi-info-circle me-2"></i>Detail
                                </a>
                            </li>
                            <!-- Edit -->
                            <li>
                                <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalEdit" data-id="' . $id_transaksi . '">
                                    <i class="bi bi-pencil me-2"></i>Ubah/Edit
                                </a>
                            </li>
                            <!-- Hapus -->
                            <li>
                                <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalHapus" data-id="' . $id_transaksi . '">
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