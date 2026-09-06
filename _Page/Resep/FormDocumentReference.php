<?php
    // CONNECTION, HELPER, SESSION & SETTING
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    include "../../_Config/SettingGeneral.php";

    date_default_timezone_set('Asia/Jakarta');
    header('Content-Type: application/json; charset=utf-8');

    function responseDR(string $status, string $message, string $html = ''): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'html'    => $html
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    function escDR($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    function readonlyDR(string $label, $value, bool $mandatory = false): string {
        $value = trim((string)($value ?? ''));
        $empty = $value === '';
        $class = ($mandatory && $empty) ? 'border-danger text-danger' : '';
        $mark  = $mandatory ? ' <span class="text-danger">*</span>' : '';

        return '
            <div class="row mb-2">
                <div class="col-md-4">
                    <label><small>'.$label.$mark.'</small></label>
                </div>
                <div class="col-md-8">
                    <input type="text" class="form-control form-control-sm '.$class.'"
                        value="'.escDR($empty ? '-' : $value).'" readonly>
                </div>
            </div>
        ';
    }

    // VALIDASI SESSION & METHOD
    if (empty($SessionIdAkses)) {
        responseDR('error', 'Sesi akses telah berakhir. Silakan login ulang.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseDR('error', 'Metode request tidak valid.');
    }

    // TANGKAP GROUP
    $id_medication_request_group = (int)($_POST['id_medication_request_group'] ?? 0);

    if ($id_medication_request_group < 1) {
        responseDR('error', 'ID Resep tidak valid.');
    }

    // AMBIL GROUP + PASIEN + KUNJUNGAN
    $stmt = $Conn->prepare("
        SELECT
            mrg.*,
            a.id_pasien,
            a.id_ihs AS patient_ihs,
            a.nama AS patient_name,
            k.id_encounter,
            k.tanggal_kunjungan,
            k.jenis_kunjungan
        FROM medication_request_group mrg
        LEFT JOIN anggota a
            ON a.id_anggota = mrg.id_anggota
        LEFT JOIN kunjungan k
            ON k.id_kunjungan = mrg.id_kunjungan
        WHERE mrg.id_medication_request_group = ?
        LIMIT 1
    ");

    if (!$stmt) {
        responseDR('error', 'Gagal mempersiapkan data resep.');
    }

    $stmt->bind_param("i", $id_medication_request_group);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        responseDR('error', 'Gagal membuka data resep.<br>Keterangan : '.escDR($error));
    }

    $group = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$group) {
        responseDR('error', 'Data resep tidak ditemukan.');
    }

    // JIKA SUDAH PUNYA DOCUMENT REFERENCE
    if (!empty($group['id_document_reference'])) {
        responseDR(
            'error',
            'Resep ini sudah mempunyai ID DocumentReference SATUSEHAT.'
        );
    }

    // KONFIGURASI SATUSEHAT
    $stmt = $Conn->prepare("
        SELECT organization_id
        FROM connection_satu_sehat
        WHERE status_connection_satu_sehat = 1
        LIMIT 1
    ");

    if (!$stmt) {
        responseDR('error', 'Gagal membuka konfigurasi SATUSEHAT.');
    }

    if (!$stmt->execute()) {
        $stmt->close();
        responseDR('error', 'Gagal membaca konfigurasi SATUSEHAT.');
    }

    $connection = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $organization_id = trim((string)($connection['organization_id'] ?? ''));

    // MAPPING GROUP
    $patient_ihs  = trim((string)($group['patient_ihs'] ?? ''));
    $patient_name = trim((string)($group['patient_name'] ?? $group['nama_pasien'] ?? ''));
    $id_pasien    = trim((string)($group['id_pasien'] ?? ''));

    $id_kunjungan = trim((string)($group['id_kunjungan'] ?? ''));
    $id_encounter = trim((string)($group['id_encounter'] ?? ''));

    $dokter_ihs  = trim((string)($group['dokter_ihs'] ?? ''));
    $dokter_nama = trim((string)($group['dokter_nama'] ?? ''));

    $datetime_creat = trim((string)($group['datetime_creat'] ?? ''));

    // FORMAT TANGGAL UTC
    $date_document = '';

    if ($datetime_creat !== '' && strtotime($datetime_creat) !== false) {
        try {
            $date = new DateTime($datetime_creat, new DateTimeZone('Asia/Jakarta'));
            $date->setTimezone(new DateTimeZone('UTC'));
            $date_document = $date->format('Y-m-d\TH:i:sP');
        } catch (Throwable $e) {
            $date_document = '';
        }
    }

    // AMBIL SEMUA ITEM MEDICATION REQUEST
    $stmt = $Conn->prepare("
        SELECT
            MedicationRequestId,
            id_medication_request,
            name_medication
        FROM medication_request
        WHERE id_medication_request_group = ?
        ORDER BY MedicationRequestId ASC
    ");

    if (!$stmt) {
        responseDR('error', 'Gagal mempersiapkan item resep.');
    }

    $stmt->bind_param("i", $id_medication_request_group);

    if (!$stmt->execute()) {
        $stmt->close();
        responseDR('error', 'Gagal membuka item resep.');
    }

    $resultItem = $stmt->get_result();

    $jumlah_item  = 0;
    $jumlah_kirim = 0;
    $itemHtml     = '';

    while ($item = $resultItem->fetch_assoc()) {
        $jumlah_item++;

        $MedicationRequestId = trim((string)($item['MedicationRequestId'] ?? ''));
        $idMedicationRequest = trim((string)($item['id_medication_request'] ?? ''));
        $nameMedication      = trim((string)($item['name_medication'] ?? ''));

        if ($idMedicationRequest !== '') {
            $jumlah_kirim++;

            $statusItem = '
                <span class="badge bg-success">
                    <i class="bi bi-check"></i> Terkirim
                </span>
            ';

            $reference = 'MedicationRequest/'.$idMedicationRequest;
        } else {
            $statusItem = '
                <span class="badge bg-danger">
                    <i class="bi bi-x"></i> Belum Dikirim
                </span>
            ';

            $reference = '-';
        }

        $itemHtml .= '
            <div class="card border shadow-none mb-2">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <small>'.escDR($nameMedication).'</small><br>
                            <small class="text-muted">'.escDR($MedicationRequestId).'</small><br>
                            <small class="text-muted">'.escDR($reference).'</small>
                        </div>
                        <div>'.$statusItem.'</div>
                    </div>
                </div>
            </div>
        ';
    }

    $stmt->close();

    // CEK KELENGKAPAN
    $missing = [];

    if ($organization_id === '') {
        $missing[] = 'Organization IHS ID';
    }

    if ($patient_ihs === '') {
        $missing[] = 'Patient IHS ID';
    }

    if ($id_kunjungan === '') {
        $missing[] = 'ID Kunjungan';
    } elseif ($id_encounter === '') {
        $missing[] = 'Encounter SATUSEHAT';
    }

    if ($dokter_ihs === '') {
        $missing[] = 'Practitioner IHS Dokter';
    }

    if ($date_document === '') {
        $missing[] = 'Tanggal DocumentReference';
    }

    if ($jumlah_item < 1) {
        $missing[] = 'Item resep';
    }

    if ($jumlah_item > 0 && $jumlah_item !== $jumlah_kirim) {
        $missing[] = 'Masih ada MedicationRequest yang belum dikirim ke SATUSEHAT';
    }

    // IDENTIFIER PEMBIAYAAN
    // Default dapat diubah user
    $coverage_system = 'http://terminology.kemkes.go.id/CodeSystem/coverage-type';

    // DATA FASYANKES
    $nama_faskes = trim((string)($title_page ?? ''));
    $alamat      = trim((string)($alamat_bisnis ?? ''));
    $telepon     = trim((string)($telepon_bisnis ?? ''));
    $email       = trim((string)($email_bisnis ?? ''));
    $homepage    = trim((string)($base_url ?? ''));

    // DEFAULT DESCRIPTION
    $description = 'Dokumen resep pasien '.$patient_name;

    // ALERT
    if (empty($missing)) {
        $can_submit = 1;

        $alert = '
            <div class="alert alert-success">
                <small>
                    <i class="bi bi-check-circle"></i>
                    Data utama sudah lengkap dan siap dikirim sebagai
                    <i>DocumentReference</i> ke SATUSEHAT.
                </small>
            </div>
        ';
    } else {
        $can_submit = 0;
        $list = '';

        foreach ($missing as $item) {
            $list .= '<li>'.escDR($item).'</li>';
        }

        $alert = '
            <div class="alert alert-danger">
                <small>
                    <i class="bi bi-exclamation-triangle"></i>
                    <b>DocumentReference belum siap dikirim.</b>
                </small>
                <ul class="mb-0 mt-2">'.$list.'</ul>
            </div>
        ';
    }

    // HTML FORM
    $html = '
        <input type="hidden" name="id_medication_request_group" value="'.$id_medication_request_group.'">
        <input type="hidden" name="can_submit" value="'.$can_submit.'">

        '.$alert.'

        <!-- IDENTITAS DOKUMEN -->
        <div class="card mb-3">
            <div class="card-header">
                <small><b>A. Informasi Dokumen</b></small>
            </div>
            <div class="card-body">
                '.readonlyDR('Status', 'current', true).'
                '.readonlyDR('Tanggal Dokumen', $date_document, true).'

                <div class="row mb-3 mt-3">
                    <div class="col-md-4">
                        <label for="description">
                            <small>Description</small>
                        </label>
                    </div>
                    <div class="col-md-8">
                        <textarea name="description" id="description" class="form-control" rows="3">
                        '.escDR($description).'
                        </textarea>
                        <small class="text-muted">
                            Catatan atau instruksi tambahan terkait dokumen resep.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- PEMBIAYAAN -->
        <div class="card mb-3">
            <div class="card-header">
                <small><b>B. Informasi Pembiayaan</b></small>
            </div>
            <div class="card-body">

                '.readonlyDR(
                    'Coverage System',
                    $coverage_system,
                    true
                ).'

                <div class="row mb-2">
                    <div class="col-md-4">
                        <label for="coverage_type">
                            <small>Jenis Pembiayaan <span class="text-danger">*</span></small>
                        </label>
                    </div>
                    <div class="col-md-8">
                        <select name="coverage_type" id="coverage_type" class="form-control" required>
                            <option value="Biaya-Sendiri" selected>Biaya Sendiri</option>
                            <option value="BPJS-K">BPJS Kesehatan</option>
                            <option value="Biaya-Perusahaan">Biaya Perusahaan</option>
                            <option value="Asuransi-Swasta">Asuransi Swasta</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-4">
                        <label for="nomor_sep">
                            <small>Nomor SEP</small>
                        </label>
                    </div>
                    <div class="col-md-8">
                        <input
                            type="text"
                            name="nomor_sep"
                            id="nomor_sep"
                            class="form-control"
                            placeholder="Diisi jika pembiayaan BPJS-K"
                        >
                    </div>
                </div>

            </div>
        </div>

        <!-- PASIEN -->
        <div class="card mb-3">
            <div class="card-header">
                <small><b>C. Pasien</b></small>
            </div>
            <div class="card-body">
                '.readonlyDR('No RM', $id_pasien).'
                '.readonlyDR(
                    'Subject Reference',
                    $patient_ihs !== '' ? 'Patient/'.$patient_ihs : '',
                    true
                ).'
                '.readonlyDR('Subject Display', $patient_name, true).'
            </div>
        </div>

        <!-- AUTHOR -->
        <div class="card mb-3">
            <div class="card-header">
                <small><b>D. Author</b></small>
            </div>
            <div class="card-body">
                '.readonlyDR(
                    'Author Reference',
                    $dokter_ihs !== '' ? 'Practitioner/'.$dokter_ihs : '',
                    true
                ).'
                '.readonlyDR('Author Display', $dokter_nama, true).'
            </div>
        </div>

        <!-- ENCOUNTER -->
        <div class="card mb-3">
            <div class="card-header">
                <small><b>E. Encounter</b></small>
            </div>
            <div class="card-body">
                '.readonlyDR('ID Kunjungan Lokal', $id_kunjungan, true).'
                '.readonlyDR(
                    'Encounter Reference',
                    $id_encounter !== '' ? 'Encounter/'.$id_encounter : '',
                    true
                ).'
                '.readonlyDR(
                    'Tanggal Kunjungan',
                    $group['tanggal_kunjungan'] ?? ''
                ).'
                '.readonlyDR(
                    'Jenis Kunjungan',
                    $group['jenis_kunjungan'] ?? ''
                ).'
            </div>
        </div>

        <!-- CUSTODIAN -->
        <div class="card mb-3">
            <div class="card-header">
                <small><b>F. Custodian / Fasyankes</b></small>
            </div>
            <div class="card-body">
                '.readonlyDR(
                    'Organization Reference',
                    $organization_id !== '' ? 'Organization/'.$organization_id : '',
                    true
                ).'
                '.readonlyDR('Nama Fasyankes', $nama_faskes, true).'
                '.readonlyDR('Alamat', $alamat).'
                '.readonlyDR('Telepon', $telepon).'
                '.readonlyDR('Email', $email).'
            </div>
        </div>

        <!-- CONTENT -->
        <div class="card mb-3">
            <div class="card-header">
                <small><b>G. Informasi Konten Dokumen</b></small>
            </div>
            <div class="card-body">

                <div class="row mb-2">
                    <div class="col-md-4">
                        <label for="homepage_url">
                            <small>Homepage Fasyankes</small>
                        </label>
                    </div>
                    <div class="col-md-8">
                        <input
                            type="url"
                            name="homepage_url"
                            id="homepage_url"
                            class="form-control"
                            value="'.escDR($homepage).'"
                        >
                        <small class="text-muted">
                            DocumentFormat DF000001
                        </small>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-4">
                        <label for="emergency_url">
                            <small>Link Kontak Emergensi</small>
                        </label>
                    </div>
                    <div class="col-md-8">
                        <input
                            type="url"
                            name="emergency_url"
                            id="emergency_url"
                            class="form-control"
                            placeholder="Opsional"
                        >
                        <small class="text-muted">
                            DocumentFormat DF000002
                        </small>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-4">
                        <label for="confirmation_url">
                            <small>Link Konfirmasi Resep</small>
                        </label>
                    </div>
                    <div class="col-md-8">
                        <input
                            type="url"
                            name="confirmation_url"
                            id="confirmation_url"
                            class="form-control"
                            placeholder="Opsional"
                        >
                        <small class="text-muted">
                            DocumentFormat DF000003
                        </small>
                    </div>
                </div>

            </div>
        </div>

        <!-- MEDICATION REQUEST -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <small><b>H. Item Medication Request</b></small>
                <small>
                    '.$jumlah_kirim.' / '.$jumlah_item.' Terkirim
                </small>
            </div>
            <div class="card-body">
                '.$itemHtml.'
            </div>
        </div>
    ';

    responseDR(
        'success',
        $can_submit === 1
            ? 'Data DocumentReference siap dikirim.'
            : 'Data DocumentReference belum lengkap.',
        $html
    );
?>