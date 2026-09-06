<?php
    // CONNECTION, HELPER & SESSION
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    date_default_timezone_set('Asia/Jakarta');
    header('Content-Type: application/json; charset=utf-8');

    function responseMedicationRequest(string $status, string $message, string $html = ''): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'html'    => $html
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    function escMR($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    function inputMR(string $label, $value, bool $mandatory = false): string {
        $value = trim((string)($value ?? ''));
        $empty = $value === '';
        $class = ($mandatory && $empty) ? 'border-danger text-danger' : '';
        $mark  = $mandatory ? ' <span class="text-danger">*</span>' : '';

        return '
            <div class="row mt-4 mb-3">
                <div class="col-md-4">
                    <label><small>'.$label.$mark.'</small></label>
                </div>
                <div class="col-md-8">
                    <input type="text" class="form-control form-control-sm '.$class.'" value="'.escMR($empty ? '-' : $value).'" readonly>
                </div>
            </div>
        ';
    }

    // VALIDASI SESSION & METHOD
    if (empty($SessionIdAkses)) {
        responseMedicationRequest('error', 'Sesi akses telah berakhir. Silakan login ulang.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseMedicationRequest('error', 'Metode request tidak valid.');
    }

    // ID ITEM RESEP
    $MedicationRequestId = trim((string)($_POST['MedicationRequestId'] ?? ''));

    if ($MedicationRequestId === '') {
        responseMedicationRequest('error', 'ID item resep tidak boleh kosong.');
    }

    // AMBIL MEDICATION REQUEST + GROUP + PASIEN + KUNJUNGAN
    $stmt = $Conn->prepare("
        SELECT
            mr.*,

            mrg.id_anggota,
            mrg.id_kunjungan,
            mrg.nama_pasien,
            mrg.priority,
            mrg.datetime_creat,
            mrg.dokter_id,
            mrg.dokter_code,
            mrg.dokter_ihs,
            mrg.dokter_nama,
            mrg.reason_code,
            mrg.reason_display,
            mrg.reason_system,
            mrg.no_resep_nasional,

            a.id_pasien,
            a.id_ihs AS patient_ihs,
            a.nama AS patient_name,

            k.id_encounter,
            k.tanggal_kunjungan,
            k.jenis_kunjungan,
            k.status AS encounter_status,

            med.id_medication,
            med.medication_name AS medication_name_local

        FROM medication_request mr

        INNER JOIN medication_request_group mrg
            ON mrg.id_medication_request_group = mr.id_medication_request_group

        LEFT JOIN anggota a
            ON a.id_anggota = mrg.id_anggota

        LEFT JOIN kunjungan k
            ON k.id_kunjungan = mrg.id_kunjungan

        LEFT JOIN medication med
            ON med.id_index_medication = mr.id_index_medication

        WHERE mr.MedicationRequestId = ?
        LIMIT 1
    ");

    if (!$stmt) {
        responseMedicationRequest('error', 'Gagal mempersiapkan data Medication Request.');
    }

    $stmt->bind_param("s", $MedicationRequestId);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();

        responseMedicationRequest(
            'error',
            'Gagal membuka Medication Request.<br>Keterangan : '.escMR($error)
        );
    }

    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$data) {
        responseMedicationRequest('error', 'Data Medication Request tidak ditemukan.');
    }

    // SUDAH PERNAH DIKIRIM
    if (!empty($data['id_medication_request'])) {
        responseMedicationRequest(
            'error',
            'Medication Request ini sudah memiliki ID SATUSEHAT.'
        );
    }

    // KONFIGURASI SATUSEHAT
    $stmt = $Conn->prepare("
        SELECT
            organization_id
        FROM connection_satu_sehat
        WHERE status_connection_satu_sehat = 1
        LIMIT 1
    ");

    if (!$stmt) {
        responseMedicationRequest('error', 'Gagal membuka konfigurasi SATUSEHAT.');
    }

    if (!$stmt->execute()) {
        $stmt->close();
        responseMedicationRequest('error', 'Gagal membaca konfigurasi SATUSEHAT.');
    }

    $connection = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $organization_id = trim((string)($connection['organization_id'] ?? ''));

    // MAPPING DASAR
    $id_medication_request = trim((string)($data['id_medication_request'] ?? ''));
    $status                = trim((string)($data['status'] ?? ''));
    $intent                = trim((string)($data['intent'] ?? ''));

    // IDENTIFIER
    $identifier_system = $organization_id !== ''
        ? 'http://sys-ids.kemkes.go.id/prescription-item/'.$organization_id
        : '';

    $identifier_value = $MedicationRequestId;

    // MEDICATION
    $id_medication = trim((string)($data['id_medication'] ?? ''));

    $medication_name = trim((string)(
        $data['medication_name_local']
        ?? $data['name_medication']
        ?? ''
    ));

    // PASIEN
    $patient_ihs  = trim((string)($data['patient_ihs'] ?? ''));
    $patient_name = trim((string)($data['patient_name'] ?? $data['nama_pasien'] ?? ''));
    $id_pasien    = trim((string)($data['id_pasien'] ?? ''));

    // ENCOUNTER
    // INI DIAMBIL DARI kunjungan.id_encounter
    $id_kunjungan = trim((string)($data['id_kunjungan'] ?? ''));
    $id_encounter = trim((string)($data['id_encounter'] ?? ''));

    // RESEP
    $authored_on = trim((string)($data['datetime_creat'] ?? ''));

    if ($authored_on !== '' && strtotime($authored_on) !== false) {
        $authored_on = date('Y-m-d\TH:i:sP', strtotime($authored_on));
    }

    // DOKTER
    $dokter_ihs  = trim((string)($data['dokter_ihs'] ?? ''));
    $dokter_nama = trim((string)($data['dokter_nama'] ?? ''));

    // REASON
    $reason_code    = trim((string)($data['reason_code'] ?? ''));
    $reason_display = trim((string)($data['reason_display'] ?? ''));
    $reason_system  = trim((string)($data['reason_system'] ?? ''));

    // DOSAGE
    $dosage_text = trim((string)($data['dosage_inst_text'] ?? ''));
    $frequency   = trim((string)($data['dosage_inst_frequency'] ?? ''));
    $period      = trim((string)($data['dosage_inst_period'] ?? ''));
    $period_unit = trim((string)($data['dosage_inst_period_unit'] ?? ''));

    $dose_value  = trim((string)($data['dose_value'] ?? ''));
    $dose_unit   = trim((string)($data['dose_unit'] ?? ''));
    $dose_code   = trim((string)($data['dose_code'] ?? ''));
    $dose_system = trim((string)($data['dose_system'] ?? ''));

    $route_display = trim((string)($data['route_display'] ?? ''));
    $route_code    = trim((string)($data['route_code'] ?? ''));
    $route_system  = trim((string)($data['route_system'] ?? ''));

    // DISPENSE
    $dispense_value  = trim((string)($data['dispense_value'] ?? ''));
    $dispense_unit   = trim((string)($data['dispense_unit'] ?? ''));
    $dispense_code   = trim((string)($data['dispense_code'] ?? ''));
    $dispense_system = trim((string)($data['dispense_sys'] ?? ''));

    $supply_duration_value = trim((string)($data['supply_duration_value'] ?? ''));
    $supply_duration_unit  = trim((string)($data['supply_duration_unit'] ?? ''));
    $supply_duration_code  = trim((string)($data['supply_duration_code'] ?? ''));
    $supply_duration_sys   = trim((string)($data['supply_duration_sys'] ?? ''));

    // RACIKAN
    $racikan_code    = trim((string)($data['racikan_code'] ?? ''));
    $racikan_display = trim((string)($data['racikan_display'] ?? ''));

    // CEK KELENGKAPAN
    $missing = [];

    if ($organization_id === '') $missing[] = 'Organization IHS ID';
    if ($identifier_system === '') $missing[] = 'Identifier System';
    if ($identifier_value === '') $missing[] = 'Identifier Value';

    if ($status === '') $missing[] = 'Status';
    if ($intent === '') $missing[] = 'Intent';

    if ($id_medication === '') $missing[] = 'ID Medication SATUSEHAT';

    if ($patient_ihs === '') $missing[] = 'Patient IHS ID';

    if ($id_kunjungan === '') {
        $missing[] = 'ID Kunjungan';
    } elseif ($id_encounter === '') {
        $missing[] = 'ID Encounter SATUSEHAT pada tabel kunjungan';
    }

    if ($authored_on === '') $missing[] = 'Tanggal Resep';
    if ($dokter_ihs === '') $missing[] = 'Practitioner IHS Dokter';

    if ($frequency === '') $missing[] = 'Frequency';
    if ($period === '') $missing[] = 'Period';
    if ($period_unit === '') $missing[] = 'Period Unit';

    if ($dose_value === '') $missing[] = 'Dose Value';
    if ($dose_code === '') $missing[] = 'Dose Code';

    if ($route_code === '') $missing[] = 'Route Code';

    if ($dispense_value === '') $missing[] = 'Dispense Value';
    if ($dispense_code === '') $missing[] = 'Dispense Code';

    if ($supply_duration_value === '') $missing[] = 'Supply Duration Value';

    // ALERT
    if (empty($missing)) {
        $can_submit = 1;

        $alert = '
            <div class="alert alert-success">
                <small>
                    <i class="bi bi-check-circle"></i>
                    Data Medication Request sudah lengkap dan siap dikirim ke SATUSEHAT.
                </small>
            </div>
        ';
    } else {
        $can_submit = 0;
        $list = '';

        foreach ($missing as $item) {
            $list .= '<li>'.escMR($item).'</li>';
        }

        $alert = '
            <div class="alert alert-danger">
                <small>
                    <i class="bi bi-exclamation-triangle"></i>
                    <b>Data Medication Request belum lengkap.</b>
                </small>
                <ul class="mb-0 mt-2">'.$list.'</ul>
            </div>
        ';
    }

    // FORM PREVIEW
    $html = '
        <input type="hidden" name="MedicationRequestId" value="'.escMR($MedicationRequestId).'">
        <input type="hidden" name="can_submit" value="'.$can_submit.'">

        '.$alert.'

        <div class="card mb-3">
            <div class="card-header">
                <small><b>A. Identifier & Status</b></small>
            </div>
            <div class="card-body">
                '.inputMR('Identifier System', $identifier_system, true).'
                '.inputMR('Identifier Value', $identifier_value, true).'
                '.inputMR('Status', $status, true).'
                '.inputMR('Intent', $intent, true).'
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <small><b>B. Medication</b></small>
            </div>
            <div class="card-body">
                '.inputMR(
                    'Medication Reference',
                    $id_medication !== '' ? 'Medication/'.$id_medication : '',
                    true
                ).'
                '.inputMR('Medication Display', $medication_name, true).'
                '.inputMR('Racikan Code', $racikan_code).'
                '.inputMR('Racikan Display', $racikan_display).'
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <small><b>C. Pasien</b></small>
            </div>
            <div class="card-body">
                '.inputMR('No RM', $id_pasien).'
                '.inputMR(
                    'Patient Reference',
                    $patient_ihs !== '' ? 'Patient/'.$patient_ihs : '',
                    true
                ).'
                '.inputMR('Patient Display', $patient_name, true).'
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <small><b>D. Encounter</b></small>
            </div>
            <div class="card-body">
                '.inputMR('ID Kunjungan Lokal', $id_kunjungan, true).'
                '.inputMR(
                    'Encounter Reference',
                    $id_encounter !== '' ? 'Encounter/'.$id_encounter : '',
                    true
                ).'
                '.inputMR('Tanggal Kunjungan', $data['tanggal_kunjungan'] ?? '').'
                '.inputMR('Jenis Kunjungan', $data['jenis_kunjungan'] ?? '').'
                '.inputMR('Status Encounter Lokal', $data['encounter_status'] ?? '').'
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <small><b>E. Peresepan</b></small>
            </div>
            <div class="card-body">
                '.inputMR('Authored On', $authored_on, true).'
                '.inputMR(
                    'Requester Reference',
                    $dokter_ihs !== '' ? 'Practitioner/'.$dokter_ihs : '',
                    true
                ).'
                '.inputMR('Requester Display', $dokter_nama, true).'
                '.inputMR('Reason System', $reason_system).'
                '.inputMR('Reason Code', $reason_code).'
                '.inputMR('Reason Display', $reason_display).'
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <small><b>F. Dosage Instruction</b></small>
            </div>
            <div class="card-body">
                '.inputMR('Instruction Text', $dosage_text).'
                '.inputMR('Frequency', $frequency, true).'
                '.inputMR('Period', $period, true).'
                '.inputMR('Period Unit', $period_unit, true).'
                '.inputMR('Dose Value', $dose_value, true).'
                '.inputMR('Dose Unit', $dose_unit).'
                '.inputMR('Dose Code', $dose_code, true).'
                '.inputMR('Dose System', $dose_system).'
                '.inputMR('Route Code', $route_code, true).'
                '.inputMR('Route Display', $route_display).'
                '.inputMR('Route System', $route_system).'
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <small><b>G. Dispense Request</b></small>
            </div>
            <div class="card-body">
                '.inputMR('Quantity Value', $dispense_value, true).'
                '.inputMR('Quantity Unit', $dispense_unit).'
                '.inputMR('Quantity Code', $dispense_code, true).'
                '.inputMR('Quantity System', $dispense_system).'
                '.inputMR('Supply Duration Value', $supply_duration_value, true).'
                '.inputMR('Supply Duration Unit', $supply_duration_unit).'
                '.inputMR('Supply Duration Code', $supply_duration_code).'
                '.inputMR('Supply Duration System', $supply_duration_sys).'
            </div>
        </div>
    ';

    responseMedicationRequest(
        'success',
        $can_submit === 1
            ? 'Data Medication Request siap dikirim.'
            : 'Data Medication Request belum lengkap.',
        $html
    );
?>