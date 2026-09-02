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
    function responseNakesTambah(string $status, string $message, array $metadata = []): void {
        echo json_encode([
            'status'   => $status,
            'message'  => $message,
            'metadata' => $metadata
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // VALIDASI SESSION
    if (empty($SessionIdAkses)) {
        responseNakesTambah('error', 'Sesi akses telah berakhir. Silakan login ulang.');
    }

    // VALIDASI METHOD
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseNakesTambah('error', 'Metode request tidak valid.');
    }

    // TANGKAP INPUT
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

    // VALIDASI WAJIB
    if ($medicalPersonelCode === '') {
        responseNakesTambah('error', 'Kode tenaga kesehatan tidak boleh kosong.');
    }
    if ($medicalPersonelCategory === '') {
        responseNakesTambah('error', 'Kategori tenaga kesehatan tidak boleh kosong.');
    }
    if ($medicalPersonelName === '') {
        responseNakesTambah('error', 'Nama tenaga kesehatan tidak boleh kosong.');
    }
    if ($medicalPersonelGender === '') {
        responseNakesTambah('error', 'Jenis kelamin tidak boleh kosong.');
    }
    if ($medicalPersonelStatus === '') {
        responseNakesTambah('error', 'Status tenaga kesehatan tidak boleh kosong.');
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
        responseNakesTambah('error', 'Kategori tenaga kesehatan tidak valid.');
    }

    // VALIDASI GENDER
    if (!in_array($medicalPersonelGender, ['Male', 'Female'], true)) {
        responseNakesTambah('error', 'Jenis kelamin tidak valid.');
    }

    // VALIDASI STATUS
    if (!in_array($medicalPersonelStatus, ['Active', 'Inactive'], true)) {
        responseNakesTambah('error', 'Status tenaga kesehatan tidak valid.');
    }

    // VALIDASI NIK
    if ($medicalPersonelNik !== '' && strlen($medicalPersonelNik) !== 16) {
        responseNakesTambah('error', 'NIK tenaga kesehatan harus terdiri dari 16 digit.');
    }

    // VALIDASI EMAIL
    if ($medicalPersonelEmail !== '' && !filter_var($medicalPersonelEmail, FILTER_VALIDATE_EMAIL)) {
        responseNakesTambah('error', 'Format alamat email tidak valid.');
    }

    // VALIDASI PANJANG TELEPON
    if ($medicalPersonelPhone !== '' && strlen($medicalPersonelPhone) > 20) {
        responseNakesTambah('error', 'Nomor telepon maksimal 20 karakter.');
    }

    // CEK DUPLIKASI KODE NAKES
    $stmtDuplicate = $Conn->prepare("SELECT medicalPersonelId FROM medical_personel WHERE medicalPersonelCode = ? LIMIT 1");
    if ($stmtDuplicate === false) {
        responseNakesTambah('error', 'Gagal mempersiapkan validasi kode tenaga kesehatan.');
    }

    $stmtDuplicate->bind_param('s', $medicalPersonelCode);

    if (!$stmtDuplicate->execute()) {
        $stmtDuplicate->close();
        responseNakesTambah('error', 'Gagal memvalidasi kode tenaga kesehatan.');
    }

    $duplicateResult = $stmtDuplicate->get_result();
    $isDuplicate = ($duplicateResult !== false && $duplicateResult->num_rows > 0);
    $stmtDuplicate->close();

    if ($isDuplicate) {
        responseNakesTambah('error', 'Kode tenaga kesehatan sudah terdaftar.');
    }

    // CEK DUPLIKASI ID PRACTITIONER
    if ($idPractitioner !== '') {
        $stmtPractitioner = $Conn->prepare("SELECT medicalPersonelId FROM medical_personel WHERE id_practitioner = ? LIMIT 1");
        if ($stmtPractitioner === false) {
            responseNakesTambah('error', 'Gagal mempersiapkan validasi ID Practitioner.');
        }

        $stmtPractitioner->bind_param('s', $idPractitioner);

        if (!$stmtPractitioner->execute()) {
            $stmtPractitioner->close();
            responseNakesTambah('error', 'Gagal memvalidasi ID Practitioner.');
        }

        $practitionerResult = $stmtPractitioner->get_result();
        $isPractitionerDuplicate = ($practitionerResult !== false && $practitionerResult->num_rows > 0);
        $stmtPractitioner->close();

        if ($isPractitionerDuplicate) {
            responseNakesTambah('error', 'ID Practitioner SATUSEHAT sudah digunakan oleh tenaga kesehatan lain.');
        }
    }

    // CEK DUPLIKASI NIK
    if ($medicalPersonelNik !== '') {
        $stmtNik = $Conn->prepare("SELECT medicalPersonelId FROM medical_personel WHERE medicalPersonelNik = ? LIMIT 1");
        if ($stmtNik === false) {
            responseNakesTambah('error', 'Gagal mempersiapkan validasi NIK.');
        }

        $stmtNik->bind_param('s', $medicalPersonelNik);

        if (!$stmtNik->execute()) {
            $stmtNik->close();
            responseNakesTambah('error', 'Gagal memvalidasi NIK tenaga kesehatan.');
        }

        $nikResult = $stmtNik->get_result();
        $isNikDuplicate = ($nikResult !== false && $nikResult->num_rows > 0);
        $stmtNik->close();

        if ($isNikDuplicate) {
            responseNakesTambah('error', 'NIK tenaga kesehatan sudah terdaftar.');
        }
    }

    // CEK DUPLIKASI EMAIL
    if ($medicalPersonelEmail !== '') {
        $stmtEmail = $Conn->prepare("SELECT medicalPersonelId FROM medical_personel WHERE medicalPersonelEmail = ? LIMIT 1");
        if ($stmtEmail === false) {
            responseNakesTambah('error', 'Gagal mempersiapkan validasi email.');
        }

        $stmtEmail->bind_param('s', $medicalPersonelEmail);

        if (!$stmtEmail->execute()) {
            $stmtEmail->close();
            responseNakesTambah('error', 'Gagal memvalidasi email tenaga kesehatan.');
        }

        $emailResult = $stmtEmail->get_result();
        $isEmailDuplicate = ($emailResult !== false && $emailResult->num_rows > 0);
        $stmtEmail->close();

        if ($isEmailDuplicate) {
            responseNakesTambah('error', 'Alamat email sudah digunakan oleh tenaga kesehatan lain.');
        }
    }

    // NILAI NULL UNTUK DATA OPSIONAL
    $idPractitionerValue = ($idPractitioner === '') ? null : $idPractitioner;
    $nikValue            = ($medicalPersonelNik === '') ? null : $medicalPersonelNik;
    $emailValue          = ($medicalPersonelEmail === '') ? null : $medicalPersonelEmail;
    $phoneValue          = ($medicalPersonelPhone === '') ? null : $medicalPersonelPhone;
    $addressValue        = ($medicalPersonelAddress === '') ? null : $medicalPersonelAddress;

    // DATA AUDIT
    $now        = date('Y-m-d H:i:s');
    $accessId   = (int)$SessionIdAkses;
    $accessName = trim((string)($SessionNama ?? ''));

    // MULAI TRANSACTION
    $Conn->begin_transaction();

    try {
        // INSERT TENAGA KESEHATAN
        $stmtInsert = $Conn->prepare("
            INSERT INTO medical_personel (
                medicalPersonelCode,
                id_practitioner,
                medicalPersonelCategory,
                medicalPersonelNik,
                medicalPersonelName,
                medicalPersonelGender,
                medicalPersonelEmail,
                medicalPersonelPhone,
                medicalPersonelAddress,
                medicalPersonelStatus,
                creat_by_id,
                creat_by_name,
                creat_at,
                update_by_id,
                update_by_name,
                update_at
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if ($stmtInsert === false) {
            throw new Exception('Gagal mempersiapkan penyimpanan tenaga kesehatan.');
        }

        $stmtInsert->bind_param(
            'ssssssssssississ',
            $medicalPersonelCode,
            $idPractitionerValue,
            $medicalPersonelCategory,
            $nikValue,
            $medicalPersonelName,
            $medicalPersonelGender,
            $emailValue,
            $phoneValue,
            $addressValue,
            $medicalPersonelStatus,
            $SessionIdAkses,
            $SessionNama,
            $now,
            $SessionIdAkses,
            $SessionNama,
            $now
        );

        if (!$stmtInsert->execute()) {
            $errorInsert = $stmtInsert->error;
            $stmtInsert->close();
            throw new Exception('Tenaga kesehatan gagal disimpan. '.$errorInsert);
        }

        $medicalPersonelId = $stmtInsert->insert_id;
        $stmtInsert->close();

        // COMMIT
        $Conn->commit();

        // RESPONSE SUCCESS
        responseNakesTambah(
            'success',
            'Data tenaga kesehatan berhasil disimpan.',
            [
                'medicalPersonelId' => $medicalPersonelId,
                'id_practitioner'   => $idPractitionerValue
            ]
        );

    } catch (Throwable $e) {
        // ROLLBACK
        $Conn->rollback();
        responseNakesTambah('error', $e->getMessage());
    }
?>