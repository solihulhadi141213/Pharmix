<?php
    // CONNECTION, HELPER & SESSION
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    date_default_timezone_set('Asia/Jakarta');
    header('Content-Type: application/json; charset=utf-8');

    function responseEditDokter(string $status, string $message): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // VALIDASI SESSION & METHOD
    if (empty($SessionIdAkses)) {
        responseEditDokter(
            'error',
            'Sesi akses telah berakhir. Silakan login ulang.'
        );
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseEditDokter(
            'error',
            'Metode request tidak valid.'
        );
    }

    // TANGKAP PARAMETER
    $id_medication_request_group = (int)(
        $_POST['id_medication_request_group'] ?? 0
    );

    $dokter_id = (int)(
        $_POST['dokter_id'] ?? 0
    );

    $dokter_code = trim(
        (string)($_POST['dokter_code'] ?? '')
    );

    $dokter_nama = trim(
        (string)($_POST['dokter_nama'] ?? '')
    );

    $dokter_ihs = trim(
        (string)($_POST['dokter_ihs'] ?? '')
    );

    // VALIDASI GROUP
    if ($id_medication_request_group < 1) {
        responseEditDokter(
            'error',
            'ID resep tidak valid.'
        );
    }

    // NAMA DOKTER WAJIB
    if ($dokter_nama === '') {
        responseEditDokter(
            'error',
            'Nama dokter tidak boleh kosong.'
        );
    }

    // CEK GROUP RESEP
    $stmt = $Conn->prepare("
        SELECT id_medication_request_group
        FROM medication_request_group
        WHERE id_medication_request_group = ?
        LIMIT 1
    ");

    if (!$stmt) {
        responseEditDokter(
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

        responseEditDokter(
            'error',
            'Gagal memeriksa data resep.<br>Keterangan : '.
            htmlspecialchars($error, ENT_QUOTES, 'UTF-8')
        );
    }

    $exists = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$exists) {
        responseEditDokter(
            'error',
            'Data resep tidak ditemukan.'
        );
    }

    /*
     * Jika dokter_id dipilih dari Select2,
     * ambil ulang master dari medical_personel.
     * Ini mencegah manipulasi dokter_code/nama/ihs dari browser.
     */
    if ($dokter_id > 0) {

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
            responseEditDokter(
                'error',
                'Gagal mempersiapkan data dokter.'
            );
        }

        $stmt->bind_param(
            "i",
            $dokter_id
        );

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();

            responseEditDokter(
                'error',
                'Gagal membuka data dokter.<br>Keterangan : '.
                htmlspecialchars($error, ENT_QUOTES, 'UTF-8')
            );
        }

        $dokter = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$dokter) {
            responseEditDokter(
                'error',
                'Dokter yang dipilih tidak ditemukan.'
            );
        }

        // Gunakan data master
        $dokter_id   = (int)$dokter['medicalPersonelId'];
        $dokter_code = trim(
            (string)($dokter['medicalPersonelCode'] ?? '')
        );

        $dokter_nama = trim(
            (string)($dokter['medicalPersonelName'] ?? '')
        );

        $dokter_ihs = trim(
            (string)($dokter['id_practitioner'] ?? '')
        );

    } else {

        /*
         * Dokter eksternal/manual:
         * dokter_id boleh NULL,
         * tetapi nama dan IHS tetap boleh disimpan.
         */
        $dokter_id = null;

        if ($dokter_code === '') {
            $dokter_code = null;
        }

        if ($dokter_ihs === '') {
            $dokter_ihs = null;
        }
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

    // UPDATE DATABASE
    $stmt = $Conn->prepare("
        UPDATE medication_request_group
        SET
            dokter_id      = ?,
            dokter_code    = ?,
            dokter_ihs     = ?,
            dokter_nama    = ?,
            update_at      = ?,
            update_by_id   = ?,
            update_by_name = ?
        WHERE id_medication_request_group = ?
    ");

    if (!$stmt) {
        responseEditDokter(
            'error',
            'Gagal mempersiapkan proses update dokter.'
        );
    }

    $stmt->bind_param(
        "issssisi",
        $dokter_id,
        $dokter_code,
        $dokter_ihs,
        $dokter_nama,
        $update_at,
        $update_by_id,
        $update_by_name,
        $id_medication_request_group
    );

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();

        responseEditDokter(
            'error',
            'Gagal memperbarui dokter.<br>Keterangan : '.
            htmlspecialchars($error, ENT_QUOTES, 'UTF-8')
        );
    }

    $stmt->close();

    responseEditDokter(
        'success',
        'Data dokter pembuat resep berhasil diperbarui.'
    );
?>