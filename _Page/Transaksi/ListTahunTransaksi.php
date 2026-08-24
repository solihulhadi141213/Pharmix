<?php
date_default_timezone_set('Asia/Jakarta');

include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/SettingGeneral.php";
include "../../_Config/Session.php";

header('Content-Type: application/json; charset=utf-8');

function responseError($message) {
    echo json_encode([
        'status'  => 'error',
        'message' => $message,
        'data'    => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($SessionIdAkses)) {
    responseError('Sesi akses sudah berakhir. Silakan login kembali.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responseError('Metode request tidak valid.');
}

$sql = "
    SELECT DISTINCT YEAR(tanggal) AS tahun
    FROM transaksi
    WHERE tanggal IS NOT NULL
    ORDER BY tahun DESC
";

$result = mysqli_query($Conn, $sql);

if (!$result) {
    responseError('Gagal mengambil daftar tahun transaksi.');
}

$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    if (!empty($row['tahun'])) {
        $data[] = (string)$row['tahun'];
    }
}

echo json_encode([
    'status'  => 'success',
    'message' => 'Daftar tahun berhasil ditemukan.',
    'data'    => $data
], JSON_UNESCAPED_UNICODE);

exit;
?>