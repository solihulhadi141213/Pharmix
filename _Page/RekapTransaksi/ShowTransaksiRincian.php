<?php
    // =========================================================
    // KONEKSI & HEADER
    // =========================================================
    include "../../_Config/Connection.php";

    header('Content-Type: application/json; charset=utf-8');

    // =========================================================
    // AMBIL & VALIDASI PARAMETER
    // =========================================================
    $tahun              = isset($_POST['tahun']) ? trim($_POST['tahun']) : '';
    $bulan              = isset($_POST['bulan']) ? trim($_POST['bulan']) : '';
    $id_transaksi_jenis = isset($_POST['id_transaksi_jenis']) ? trim($_POST['id_transaksi_jenis']) : '';
    $page               = isset($_POST['page']) ? trim($_POST['page']) : '1';

    //------ Validasi Tahun
    if ($tahun === '' || !preg_match('/^\d{4}$/', $tahun)) {

        echo json_encode([
            'status'  => false,
            'message' => 'Tahun tidak valid.'
        ], JSON_UNESCAPED_UNICODE);

        exit;

    }

    $tahun = (int) $tahun;

    //------ Validasi Bulan
    if ($bulan === '' || !ctype_digit($bulan)) {

        echo json_encode([
            'status'  => false,
            'message' => 'Bulan tidak valid.'
        ], JSON_UNESCAPED_UNICODE);

        exit;

    }

    $bulan = (int) $bulan;

    if ($bulan < 1 || $bulan > 12) {

        echo json_encode([
            'status'  => false,
            'message' => 'Bulan tidak valid.'
        ], JSON_UNESCAPED_UNICODE);

        exit;

    }

    //------ Validasi ID Jenis Transaksi
    if ($id_transaksi_jenis !== '') {

        if (!ctype_digit($id_transaksi_jenis)) {

            echo json_encode([
                'status'  => false,
                'message' => 'ID jenis transaksi tidak valid.'
            ], JSON_UNESCAPED_UNICODE);

            exit;

        }

        $id_transaksi_jenis = (int) $id_transaksi_jenis;

    } else {

        $id_transaksi_jenis = null;

    }

    //------ Validasi Page
    $page = ctype_digit($page)
        ? (int) $page
        : 1;

    if ($page < 1) {
        $page = 1;
    }

    //------ Jumlah Data Per Halaman
    $limit  = 20;
    $offset = ($page - 1) * $limit;

    // =========================================================
    // QUERY TOTAL DATA
    // =========================================================
    $sql_total = "
        SELECT
            COUNT(*) AS total
        FROM transaksi_rincian tr
        INNER JOIN transaksi t
            ON t.id_transaksi = tr.id_transaksi
        WHERE YEAR(t.tanggal) = ?
            AND MONTH(t.tanggal) = ?
    ";

    $params_total = [$tahun, $bulan];
    $types_total  = 'ii';

    //------ Filter Jenis Transaksi
    if ($id_transaksi_jenis !== null) {

        $sql_total .= "
            AND t.id_transaksi_jenis = ?
        ";

        $params_total[] = $id_transaksi_jenis;
        $types_total   .= 'i';

    }

    // =========================================================
    // EKSEKUSI TOTAL
    // =========================================================
    $stmt_total = $Conn->prepare($sql_total);

    if (!$stmt_total) {

        echo json_encode([
            'status'  => false,
            'message' => 'Query total gagal dipersiapkan.',
            'error'   => $Conn->error
        ], JSON_UNESCAPED_UNICODE);

        exit;

    }

    $bind_total = [$types_total];

    foreach ($params_total as $key => $value) {
        $bind_total[] = &$params_total[$key];
    }

    call_user_func_array(
        [$stmt_total, 'bind_param'],
        $bind_total
    );

    if (!$stmt_total->execute()) {

        echo json_encode([
            'status'  => false,
            'message' => 'Gagal menghitung data transaksi.',
            'error'   => $stmt_total->error
        ], JSON_UNESCAPED_UNICODE);

        $stmt_total->close();

        exit;

    }

    $result_total = $stmt_total->get_result();
    $data_total   = $result_total->fetch_assoc();

    $total_data = (int) ($data_total['total'] ?? 0);

    $stmt_total->close();

    // =========================================================
    // PAGINATION
    // =========================================================
    $total_page = $total_data > 0
        ? (int) ceil($total_data / $limit)
        : 1;

    if ($page > $total_page) {
        $page = $total_page;
        $offset = ($page - 1) * $limit;
    }

    // =========================================================
    // QUERY DATA RINCIAN
    // =========================================================
    $sql = "
        SELECT
            tr.id_transaksi_rincian,
            t.id_transaksi,
            t.tanggal,
            tj.id_transaksi_jenis,
            tj.nama AS nama_transaksi,
            tr.rincian_transaksi,
            tr.harga,
            tr.qty,
            tr.satuan,
            tr.jumlah
        FROM transaksi_rincian tr

        INNER JOIN transaksi t
            ON t.id_transaksi = tr.id_transaksi

        INNER JOIN transaksi_jenis tj
            ON tj.id_transaksi_jenis = t.id_transaksi_jenis

        WHERE YEAR(t.tanggal) = ?
            AND MONTH(t.tanggal) = ?
    ";

    $params = [$tahun, $bulan];
    $types  = 'ii';

    //------ Filter Jenis Transaksi
    if ($id_transaksi_jenis !== null) {

        $sql .= "
            AND t.id_transaksi_jenis = ?
        ";

        $params[] = $id_transaksi_jenis;
        $types   .= 'i';

    }

    //------ Urutan Data
    $sql .= "
        ORDER BY
            t.tanggal ASC,
            t.id_transaksi ASC,
            tr.id_transaksi_rincian ASC
        LIMIT ? OFFSET ?
    ";

    $params[] = $limit;
    $params[] = $offset;
    $types   .= 'ii';

    // =========================================================
    // EKSEKUSI QUERY
    // =========================================================
    $stmt = $Conn->prepare($sql);

    if (!$stmt) {

        echo json_encode([
            'status'  => false,
            'message' => 'Query data gagal dipersiapkan.',
            'error'   => $Conn->error
        ], JSON_UNESCAPED_UNICODE);

        exit;

    }

    $bindParams = [$types];

    foreach ($params as $key => $value) {
        $bindParams[] = &$params[$key];
    }

    call_user_func_array(
        [$stmt, 'bind_param'],
        $bindParams
    );

    if (!$stmt->execute()) {

        echo json_encode([
            'status'  => false,
            'message' => 'Gagal mengambil data rincian transaksi.',
            'error'   => $stmt->error
        ], JSON_UNESCAPED_UNICODE);

        $stmt->close();

        exit;

    }

    $result = $stmt->get_result();

    $data = [];

    while ($row = $result->fetch_assoc()) {

        $data[] = [
            'id_transaksi_rincian' => (int) $row['id_transaksi_rincian'],
            'id_transaksi'         => (int) $row['id_transaksi'],
            'tanggal'              => date(
                'd-m-Y H:i',
                strtotime($row['tanggal'])
            ),
            'id_transaksi_jenis'   => (int) $row['id_transaksi_jenis'],
            'nama_transaksi'       => $row['nama_transaksi'],
            'rincian_transaksi'    => $row['rincian_transaksi'],
            'harga'                => (int) $row['harga'],
            'qty'                  => (int) $row['qty'],
            'satuan'               => $row['satuan'],
            'jumlah'               => (int) $row['jumlah']
        ];

    }

    $stmt->close();

    // =========================================================
    // RESPONSE
    // =========================================================
    echo json_encode([
        'status' => true,

        'filter' => [
            'tahun'              => $tahun,
            'bulan'              => $bulan,
            'id_transaksi_jenis' => $id_transaksi_jenis
        ],

        'pagination' => [
            'page'       => $page,
            'limit'      => $limit,
            'total_data' => $total_data,
            'total_page' => $total_page
        ],

        'data' => $data

    ], JSON_UNESCAPED_UNICODE);