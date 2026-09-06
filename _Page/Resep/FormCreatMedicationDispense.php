<?php
    // CONNECTION, HELPER & SESSION
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    date_default_timezone_set('Asia/Jakarta');
    header('Content-Type: application/json; charset=utf-8');

    function responseDispense(string $status, string $message, string $html = ''): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'html'    => $html
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    function escDispense($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    function getReferenceIdDispense($reference): string {
        $reference = trim((string)$reference);
        if ($reference === '') return '';

        $parts = explode('/', $reference);
        return trim((string)end($parts));
    }

    function readonlyDispense(string $label, $value, bool $mandatory = false): string {
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
                    <input type="text"
                        class="form-control form-control-sm '.$class.'"
                        value="'.escDispense($empty ? '-' : $value).'"
                        readonly>
                </div>
            </div>
        ';
    }

    // VALIDASI SESSION & METHOD
    if (empty($SessionIdAkses)) {
        responseDispense('error', 'Sesi akses telah berakhir. Silakan login ulang.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseDispense('error', 'Metode request tidak valid.');
    }

    $MedicationRequestId = trim((string)($_POST['MedicationRequestId'] ?? ''));

    if ($MedicationRequestId === '') {
        responseDispense('error', 'ID item resep tidak boleh kosong.');
    }

    // AMBIL ITEM RESEP + GROUP + PATIENT + ENCOUNTER + MEDICATION
    $stmt = $Conn->prepare("
        SELECT
            mr.MedicationRequestId,
            mr.id_medication_request_group,
            mr.id_medication_request,
            mr.id_index_medication,
            mr.name_medication,
            mr.status AS medication_request_status,
            mr.intent,
            mr.dosage_inst_text,
            mr.dosage_inst_frequency,
            mr.dosage_inst_period,
            mr.dosage_inst_period_unit,
            mr.dose_value,
            mr.dose_unit,
            mr.dose_code,
            mr.dose_system,
            mr.route_display,
            mr.route_code,
            mr.route_system,
            mr.dispense_value,
            mr.dispense_unit,
            mr.dispense_code,
            mr.dispense_sys,
            mr.supply_duration_value,
            mr.supply_duration_unit,
            mr.supply_duration_code,
            mr.supply_duration_sys,

            mrg.id_anggota,
            mrg.id_kunjungan,
            mrg.nama_pasien,
            mrg.apoteker_id,
            mrg.apoteker_code,
            mrg.apoteker_nama,
            mrg.apoteker_ihs,
            mrg.no_resep_nasional,
            mrg.sumber_resep,

            a.id_ihs AS patient_ihs,
            a.id_pasien,

            k.id_encounter,

            med.id_medication,
            med.medication_code,
            med.medication_name,
            med.kfa_code,
            med.kfa_display,
            med.sediaan_code,
            med.sediaan_display,
            med.racikan_code,
            med.racikan_display

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
        responseDispense(
            'error',
            'Gagal mempersiapkan data item resep.<br>Keterangan : '.
            escDispense($Conn->error)
        );
    }

    $stmt->bind_param("s", $MedicationRequestId);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();

        responseDispense(
            'error',
            'Gagal membuka data item resep.<br>Keterangan : '.escDispense($error)
        );
    }

    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$data) {
        responseDispense('error', 'Data item resep tidak ditemukan.');
    }

    // MAPPING DASAR
    $id_medication_request = trim((string)($data['id_medication_request'] ?? ''));
    $id_medication         = trim((string)($data['id_medication'] ?? ''));
    $name_medication       = trim((string)($data['medication_name'] ?? $data['name_medication'] ?? ''));

    $patient_ihs = trim((string)($data['patient_ihs'] ?? ''));
    $patient_rm  = trim((string)($data['id_pasien'] ?? ''));
    $nama_pasien = trim((string)($data['nama_pasien'] ?? ''));

    $id_encounter = trim((string)($data['id_encounter'] ?? ''));

    $apoteker_id   = (int)($data['apoteker_id'] ?? 0);
    $apoteker_code = trim((string)($data['apoteker_code'] ?? ''));
    $apoteker_nama = trim((string)($data['apoteker_nama'] ?? ''));
    $apoteker_ihs  = trim((string)($data['apoteker_ihs'] ?? ''));

    /*
     * Jika resep berasal dari NRN dan Patient/Encounter tidak terhubung
     * ke tabel lokal, kita dapat mengambil kembali reference dari
     * MedicationRequest SATUSEHAT.
     */
    $mrSatusehat = [];

    if (
        $id_medication_request !== '' &&
        ($patient_ihs === '' || $id_encounter === '')
    ) {
        $stmt = $Conn->prepare("
            SELECT url_connection_satu_sehat
            FROM connection_satu_sehat
            WHERE status_connection_satu_sehat = 1
            LIMIT 1
        ");

        if ($stmt && $stmt->execute()) {
            $config = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $base_url = rtrim(
                trim((string)($config['url_connection_satu_sehat'] ?? '')),
                '/'
            );

            if ($base_url !== '') {
                $tokenResult = generateTokenSatuSehat($Conn);

                if (
                    !empty($tokenResult) &&
                    ($tokenResult['status'] ?? '') === 'success' &&
                    !empty($tokenResult['token'])
                ) {
                    $curl = curl_init();

                    curl_setopt_array($curl, [
                        CURLOPT_URL => $base_url.
                            '/fhir-r4/v1/MedicationRequest/'.
                            rawurlencode($id_medication_request),
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_TIMEOUT        => 20,
                        CURLOPT_CONNECTTIMEOUT => 10,
                        CURLOPT_HTTPHEADER     => [
                            'Authorization: Bearer '.$tokenResult['token'],
                            'Accept: application/fhir+json'
                        ],
                        CURLOPT_SSL_VERIFYPEER => true
                    ]);

                    $response = curl_exec($curl);
                    $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    curl_close($curl);

                    if ($httpCode >= 200 && $httpCode < 300 && $response !== false) {
                        $decoded = json_decode($response, true);

                        if (
                            is_array($decoded) &&
                            ($decoded['resourceType'] ?? '') === 'MedicationRequest'
                        ) {
                            $mrSatusehat = $decoded;
                        }
                    }
                }
            }
        }
    }

    // FALLBACK PATIENT DARI MEDICATION REQUEST SATUSEHAT
    if ($patient_ihs === '' && !empty($mrSatusehat['subject']['reference'])) {
        $patient_ihs = getReferenceIdDispense(
            $mrSatusehat['subject']['reference']
        );

        if ($nama_pasien === '') {
            $nama_pasien = trim(
                (string)($mrSatusehat['subject']['display'] ?? '')
            );
        }
    }

    // FALLBACK ENCOUNTER DARI MEDICATION REQUEST SATUSEHAT
    if ($id_encounter === '' && !empty($mrSatusehat['encounter']['reference'])) {
        $id_encounter = getReferenceIdDispense(
            $mrSatusehat['encounter']['reference']
        );
    }

    // DATA MEDICATION
    $kfa_code        = trim((string)($data['kfa_code'] ?? ''));
    $kfa_display     = trim((string)($data['kfa_display'] ?? ''));
    $sediaan_code    = trim((string)($data['sediaan_code'] ?? ''));
    $sediaan_display = trim((string)($data['sediaan_display'] ?? ''));

    // DATA RESEP
    $no_resep_nasional = trim((string)($data['no_resep_nasional'] ?? ''));
    $sumber_resep      = trim((string)($data['sumber_resep'] ?? ''));

    // DEFAULT DATA DISPENSE DARI MEDICATION REQUEST
    $dispense_value = (float)($data['dispense_value'] ?? 0);
    $dispense_unit  = trim((string)($data['dispense_unit'] ?? ''));
    $dispense_code  = trim((string)($data['dispense_code'] ?? ''));
    $dispense_sys   = trim((string)($data['dispense_sys'] ?? ''));

    $days_value = (int)($data['supply_duration_value'] ?? 0);
    $days_unit  = trim((string)($data['supply_duration_unit'] ?? ''));
    $days_code  = trim((string)($data['supply_duration_code'] ?? ''));
    $days_sys   = trim((string)($data['supply_duration_sys'] ?? ''));

    $dosage_text = trim((string)($data['dosage_inst_text'] ?? ''));
    $frequency   = (int)($data['dosage_inst_frequency'] ?? 0);
    $period      = (int)($data['dosage_inst_period'] ?? 0);
    $period_unit = trim((string)($data['dosage_inst_period_unit'] ?? ''));

    $dose_value  = (float)($data['dose_value'] ?? 0);
    $dose_unit   = trim((string)($data['dose_unit'] ?? ''));
    $dose_code   = trim((string)($data['dose_code'] ?? ''));
    $dose_system = trim((string)($data['dose_system'] ?? ''));

    $route_display = trim((string)($data['route_display'] ?? ''));
    $route_code    = trim((string)($data['route_code'] ?? ''));
    $route_system  = trim((string)($data['route_system'] ?? ''));

    // FORMAT ANGKA UNTUK FORM
    $formatNumber = function ($number) {
        $number = (float)$number;

        if (floor($number) == $number) {
            return (string)(int)$number;
        }

        return rtrim(rtrim(number_format($number, 2, '.', ''), '0'), '.');
    };

    $dispense_value_form = $formatNumber($dispense_value);
    $dose_value_form     = $formatNumber($dose_value);

    // DEFAULT WAKTU PENYERAHAN
    $nowForm = date('Y-m-d\TH:i');

    // CEK KELENGKAPAN
    $missing = [];
    $warning = [];

    if ($id_medication_request === '') {
        $missing[] = 'ID MedicationRequest SATUSEHAT';
    }

    if ($patient_ihs === '') {
        $missing[] = 'Patient IHS ID';
    }

    if ($id_encounter === '') {
        $missing[] = 'Encounter ID SATUSEHAT';
    }

    if ($apoteker_ihs === '') {
        $missing[] = 'Practitioner IHS Apoteker';
    }

    if ($dispense_value <= 0) {
        $missing[] = 'Jumlah obat yang diserahkan';
    }

    if ($dispense_code === '') {
        $missing[] = 'Kode satuan obat yang diserahkan';
    }

    if ($days_value < 1) {
        $missing[] = 'Days Supply';
    }

    // id_medication tidak selalu wajib sudah ada,
    // karena Medication dapat dibuat saat proses dispense.
    if ($id_medication === '') {
        if ($kfa_code === '') {
            $missing[] = 'Medication SATUSEHAT / KFA Medication';
        } else {
            $warning[] = 'Medication belum mempunyai ID SATUSEHAT. Resource Medication perlu dibuat terlebih dahulu pada saat proses dispense.';
        }
    }

    // ALERT
    $alert = '';

    if (!empty($missing)) {
        $list = '';

        foreach ($missing as $item) {
            $list .= '<li>'.escDispense($item).'</li>';
        }

        $alert .= '
            <div class="alert alert-danger">
                <small>
                    <i class="bi bi-exclamation-triangle"></i>
                    <b>Data belum lengkap untuk MedicationDispense.</b>
                </small>
                <ul class="mb-0 mt-2">'.$list.'</ul>
            </div>
        ';
    } else {
        $alert .= '
            <div class="alert alert-success">
                <small>
                    <i class="bi bi-check-circle"></i>
                    Data referensi utama sudah lengkap. Silakan sesuaikan data penyerahan obat dengan kondisi sebenarnya.
                </small>
            </div>
        ';
    }

    foreach ($warning as $item) {
        $alert .= '
            <div class="alert alert-warning">
                <small>
                    <i class="bi bi-info-circle"></i>
                    '.escDispense($item).'
                </small>
            </div>
        ';
    }

    $can_submit = empty($missing) ? 1 : 0;

    // HTML
    $html = '
        <input type="hidden" name="MedicationRequestId"
            value="'.escDispense($MedicationRequestId).'">

        <input type="hidden" name="can_submit"
            value="'.$can_submit.'">

        <input type="hidden" name="id_medication_request"
            value="'.escDispense($id_medication_request).'">

        <input type="hidden" name="id_index_medication"
            value="'.(int)$data['id_index_medication'].'">

        '.$alert.'

        <div class="border border-secondary border-opacity-50 rounded-3 p-3 mb-3">

            <div class="row mb-3">
                <div class="col-md-12">
                    <h6 class="mb-1">
                        <i class="bi bi-prescription2"></i>
                        Referensi Resep
                    </h6>
                    <small class="text-muted">
                        Data ini berasal dari resep dan tidak dapat diubah pada proses penyerahan.
                    </small>
                </div>
            </div>

            '.readonlyDispense(
                'MedicationRequest',
                $id_medication_request !== ''
                    ? 'MedicationRequest/'.$id_medication_request
                    : '',
                true
            ).'

            '.readonlyDispense(
                'Nomor Resep Nasional',
                $no_resep_nasional
            ).'

            '.readonlyDispense(
                'Sumber Resep',
                $sumber_resep
            ).'

            '.readonlyDispense(
                'Medication',
                $id_medication !== ''
                    ? 'Medication/'.$id_medication
                    : 'Akan dibuat pada proses dispense'
            ).'

            '.readonlyDispense(
                'Nama Medication',
                $name_medication,
                true
            ).'

            '.readonlyDispense(
                'KFA',
                trim($kfa_code.' - '.$kfa_display, ' -')
            ).'

            '.readonlyDispense(
                'Sediaan',
                trim($sediaan_code.' - '.$sediaan_display, ' -')
            ).'

        </div>

        <div class="border border-secondary border-opacity-50 rounded-3 p-3 mb-3">

            <div class="row mb-3">
                <div class="col-md-12">
                    <h6 class="mb-1">
                        <i class="bi bi-person"></i>
                        Pasien & Encounter
                    </h6>
                </div>
            </div>

            '.readonlyDispense(
                'Nomor RM',
                $patient_rm
            ).'

            '.readonlyDispense(
                'Pasien',
                $nama_pasien,
                true
            ).'

            '.readonlyDispense(
                'Patient Reference',
                $patient_ihs !== ''
                    ? 'Patient/'.$patient_ihs
                    : '',
                true
            ).'

            '.readonlyDispense(
                'Encounter Reference',
                $id_encounter !== ''
                    ? 'Encounter/'.$id_encounter
                    : '',
                true
            ).'

        </div>

        <div class="border border-secondary border-opacity-50 rounded-3 p-3 mb-3">

            <div class="row mb-3">
                <div class="col-md-12">
                    <h6 class="mb-1">
                        <i class="bi bi-person-badge"></i>
                        Petugas Penyerahan
                    </h6>
                </div>
            </div>

            '.readonlyDispense(
                'Kode Apoteker',
                $apoteker_code
            ).'

            '.readonlyDispense(
                'Nama Apoteker',
                $apoteker_nama,
                true
            ).'

            '.readonlyDispense(
                'Practitioner Reference',
                $apoteker_ihs !== ''
                    ? 'Practitioner/'.$apoteker_ihs
                    : '',
                true
            ).'

        </div>

        <div class="border border-primary border-opacity-50 rounded-3 p-3 mb-3">

            <div class="row mb-3">
                <div class="col-md-12">
                    <h6 class="mb-1">
                        <i class="bi bi-box-seam"></i>
                        Data Aktual Penyerahan
                    </h6>
                    <small class="text-muted">
                        Bagian ini dapat disesuaikan dengan obat yang benar-benar diserahkan kepada pasien.
                    </small>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-4">
                    <label for="dispense_status">
                        <small>Status Dispense <span class="text-danger">*</span></small>
                    </label>
                </div>
                <div class="col-md-8">
                    <select name="dispense_status" id="dispense_status"
                        class="form-control form-control-sm" required>
                        <option value="">Pilih</option>
                        <option value="preparation">Preparation</option>
                        <option value="in-progress">In Progress</option>
                        <option value="on-hold">On Hold</option>
                        <option value="completed" selected>Completed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="stopped">Stopped</option>
                        <option value="declined">Declined</option>
                        <option value="entered-in-error">Entered In Error</option>
                    </select>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-4">
                    <label for="quantity_value">
                        <small>Jumlah Diserahkan <span class="text-danger">*</span></small>
                    </label>
                </div>
                <div class="col-md-8">
                    <div class="input-group input-group-sm">
                        <input type="number"
                            name="quantity_value"
                            id="quantity_value"
                            class="form-control"
                            min="0.01"
                            step="0.01"
                            value="'.escDispense($dispense_value_form).'"
                            required>

                        <input type="text"
                            name="quantity_unit"
                            class="form-control"
                            value="'.escDispense($dispense_unit).'"
                            readonly>
                    </div>

                    <input type="hidden"
                        name="quantity_code"
                        value="'.escDispense($dispense_code).'">

                    <input type="hidden"
                        name="quantity_system"
                        value="'.escDispense($dispense_sys).'">

                    <small class="text-muted">
                        '.escDispense($dispense_code).' |
                        '.escDispense($dispense_sys).'
                    </small>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-4">
                    <label for="days_supply_value">
                        <small>Days Supply <span class="text-danger">*</span></small>
                    </label>
                </div>
                <div class="col-md-8">
                    <div class="input-group input-group-sm">
                        <input type="number"
                            name="days_supply_value"
                            id="days_supply_value"
                            class="form-control"
                            min="1"
                            step="1"
                            value="'.(int)$days_value.'"
                            required>

                        <input type="text"
                            name="days_supply_unit"
                            class="form-control"
                            value="'.escDispense($days_unit).'"
                            readonly>
                    </div>

                    <input type="hidden"
                        name="days_supply_code"
                        value="'.escDispense($days_code).'">

                    <input type="hidden"
                        name="days_supply_system"
                        value="'.escDispense($days_sys).'">

                    <small class="text-muted">
                        '.escDispense($days_code).' |
                        '.escDispense($days_sys).'
                    </small>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-4">
                    <label for="when_prepared">
                        <small>Waktu Penyiapan <span class="text-danger">*</span></small>
                    </label>
                </div>
                <div class="col-md-8">
                    <input type="datetime-local"
                        name="when_prepared"
                        id="when_prepared"
                        class="form-control form-control-sm"
                        value="'.$nowForm.'"
                        required>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-4">
                    <label for="when_handed_over">
                        <small>Waktu Penyerahan <span class="text-danger">*</span></small>
                    </label>
                </div>
                <div class="col-md-8">
                    <input type="datetime-local"
                        name="when_handed_over"
                        id="when_handed_over"
                        class="form-control form-control-sm"
                        value="'.$nowForm.'"
                        required>
                </div>
            </div>

        </div>

        <div class="border border-primary border-opacity-50 rounded-3 p-3">

            <div class="row mb-3">
                <div class="col-md-12">
                    <h6 class="mb-1">
                        <i class="bi bi-capsule"></i>
                        Instruksi Penggunaan Aktual
                    </h6>
                    <small class="text-muted">
                        Nilai awal diambil dari MedicationRequest dan dapat disesuaikan bila diperlukan.
                    </small>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-4">
                    <label for="dosage_text">
                        <small>Instruksi</small>
                    </label>
                </div>
                <div class="col-md-8">
                    <textarea name="dosage_text"
                        id="dosage_text"
                        class="form-control form-control-sm"
                        rows="2">'.escDispense($dosage_text).'</textarea>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-4">
                    <label for="frequency">
                        <small>Frequency</small>
                    </label>
                </div>
                <div class="col-md-8">
                    <input type="number"
                        name="frequency"
                        id="frequency"
                        class="form-control form-control-sm"
                        min="1"
                        value="'.$frequency.'">
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-4">
                    <label for="period">
                        <small>Period</small>
                    </label>
                </div>
                <div class="col-md-8">
                    <div class="input-group input-group-sm">
                        <input type="number"
                            name="period"
                            id="period"
                            class="form-control"
                            min="1"
                            value="'.$period.'">

                        <input type="text"
                            name="period_unit"
                            class="form-control"
                            value="'.escDispense($period_unit).'"
                            readonly>
                    </div>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-4">
                    <label for="dose_value">
                        <small>Dosis Per Pemakaian</small>
                    </label>
                </div>
                <div class="col-md-8">
                    <div class="input-group input-group-sm">
                        <input type="number"
                            name="dose_value"
                            id="dose_value"
                            class="form-control"
                            min="0.01"
                            step="0.01"
                            value="'.escDispense($dose_value_form).'">

                        <input type="text"
                            name="dose_unit"
                            class="form-control"
                            value="'.escDispense($dose_unit).'"
                            readonly>
                    </div>

                    <input type="hidden"
                        name="dose_code"
                        value="'.escDispense($dose_code).'">

                    <input type="hidden"
                        name="dose_system"
                        value="'.escDispense($dose_system).'">
                </div>
            </div>

            '.readonlyDispense(
                'Route',
                trim(
                    $route_code.' - '.$route_display.' | '.$route_system,
                    ' -|'
                )
            ).'

        </div>
    ';

    responseDispense(
        'success',
        $can_submit === 1
            ? 'Data MedicationDispense siap diproses.'
            : 'Data MedicationDispense belum lengkap.',
        $html
    );
?>