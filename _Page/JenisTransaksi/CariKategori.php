<?php

include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";

header('Content-Type: application/json; charset=utf-8');


// =====================================================
// RESPONSE
// =====================================================

$response = [
    "results" => [],
    "more"    => false
];


// =====================================================
// KEYWORD
// =====================================================

$keyword = isset($_POST['keyword'])
    ? trim($_POST['keyword'])
    : '';


// =====================================================
// PAGE
// =====================================================

$page = isset($_POST['page'])
    ? (int) $_POST['page']
    : 1;

if ($page < 1) {
    $page = 1;
}


// =====================================================
// LIMIT
// =====================================================

$limit = 10;


// =====================================================
// OFFSET
// =====================================================

$offset = ($page - 1) * $limit;


// =====================================================
// ESCAPE
// =====================================================

$keyword_safe = mysqli_real_escape_string(
    $Conn,
    $keyword
);


// =====================================================
// QUERY
// =====================================================

if ($keyword === '') {

    $Sql = "
        SELECT DISTINCT kategori
        FROM transaksi_jenis
        WHERE kategori IS NOT NULL
        AND kategori != ''
        ORDER BY kategori ASC
        LIMIT $offset, " . ($limit + 1);

} else {

    $Sql = "
        SELECT DISTINCT kategori
        FROM transaksi_jenis
        WHERE kategori IS NOT NULL
        AND kategori != ''
        AND kategori LIKE '%$keyword_safe%'
        ORDER BY kategori ASC
        LIMIT $offset, " . ($limit + 1);
}


// =====================================================
// EXECUTE
// =====================================================

$QryKategori = mysqli_query($Conn, $Sql);


// =====================================================
// PROCESS RESULT
// =====================================================

if ($QryKategori) {

    $DataCount = 0;

    while ($DataKategori = mysqli_fetch_assoc($QryKategori)) {

        $Kategori = trim($DataKategori['kategori']);

        if ($Kategori === '') {
            continue;
        }


        /*
         * Hanya masukkan maksimal 10 data
         */

        if ($DataCount >= $limit) {
            $response['more'] = true;
            break;
        }


        $response['results'][] = [
            "id"   => $Kategori,
            "text" => $Kategori
        ];

        $DataCount++;
    }
}


// =====================================================
// OUTPUT
// =====================================================

echo json_encode(
    $response,
    JSON_UNESCAPED_UNICODE
);

exit;