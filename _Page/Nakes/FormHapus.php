<?php
    header('Content-Type: application/json; charset=utf-8');

    // INCLUDE
    include __DIR__ . "/../../_Config/Connection.php";
    include __DIR__ . "/../../_Config/GlobalFunction.php";
    include __DIR__ . "/../../_Config/Session.php";

    date_default_timezone_set('Asia/Jakarta');

    // FUNCTION RESPONSE
    function responseNakesHapus(string $status, string $message, string $html = ''): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'html'    => $html
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // FUNCTION VALUE
    function detailValue($value): string {
        if ($value === null || trim((string)$value) === '') {
            return '-';
        }
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    // FUNCTION SENSOR NIK
    function detailNik($value): string {
        $value = trim((string)$value);

        if ($value === '') {
            return '-';
        }

        return strlen($value) > 3
            ? htmlspecialchars(substr($value, 0, -3).'***', ENT_QUOTES, 'UTF-8')
            : htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    // VALIDASI SESSION
    if (empty($SessionIdAkses)) {
        responseNakesHapus('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
    }

    // VALIDASI METHOD
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseNakesHapus('error', 'Metode request tidak valid.');
    }

    // VALIDASI ID NAKES
    $medicalPersonelId = filter_var(
        $_POST['medicalPersonelId'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($medicalPersonelId === false || $medicalPersonelId === null) {
        responseNakesHapus('error', 'ID tenaga kesehatan tidak valid.');
    }

    // BUKA DATA NAKES
    $stmt = $Conn->prepare("
        SELECT
            medicalPersonelId,
            medicalPersonelCode,
            id_practitioner,
            medicalPersonelCategory,
            medicalPersonelNik,
            medicalPersonelName,
            medicalPersonelGender,
            medicalPersonelEmail,
            medicalPersonelPhone,
            medicalPersonelStatus,
            id_akses
        FROM medical_personel
        WHERE medicalPersonelId = ?
        LIMIT 1
    ");

    if ($stmt === false) {
        responseNakesHapus('error', 'Gagal menyiapkan query data tenaga kesehatan.');
    }

    $stmt->bind_param('i', $medicalPersonelId);

    if (!$stmt->execute()) {
        $stmt->close();
        responseNakesHapus('error', 'Gagal mengambil data tenaga kesehatan.');
    }

    $result = $stmt->get_result();
    $data   = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$data) {
        responseNakesHapus('error', 'Data tenaga kesehatan tidak ditemukan.');
    }

    // STATUS BADGE
    $status = $data['medicalPersonelStatus'] ?? '';

    if ($status === 'Active') {
        $statusBadge = '<span class="badge bg-success">Active</span>';
    } elseif ($status === 'Inactive') {
        $statusBadge = '<span class="badge bg-danger">Inactive</span>';
    } else {
        $statusBadge = '<span class="badge bg-secondary">-</span>';
    }

    // GENDER
    $genderRaw = $data['medicalPersonelGender'] ?? '';

    if ($genderRaw === 'Male') {
        $gender = 'Laki-laki';
    } elseif ($genderRaw === 'Female') {
        $gender = 'Perempuan';
    } else {
        $gender = '-';
    }

    // GENERATE HTML
    $html = '
        <input type="hidden" name="medicalPersonelId" value="'.(int)$medicalPersonelId.'">

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
                <div class="col-5"><small>Nama Nakes</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <small class="text-muted">'.detailValue($data['medicalPersonelName']).'</small>
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
                <div class="col-5"><small>Jenis Kelamin</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <small class="text-muted">'.detailValue($gender).'</small>
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
                <div class="col-5"><small>Kontak</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <small class="text-muted">'.detailValue($data['medicalPersonelPhone']).'</small>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-5"><small>Status</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    '.$statusBadge.'
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert alert-warning text-center mb-0">
                        <small>
                            <b>Penting!</b><br>
                            Data yang sudah dihapus tidak dapat dikembalikan lagi.<br><br>
                            <i>Apakah Anda yakin akan menghapus tenaga kesehatan ini?</i>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    ';

    responseNakesHapus(
        'success',
        'Data tenaga kesehatan berhasil dimuat.',
        $html
    );
?>