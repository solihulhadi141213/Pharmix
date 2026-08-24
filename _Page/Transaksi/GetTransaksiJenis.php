<?php

include "../../_Config/Connection.php";

header('Content-Type: application/json; charset=utf-8');

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page   = isset($_GET['page']) ? (int) $_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}

$limit  = 10;
$offset = ($page - 1) * $limit;

// Ambil 1 tambahan untuk mengetahui apakah masih ada data
$limitQuery = $limit + 1;

if ($search !== '') {

    $keyword = '%' . $search . '%';

    $sql = "
        SELECT
            id_transaksi_jenis,
            nama,
            kategori
        FROM transaksi_jenis
        WHERE
            nama LIKE ?
            OR kategori LIKE ?
        ORDER BY nama ASC
        LIMIT ? OFFSET ?
    ";

    $stmt = mysqli_prepare($Conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ssii",
        $keyword,
        $keyword,
        $limitQuery,
        $offset
    );

} else {

    $sql = "
        SELECT
            id_transaksi_jenis,
            nama,
            kategori
        FROM transaksi_jenis
        ORDER BY nama ASC
        LIMIT ? OFFSET ?
    ";

    $stmt = mysqli_prepare($Conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $limitQuery,
        $offset
    );
}

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$results = [];

while ($data = mysqli_fetch_assoc($result)) {

    $results[] = [
        'id'       => (int) $data['id_transaksi_jenis'],
        'text'     => $data['nama'] . ' (' . $data['kategori'] . ')',
        'nama'     => $data['nama'],
        'kategori' => $data['kategori']
    ];
}

$more = count($results) > $limit;

if ($more) {
    array_pop($results);
}

echo json_encode([
    'results' => $results,
    'pagination' => [
        'more' => $more
    ]
], JSON_UNESCAPED_UNICODE);