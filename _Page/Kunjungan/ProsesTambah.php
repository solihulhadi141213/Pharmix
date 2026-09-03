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

$id_anggota = isset($_POST['id_anggota']) ? (int) $_POST['id_anggota'] : 0;
$tanggal_kunjungan = trim($_POST['tanggal_kunjungan'] ?? '');
$jam_kunjungan = trim($_POST['jam_kunjungan'] ?? '');
$priority = trim($_POST['priority'] ?? '');
$keluhan = trim($_POST['keluhan'] ?? '');
$jenis_kunjungan = trim($_POST['jenis_kunjungan'] ?? '');
$status = trim($_POST['status'] ?? 'arrived');
$kelas_inap = trim($_POST['kelas_inap'] ?? '');
$ruang_inap = trim($_POST['ruang_inap'] ?? '');

$id_dokter_penerima = !empty($_POST['id_dokter_penerima']) ? (int) $_POST['id_dokter_penerima'] : null;
$id_dpjp = !empty($_POST['id_dpjp']) ? (int) $_POST['id_dpjp'] : null;
$id_poli = !empty($_POST['id_poli']) ? (int) $_POST['id_poli'] : null;

$kelas_inap = ($kelas_inap !== '') ? $kelas_inap : null;
$ruang_inap = ($ruang_inap !== '') ? $ruang_inap : null;

if ($id_anggota <= 0 || $tanggal_kunjungan === '' || $jam_kunjungan === '' || $priority === '' || $jenis_kunjungan === '' || $status === '') {
    responseJson('error', 'Silakan lengkapi semua field yang bertanda bintang (*).');
}

$allowedPriority = ['Normal', 'Urgent', 'Emergency'];
if (!in_array($priority, $allowedPriority, true)) {
    responseJson('error', 'Priority kunjungan tidak valid.');
}

$allowedJenisKunjungan = ['AMB', 'IMP', 'EMER'];
if (!in_array($jenis_kunjungan, $allowedJenisKunjungan, true)) {
    responseJson('error', 'Jenis kunjungan tidak valid.');
}

$allowedStatus = ['planned', 'arrived', 'triaged', 'in-progress', 'onleave', 'finished', 'cancelled', 'entered-in-error', 'unknown'];
if (!in_array($status, $allowedStatus, true)) {
    responseJson('error', 'Status kunjungan tidak valid.');
}

$dateObject = DateTime::createFromFormat('Y-m-d', $tanggal_kunjungan);
if (!$dateObject || $dateObject->format('Y-m-d') !== $tanggal_kunjungan) {
    responseJson('error', 'Format tanggal kunjungan tidak valid.');
}

$timeObject = DateTime::createFromFormat('H:i', $jam_kunjungan);
if (!$timeObject || $timeObject->format('H:i') !== $jam_kunjungan) {
    responseJson('error', 'Format jam kunjungan tidak valid.');
}

$tanggal_kunjungan_db = $tanggal_kunjungan . ' ' . $jam_kunjungan . ':00';

$stmtPasien = $Conn->prepare("SELECT id_anggota FROM anggota WHERE id_anggota = ? LIMIT 1");
if (!$stmtPasien) {
    responseJson('error', 'Gagal mempersiapkan validasi pasien.');
}
$stmtPasien->bind_param('i', $id_anggota);
$stmtPasien->execute();
$resultPasien = $stmtPasien->get_result();
if ($resultPasien->num_rows < 1) {
    $stmtPasien->close();
    responseJson('error', 'Data pasien tidak ditemukan.');
}
$stmtPasien->close();

$kode_dokter_penerima = null;
$nama_dokter_penerima = null;
if ($id_dokter_penerima !== null) {
    $stmtDokterPenerima = $Conn->prepare("SELECT medicalPersonelCode, medicalPersonelName FROM medical_personel WHERE medicalPersonelId = ? LIMIT 1");
    if (!$stmtDokterPenerima) {
        responseJson('error', 'Gagal mempersiapkan data dokter penerima.');
    }
    $stmtDokterPenerima->bind_param('i', $id_dokter_penerima);
    $stmtDokterPenerima->execute();
    $dataDokterPenerima = $stmtDokterPenerima->get_result()->fetch_assoc();
    $stmtDokterPenerima->close();
    if (!$dataDokterPenerima) {
        responseJson('error', 'Dokter penerima yang dipilih tidak ditemukan pada data master.');
    }
    $kode_dokter_penerima = $dataDokterPenerima['medicalPersonelCode'];
    $nama_dokter_penerima = $dataDokterPenerima['medicalPersonelName'];
}

