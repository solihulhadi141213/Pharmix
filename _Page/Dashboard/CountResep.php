<?php
header('Content-Type: application/json; charset=utf-8');

$response = ['status' => 'Error', 'message' => 'Permintaan harus menggunakan metode POST.'];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    try {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        require_once __DIR__ . '/../../_Config/Connection.php';
        require_once __DIR__ . '/../../_Config/GlobalFunction.php';
        require_once __DIR__ . '/../../_Config/Session.php';

        if (empty($SessionIdAkses)) {
            $response['message'] = 'Sesi akses sudah berakhir. Silakan login kembali.';
        } else {
            $result = $Conn->query('SELECT COUNT(*) AS total FROM medication_request_group');
            $row = $result->fetch_assoc();
            $result->free();

            $response = [
                'status' => 'Success',
                'message' => 'Jumlah resep berhasil dihitung.',
                'count_resep' => number_format((int) $row['total'], 0, ',', '.')
            ];
        }
    } catch (mysqli_sql_exception $e) {
        $response['message'] = 'Terjadi kesalahan database saat menghitung jumlah resep.';
    }
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
