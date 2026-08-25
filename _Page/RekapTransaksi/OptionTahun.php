<?php

include "../../_Config/Connection.php";

header('Content-Type: application/json; charset=utf-8');


// ======================================================
// Query Tahun
// ======================================================

$sql = "
    SELECT DISTINCT
        YEAR(tanggal) AS tahun
    FROM transaksi
    WHERE tanggal IS NOT NULL
    ORDER BY tahun DESC
";


$result = $Conn->query($sql);


// ======================================================
// Response
// ======================================================

$data = [];

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $tahun = (int) $row['tahun'];

        if ($tahun > 0) {

            $data[] = $tahun;

        }

    }

}


echo json_encode(
    $data,
    JSON_UNESCAPED_UNICODE
);