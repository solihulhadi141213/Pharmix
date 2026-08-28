<?php
    // ==========================================
    // KONEKSI, FUNGSI DAN SESSION
    // ==========================================
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');

    // ==========================================
    // DEFAULT RESPONSE
    // ==========================================
    $response = [
        "status"     => "error",
        "html"       => "",
        "page"       => 1,
        "total_page" => 0
    ];

    // ==========================================
    // VALIDASI SESSION
    // ==========================================
    if (empty($SessionIdAkses)) {
        $response["html"] = '
            <tr>
                <td colspan="10" class="text-center text-danger">
                    Sesi Akses Sudah Berakhir! Silahkan Login Ulang
                </td>
            </tr>
        ';
        echo json_encode($response);
        exit;
    }

    // ==========================================
    // FUNCTION BIND PARAMETER DINAMIS
    // ==========================================
    if (!function_exists('bind_stmt_params')) {
        function bind_stmt_params($stmt, $types, &$params) {
            $bind_names = [$types];
            foreach ($params as $key => &$value) {
                $bind_names[] = &$value;
            }
            return call_user_func_array(
                'mysqli_stmt_bind_param',
                array_merge([$stmt], $bind_names)
            );
        }
    }

    // ==========================================
    // VALIDASI INPUT PARAMETER
    // ==========================================
    $allowed_batas = [5, 10, 25, 50, 100];
    $batas = isset($_POST['batas']) ? (int) $_POST['batas'] : 10;
    if (!in_array($batas, $allowed_batas, true)) {
        $batas = 10;
    }

    $page = isset($_POST['page']) ? (int) $_POST['page'] : 1;
    if ($page < 1) {
        $page = 1;
    }

    $ShortBy = isset($_POST['ShortBy']) ? strtoupper(trim($_POST['ShortBy'])) : 'DESC';
    if (!in_array($ShortBy, ['ASC', 'DESC'], true)) {
        $ShortBy = 'DESC';
    }

    $order_mapping = [
        "id_transaksi_jual_beli" => "tjb.id_transaksi_jual_beli",
        "tanggal"                => "tjb.tanggal",
        "kategori"               => "tjb.kategori",
        "total"                  => "tjb.total",
        "cash"                   => "tjb.cash",
        "status"                 => "tjb.status",
        "nama"                   => "a.nama"
    ];

    $OrderByInput = isset($_POST['OrderBy']) ? trim($_POST['OrderBy']) : 'tanggal';
    if (!array_key_exists($OrderByInput, $order_mapping)) {
        $OrderByInput = 'tanggal';
    }
    $OrderBy = $order_mapping[$OrderByInput];

    $allowed_keyword_by = ["tanggal", "kategori", "nama"];
    $keyword_by = isset($_POST['keyword_by']) ? trim($_POST['keyword_by']) : '';
    if (!empty($keyword_by) && !in_array($keyword_by, $allowed_keyword_by, true)) {
        $keyword_by = '';
    }

    $keyword = isset($_POST['keyword']) ? trim($_POST['keyword']) : '';
    $keyword_like = '%' . $keyword . '%';

    // ==========================================
    // BASE FROM & WHERE CLAUSE
    // ==========================================
    $base_from = "
        FROM transaksi_jual_beli tjb
        LEFT JOIN (
            SELECT id_transaksi_jual_beli, SUM(COALESCE(jumlah, 0)) AS total_pembayaran
            FROM transaksi_pembayaran
            WHERE id_transaksi_jual_beli IS NOT NULL
            GROUP BY id_transaksi_jual_beli
        ) tp ON tp.id_transaksi_jual_beli = tjb.id_transaksi_jual_beli
        LEFT JOIN (
            SELECT id_transaksi_jual_beli, MAX(tanggal_tempo) AS tanggal_tempo
            FROM transaksi_tempo
            WHERE id_transaksi_jual_beli IS NOT NULL
            GROUP BY id_transaksi_jual_beli
        ) tt ON tt.id_transaksi_jual_beli = tjb.id_transaksi_jual_beli
        LEFT JOIN anggota a ON tjb.id_anggota = a.id_anggota
    ";

    $where_clause = "WHERE tjb.status = 'Kredit'";
    $types = "";
    $params = [];

    if (!empty($keyword)) {
        if (empty($keyword_by)) {
            $where_clause .= " AND (tjb.tanggal LIKE ? OR tjb.kategori LIKE ? OR a.nama LIKE ?)";
            $types .= "sss";
            $params[] = $keyword_like;
            $params[] = $keyword_like;
            $params[] = $keyword_like;
        } else {
            switch ($keyword_by) {
                case "nama":
                    $where_clause .= " AND a.nama LIKE ?";
                    break;
                case "tanggal":
                    $where_clause .= " AND tjb.tanggal LIKE ?";
                    break;
                case "kategori":
                    $where_clause .= " AND tjb.kategori LIKE ?";
                    break;
            }
            $types .= "s";
            $params[] = $keyword_like;
        }
    }

    // ==========================================
    // HITUNG JUMLAH DATA & HALAMAN
    // ==========================================
    $sql_count = "SELECT COUNT(tjb.id_transaksi_jual_beli) AS jml_data " . $base_from . " " . $where_clause;
    $stmt_jml = mysqli_prepare($Conn, $sql_count);

    if (!$stmt_jml) {
        $response["html"] = '<tr><td colspan="10" class="text-center text-danger">Terjadi kesalahan pada query database.</td></tr>';
        echo json_encode($response);
        exit;
    }

    if (!empty($types)) {
        bind_stmt_params($stmt_jml, $types, $params);
    }

    mysqli_stmt_execute($stmt_jml);
    $result_jml = mysqli_stmt_get_result($stmt_jml);
    $row_jml = mysqli_fetch_assoc($result_jml);
    $jml_data = isset($row_jml['jml_data']) ? (int) $row_jml['jml_data'] : 0;
    mysqli_stmt_close($stmt_jml);

    $total_page = ($jml_data > 0) ? (int) ceil($jml_data / $batas) : 0;

    if ($jml_data < 1) {
        $response = [
            "status"     => "success",
            "html"       => '<tr><td colspan="10" class="text-center text-muted">Tidak Ada Data Yang Ditampilkan.</td></tr>',
            "page"       => 1,
            "total_page" => 0
        ];
        echo json_encode($response);
        exit;
    }

    if ($page > $total_page) {
        $page = $total_page;
    }

    $posisi = ($page - 1) * $batas;

    // ==========================================
    // QUERY DATA UTAMA
    // ==========================================
    $sql = "
        SELECT 
            tjb.*, 
            a.nama AS nama_anggota, 
            COALESCE(tp.total_pembayaran, 0) AS total_pembayaran, 
            tt.tanggal_tempo
        " . $base_from . "
        " . $where_clause . "
        ORDER BY " . $OrderBy . " " . $ShortBy . "
        LIMIT ?, ?
    ";

    $query = mysqli_prepare($Conn, $sql);
    if (!$query) {
        $response["html"] = '<tr><td colspan="10" class="text-center text-danger">Gagal mempersiapkan query data.</td></tr>';
        echo json_encode($response);
        exit;
    }

    $bind_params = $params;
    $bind_params[] = (int) $posisi;
    $bind_params[] = (int) $batas;
    $bind_types = $types . "ii";

    bind_stmt_params($query, $bind_types, $bind_params);
    mysqli_stmt_execute($query);
    $result = mysqli_stmt_get_result($query);

    // ==========================================
    // MEMBUAT HTML TABLE
    // ==========================================
    $html = "";
    $no   = $posisi + 1;

    while ($data = mysqli_fetch_assoc($result)) {
        $id_transaksi_jual_beli = htmlspecialchars($data['id_transaksi_jual_beli'], ENT_QUOTES, 'UTF-8');
        $kategori = $data['kategori'];
        $tanggal = $data['tanggal'];
        $total = (float) ($data['total'] ?? 0);
        $cash = (float) ($data['cash'] ?? 0);
        $total_pembayaran = (float) ($data['total_pembayaran'] ?? 0);

        // Hitung Sisa Pembayaran
        $sisa_pembayaran = $total - $cash - $total_pembayaran;
        if ($sisa_pembayaran < 0) {
            $sisa_pembayaran = 0;
        }

        // Format Rupiah
        $total_rp = "Rp " . number_format($total, 0, ',', '.');
        $cash_rp = "Rp " . number_format($cash, 0, ',', '.');
        $total_pembayaran_rp = "Rp " . number_format($total_pembayaran, 0, ',', '.');
        $sisa_pembayaran_rp = "Rp " . number_format($sisa_pembayaran, 0, ',', '.');

        // Format Tanggal
        $TanggalTransaksi = date('d/m/Y H:i', strtotime($tanggal));
        $TanggalTempo = (!empty($data['tanggal_tempo']) && $data['tanggal_tempo'] !== '0000-00-00 00:00:00') 
            ? date('d/m/Y', strtotime($data['tanggal_tempo'])) 
            : '-';

        // Label Kategori dan Status
        switch ($kategori) {
            case "Penjualan":
                $label_kategori = '<small class="text-primary">Penjualan</small>';
                $label_status = '<span class="badge bg-success">Piutang</span>';
                break;
            case "Retur Penjualan":
                $label_kategori = '<small class="text-info">Ret. Penjualan</small>';
                $label_status = '<span class="badge bg-danger">Utang</span>';
                break;
            case "Pembelian":
                $label_kategori = '<small class="text-warning">Pembelian</small>';
                $label_status = '<span class="badge bg-danger">Utang</span>';
                break;
            case "Retur Pembelian":
                $label_kategori = '<small class="text-danger">Ret. Pembelian</small>';
                $label_status = '<span class="badge bg-success">Piutang</span>';
                break;
            default:
                $label_kategori = '<small class="text-muted">None</small>';
                $label_status = '<span class="badge bg-secondary">None</span>';
                break;
        }

        // Susun Baris HTML
        $html .= '
            <tr>
                <td><small class="text-muted">' . $no . '</small></td>
                <td>
                    <a href="javascript:void(0);" class="text" data-bs-toggle="modal" data-bs-target="#ModalDetailTransaksiJualBeli" data-id="' . $id_transaksi_jual_beli . '">
                        ' . $TanggalTransaksi . '
                    </a>
                </td>
                <td>' . $kategori . '</td>
                <td><small>' . $total_rp . '</small></td>
                <td><small>' . $cash_rp . '</small></td>
                <td><small>' . $total_pembayaran_rp . '</small></td>
                <td><small>' . $sisa_pembayaran_rp . '</small></td>
                <td>' . $label_status . '</td>
                <td><small class="text-muted">' . $TanggalTempo . '</small></td>
                <td>
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#ModalRiwayatPembayaran" data-id="' . $id_transaksi_jual_beli . '" data-kategori="jual_beli" title="Bayar Piutang / Utang">
                        <i class="bi bi-clock-history"></i> Bayar
                    </button>
                </td>
            </tr>
        ';
        $no++;
    }

    mysqli_stmt_close($query);

    // ==========================================
    // RESPONSE JSON AKHIR
    // ==========================================
    $response = [
        "status"     => "success",
        "html"       => $html,
        "page"       => $page,
        "total_page" => $total_page
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>