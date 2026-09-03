<?php
date_default_timezone_set('Asia/Jakarta');
include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";
header('Content-Type: application/json; charset=utf-8');

function responseJson($status, $message, $data = []) {
    echo json_encode(array_merge(['status' => $status, 'message' => $message], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($SessionIdAkses)) {
    responseJson('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responseJson('error', 'Metode request tidak valid.');
}

$id_kunjungan = isset($_POST['id_kunjungan']) ? (int) $_POST['id_kunjungan'] : 0;
if ($id_kunjungan <= 0) {
    responseJson('error', 'ID Kunjungan tidak valid.');
}

// Ambil data kunjungan beserta nama pasien
$query = "SELECT kunjungan.*, anggota.id_pasien as rm_pasien, anggota.nama as nama_pasien 
          FROM kunjungan 
          LEFT JOIN anggota ON kunjungan.id_anggota = anggota.id_anggota 
          WHERE kunjungan.id_kunjungan = ?";
          
$stmt = $Conn->prepare($query);
if (!$stmt) {
    responseJson('error', 'Gagal mempersiapkan query database.');
}

$stmt->bind_param('i', $id_kunjungan);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    responseJson('error', 'Data kunjungan tidak ditemukan.');
}

$nama_pasien = htmlspecialchars($data['nama_pasien'] ?? 'Tanpa Nama');
$rm_pasien   = htmlspecialchars($data['rm_pasien'] ?? '-');
$tanggal     = htmlspecialchars($data['tanggal_kunjungan']);
$poli        = htmlspecialchars($data['nama_poli'] ?? '-');
$status      = htmlspecialchars($data['status']);

$html = '
    <input type="hidden" name="id_kunjungan" value="' . $id_kunjungan . '">
    <div class="alert alert-warning mb-0">
        <p class="mb-2">Apakah Anda yakin ingin menghapus data kunjungan berikut?</p>
        <ul class="mb-0 ps-3">
            <li><b>Nama Pasien:</b> ' . $nama_pasien . ' (RM: ' . $rm_pasien . ')</li>
            <li><b>Tanggal Kunjungan:</b> ' . $tanggal . '</li>
            <li><b>Poliklinik:</b> ' . $poli . '</li>
            <li><b>Status:</b> ' . $status . '</li>
        </ul>
        <small class="text-danger mt-2 d-block"><b>Catatan:</b> Tindakan ini tidak dapat dibatalkan.</small>
    </div>
';

responseJson('success', 'Berhasil memuat data.', ['html' => $html]);
?>