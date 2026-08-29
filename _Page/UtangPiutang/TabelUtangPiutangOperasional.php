<?php
    // ======================================================
    // KONEKSI, FUNGSI DAN SESSION
    // ======================================================
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');

    // ======================================================
    // DEFAULT RESPONSE
    // ======================================================
    $response = [
        "status"     => "error",
        "html"       => "",
        "page"       => 1,
        "total_page" => 0
    ];

    // ======================================================
    // VALIDASI SESSION
    // ======================================================
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

    // ======================================================
    // FUNGSI FORMAT RUPIAH
    // ======================================================
    function FormatRupiahOperasional($nominal) {
        return 'Rp ' . number_format((float)$nominal, 0, ',', '.');
    }

    // ======================================================
    // PARAMETER FILTER
    // ======================================================
    $batas = isset($_POST['batas']) ? (int)$_POST['batas'] : 10;
    $allowedLimit = [5, 10, 25, 50, 100];

    if (!in_array($batas, $allowedLimit)) {
        $batas = 10;
    }

    // ======================================================
    // PAGE
    // ======================================================
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;

    if ($page < 1) {
        $page = 1;
    }

    // ======================================================
    // ORDER BY
    // ======================================================
    $OrderBy = isset($_POST['OrderBy']) ? $_POST['OrderBy'] : 'tanggal';
    $ShortBy = isset($_POST['ShortBy']) ? strtoupper($_POST['ShortBy']) : 'DESC';

    $allowedOrder = [
        'tanggal' => 't.tanggal',
        'nama'    => 'tj.nama'
    ];

    if (!array_key_exists($OrderBy, $allowedOrder)) {
        $OrderBy = 'tanggal';
    }

    $orderColumn = $allowedOrder[$OrderBy];

    if (!in_array($ShortBy, ['ASC', 'DESC'])) {
        $ShortBy = 'DESC';
    }

    // ======================================================
    // FILTER KEYWORD
    // ======================================================
    $KeywordBy = isset($_POST['keyword_by']) ? trim($_POST['keyword_by']) : '';
    $Keyword = isset($_POST['keyword']) ? trim($_POST['keyword']) : '';

    // ======================================================
    // BASE WHERE (Tampilkan jika pembayaran cash belum melunasi jumlah tagihan)
    // ======================================================
    $where = [
        "t.pembayaran < t.jumlah"
    ];

    $params = [];
    $types = "";

    // ======================================================
    // FILTER TANGGAL
    // ======================================================
    if ($KeywordBy == "tanggal" && !empty($Keyword)) {
        $where[] = "DATE(t.tanggal) = ?";
        $params[] = $Keyword;
        $types .= "s";
    }

    // ======================================================
    // FILTER NAMA TRANSAKSI
    // ======================================================
    if ($KeywordBy == "nama" && !empty($Keyword)) {
        $where[] = "t.id_transaksi_jenis = ?";
        $params[] = (int)$Keyword;
        $types .= "i";
    }

    // ======================================================
    // KONDISI WHERE
    // ======================================================
    $whereSQL = "";

    if (!empty($where)) {
        $whereSQL = "WHERE " . implode(" AND ", $where);
    }

    // ======================================================
    // QUERY COUNT DATA
    // ======================================================
    $sqlCount = "
        SELECT COUNT(DISTINCT t.id_transaksi) AS total_data
        FROM transaksi t
        INNER JOIN transaksi_jenis tj
            ON t.id_transaksi_jenis = tj.id_transaksi_jenis
        $whereSQL
    ";

    $stmtCount = mysqli_prepare($Conn, $sqlCount);

    if ($stmtCount) {
        if (!empty($types)) {
            mysqli_stmt_bind_param($stmtCount, $types, ...$params);
        }

        mysqli_stmt_execute($stmtCount);
        $resultCount = mysqli_stmt_get_result($stmtCount);
        $dataCount = mysqli_fetch_assoc($resultCount);
        $total_data = (int)$dataCount['total_data'];

        mysqli_stmt_close($stmtCount);
    } else {
        $total_data = 0;
    }

    // ======================================================
    // TOTAL PAGE
    // ======================================================
    $total_page = $total_data > 0 ? (int)ceil($total_data / $batas) : 1;

    if ($page > $total_page) {
        $page = $total_page;
    }

    // ======================================================
    // OFFSET
    // ======================================================
    $offset = ($page - 1) * $batas;

    // ======================================================
    // QUERY DATA
    // ======================================================
    $sql = "
        SELECT
            t.id_transaksi,
            t.id_transaksi_jenis,
            t.tanggal,
            t.jumlah,
            t.pembayaran AS pembayaran_cash,
            t.keterangan,
            t.status,
            tj.nama,
            tj.kategori,
            COALESCE(
                (
                    SELECT SUM(tp.jumlah)
                    FROM transaksi_pembayaran tp
                    WHERE tp.id_transaksi = t.id_transaksi
                ),
                0
            ) AS total_pembayaran,
            (
                SELECT MIN(tt.tanggal_tempo)
                FROM transaksi_tempo tt
                WHERE tt.id_transaksi = t.id_transaksi
            ) AS tanggal_tempo
        FROM transaksi t
        INNER JOIN transaksi_jenis tj
            ON t.id_transaksi_jenis = tj.id_transaksi_jenis
        $whereSQL
        ORDER BY $orderColumn $ShortBy
        LIMIT ?, ?
    ";

    // ======================================================
    // PREPARE QUERY
    // ======================================================
    $stmt = mysqli_prepare($Conn, $sql);

    if (!$stmt) {
        $response["html"] = '
            <tr>
                <td colspan="10" class="text-center text-danger">
                    Terjadi kesalahan pada query database.
                </td>
            </tr>
        ';

        echo json_encode($response);
        exit;
    }

    // ======================================================
    // BIND PARAMETER
    // ======================================================
    $typesData = $types . "ii";
    $paramsData = $params;
    $paramsData[] = $offset;
    $paramsData[] = $batas;

    mysqli_stmt_bind_param($stmt, $typesData, ...$paramsData);

    // ======================================================
    // EXECUTE QUERY
    // ======================================================
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // ======================================================
    // GENERATE HTML
    // ======================================================
    $html = "";
    $no = $offset + 1;

    while ($data = mysqli_fetch_assoc($result)) {
        $id_transaksi = $data['id_transaksi'];
        $tanggal = !empty($data['tanggal']) ? date('d/m/Y H:i', strtotime($data['tanggal'])) : '-';
        $nama = $data['nama'] ?? '-';
        $kategori = $data['kategori'] ?? '-';
        $jumlah = (int)($data['jumlah'] ?? 0);
        $pembayaran_cash = (int)($data['pembayaran_cash'] ?? 0);
        $total_pembayaran = (int)($data['total_pembayaran'] ?? 0);
        $status = $data['status'] ?? '-';
        $tanggal_tempo = $data['tanggal_tempo'];

        // ==================================================
        // HITUNG SISA UTANG / PIUTANG
        // ==================================================
        $sisa = $jumlah - $pembayaran_cash - $total_pembayaran;

        if ($sisa < 0) {
            $sisa = 0;
        }

        // ==================================================
        // STATUS
        // ==================================================
        if ($status == "Lunas") {
            $statusBadge = '<span class="badge bg-success">Lunas</span>';
        } elseif ($status == "Utang") {
            $statusBadge = '<span class="badge bg-danger">Utang</span>';
        } elseif ($status == "Piutang") {
            $statusBadge = '<span class="badge bg-warning text-dark">Piutang</span>';
        } else {
            $statusBadge = '<span class="badge bg-secondary">-</span>';
        }

        // ==================================================
        // FORMAT TEMPO
        // ==================================================
        if (!empty($tanggal_tempo)) {
            $tempo = date('d/m/Y', strtotime($tanggal_tempo));
            $tombol_tempo = '
                <a href="Javascript:(0);" class="text-success" data-bs-toggle="dropdown" aria-expanded="false">
                    ' . $tempo . ' <i class="bi bi-three-dots-vertical"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow" style="">
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalTempo" data-id="'.$id_transaksi.'" data-kategori="operasional">
                            <i class="bi bi-pencil"></i> Ubah Tanggal Tempo
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#ModalHapusTempo" data-id="'.$id_transaksi.'" data-kategori="operasional">
                            <i class="bi bi-trash"></i> Hapus Tanggal Tempo
                        </a>
                    </li>
                </ul>
            ';
        } else {
            $tombol_tempo = '
                <small>
                    <a href="Javascript:(0);" class="text-danger" data-bs-toggle="modal" data-bs-target="#ModalTempo" data-id="' . $id_transaksi . '" data-kategori="operasional" title="Tempo Pembayaran">
                        <i class="bi bi-plus-lg"></i> Add
                    </a>
                </small>
            ';
        }

        // ==================================================
        // KATEGORI
        // ==================================================
        if ($kategori == "Pengeluaran") {
            $kategoriBadge = '<span class="badge bg-danger">Pengeluaran</span>';
        } else {
            $kategoriBadge = '<span class="badge bg-success">Pemasukan</span>';
        }

        // ==================================================
        // TAMPILKAN BARIS
        // ==================================================
        $sisaColorClass = ($status == "Utang") ? "text-danger" : (($status == "Piutang") ? "text-warning" : "text-success");

        $html .= '
            <tr>
                <td class="text-center">' . $no . '</td>
                <td>
                    <a href="javascript:void(0);" class="text" data-bs-toggle="modal" data-bs-target="#ModalDetailTransaksiOperasional" data-id="' . $id_transaksi . '">
                        ' . $tanggal . '
                    </a>
                </td>
                <td><small>' . $nama . '</small></td>
                <td>' . FormatRupiahOperasional($jumlah) . '</td>
                <td>' . FormatRupiahOperasional($pembayaran_cash) . '</td>
                <td>
                    <a href="Javascript:(0);" class="text-primary" data-bs-toggle="modal" data-bs-target="#ModalRiwayatPembayaran" data-id="' . $id_transaksi . '" data-kategori="operasional" title="Bayar Piutang / Utang">
                        <i class="bi bi-pencil"></i> ' . FormatRupiahOperasional($total_pembayaran) . '
                    </a>
                </td>
                <td>' . FormatRupiahOperasional($sisa) . '</td>
                <td>' . $statusBadge . '</td>
                <td>' . $tombol_tempo . '</td>
            </tr>
        ';

        $no++;
    }

    // ======================================================
    // APABILA DATA TIDAK ADA
    // ======================================================
    if (empty($html)) {
        $html = '
            <tr>
                <td colspan="10" class="text-center">
                    <small class="text-muted">Tidak Ada Data</small>
                </td>
            </tr>
        ';
    }

    // ======================================================
    // CLOSE STATEMENT
    // ======================================================
    mysqli_stmt_close($stmt);

    // ======================================================
    // RESPONSE
    // ======================================================
    $response = [
        "status"     => "success",
        "html"       => $html,
        "page"       => $page,
        "total_page" => $total_page
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
?>