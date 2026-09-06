<?php
    // CONNECTION, HELPER & SESSION
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    date_default_timezone_set('Asia/Jakarta');
    header('Content-Type: application/json; charset=utf-8');

    function responseEditApoteker(string $status, string $message): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // VALIDASI SESSION & METHOD
    if (empty($SessionIdAkses)) {
        responseEditApoteker(
            'error',
            'Sesi akses telah berakhir. Silakan login ulang.'
        );
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseEditApoteker(
            'error',
            'Metode request tidak valid.'
        );
    }

    // TANGKAP PARAMETER
    $id_medication_request_group = (int)(
        $_POST['id_medication_request_group'] ?? 0
    );

    $apoteker_id = (int)(
        $_POST['apoteker_id'] ?? 0
    );

    $apoteker_code = trim(
        (string)($_POST['apoteker_code'] ?? '')
    );

    $apoteker_nama = trim(
        (string)($_POST['apoteker_nama'] ?? '')
    );

    $apoteker_ihs = trim(
        (string)($_POST['apoteker_ihs'] ?? '')
    );

    // VALIDASI GROUP
    if ($id_medication_request_group < 1) {
        responseEditApoteker(
            'error',
            'ID resep tidak valid.'
        );
    }

    // NAMA APOTEKER WAJIB
    if ($apoteker_nama === '') {
        responseEditApoteker(
            'error',
            'Nama apoteker tidak boleh kosong.'
        );
    }

    // CEK GROUP
    $stmt = $Conn->prepare("
        SELECT id_medication_request_group
        FROM medication_request_group
        WHERE id_medication_request_group = ?
        LIMIT 1
    ");

    if (!$stmt) {
        responseEditApoteker(
            'error',
            'Gagal mempersiapkan data resep.'
        );
    }

    $stmt->bind_param(
        "i",
        $id_medication_request_group
    );

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();

        responseEditApoteker(
            'error',
            'Gagal memeriksa data resep.<br>Keterangan : '.
            htmlspecialchars($error, ENT_QUOTES, 'UTF-8')
        );
    }

    $exists = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$exists) {
        responseEditApoteker(
            'error',
            'Data resep tidak ditemukan.'
        );
    }

    /*
     * Jika apoteker dipilih dari Select2,
     * ambil ulang data dari medical_personel.
     */
    if ($apoteker_id > 0) {

        $stmt = $Conn->prepare("
            SELECT
                medicalPersonelId,
                medicalPersonelCode,
                medicalPersonelName,
                id_practitioner
            FROM medical_personel
            WHERE medicalPersonelId = ?
            LIMIT 1
        ");

        if (!$stmt) {
            responseEditApoteker(
                'error',
                'Gagal mempersiapkan data apoteker.'
            );
        }

        $stmt->bind_param(
            "i",
            $apoteker_id
        );

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();

            responseEditApoteker(
                'error',
                'Gagal membuka data apoteker.<br>Keterangan : '.
                htmlspecialchars($error, ENT_QUOTES, 'UTF-8')
            );
        }

        $apoteker = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$apoteker) {
            responseEditApoteker(
                'error',
                'Apoteker yang dipilih tidak ditemukan.'
            );
        }

        // Gunakan data master
        $apoteker_id = (int)$apoteker['medicalPersonelId'];

        $apoteker_code = trim(
            (string)($apoteker['medicalPersonelCode'] ?? '')
        );

        $apoteker_nama = trim(
            (string)($apoteker['medicalPersonelName'] ?? '')
        );

        $apoteker_ihs = trim(
            (string)($apoteker['id_practitioner'] ?? '')
        );

    } else {

        // Apoteker manual/eksternal
        $apoteker_id = null;

        if ($apoteker_code === '') {
            $apoteker_code = null;
        }

        if ($apoteker_ihs === '') {
            $apoteker_ihs = null;
        }
    }

    // VALIDASI ULANG NAMA
    if ($apoteker_nama === '') {
        responseEditApoteker(
            'error',
            'Nama apoteker tidak boleh kosong.'
        );
    }

    // METADATA UPDATE
    $update_at = date('Y-m-d H:i:s');

    $update_by_id = (int)$SessionIdAkses;

    $update_by_name = trim(
        (string)($SessionNama ?? '')
    );

    if ($update_by_name === '') {
        $update_by_name = 'System';
    }

    // UPDATE
    $stmt = $Conn->prepare("
        UPDATE medication_request_group
        SET
            apoteker_id      = ?,
            apoteker_code    = ?,
            apoteker_nama    = ?,
            apoteker_ihs     = ?,
            update_at        = ?,
            update_by_id     = ?,
            update_by_name   = ?
        WHERE id_medication_request_group = ?
    ");

    if (!$stmt) {
        responseEditApoteker(
            'error',
            'Gagal mempersiapkan proses update apoteker.'
        );
    }

    $stmt->bind_param(
        "issssisi",
        $apoteker_id,
        $apoteker_code,
        $apoteker_nama,
        $apoteker_ihs,
        $update_at,
        $update_by_id,
        $update_by_name,
        $id_medication_request_group
    );

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();

        responseEditApoteker(
            'error',
            'Gagal memperbarui apoteker.<br>Keterangan : '.
            htmlspecialchars($error, ENT_QUOTES, 'UTF-8')
        );
    }

    $stmt->close();

    responseEditApoteker(
        'success',
        'Data apoteker berhasil diperbarui.'
    );
?>