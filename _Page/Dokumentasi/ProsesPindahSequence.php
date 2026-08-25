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
            "PHP Exception: " . $exception->getMessage(),
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
    // VALIDASI SESSION
    // =========================================================

    if (empty($SessionIdAkses)) {

        jsonResponse(
            false,
            "Sesi akses sudah berakhir. Silakan login kembali."
        );
    }


    // =========================================================
    // VALIDASI METHOD
    // =========================================================

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        jsonResponse(
            false,
            "Metode request tidak valid."
        );
    }


    // =========================================================
    // VALIDASI ID
    // =========================================================

    $id_dokumentasi_konten =
        trim($_POST['id_dokumentasi_konten'] ?? '');

    $arah =
        trim($_POST['arah'] ?? '');


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
    // VALIDASI ARAH
    // =========================================================

    if (!in_array(
        $arah,
        ['atas', 'bawah'],
        true
    )) {

        jsonResponse(
            false,
            "Arah perpindahan tidak valid."
        );
    }


    // =========================================================
    // AMBIL DATA KONTEN SAAT INI
    // =========================================================

    $Qry = $Conn->prepare("
        SELECT
            id_dokumentasi_konten,
            id_dokumentasi,
            sequence
        FROM dokumentasi_konten
        WHERE id_dokumentasi_konten = ?
        LIMIT 1
    ");

    if (!$Qry) {

        jsonResponse(
            false,
            "Gagal menyiapkan query konten.",
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
    // DATA KONTEN
    // =========================================================

    $id_dokumentasi =
        (int) $Data['id_dokumentasi'];

    $sequence =
        (int) $Data['sequence'];


    // =========================================================
    // HITUNG TARGET SEQUENCE
    // =========================================================

    if ($arah === 'atas') {

        $target_sequence =
            $sequence - 1;

    } else {

        $target_sequence =
            $sequence + 1;
    }


    // =========================================================
    // CEK BATAS
    // =========================================================

    if ($target_sequence < 1) {

        jsonResponse(
            false,
            "Konten sudah berada pada posisi paling atas."
        );
    }


    // =========================================================
    // CEK KONTEN TARGET
    // =========================================================

    $QryTarget = $Conn->prepare("
        SELECT
            id_dokumentasi_konten,
            sequence
        FROM dokumentasi_konten
        WHERE
            id_dokumentasi = ?
            AND sequence = ?
        LIMIT 1
    ");

    if (!$QryTarget) {

        jsonResponse(
            false,
            "Gagal menyiapkan query konten tujuan.",
            [
                "error" => $Conn->error
            ]
        );
    }


    $QryTarget->bind_param(
        "ii",
        $id_dokumentasi,
        $target_sequence
    );


    if (!$QryTarget->execute()) {

        $error = $QryTarget->error;

        $QryTarget->close();

        jsonResponse(
            false,
            "Gagal mencari konten tujuan.",
            [
                "error" => $error
            ]
        );
    }


    $ResultTarget =
        $QryTarget->get_result();


    if ($ResultTarget->num_rows === 0) {

        $QryTarget->close();

        if ($arah === 'bawah') {

            jsonResponse(
                false,
                "Konten sudah berada pada posisi paling bawah."
            );

        } else {

            jsonResponse(
                false,
                "Konten tujuan tidak ditemukan."
            );
        }
    }


    $DataTarget =
        $ResultTarget->fetch_assoc();

    $QryTarget->close();


    $id_target =
        (int) $DataTarget['id_dokumentasi_konten'];


    // =========================================================
    // TRANSACTION
    // =========================================================

    $Conn->begin_transaction();

    try {

        // -----------------------------------------------------
        // Gunakan sequence sementara
        // agar tidak terjadi benturan UNIQUE
        // -----------------------------------------------------

        $sequence_sementara = -1;


        // -----------------------------------------------------
        // Konten saat ini -> temporary
        // -----------------------------------------------------

        $QryTemp = $Conn->prepare("
            UPDATE dokumentasi_konten
            SET sequence = ?
            WHERE id_dokumentasi_konten = ?
        ");

        if (!$QryTemp) {
            throw new Exception(
                "Gagal menyiapkan query temporary: "
                . $Conn->error
            );
        }


        $QryTemp->bind_param(
            "ii",
            $sequence_sementara,
            $id_dokumentasi_konten
        );


        if (!$QryTemp->execute()) {

            throw new Exception(
                "Gagal mengubah sequence sementara: "
                . $QryTemp->error
            );
        }


        $QryTemp->close();


        // -----------------------------------------------------
        // Konten target -> sequence konten saat ini
        // -----------------------------------------------------

        $QryTargetUpdate = $Conn->prepare("
            UPDATE dokumentasi_konten
            SET sequence = ?
            WHERE id_dokumentasi_konten = ?
        ");

        if (!$QryTargetUpdate) {

            throw new Exception(
                "Gagal menyiapkan update target: "
                . $Conn->error
            );
        }


        $QryTargetUpdate->bind_param(
            "ii",
            $sequence,
            $id_target
        );


        if (!$QryTargetUpdate->execute()) {

            throw new Exception(
                "Gagal mengubah sequence target: "
                . $QryTargetUpdate->error
            );
        }


        $QryTargetUpdate->close();


        // -----------------------------------------------------
        // Konten utama -> target sequence
        // -----------------------------------------------------

        $QryCurrent = $Conn->prepare("
            UPDATE dokumentasi_konten
            SET sequence = ?
            WHERE id_dokumentasi_konten = ?
        ");

        if (!$QryCurrent) {

            throw new Exception(
                "Gagal menyiapkan update konten utama: "
                . $Conn->error
            );
        }


        $QryCurrent->bind_param(
            "ii",
            $target_sequence,
            $id_dokumentasi_konten
        );


        if (!$QryCurrent->execute()) {

            throw new Exception(
                "Gagal mengubah sequence konten utama: "
                . $QryCurrent->error
            );
        }


        $QryCurrent->close();


        // -----------------------------------------------------
        // Commit
        // -----------------------------------------------------

        $Conn->commit();


        jsonResponse(
            true,
            $arah === 'atas'
                ? "Konten berhasil dipindahkan ke atas."
                : "Konten berhasil dipindahkan ke bawah.",
            [
                "id_dokumentasi_konten" => $id_dokumentasi_konten,
                "id_target"             => $id_target,
                "sequence_lama"         => $sequence,
                "sequence_baru"         => $target_sequence
            ]
        );

    } catch (Exception $e) {

        // -----------------------------------------------------
        // Rollback
        // -----------------------------------------------------

        $Conn->rollback();


        jsonResponse(
            false,
            "Gagal memindahkan konten.",
            [
                "error" => $e->getMessage()
            ]
        );
    }