$kode_dpjp = null;
$nama_dpjp = null;
if ($id_dpjp !== null) {
    $stmtDpjp = $Conn->prepare("SELECT medicalPersonelCode, medicalPersonelName FROM medical_personel WHERE medicalPersonelId = ? LIMIT 1");
    if (!$stmtDpjp) {
        responseJson('error', 'Gagal mempersiapkan data dokter DPJP.');
    }
    $stmtDpjp->bind_param('i', $id_dpjp);
    $stmtDpjp->execute();
    $dataDpjp = $stmtDpjp->get_result()->fetch_assoc();
    $stmtDpjp->close();
    if (!$dataDpjp) {
        responseJson('error', 'Dokter DPJP yang dipilih tidak ditemukan pada data master.');
    }
    $kode_dpjp = $dataDpjp['medicalPersonelCode'];
    $nama_dpjp = $dataDpjp['medicalPersonelName'];
}

$kode_poli = null;
$nama_poli = null;
if ($id_poli !== null) {
    $stmtPoli = $Conn->prepare("SELECT polyclinicCode, polyclinicName FROM polyclinic WHERE polyclinicId = ? LIMIT 1");
    if (!$stmtPoli) {
        responseJson('error', 'Gagal mempersiapkan data poliklinik.');
    }
    $stmtPoli->bind_param('i', $id_poli);
    $stmtPoli->execute();
    $dataPoli = $stmtPoli->get_result()->fetch_assoc();
    $stmtPoli->close();
    if (!$dataPoli) {
        responseJson('error', 'Poliklinik yang dipilih tidak ditemukan pada data master.');
    }
    $kode_poli = $dataPoli['polyclinicCode'];
    $nama_poli = $dataPoli['polyclinicName'];
}

$creat_at = date('Y-m-d H:i:s');
$creat_by_id = (int) $SessionIdAkses;
$nama_creator = '';

$stmtCreator = $Conn->prepare("SELECT nama_akses FROM akses WHERE id_akses = ? LIMIT 1");
if (!$stmtCreator) {
    responseJson('error', 'Gagal mempersiapkan data creator.');
}
$stmtCreator->bind_param('i', $creat_by_id);
$stmtCreator->execute();
$dataCreator = $stmtCreator->get_result()->fetch_assoc();
$stmtCreator->close();
if (!$dataCreator) {
    responseJson('error', 'Data pengguna pembuat tidak ditemukan.');
}
$nama_creator = $dataCreator['nama_akses'];

$update_at = $creat_at;
$update_by_id = $creat_by_id;
$update_by_name = $nama_creator;

$Conn->begin_transaction();

try {
    $insertQuery = "
        INSERT INTO kunjungan (
            id_anggota, tanggal_kunjungan, priority, keluhan, jenis_kunjungan,
            id_dokter_penerima, kode_dokter_penerima, nama_dokter_penerima,
            id_dpjp, kode_dpjp, nama_dpjp,
            id_poli, kode_poli, nama_poli,
            kelas_inap, ruang_inap, status,
            creat_at, creat_by_id, creat_by_name,
            update_at, update_by_id, update_by_name
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $Conn->prepare($insertQuery);
    if (!$stmt) {
        throw new Exception('Prepare INSERT gagal: ' . $Conn->error);
    }

    $types = "issssissississssssissis";
    $stmt->bind_param(
        $types,
        $id_anggota, $tanggal_kunjungan_db, $priority, $keluhan, $jenis_kunjungan,
        $id_dokter_penerima, $kode_dokter_penerima, $nama_dokter_penerima,
        $id_dpjp, $kode_dpjp, $nama_dpjp,
        $id_poli, $kode_poli, $nama_poli,
        $kelas_inap, $ruang_inap, $status,
        $creat_at, $creat_by_id, $nama_creator,
        $update_at, $update_by_id, $update_by_name
    );

    if (!$stmt->execute()) {
        throw new Exception('Gagal menyimpan kunjungan: ' . $stmt->error);
    }

    $id_kunjungan = $Conn->insert_id;
    $stmt->close();
    $Conn->commit();

    responseJson('success', 'Data kunjungan berhasil disimpan.', ['id_kunjungan' => $id_kunjungan]);

} catch (Throwable $e) {
    $Conn->rollback();
    responseJson('error', $e->getMessage());
}
?>