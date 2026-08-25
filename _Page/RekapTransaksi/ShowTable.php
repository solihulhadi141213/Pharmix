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
$id_transaksi_jenis = isset($_POST['id_transaksi_jenis']) ? trim($_POST['id_transaksi_jenis']) : '';

//------ Validasi Tahun
if ($tahun !== '') {
    if (!preg_match('/^\d{4}$/', $tahun)) {
        echo json_encode([
            'status'  => false,
            'message' => 'Format tahun tidak valid.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $tahun = (int) $tahun;
} else {
    $tahun = (int) date('Y');
}

//------ Validasi ID Transaksi Jenis
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

// =========================================================
// ARRAY BULAN
// =========================================================
$nama_bulan = [
    1  => 'Januari',
    2  => 'Februari',
    3  => 'Maret',
    4  => 'April',
    5  => 'Mei',
    6  => 'Juni',
    7  => 'Juli',
    8  => 'Agustus',
    9  => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];

// =========================================================
// PENYUSUNAN QUERY
// =========================================================
$sql = "
    SELECT
        MONTH(tanggal) AS bulan,
        COALESCE(SUM(jumlah), 0) AS subtotal,
        COALESCE(SUM(pembayaran), 0) AS pembayaran,
        COALESCE(SUM(CASE WHEN status = 'Lunas' THEN jumlah ELSE 0 END), 0) AS total_lunas,
        COALESCE(SUM(CASE WHEN status = 'Utang' THEN jumlah ELSE 0 END), 0) AS total_utang,
        COALESCE(SUM(CASE WHEN status = 'Piutang' THEN jumlah ELSE 0 END), 0) AS total_piutang
    FROM transaksi
    WHERE YEAR(tanggal) = ?
";

$params = [$tahun];
$types  = 'i';

//------ Filter Jenis Transaksi
if ($id_transaksi_jenis !== null) {
    $sql .= " AND id_transaksi_jenis = ?";
    $params[] = $id_transaksi_jenis;
    $types   .= 'i';
}

// =========================================================
// GROUP DATA
// =========================================================
$sql .= "
    GROUP BY
        MONTH(tanggal)
    ORDER BY
        MONTH(tanggal) ASC
";

// =========================================================
// EKSEKUSI PREPARED STATEMENT
// =========================================================
$stmt = $Conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        'status'  => false,
        'message' => 'Query gagal dipersiapkan.',
        'error'   => $Conn->error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!empty($params)) {
    $bindParams = [$types];

    foreach ($params as $key => $value) {
        $bindParams[] = &$params[$key];
    }

    call_user_func_array(
        [$stmt, 'bind_param'],
        $bindParams
    );
}

if (!$stmt->execute()) {
    echo json_encode([
        'status'  => false,
        'message' => 'Gagal mengambil data transaksi.',
        'error'   => $stmt->error
    ], JSON_UNESCAPED_UNICODE);

    $stmt->close();
    exit;
}

$result = $stmt->get_result();

// =========================================================
// INDEXING DATA BERDASARKAN BULAN DARI DATABASE
// =========================================================
$data_db = [];

while ($row = $result->fetch_assoc()) {

    $bulan_index = (int) $row['bulan'];

    $data_db[$bulan_index] = [
        'subtotal'   => (int) $row['subtotal'],
        'pembayaran' => (int) $row['pembayaran'],
        'lunas'      => (int) $row['total_lunas'],
        'utang'      => (int) $row['total_utang'],
        'piutang'    => (int) $row['total_piutang']
    ];

}

$stmt->close();

// =========================================================
// GENERATE 12 BULAN (JANUARI S/D DESEMBER)
// =========================================================
$data = [];

$total_subtotal   = 0;
$total_pembayaran = 0;
$total_lunas      = 0;
$total_utang      = 0;
$total_piutang    = 0;

for ($i = 1; $i <= 12; $i++) {

    $subtotal   = $data_db[$i]['subtotal'] ?? 0;
    $pembayaran = $data_db[$i]['pembayaran'] ?? 0;
    $lunas      = $data_db[$i]['lunas'] ?? 0;
    $utang      = $data_db[$i]['utang'] ?? 0;
    $piutang    = $data_db[$i]['piutang'] ?? 0;

    $data[] = [
        'tahun'      => $tahun,
        'bulan'      => $nama_bulan[$i],
        'bulan_index' => $i,
        'subtotal'   => $subtotal,
        'pembayaran' => $pembayaran,
        'lunas'      => $lunas,
        'utang'      => $utang,
        'piutang'    => $piutang
    ];

    //------ Hitung Total
    $total_subtotal   += $subtotal;
    $total_pembayaran += $pembayaran;
    $total_lunas      += $lunas;
    $total_utang      += $utang;
    $total_piutang    += $piutang;

}

// =========================================================
// TAMBAHKAN BARIS TOTAL
// =========================================================
$data[] = [
    'tahun'      => $tahun,
    'bulan'      => 'TOTAL',
    'subtotal'   => $total_subtotal,
    'pembayaran' => $total_pembayaran,
    'lunas'      => $total_lunas,
    'utang'      => $total_utang,
    'piutang'    => $total_piutang
];

// =========================================================
// OUTPUT RESPONSE JSON
// =========================================================
echo json_encode([
    'status' => true,
    'tahun'  => $tahun,
    'data'   => $data
], JSON_UNESCAPED_UNICODE);