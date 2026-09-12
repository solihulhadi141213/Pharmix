<?php
// Koneksi, function, dan session.
date_default_timezone_set('Asia/Jakarta');
require __DIR__ . '/../../_Config/Connection.php';
require __DIR__ . '/../../_Config/GlobalFunction.php';
require __DIR__ . '/../../_Config/Session.php';

header('Content-Type: application/json; charset=utf-8');

function responseFormExportKunjungan($status, $message, $html = '')
{
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'html' => $html,
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if (empty($SessionIdAkses)) {
    responseFormExportKunjungan('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
}

try {
    $query = $Conn->query('SELECT COUNT(*) AS jumlah FROM kunjungan');
    if (!$query) {
        throw new RuntimeException('Gagal menghitung data kunjungan.');
    }
    $jml_data = (int) $query->fetch_assoc()['jumlah'];
    $query->free();
} catch (Throwable $exception) {
    responseFormExportKunjungan('error', 'Gagal mengambil jumlah data kunjungan. Silakan coba lagi.');
}

if ($jml_data === 0) {
    responseFormExportKunjungan('error', 'Tidak ada data kunjungan yang bisa diexport. Silakan tambahkan data kunjungan terlebih dahulu.');
}

$html = '
    <div class="row mb-2">
        <div class="col-12 text-center">
            <small>Jumlah Data Kunjungan</small><br>
            <h2>' . $jml_data . '</h2>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-12 text-center">
            <small>Format Data : Excel</small>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-12">
            <div class="alert alert-warning text-center">
                <small>Semakin banyak data kunjungan, semakin lama proses export berlangsung.</small>
            </div>
        </div>
    </div>
';

responseFormExportKunjungan('success', 'Form export kunjungan berhasil dimuat.', $html);
