<?php
include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";

date_default_timezone_set("Asia/Jakarta");

$response = [
    'status'  => 'error',
    'message' => 'Terjadi kesalahan sistem'
];

if (empty($SessionIdAkses)) {
    $response['message'] = 'Sesi berakhir';
    echo json_encode($response);
    exit;
}

// Ambil input
$id_connection_satu_sehat     = validateAndSanitizeInput($_POST['id_connection_satu_sehat'] ?? '');
$name_connection_satu_sehat   = validateAndSanitizeInput($_POST['name_connection_satu_sehat'] ?? '');
$url_connection_satu_sehat    = validateAndSanitizeInput($_POST['url_connection_satu_sehat'] ?? '');
$organization_id              = validateAndSanitizeInput($_POST['organization_id'] ?? '');
$client_key                   = validateAndSanitizeInput($_POST['client_key'] ?? '');
$secret_key                   = validateAndSanitizeInput($_POST['secret_key'] ?? '');
$status_connection_satu_sehat = validateAndSanitizeInput($_POST['status_connection_satu_sehat'] ?? '');

// Validasi wajib
if (
    empty($id_connection_satu_sehat) ||
    empty($name_connection_satu_sehat) ||
    empty($url_connection_satu_sehat) ||
    empty($organization_id) ||
    empty($client_key) ||
    empty($secret_key)
) {
    $response['message'] = 'Semua field wajib diisi';
    echo json_encode($response);
    exit;
}

// Validasi panjang
foreach ([
    $name_connection_satu_sehat,
    $url_connection_satu_sehat,
    $organization_id,
    $secret_key,
    $client_key
] as $val) {
    if (strlen($val) > 200) {
        $response['message'] = 'Panjang karakter maksimal 200';
        echo json_encode($response);
        exit;
    }
}

// Transaction
$Conn->begin_transaction();

try {

    // Jika aktif → nonaktifkan lainnya
    if ($status_connection_satu_sehat == '1') {
        $QryReset = $Conn->prepare("
            UPDATE connection_satu_sehat 
            SET status_connection_satu_sehat = 0
            WHERE id_connection_satu_sehat != ?
        ");
        if (!$QryReset) {
            throw new Exception($Conn->error);
        }

        $QryReset->bind_param("i", $id_connection_simrs);
        $QryReset->execute();
        $QryReset->close();
    }

    // UPDATE utama (❗ HAPUS datetime_update JIKA TIDAK ADA)
    $QryUpdate = $Conn->prepare("
        UPDATE connection_satu_sehat SET
            name_connection_satu_sehat   = ?,
            url_connection_satu_sehat    = ?,
            organization_id               = ?,
            client_key              = ?,
            secret_key              = ?,
            status_connection_satu_sehat = ?
        WHERE id_connection_satu_sehat = ?
    ");

    if (!$QryUpdate) {
        throw new Exception($Conn->error);
    }

    $QryUpdate->bind_param(
        "sssssii",
        $name_connection_satu_sehat,
        $url_connection_satu_sehat,
        $organization_id,
        $client_key,
        $secret_key,
        $status_connection_satu_sehat,
        $id_connection_satu_sehat
    );

    $QryUpdate->execute();
    $QryUpdate->close();

    $Conn->commit();

    $response['status']  = 'success';
    $response['message'] = 'Edit koneksi Satu Sehat berhasil';

} catch (Exception $e) {
    $Conn->rollback();
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
