<?php
// koneksi dan session
include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";

// Zona Waktu
date_default_timezone_set("Asia/Jakarta");

// Response default
$response = [
    'status'  => 'error',
    'message' => 'Terjadi kesalahan sistem'
];

// =======================
// VALIDASI SESSION
// =======================
if (empty($SessionIdAkses)) {
    $response['message'] = 'Sesi akses telah berakhir. Silakan login ulang.';
    echo json_encode($response);
    exit;
}

// =======================
// VALIDASI INPUT
// =======================
$id_connection_satu_sehat = validateAndSanitizeInput($_POST['id_connection_satu_sehat'] ?? '');

if (empty($id_connection_satu_sehat)) {
    $response['message'] = 'ID koneksi Satu Sehat tidak valid.';
    echo json_encode($response);
    exit;
}

// =======================
// CEK DATA ADA ATAU TIDAK
// =======================
$QryCheck = $Conn->prepare("
    SELECT status_connection_satu_sehat 
    FROM connection_satu_sehat 
    WHERE id_connection_satu_sehat = ?
");
if (!$QryCheck) {
    $response['message'] = $Conn->error;
    echo json_encode($response);
    exit;
}

$QryCheck->bind_param("i", $id_connection_satu_sehat);
$QryCheck->execute();
$Result = $QryCheck->get_result();
$Data   = $Result->fetch_assoc();
$QryCheck->close();

if (!$Data) {
    $response['message'] = 'Data koneksi Satu Sehat tidak ditemukan.';
    echo json_encode($response);
    exit;
}

// =======================
// OPTIONAL (AMAN): CEGAH HAPUS JIKA AKTIF
// =======================
if ($Data['status_connection_satu_sehat'] == 1) {
    $response['message'] = 'Koneksi Satu Sehat masih berstatus aktif. Nonaktifkan terlebih dahulu sebelum menghapus.';
    echo json_encode($response);
    exit;
}

// =======================
// PROSES DELETE
// =======================
$Conn->begin_transaction();

try {

    $QryDelete = $Conn->prepare("
        DELETE FROM connection_satu_sehat 
        WHERE id_connection_satu_sehat = ?
    ");
    if (!$QryDelete) {
        throw new Exception($Conn->error);
    }

    $QryDelete->bind_param("i", $id_connection_satu_sehat);

    if (!$QryDelete->execute()) {
        throw new Exception('Gagal menghapus data koneksi SIMRS.');
    }

    $QryDelete->close();
    $Conn->commit();

    $response['status']  = 'success';
    $response['message'] = 'Koneksi Satu Sehat berhasil dihapus.';

} catch (Exception $e) {
    $Conn->rollback();
    $response['message'] = $e->getMessage();
}

// =======================
// OUTPUT JSON
// =======================
echo json_encode($response);
