<?php
    header('Content-Type: application/json; charset=utf-8');

    include __DIR__ . "/../../_Config/Connection.php";
    include __DIR__ . "/../../_Config/GlobalFunction.php";
    include __DIR__ . "/../../_Config/Session.php";

    date_default_timezone_set('Asia/Jakarta');

    // FUNCTION RESPONSE
    function responseNakesDetail(string $status, string $message, string $html = ''): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'html'    => $html
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // FUNCTION TAMPILKAN VALUE
    function detailValue($value): string {
        if ($value === null || trim((string)$value) === '') {
            return '-';
        }
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    // FUNCTION FORMAT TANGGAL
    function detailDate($value): string {
        if ($value === null || trim((string)$value) === '') {
            return '-';
        }

        $timestamp = strtotime((string)$value);
        return $timestamp === false
            ? detailValue($value)
            : date('d/m/Y H:i', $timestamp);
    }

    // FUNCTION SENSOR NIK
    function detailNik($value): string {
        $value = trim((string)$value);

        if ($value === '') {
            return '-';
        }

        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

        return strlen($value) > 3
            ? substr($value, 0, -3).'***'
            : str_repeat('*', strlen($value));
    }

    // VALIDASI SESSION
    if (empty($SessionIdAkses)) {
        responseNakesDetail('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
    }

    // VALIDASI ID NAKES
    $medicalPersonelId = filter_var(
        $_POST['medicalPersonelId'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($medicalPersonelId === false || $medicalPersonelId === null) {
        responseNakesDetail('error', 'ID tenaga kesehatan tidak valid.');
    }

    // BUKA DATA NAKES
    $stmt = $Conn->prepare(
        'SELECT
            medicalPersonelId,
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
            update_at,
            id_akses
        FROM medical_personel
        WHERE medicalPersonelId = ?
        LIMIT 1'
    );

    if ($stmt === false) {
        responseNakesDetail('error', 'Gagal menyiapkan query detail tenaga kesehatan.');
    }

    $stmt->bind_param('i', $medicalPersonelId);

    if (!$stmt->execute()) {
        $stmt->close();
        responseNakesDetail('error', 'Gagal mengambil detail tenaga kesehatan.');
    }

    $result = $stmt->get_result();
    $data   = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$data) {
        responseNakesDetail('error', 'Data tenaga kesehatan tidak ditemukan.');
    }

    // STATUS BADGE
    $status = $data['medicalPersonelStatus'] ?? '';
    $statusBadge = $status === 'Active'
        ? '<span class="badge bg-success">Active</span>'
        : '<span class="badge bg-danger">Inactive</span>';

    // GENDER
    $genderRaw = $data['medicalPersonelGender'] ?? '';

    if ($genderRaw === 'Male') {
        $gender = 'Laki-laki';
    } elseif ($genderRaw === 'Female') {
        $gender = 'Perempuan';
    } else {
        $gender = '-';
    }

    // CREATOR
    if (!empty($data['creat_by_id'])) {
        $creator = GetDetailData(
            $Conn,
            'akses',
            'id_akses',
            $data['creat_by_id'],
            'nama_akses'
        );
    } else {
        $creator = $data['creat_by_name'] ?? '-';
    }

    // UPDATER
    if (!empty($data['update_by_id'])) {
        $updater = GetDetailData(
            $Conn,
            'akses',
            'id_akses',
            $data['update_by_id'],
            'nama_akses'
        );
    } else {
        $updater = $data['update_by_name'] ?? '-';
    }

    // AKUN AKSES NAKES
    if (!empty($data['id_akses'])) {
        $aksesNakes = GetDetailData(
            $Conn,
            'akses',
            'id_akses',
            $data['id_akses'],
            'nama_akses'
        );

        if (empty($aksesNakes)) {
            $aksesNakes = '-';
        }
    } else {
        $aksesNakes = '-';
    }

    // GENERATE HTML
    $html = '
        <div class="container-fluid px-0">

            <div class="row mb-2">
                <div class="col-5"><small>ID Nakes</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <small class="text-muted">'.detailValue($data['medicalPersonelId']).'</small>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-5"><small>Kode Nakes</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <small class="text-muted">'.detailValue($data['medicalPersonelCode']).'</small>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-5"><small>ID Practitioner</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <small class="text-muted text-break">'.detailValue($data['id_practitioner']).'</small>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-5"><small>Kategori</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <small class="text-muted">'.detailValue($data['medicalPersonelCategory']).'</small>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-5"><small>NIK</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <small class="text-muted">'.detailNik($data['medicalPersonelNik']).'</small>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-5"><small>Nama</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <small class="text-muted">'.detailValue($data['medicalPersonelName']).'</small>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-5"><small>Jenis Kelamin</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <small class="text-muted">'.htmlspecialchars($gender, ENT_QUOTES, 'UTF-8').'</small>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-5"><small>Email</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <small class="text-muted text-break">'.detailValue($data['medicalPersonelEmail']).'</small>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-5"><small>Telepon</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <small class="text-muted">'.detailValue($data['medicalPersonelPhone']).'</small>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-5"><small>Alamat</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <small class="text-muted">'.detailValue($data['medicalPersonelAddress']).'</small>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-5"><small>Status</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">'.$statusBadge.'</div>
            </div>

            <div class="row mt-4 mb-2">
                <div class="col-12">
                    <small><b># Akun Akses</b></small>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-5"><small>Akun</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <small class="text-muted">'.detailValue($aksesNakes).'</small>
                </div>
            </div>

            <div class="row mt-4 mb-2">
                <div class="col-12">
                    <small><b># Metadata</b></small>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-5"><small>Creat At</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <small class="text-muted">'.detailDate($data['creat_at']).'</small>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-5"><small>Update At</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <small class="text-muted">'.detailDate($data['update_at']).'</small>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-5"><small>Creator</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <small class="text-muted">'.detailValue($creator).'</small>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-5"><small>Updater</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <small class="text-muted">'.detailValue($updater).'</small>
                </div>
            </div>

        </div>
    ';

    responseNakesDetail(
        'success',
        'Detail tenaga kesehatan berhasil dimuat.',
        $html
    );
?>