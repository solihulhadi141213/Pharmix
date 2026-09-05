<?php
    //------------------------------------------
    // Koneksi, Session dan Helper
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    include "../../_Config/FungsiAkses.php";

    //------------------------------------------
    // Default JSON Response
    header('Content-Type: application/json; charset=utf-8');

    //------------------------------------------
    // Default Datetime Zone
    date_default_timezone_set('Asia/Jakarta');
    $now = date('Y-m-d H:i:s');

    //------------------------------------------
    // Default Response
    $response = [
        'status'  => 'error',
        'message' => 'Terjadi kesalahan.'
    ];

    //------------------------------------------
    // Validasi Session
    if (empty($SessionIdAkses)) {
        $response['message'] = 'Sesi akses sudah berakhir.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //------------------------------------------
    // Validasi Method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response['message'] = 'Metode request tidak valid.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //------------------------------------------
    // Tangkap Parameter
    $kategori_resep     = trim($_POST['kategori_resep'] ?? '');
    $sumber_resep       = trim($_POST['sumber_resep'] ?? '');
    $id_anggota         = (int) ($_POST['id_anggota'] ?? 0);
    $id_kunjungan       = (int) ($_POST['id_kunjungan'] ?? 0);
    $priority           = trim($_POST['priority'] ?? '');
    $date_creat         = trim($_POST['date_creat'] ?? '');
    $time_creat         = trim($_POST['time_creat'] ?? '');
    $dokter_id          = (int) ($_POST['dokter_id'] ?? 0);
    $reason_code        = trim($_POST['reason_code'] ?? '');
    $reason_display     = trim($_POST['reason_display'] ?? '');
    $reason_system      = trim($_POST['reason_system'] ?? '');
    $apoteker_id        = (int) ($_POST['apoteker_id'] ?? 0);
    $no_resep_nasional  = trim($_POST['no_resep_nasional'] ?? '');
    $status_resep       = trim($_POST['status_resep'] ?? '');

    //------------------------------------------
    // Validasi Kategori Resep
    if (!in_array($kategori_resep, ['Keluar', 'Masuk'], true)) {
        $response['message'] = 'Kategori resep tidak valid.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //------------------------------------------
    // Validasi Sumber Resep
    if ($sumber_resep === '') {
        $response['message'] = 'Sumber resep tidak boleh kosong.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //------------------------------------------
    // Validasi Pasien
    if ($id_anggota < 1) {
        $response['message'] = 'Pasien belum dipilih.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //------------------------------------------
    // Validasi Priority
    if (!in_array($priority, ['routine', 'urgent', 'asap', 'stat'], true)) {
        $response['message'] = 'Priority resep tidak valid.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //------------------------------------------
    // Validasi Tanggal & Waktu
    if ($date_creat === '' || $time_creat === '') {
        $response['message'] = 'Tanggal dan jam resep tidak boleh kosong.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $datetime_creat = DateTime::createFromFormat(
        'Y-m-d H:i',
        $date_creat . ' ' . $time_creat
    );

    if (!$datetime_creat || $datetime_creat->format('Y-m-d H:i') !== $date_creat . ' ' . $time_creat) {
        $response['message'] = 'Format tanggal atau jam resep tidak valid.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $datetime_creat = $datetime_creat->format('Y-m-d H:i:s');

    //------------------------------------------
    // Validasi Status Resep
    $statusValid = [
        'Draft',
        'Verified',
        'Partially',
        'Completed',
        'Cancelled'
    ];

    if (!in_array($status_resep, $statusValid, true)) {
        $response['message'] = 'Status resep tidak valid.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //------------------------------------------
    // Ambil Data Pasien
    $stmt = $Conn->prepare("
        SELECT nama
        FROM anggota
        WHERE id_anggota = ?
        LIMIT 1
    ");

    if (!$stmt) {
        $response['message'] = 'Gagal mempersiapkan data pasien.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt->bind_param("i", $id_anggota);
    $stmt->execute();

    $result = $stmt->get_result();
    $dataPasien = $result->fetch_assoc();
    $stmt->close();

    if (!$dataPasien) {
        $response['message'] = 'Data pasien tidak ditemukan.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $nama_pasien = trim($dataPasien['nama'] ?? '');

    //------------------------------------------
    // Validasi Kunjungan
    if ($id_kunjungan > 0) {
        $stmt = $Conn->prepare("
            SELECT id_kunjungan
            FROM kunjungan
            WHERE id_kunjungan = ?
            AND id_anggota = ?
            LIMIT 1
        ");

        if (!$stmt) {
            $response['message'] = 'Gagal mempersiapkan data kunjungan.';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt->bind_param("ii", $id_kunjungan, $id_anggota);
        $stmt->execute();

        $result = $stmt->get_result();
        $dataKunjungan = $result->fetch_assoc();
        $stmt->close();

        if (!$dataKunjungan) {
            $response['message'] = 'Data kunjungan tidak sesuai dengan pasien.';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }
    } else {
        $id_kunjungan = null;
    }

    //------------------------------------------
    // Default Data Dokter
    $dokter_code = null;
    $dokter_ihs  = null;
    $dokter_nama = '';

    //------------------------------------------
    // Ambil Data Dokter
    if ($dokter_id > 0) {
        $stmt = $Conn->prepare("
            SELECT
                medicalPersonelCode,
                id_practitioner,
                medicalPersonelName,
                medicalPersonelCategory
            FROM medical_personel
            WHERE medicalPersonelId = ?
            AND medicalPersonelStatus = 'Active'
            LIMIT 1
        ");

        if (!$stmt) {
            $response['message'] = 'Gagal mempersiapkan data dokter.';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt->bind_param("i", $dokter_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $dataDokter = $result->fetch_assoc();
        $stmt->close();

        if (!$dataDokter) {
            $response['message'] = 'Data dokter tidak ditemukan.';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!in_array($dataDokter['medicalPersonelCategory'], ['Dokter Umum', 'Dokter Spesialis'], true)) {
            $response['message'] = 'Tenaga medis yang dipilih bukan dokter.';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }

        $dokter_code = trim($dataDokter['medicalPersonelCode'] ?? '');
        $dokter_ihs  = trim($dataDokter['id_practitioner'] ?? '');
        $dokter_nama = trim($dataDokter['medicalPersonelName'] ?? '');
    } else {
        $dokter_id = null;
    }

    //------------------------------------------
    // Validasi Reason
    if ($reason_code !== '') {
        $stmt = $Conn->prepare("
            SELECT kode, long_des
            FROM icd
            WHERE kode = ?
            AND icd = 'ICD10'
            LIMIT 1
        ");

        if (!$stmt) {
            $response['message'] = 'Gagal mempersiapkan data diagnosis.';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt->bind_param("s", $reason_code);
        $stmt->execute();

        $result = $stmt->get_result();
        $dataIcd = $result->fetch_assoc();
        $stmt->close();

        if (!$dataIcd) {
            $response['message'] = 'Kode diagnosis ICD-10 tidak ditemukan.';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }

        $reason_code    = trim($dataIcd['kode']);
        $reason_display = trim($dataIcd['long_des']);
        $reason_system  = 'http://hl7.org/fhir/sid/icd-10';
    } else {
        $reason_code    = null;
        $reason_display = null;
        $reason_system  = null;
    }

    //------------------------------------------
    // Default Data Apoteker
    $apoteker_code = null;
    $apoteker_nama = null;
    $apoteker_ihs  = null;

    //------------------------------------------
    // Ambil Data Apoteker
    if ($apoteker_id > 0) {
        $stmt = $Conn->prepare("
            SELECT
                medicalPersonelCode,
                id_practitioner,
                medicalPersonelName,
                medicalPersonelCategory
            FROM medical_personel
            WHERE medicalPersonelId = ?
            AND medicalPersonelStatus = 'Active'
            LIMIT 1
        ");

        if (!$stmt) {
            $response['message'] = 'Gagal mempersiapkan data apoteker.';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt->bind_param("i", $apoteker_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $dataApoteker = $result->fetch_assoc();
        $stmt->close();

        if (!$dataApoteker) {
            $response['message'] = 'Data apoteker tidak ditemukan.';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($dataApoteker['medicalPersonelCategory'] !== 'Apoteker') {
            $response['message'] = 'Tenaga medis yang dipilih bukan apoteker.';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }

        $apoteker_code = trim($dataApoteker['medicalPersonelCode'] ?? '');
        $apoteker_nama = trim($dataApoteker['medicalPersonelName'] ?? '');
        $apoteker_ihs  = trim($dataApoteker['id_practitioner'] ?? '');
    } else {
        $apoteker_id = null;
    }

    //------------------------------------------
    // Normalisasi NRN
    if ($no_resep_nasional === '') {
        $no_resep_nasional = null;
    }

    //------------------------------------------
    // Tentukan Datetime Status
    $datetime_verified  = null;
    $datetime_completed = null;

    if (in_array($status_resep, ['Verified', 'Partially', 'Completed'], true)) {
        $datetime_verified = $now;
    }

    if ($status_resep === 'Completed') {
        $datetime_completed = $now;
    }

    //------------------------------------------
    // Mulai Transaction
    $Conn->begin_transaction();

    try {

        //------------------------------------------
        // Insert Medication Request Group
        $sql = "
            INSERT INTO medication_request_group (
                id_anggota,
                id_kunjungan,
                nama_pasien,
                priority,
                datetime_creat,
                datetime_verified,
                datetime_completed,
                dokter_id,
                dokter_code,
                dokter_ihs,
                dokter_nama,
                reason_code,
                reason_display,
                reason_system,
                apoteker_id,
                apoteker_code,
                apoteker_nama,
                apoteker_ihs,
                sumber_resep,
                status_resep,
                no_resep_nasional,
                creat_at,
                creat_by_id,
                creat_by_name,
                update_at,
                update_by_id,
                update_by_name
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?
            )
        ";

        $stmt = $Conn->prepare($sql);

        if (!$stmt) {
            throw new Exception('Gagal mempersiapkan proses penyimpanan resep.');
        }

        $stmt->bind_param(
            "iisssssissssssisssssssisisi",
            $id_anggota,
            $id_kunjungan,
            $nama_pasien,
            $priority,
            $datetime_creat,
            $datetime_verified,
            $datetime_completed,
            $dokter_id,
            $dokter_code,
            $dokter_ihs,
            $dokter_nama,
            $reason_code,
            $reason_display,
            $reason_system,
            $apoteker_id,
            $apoteker_code,
            $apoteker_nama,
            $apoteker_ihs,
            $sumber_resep,
            $status_resep,
            $no_resep_nasional,
            $now,
            $SessionIdAkses,
            $SessionNama,
            $now,
            $SessionIdAkses,
            $SessionNama
        );

        if (!$stmt->execute()) {
            throw new Exception('Gagal menyimpan data resep.');
        }

        $id_medication_request_group = $stmt->insert_id;
        $stmt->close();

        //------------------------------------------
        // Commit
        $Conn->commit();

        //------------------------------------------
        // Response Success
        $response = [
            'status'  => 'success',
            'message' => 'Data resep berhasil disimpan.',
            'data'    => [
                'id_medication_request_group' => $id_medication_request_group
            ]
        ];

    } catch (Throwable $e) {

        //------------------------------------------
        // Rollback
        $Conn->rollback();

        $response['message'] = $e->getMessage();
    }

    //------------------------------------------
    // Response
    echo json_encode(
        $response,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
?>