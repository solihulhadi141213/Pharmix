<?php

include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";

header('Content-Type: application/json; charset=utf-8');


// ============================================================
// Validasi Session
// ============================================================
if (empty($SessionIdAkses)) {

    echo json_encode([
        'status'  => 'error',
        'message' => 'Sesi akses sudah berakhir.',
        'results' => []
    ]);

    exit;
}


// ============================================================
// Parameter Select2
// ============================================================
$search = trim($_GET['search'] ?? '');
$page   = (int) ($_GET['page'] ?? 1);

if ($page < 1) {
    $page = 1;
}

$limit  = 10;
$offset = ($page - 1) * $limit;


// ============================================================
// Query
// ============================================================
$where = '';
$params = [];
$types = '';

if ($search !== '') {

    $where = "
        WHERE
            nama LIKE ?
            OR kategori LIKE ?
    ";

    $keyword = '%' . $search . '%';

    $params[] = $keyword;
    $params[] = $keyword;

    $types .= 'ss';
}


// ============================================================
// Ambil data + 1 data tambahan untuk menentukan pagination
// ============================================================
$sql = "
    SELECT
        id_transaksi_jenis,
        nama,
        kategori

    FROM transaksi_jenis

    $where

    ORDER BY nama ASC

    LIMIT ?, ?
";


$params[] = $offset;
$params[] = $limit + 1;

$types .= 'ii';


$stmt = mysqli_prepare($Conn, $sql);

if (!$stmt) {

    echo json_encode([
        'status'  => 'error',
        'message' => 'Gagal mempersiapkan query.',
        'results' => []
    ]);

    exit;
}


mysqli_stmt_bind_param(
    $stmt,
    $types,
    ...$params
);


if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    echo json_encode([
        'status'  => 'error',
        'message' => 'Gagal mengambil data.',
        'results' => []
    ]);

    exit;
}


$result = mysqli_stmt_get_result($stmt);


// ============================================================
// Build Result
// ============================================================
$results = [];

while ($data = mysqli_fetch_assoc($result)) {

    $results[] = [
        'id'       => (int) $data['id_transaksi_jenis'],
        'text'     => $data['nama'] . ' (' . $data['kategori'] . ')',
        'nama'     => $data['nama'],
        'kategori' => $data['kategori']
    ];
}


mysqli_stmt_close($stmt);


// ============================================================
// Infinite Scroll
// ============================================================
$more = count($results) > $limit;


// Hapus data tambahan
if ($more) {
    array_pop($results);
}


// ============================================================
// Response Select2
// ============================================================
echo json_encode([
    'results' => $results,
    'pagination' => [
        'more' => $more
    ]
], JSON_UNESCAPED_UNICODE);

exit;