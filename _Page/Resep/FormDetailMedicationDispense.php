<?php
    // CONNECTION, HELPER & SESSION
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');

    function responseMD(string $status, string $message, string $html = ''): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'html'    => $html
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    function escMD($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    function rowMD(string $label, $value): string {
        $value = trim((string)($value ?? ''));

        return '
            <div class="row mb-2">
                <div class="col-md-4">
                    <small>'.$label.'</small>
                </div>
                <div class="col-md-8">
                    <small>'.escMD($value !== '' ? $value : '-').'</small>
                </div>
            </div>
        ';
    }

    function operationOutcomeMD(array $data): string {
        if (($data['resourceType'] ?? '') !== 'OperationOutcome' || empty($data['issue'])) {
            return 'SATUSEHAT mengembalikan response error.';
        }

        $messages = [];

        foreach ($data['issue'] as $issue) {
            $message = $issue['details']['text']
                ?? $issue['diagnostics']
                ?? $issue['code']
                ?? '';

            if ($message !== '') {
                $messages[] = $message;
            }
        }

        return !empty($messages)
            ? implode(' | ', $messages)
            : 'SATUSEHAT mengembalikan OperationOutcome.';
    }

    // VALIDASI SESSION & METHOD
    if (empty($SessionIdAkses)) {
        responseMD('error', 'Sesi akses telah berakhir. Silakan login ulang.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseMD('error', 'Metode request tidak valid.');
    }

    // ID MEDICATION DISPENSE
    $id_medication_dispense = trim(
        (string)($_POST['id_medication_dispense'] ?? '')
    );

    if ($id_medication_dispense === '') {
        responseMD('error', 'ID Medication Dispense tidak boleh kosong.');
    }

    // KONFIGURASI SATUSEHAT
    $stmt = $Conn->prepare("
        SELECT url_connection_satu_sehat
        FROM connection_satu_sehat
        WHERE status_connection_satu_sehat = 1
        LIMIT 1
    ");

    if (!$stmt) {
        responseMD('error', 'Gagal membuka konfigurasi SATUSEHAT.');
    }

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();

        responseMD(
            'error',
            'Gagal membaca konfigurasi SATUSEHAT.<br>Keterangan : '.escMD($error)
        );
    }

    $config = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$config) {
        responseMD('error', 'Konfigurasi SATUSEHAT aktif tidak ditemukan.');
    }

    $base_url = rtrim(
        trim((string)($config['url_connection_satu_sehat'] ?? '')),
        '/'
    );

    if ($base_url === '') {
        responseMD('error', 'URL SATUSEHAT belum dikonfigurasi.');
    }

    // TOKEN
    $tokenResult = generateTokenSatuSehat($Conn);

    if (
        empty($tokenResult) ||
        ($tokenResult['status'] ?? '') !== 'success' ||
        empty($tokenResult['token'])
    ) {
        responseMD(
            'error',
            'Gagal membuat token SATUSEHAT.<br>'.
            escMD($tokenResult['message'] ?? '')
        );
    }

    $token = trim((string)$tokenResult['token']);

    // GET MEDICATION DISPENSE BY ID
    $url = $base_url
        .'/fhir-r4/v1/MedicationDispense/'
        .rawurlencode($id_medication_dispense);

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_CUSTOMREQUEST  => 'GET',
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer '.$token,
            'Accept: application/fhir+json'
        ],
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $response = curl_exec($curl);

    if ($response === false) {
        $error = curl_error($curl);
        curl_close($curl);

        responseMD(
            'error',
            'Gagal mengambil Medication Dispense dari SATUSEHAT.<br>'.
            escMD($error)
        );
    }

    $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    // DECODE RESPONSE
    $data = json_decode($response, true);

    if (
        json_last_error() !== JSON_ERROR_NONE ||
        !is_array($data)
    ) {
        responseMD(
            'error',
            'Response SATUSEHAT tidak valid.<br>HTTP Code : '.$httpCode
        );
    }

    if (
        $httpCode < 200 ||
        $httpCode >= 300 ||
        ($data['resourceType'] ?? '') === 'OperationOutcome'
    ) {
        responseMD(
            'error',
            'Gagal mengambil Medication Dispense.<br>'.
            escMD(operationOutcomeMD($data))
        );
    }

    if (($data['resourceType'] ?? '') !== 'MedicationDispense') {
        responseMD('error', 'Resource yang diterima bukan MedicationDispense.');
    }

    // INFORMASI DASAR
    $resource_id      = trim((string)($data['id'] ?? ''));
    $status           = trim((string)($data['status'] ?? ''));
    $when_prepared    = trim((string)($data['whenPrepared'] ?? ''));
    $when_handed_over = trim((string)($data['whenHandedOver'] ?? ''));

    // MEDICATION
    $medication_reference = trim(
        (string)($data['medicationReference']['reference'] ?? '')
    );

    $medication_display = trim(
        (string)($data['medicationReference']['display'] ?? '')
    );

    // SUBJECT
    $subject_reference = trim(
        (string)($data['subject']['reference'] ?? '')
    );

    $subject_display = trim(
        (string)($data['subject']['display'] ?? '')
    );

    // ENCOUNTER
    $context_reference = trim(
        (string)($data['context']['reference'] ?? '')
    );

    // PERFORMER
    $performer_reference = '';
    $performer_display   = '';

    if (!empty($data['performer'][0]['actor'])) {
        $performer_reference = trim(
            (string)($data['performer'][0]['actor']['reference'] ?? '')
        );

        $performer_display = trim(
            (string)($data['performer'][0]['actor']['display'] ?? '')
        );
    }

    // AUTHORIZING PRESCRIPTION
    $prescription_reference = '';

    if (!empty($data['authorizingPrescription'][0]['reference'])) {
        $prescription_reference = trim(
            (string)$data['authorizingPrescription'][0]['reference']
        );
    }

    // QUANTITY
    $quantity = $data['quantity'] ?? [];

    $quantity_value  = $quantity['value'] ?? '';
    $quantity_unit   = trim((string)($quantity['unit'] ?? ''));
    $quantity_code   = trim((string)($quantity['code'] ?? ''));
    $quantity_system = trim((string)($quantity['system'] ?? ''));

    // DAYS SUPPLY
    $daysSupply = $data['daysSupply'] ?? [];

    $days_value  = $daysSupply['value'] ?? '';
    $days_unit   = trim((string)($daysSupply['unit'] ?? ''));
    $days_code   = trim((string)($daysSupply['code'] ?? ''));
    $days_system = trim((string)($daysSupply['system'] ?? ''));

    // DOSAGE
    $dosage = $data['dosageInstruction'][0] ?? [];

    $dosage_text = trim(
        (string)($dosage['text'] ?? '')
    );

    $patient_instruction = trim(
        (string)($dosage['patientInstruction'] ?? '')
    );

    $sequence = (int)($dosage['sequence'] ?? 0);

    // TIMING
    $repeat = $dosage['timing']['repeat'] ?? [];

    $frequency  = $repeat['frequency'] ?? '';
    $period     = $repeat['period'] ?? '';
    $periodUnit = trim((string)($repeat['periodUnit'] ?? ''));

    // ROUTE
    $route = $dosage['route']['coding'][0] ?? [];

    $route_system  = trim((string)($route['system'] ?? ''));
    $route_code    = trim((string)($route['code'] ?? ''));
    $route_display = trim((string)($route['display'] ?? ''));

    // DOSE
    $doseAndRate = $dosage['doseAndRate'][0] ?? [];
    $dose        = $doseAndRate['doseQuantity'] ?? [];

    $dose_value  = $dose['value'] ?? '';
    $dose_unit   = trim((string)($dose['unit'] ?? ''));
    $dose_code   = trim((string)($dose['code'] ?? ''));
    $dose_system = trim((string)($dose['system'] ?? ''));

    // DOSE RATE TYPE
    $dose_type = $doseAndRate['type']['coding'][0] ?? [];

    $dose_type_code    = trim((string)($dose_type['code'] ?? ''));
    $dose_type_display = trim((string)($dose_type['display'] ?? ''));
    $dose_type_system  = trim((string)($dose_type['system'] ?? ''));

    // SUBSTITUTION
    $was_substituted = $data['substitution']['wasSubstituted'] ?? null;

    if ($was_substituted === true) {
        $substitution_display = 'Ya';
    } elseif ($was_substituted === false) {
        $substitution_display = 'Tidak';
    } else {
        $substitution_display = '-';
    }

    // IDENTIFIER
    $identifierHtml = '';

    foreach (($data['identifier'] ?? []) as $identifier) {
        $identifierHtml .= '
            <div class="row mb-2">
                <div class="col-md-4">
                    <small>Identifier</small>
                </div>
                <div class="col-md-8">
                    <small>
                        '.escMD($identifier['value'] ?? '-').'<br>
                        <span class="text-muted">
                            '.escMD($identifier['system'] ?? '-').'
                        </span>
                    </small>
                </div>
            </div>
        ';
    }

    if ($identifierHtml === '') {
        $identifierHtml = rowMD('Identifier', '-');
    }

    // FORMAT WAKTU UNTUK TAMPILAN
    $formatDate = function ($value) {
        $value = trim((string)$value);

        if ($value === '' || strtotime($value) === false) {
            return $value;
        }

        try {
            $dt = new DateTime($value);
            $dt->setTimezone(new DateTimeZone('Asia/Jakarta'));
            return $dt->format('d-m-Y H:i:s');
        } catch (Throwable $e) {
            return $value;
        }
    };

    $when_prepared_display    = $formatDate($when_prepared);
    $when_handed_over_display = $formatDate($when_handed_over);

    // JSON PREVIEW
    $jsonPretty = json_encode(
        $data,
        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    // HTML
    $html = '
        <div class="border border-secondary border-opacity-50 rounded-3 p-3">

            <div class="row mb-3">
                <div class="col-md-12">
                    <h6 class="mb-1">
                        <i class="bi bi-info-circle"></i>
                        Informasi Resource
                    </h6>
                </div>
            </div>

            '.rowMD('Resource Type', $data['resourceType'] ?? '').'
            '.rowMD('MedicationDispense ID', $resource_id).'
            '.rowMD('Status', $status).'

            <hr>

            '.$identifierHtml.'

            <hr>

            <div class="row mb-2">
                <div class="col-md-12">
                    <small><b>Medication</b></small>
                </div>
            </div>

            '.rowMD('Medication Reference', $medication_reference).'
            '.rowMD('Medication Display', $medication_display).'

            <hr>

            <div class="row mb-2">
                <div class="col-md-12">
                    <small><b>Pasien & Encounter</b></small>
                </div>
            </div>

            '.rowMD('Subject Reference', $subject_reference).'
            '.rowMD('Subject Display', $subject_display).'
            '.rowMD('Encounter Reference', $context_reference).'

            <hr>

            <div class="row mb-2">
                <div class="col-md-12">
                    <small><b>Petugas Penyerahan</b></small>
                </div>
            </div>

            '.rowMD('Performer Reference', $performer_reference).'
            '.rowMD('Performer Display', $performer_display).'

            <hr>

            <div class="row mb-2">
                <div class="col-md-12">
                    <small><b>Resep Otorisasi</b></small>
                </div>
            </div>

            '.rowMD('Authorizing Prescription', $prescription_reference).'

            <hr>

            <div class="row mb-2">
                <div class="col-md-12">
                    <small><b>Data Penyerahan</b></small>
                </div>
            </div>

            '.rowMD(
                'Quantity',
                trim(
                    $quantity_value.' '.$quantity_unit.
                    ' ['.$quantity_code.']',
                    ' []'
                )
            ).'

            '.rowMD('Quantity System', $quantity_system).'

            '.rowMD(
                'Days Supply',
                trim(
                    $days_value.' '.$days_unit.
                    ' ['.$days_code.']',
                    ' []'
                )
            ).'

            '.rowMD('Days Supply System', $days_system).'

            '.rowMD('When Prepared', $when_prepared_display).'
            '.rowMD('When Handed Over', $when_handed_over_display).'

            <hr>

            <div class="row mb-2">
                <div class="col-md-12">
                    <small><b>Dosage Instruction</b></small>
                </div>
            </div>

            '.rowMD('Sequence', $sequence).'
            '.rowMD('Dosage Text', $dosage_text).'
            '.rowMD('Patient Instruction', $patient_instruction).'

            '.rowMD(
                'Frequency',
                $frequency !== ''
                    ? $frequency.' kali / '.$period.' '.$periodUnit
                    : ''
            ).'

            '.rowMD(
                'Route',
                trim(
                    $route_code.' - '.$route_display,
                    ' -'
                )
            ).'

            '.rowMD('Route System', $route_system).'

            '.rowMD(
                'Dose Type',
                trim(
                    $dose_type_code.' - '.$dose_type_display,
                    ' -'
                )
            ).'

            '.rowMD('Dose Type System', $dose_type_system).'

            '.rowMD(
                'Dose',
                trim(
                    $dose_value.' '.$dose_unit.
                    ' ['.$dose_code.']',
                    ' []'
                )
            ).'

            '.rowMD('Dose System', $dose_system).'

            <hr>

            <div class="row mb-2">
                <div class="col-md-12">
                    <small><b>Substitution</b></small>
                </div>
            </div>

            '.rowMD('Obat Disubstitusi', $substitution_display).'

            <hr>

            <details>
                <summary class="text-primary" style="cursor:pointer;">
                    <small>Tampilkan JSON Response</small>
                </summary>

                <pre class="bg-light border rounded p-3 mt-2 mb-0"
                    style="max-height:500px; overflow:auto; font-size:12px;">'.escMD($jsonPretty).'</pre>
            </details>

        </div>
    ';

    responseMD(
        'success',
        'Detail Medication Dispense berhasil ditemukan.',
        $html
    );
?>