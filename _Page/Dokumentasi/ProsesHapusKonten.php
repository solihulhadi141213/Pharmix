<?php

// =========================================================
// KONFIGURASI
// =========================================================

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

date_default_timezone_set('Asia/Jakarta');

header('Content-Type: application/json; charset=utf-8');


// =========================================================
// RESPONSE
// =========================================================

function jsonResponse($status, $message, $data = null)
{
    $response = [
        "status"  => $status,
        "message" => $message
    ];

    if ($data !== null) {
        $response["data"] = $data;
    }

    echo json_encode(
        $response,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;
}


// =========================================================
// ERROR HANDLER
// =========================================================

set_error_handler(function (
    $severity,
    $message,
    $file,
    $line
) {

    if (!(error_reporting() & $severity)) {
        return false;
    }

    jsonResponse(
        false,
        "PHP Error: " . $message,
        [
            "file" => $file,
            "line" => $line
        ]
    );
});


// =========================================================
// EXCEPTION HANDLER
// =========================================================

set_exception_handler(function ($exception) {

    jsonResponse(
        false,
        "PHP Exception: " .
        $exception->getMessage(),
        [
            "file" => $exception->getFile(),
            "line" => $exception->getLine()
        ]
    );
});


// =========================================================
// INCLUDE
// =========================================================

include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";


// =========================================================
// SESSION
// =========================================================

if (empty($SessionIdAkses)) {

    jsonResponse(
        false,
        "Sesi akses sudah berakhir. Silakan login kembali."
    );
}


// =========================================================
// METHOD
// =========================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    jsonResponse(
        false,
        "Metode request tidak valid."
    );
}


// =========================================================
// ID
// =========================================================

$id_dokumentasi_konten =
    trim($_POST['id_dokumentasi_konten'] ?? '');


if (
    $id_dokumentasi_konten === '' ||
    !ctype_digit($id_dokumentasi_konten)
) {

    jsonResponse(
        false,
        "ID konten dokumentasi tidak valid."
    );
}


$id_dokumentasi_konten =
    (int) $id_dokumentasi_konten;


// =========================================================
// AMBIL DATA KONTEN
// =========================================================

$Qry = $Conn->prepare("
    SELECT
        id_dokumentasi_konten,
        id_dokumentasi,
        sequence,
        tipe_konten,
        local_image_konten
    FROM dokumentasi_konten
    WHERE id_dokumentasi_konten = ?
    LIMIT 1
");


if (!$Qry) {

    jsonResponse(
        false,
        "Gagal menyiapkan query.",
        [
            "error" => $Conn->error
        ]
    );
}


$Qry->bind_param(
    "i",
    $id_dokumentasi_konten
);


if (!$Qry->execute()) {

    $error = $Qry->error;

    $Qry->close();

    jsonResponse(
        false,
        "Gagal mengambil data konten.",
        [
            "error" => $error
        ]
    );
}


$Result = $Qry->get_result();


if ($Result->num_rows === 0) {

    $Qry->close();

    jsonResponse(
        false,
        "Konten dokumentasi tidak ditemukan."
    );
}


$Data = $Result->fetch_assoc();

$Qry->close();


// =========================================================
// DATA
// =========================================================

$id_dokumentasi =
    (int) $Data['id_dokumentasi'];

$sequence =
    (int) $Data['sequence'];

$local_image =
    $Data['local_image_konten'];


// =========================================================
// TRANSACTION
// =========================================================

$Conn->begin_transaction();


try {

    // =====================================================
    // HAPUS DATA
    // =====================================================

    $QryDelete = $Conn->prepare("
        DELETE FROM dokumentasi_konten
        WHERE id_dokumentasi_konten = ?
        LIMIT 1
    ");


    if (!$QryDelete) {

        throw new Exception(
            "Gagal menyiapkan query hapus: "
            . $Conn->error
        );
    }


    $QryDelete->bind_param(
        "i",
        $id_dokumentasi_konten
    );


    if (!$QryDelete->execute()) {

        throw new Exception(
            "Gagal menghapus konten: "
            . $QryDelete->error
        );
    }


    if ($QryDelete->affected_rows === 0) {

        $QryDelete->close();

        throw new Exception(
            "Data konten tidak berhasil dihapus."
        );
    }


    $QryDelete->close();


    // =====================================================
    // RAPATKAN SEQUENCE
    // =====================================================
    /*
     * Contoh:
     *
     * Sebelum:
     * 1
     * 2 <- dihapus
     * 3
     * 4
     *
     * Sesudah:
     * 1
     * 2
     * 3
     *
     */

    $QrySequence = $Conn->prepare("
        UPDATE dokumentasi_konten
        SET sequence = sequence - 1
        WHERE
            id_dokumentasi = ?
            AND sequence > ?
    ");


    if (!$QrySequence) {

        throw new Exception(
            "Gagal menyiapkan query sequence: "
            . $Conn->error
        );
    }


    $QrySequence->bind_param(
        "ii",
        $id_dokumentasi,
        $sequence
    );


    if (!$QrySequence->execute()) {

        throw new Exception(
            "Gagal merapikan sequence: "
            . $QrySequence->error
        );
    }


    $QrySequence->close();


    // =====================================================
    // COMMIT
    // =====================================================

    $Conn->commit();


    // =====================================================
    // HAPUS FILE GAMBAR
    // =====================================================

    if (
        !empty($local_image) &&
        !empty($Data['tipe_konten']) &&
        $Data['tipe_konten'] === 'Local Image'
    ) {

        $file_gambar =
            "../../assets/img/dokumentasi/"
            . $local_image;

        if (file_exists($file_gambar)) {

            unlink($file_gambar);
        }
    }


    // =====================================================
    // RESPONSE
    // =====================================================

    jsonResponse(
        true,
        "Konten dokumentasi berhasil dihapus.",
        [
            "id_dokumentasi_konten" =>
                $id_dokumentasi_konten,

            "id_dokumentasi" =>
                $id_dokumentasi,

            "sequence_lama" =>
                $sequence
        ]
    );


} catch (Exception $e) {

    // =====================================================
    // ROLLBACK
    // =====================================================

    $Conn->rollback();


    jsonResponse(
        false,
        "Gagal menghapus konten dokumentasi.",
        [
            "error" => $e->getMessage()
        ]
    );
}