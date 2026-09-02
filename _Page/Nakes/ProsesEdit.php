<?php
    // HEADER
    header('Content-Type: application/json; charset=utf-8');

    // INCLUDE
    include __DIR__ . "/../../_Config/Connection.php";
    include __DIR__ . "/../../_Config/GlobalFunction.php";
    include __DIR__ . "/../../_Config/Session.php";
    include __DIR__ . "/../../_Config/FungsiAkses.php";

    date_default_timezone_set('Asia/Jakarta');

    // FUNCTION RESPONSE
    function responseNakesEdit(string $status, string $message, array $metadata = []): void {
        echo json_encode([
            'status'   => $status,
            'message'  => $message,
            'metadata' => $metadata
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // VALIDASI SESSION
    if (empty($SessionIdAkses)) {
        responseNakesEdit('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
    }

    // VALIDASI METHOD
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseNakesEdit('error', 'Metode request tidak valid.');
    }

    // TANGKAP INPUT
    $medicalPersonelId       = filter_var($_POST['medicalPersonelId'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $medicalPersonelCode     = trim((string)($_POST['medicalPersonelCode'] ?? ''));
    $idPractitioner          = trim((string)($_POST['id_practitioner'] ?? ''));
    $medicalPersonelCategory = trim((string)($_POST['medicalPersonelCategory'] ?? ''));
    $medicalPersonelNik      = preg_replace('/\D/', '', trim((string)($_POST['medicalPersonelNik'] ?? '')));
    $medicalPersonelName     = trim((string)($_POST['medicalPersonelName'] ?? ''));
    $medicalPersonelGender   = trim((string)($_POST['medicalPersonelGender'] ?? ''));
    $medicalPersonelEmail    = trim((string)($_POST['medicalPersonelEmail'] ?? ''));
    $medicalPersonelPhone    = trim((string)($_POST['medicalPersonelPhone'] ?? ''));
    $medicalPersonelAddress  = trim((string)($_POST['medicalPersonelAddress'] ?? ''));
    $medicalPersonelStatus   = trim((string)($_POST['medicalPersonelStatus'] ?? ''));

    // VALIDASI ID
    if ($medicalPersonelId === false || $medicalPersonelId === null) {
        responseNakesEdit('error', 'ID tenaga kesehatan tidak valid.');
    }

    // VALIDASI FIELD WAJIB
    if ($medicalPersonelCode === '') {
        responseNakesEdit('error', 'Kode tenaga kesehatan tidak boleh kosong.');
    }

    if ($medicalPersonelName === '') {
        responseNakesEdit('error', 'Nama tenaga kesehatan tidak boleh kosong.');
    }

    if ($medicalPersonelNik === '') {
        responseNakesEdit('error', 'NIK tenaga kesehatan tidak boleh kosong.');
    }

    if ($medicalPersonelCategory === '') {
        responseNakesEdit('error', 'Kategori tenaga kesehatan tidak boleh kosong.');
    }

    if ($medicalPersonelGender === '') {
        responseNakesEdit('error', 'Jenis kelamin tidak boleh kosong.');
    }

    if ($medicalPersonelStatus === '') {
        responseNakesEdit('error', 'Status tenaga kesehatan tidak boleh kosong.');
    }

    // VALIDASI NIK
    if (strlen($medicalPersonelNik) !== 16) {
        responseNakesEdit('error', 'NIK tenaga kesehatan harus terdiri dari 16 digit.');
    }

    // VALIDASI KATEGORI
    $allowedCategory = [
        'Dokter Umum',
        'Dokter Spesialis',
        'Perawat',
        'Bidan',
        'Rekam Medis',
        'Administrasi',
        'Apoteker',
        'Analis Laboratorium',
        'Radiografer',
        'Terapis',
        'Gizi',
        'Penata Anestesi',
        'Elektromedis',
        'Sanitarian',
        'Epidemiolog',
        'Kesehatan Lingkungan',
        'Kesehatan Masyarakat'
    ];

    if (!in_array($medicalPersonelCategory, $allowedCategory, true)) {
        responseNakesEdit('error', 'Kategori tenaga kesehatan tidak valid.');
    }

    // VALIDASI GENDER
    if (!in_array($medicalPersonelGender, ['Male', 'Female'], true)) {
        responseNakesEdit('error', 'Jenis kelamin tidak valid.');
    }

    // VALIDASI STATUS
    if (!in_array($medicalPersonelStatus, ['Active', 'Inactive'], true)) {
        responseNakesEdit('error', 'Status tenaga kesehatan tidak valid.');
    }

    // VALIDASI EMAIL
    if ($medicalPersonelEmail !== '' && !filter_var($medicalPersonelEmail, FILTER_VALIDATE_EMAIL)) {
        responseNakesEdit('error', 'Format alamat email tidak valid.');
    }

    // VALIDASI TELEPON
    if ($medicalPersonelPhone !== '' && strlen($medicalPersonelPhone) > 20) {
        responseNakesEdit('error', 'Nomor telepon maksimal 20 karakter.');
    }

    // PASTIKAN DATA NAKES ADA
    $stmtCheck = $Conn->prepare("
        SELECT medicalPersonelId
        FROM medical_personel
        WHERE medicalPersonelId = ?
        LIMIT 1
    ");

    if ($stmtCheck === false) {
        responseNakesEdit('error', 'Gagal menyiapkan validasi data tenaga kesehatan.');
    }

    $stmtCheck->bind_param('i', $medicalPersonelId);

    if (!$stmtCheck->execute()) {
        $stmtCheck->close();
        responseNakesEdit('error', 'Gagal memvalidasi data tenaga kesehatan.');
    }

    $resultCheck = $stmtCheck->get_result();
    $dataExists  = $resultCheck && $resultCheck->num_rows > 0;
    $stmtCheck->close();

    if (!$dataExists) {
        responseNakesEdit('error', 'Data tenaga kesehatan tidak ditemukan.');
    }

    // CEK DUPLIKASI KODE
    $stmtDuplicate = $Conn->prepare("
        SELECT medicalPersonelId
        FROM medical_personel
        WHERE medicalPersonelCode = ?
          AND medicalPersonelId <> ?
        LIMIT 1
    ");

    if ($stmtDuplicate === false) {
        responseNakesEdit('error', 'Gagal menyiapkan validasi kode tenaga kesehatan.');
    }

    $stmtDuplicate->bind_param('si', $medicalPersonelCode, $medicalPersonelId);

    if (!$stmtDuplicate->execute()) {
        $stmtDuplicate->close();
        responseNakesEdit('error', 'Gagal memvalidasi kode tenaga kesehatan.');
    }

    $duplicateResult = $stmtDuplicate->get_result();
    $isDuplicateCode = $duplicateResult && $duplicateResult->num_rows > 0;
    $stmtDuplicate->close();

    if ($isDuplicateCode) {
        responseNakesEdit('error', 'Kode tenaga kesehatan sudah digunakan oleh data lain.');
    }

    // CEK DUPLIKASI NIK
    $stmtNik = $Conn->prepare("
        SELECT medicalPersonelId
        FROM medical_personel
        WHERE medicalPersonelNik = ?
          AND medicalPersonelId <> ?
        LIMIT 1
    ");

    if ($stmtNik === false) {
        responseNakesEdit('error', 'Gagal menyiapkan validasi NIK.');
    }

    $stmtNik->bind_param('si', $medicalPersonelNik, $medicalPersonelId);

    if (!$stmtNik->execute()) {
        $stmtNik->close();
        responseNakesEdit('error', 'Gagal memvalidasi NIK tenaga kesehatan.');
    }

    $nikResult = $stmtNik->get_result();
    $isDuplicateNik = $nikResult && $nikResult->num_rows > 0;
    $stmtNik->close();

    if ($isDuplicateNik) {
        responseNakesEdit('error', 'NIK tenaga kesehatan sudah digunakan oleh data lain.');
    }

    // CEK DUPLIKASI PRACTITIONER
    if ($idPractitioner !== '') {
        $stmtPractitioner = $Conn->prepare("
            SELECT medicalPersonelId
            FROM medical_personel
            WHERE id_practitioner = ?
              AND medicalPersonelId <> ?
            LIMIT 1
        ");

        if ($stmtPractitioner === false) {
            responseNakesEdit('error', 'Gagal menyiapkan validasi ID Practitioner.');
        }

        $stmtPractitioner->bind_param('si', $idPractitioner, $medicalPersonelId);

        if (!$stmtPractitioner->execute()) {
            $stmtPractitioner->close();
            responseNakesEdit('error', 'Gagal memvalidasi ID Practitioner.');
        }

        $practitionerResult = $stmtPractitioner->get_result();
        $isDuplicatePractitioner = $practitionerResult && $practitionerResult->num_rows > 0;
        $stmtPractitioner->close();

        if ($isDuplicatePractitioner) {
            responseNakesEdit('error', 'ID Practitioner SATUSEHAT sudah digunakan oleh tenaga kesehatan lain.');
        }
    }

    // CEK DUPLIKASI EMAIL
    if ($medicalPersonelEmail !== '') {
        $stmtEmail = $Conn->prepare("
            SELECT medicalPersonelId
            FROM medical_personel
            WHERE medicalPersonelEmail = ?
              AND medicalPersonelId <> ?
            LIMIT 1
        ");

        if ($stmtEmail === false) {
            responseNakesEdit('error', 'Gagal menyiapkan validasi email.');
        }

        $stmtEmail->bind_param('si', $medicalPersonelEmail, $medicalPersonelId);

        if (!$stmtEmail->execute()) {
            $stmtEmail->close();
            responseNakesEdit('error', 'Gagal memvalidasi email tenaga kesehatan.');
        }

        $emailResult = $stmtEmail->get_result();
        $isDuplicateEmail = $emailResult && $emailResult->num_rows > 0;
        $stmtEmail->close();

        if ($isDuplicateEmail) {
            responseNakesEdit('error', 'Alamat email sudah digunakan oleh tenaga kesehatan lain.');
        }
    }

    // NILAI OPTIONAL MENJADI NULL
    $idPractitionerValue = $idPractitioner === '' ? null : $idPractitioner;
    $emailValue          = $medicalPersonelEmail === '' ? null : $medicalPersonelEmail;
    $phoneValue          = $medicalPersonelPhone === '' ? null : $medicalPersonelPhone;
    $addressValue        = $medicalPersonelAddress === '' ? null : $medicalPersonelAddress;

    // DATA AUDIT
    $now        = date('Y-m-d H:i:s');
    $accessId   = (int)$SessionIdAkses;
    $accessName = trim((string)($SessionNama ?? ''));

    // TRANSACTION
    $Conn->begin_transaction();

    try {
        // UPDATE NAKES
        $stmtUpdate = $Conn->prepare("
            UPDATE medical_personel SET
                medicalPersonelCode     = ?,
                id_practitioner          = ?,
                medicalPersonelCategory = ?,
                medicalPersonelNik      = ?,
                medicalPersonelName     = ?,
                medicalPersonelGender   = ?,
                medicalPersonelEmail    = ?,
                medicalPersonelPhone    = ?,
                medicalPersonelAddress  = ?,
                medicalPersonelStatus   = ?,
                update_by_id             = ?,
                update_by_name           = ?,
                update_at                = ?
            WHERE medicalPersonelId = ?
            LIMIT 1
        ");

        if ($stmtUpdate === false) {
            throw new Exception('Gagal menyiapkan proses update tenaga kesehatan.');
        }

        $stmtUpdate->bind_param(
            'ssssssssssissi',
            $medicalPersonelCode,
            $idPractitionerValue,
            $medicalPersonelCategory,
            $medicalPersonelNik,
            $medicalPersonelName,
            $medicalPersonelGender,
            $emailValue,
            $phoneValue,
            $addressValue,
            $medicalPersonelStatus,
            $SessionIdAkses,
            $SessionNama,
            $now,
            $medicalPersonelId
        );

        if (!$stmtUpdate->execute()) {
            $errorUpdate = $stmtUpdate->error;
            $stmtUpdate->close();
            throw new Exception('Data tenaga kesehatan gagal diperbarui. '.$errorUpdate);
        }

        $affectedRows = $stmtUpdate->affected_rows;
        $stmtUpdate->close();

        // COMMIT
        $Conn->commit();

        // RESPONSE SUCCESS
        responseNakesEdit(
            'success',
            $affectedRows > 0
                ? 'Data tenaga kesehatan berhasil diperbarui.'
                : 'Tidak ada perubahan data.',
            [
                'medicalPersonelId' => $medicalPersonelId,
                'id_practitioner'   => $idPractitionerValue
            ]
        );

    } catch (Throwable $e) {
        // ROLLBACK
        $Conn->rollback();
        responseNakesEdit('error', $e->getMessage());
    }
?>