<?php
    // CONNECTION, HELPER & SESSION
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');

    function responseMR(string $status, string $message, string $html = ''): void {
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

    function rowMR(string $label, $value): string {
        $value = trim((string)($value ?? ''));

        return '
            <div class="row mb-2">
                <div class="col-md-4">
                    <small>'.$label.'</small>
                </div>
                <div class="col-md-8">
                    <small>'.escMR($value !== '' ? $value : '-').'</small>
                </div>
            </div>
        ';
    }

    function operationOutcomeMR(array $data): string {
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
        responseMR('error', 'Sesi akses telah berakhir. Silakan login ulang.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseMR('error', 'Metode request tidak valid.');
    }

    // ID MEDICATION REQUEST
    $id_medication_request = trim(
        (string)($_POST['id_medication_request'] ?? '')
    );

    if ($id_medication_request === '') {
        responseMR('error', 'ID Medication Request tidak boleh kosong.');
    }

    // KONFIGURASI SATUSEHAT
    $stmt = $Conn->prepare("
        SELECT url_connection_satu_sehat
        FROM connection_satu_sehat
        WHERE status_connection_satu_sehat = 1
        LIMIT 1
    ");

    if (!$stmt) {
        responseMR('error', 'Gagal membuka konfigurasi SATUSEHAT.');
    }

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();

        responseMR(
            'error',
            'Gagal membaca konfigurasi SATUSEHAT.<br>Keterangan : '.escMR($error)
        );
    }

    $config = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$config) {
        responseMR('error', 'Konfigurasi SATUSEHAT aktif tidak ditemukan.');
    }

    $base_url = rtrim(
        trim((string)($config['url_connection_satu_sehat'] ?? '')),
        '/'
    );

    if ($base_url === '') {
        responseMR('error', 'URL SATUSEHAT belum dikonfigurasi.');
    }

    // TOKEN
    $tokenResult = generateTokenSatuSehat($Conn);

    if (
        empty($tokenResult) ||
        ($tokenResult['status'] ?? '') !== 'success' ||
        empty($tokenResult['token'])
    ) {
        responseMR(
            'error',
            'Gagal membuat token SATUSEHAT.<br>'.
            escMR($tokenResult['message'] ?? '')
        );
    }

    $token = trim((string)$tokenResult['token']);

    // GET MEDICATION REQUEST BY ID
    $url = $base_url
        .'/fhir-r4/v1/MedicationRequest/'
        .rawurlencode($id_medication_request);

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

        responseMR(
            'error',
            'Gagal mengambil Medication Request dari SATUSEHAT.<br>'.
            escMR($error)
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
        responseMR(
            'error',
            'Response SATUSEHAT tidak valid.<br>HTTP Code : '.$httpCode
        );
    }

    if (
        $httpCode < 200 ||
        $httpCode >= 300 ||
        ($data['resourceType'] ?? '') === 'OperationOutcome'
    ) {
        responseMR(
            'error',
            'Gagal mengambil Medication Request.<br>'.
            escMR(operationOutcomeMR($data))
        );
    }

    if (($data['resourceType'] ?? '') !== 'MedicationRequest') {
        responseMR('error', 'Resource yang diterima bukan MedicationRequest.');
    }

    // INFORMASI DASAR
    $resource_id = trim((string)($data['id'] ?? ''));
    $status      = trim((string)($data['status'] ?? ''));
    $intent      = trim((string)($data['intent'] ?? ''));
    $priority    = trim((string)($data['priority'] ?? ''));
    $authoredOn  = trim((string)($data['authoredOn'] ?? ''));

    // SUBJECT
    $subject_reference = trim(
        (string)($data['subject']['reference'] ?? '')
    );

    $subject_display = trim(
        (string)($data['subject']['display'] ?? '')
    );

    // ENCOUNTER
    $encounter_reference = trim(
        (string)($data['encounter']['reference'] ?? '')
    );

    $encounter_display = trim(
        (string)($data['encounter']['display'] ?? '')
    );

    // REQUESTER
    $requester_reference = trim(
        (string)($data['requester']['reference'] ?? '')
    );

    $requester_display = trim(
        (string)($data['requester']['display'] ?? '')
    );

    // MEDICATION REFERENCE
    $medication_reference = trim(
        (string)($data['medicationReference']['reference'] ?? '')
    );

    $medication_display = trim(
        (string)($data['medicationReference']['display'] ?? '')
    );

    // REASON
    $reason_reference = '';
    $reason_display   = '';

    if (!empty($data['reasonReference'][0])) {
        $reason_reference = trim(
            (string)($data['reasonReference'][0]['reference'] ?? '')
        );

        $reason_display = trim(
            (string)($data['reasonReference'][0]['display'] ?? '')
        );
    }

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

    // METHOD
    $method = $dosage['method']['coding'][0] ?? [];

    $method_system  = trim((string)($method['system'] ?? ''));
    $method_code    = trim((string)($method['code'] ?? ''));
    $method_display = trim((string)($method['display'] ?? ''));

    // DOSE
    $dose = $dosage['doseAndRate'][0]['doseQuantity'] ?? [];

    $dose_value  = $dose['value'] ?? '';
    $dose_unit   = trim((string)($dose['unit'] ?? ''));
    $dose_code   = trim((string)($dose['code'] ?? ''));
    $dose_system = trim((string)($dose['system'] ?? ''));

    // DISPENSE REQUEST
    $dispense = $data['dispenseRequest'] ?? [];

    $quantity = $dispense['quantity'] ?? [];

    $dispense_value  = $quantity['value'] ?? '';
    $dispense_unit   = trim((string)($quantity['unit'] ?? ''));
    $dispense_code   = trim((string)($quantity['code'] ?? ''));
    $dispense_system = trim((string)($quantity['system'] ?? ''));

    // EXPECTED SUPPLY DURATION
    $duration = $dispense['expectedSupplyDuration'] ?? [];

    $duration_value  = $duration['value'] ?? '';
    $duration_unit   = trim((string)($duration['unit'] ?? ''));
    $duration_code   = trim((string)($duration['code'] ?? ''));
    $duration_system = trim((string)($duration['system'] ?? ''));

    // IDENTIFIER
    $identifierHtml = '';

    foreach (($data['identifier'] ?? []) as $identifier) {
        $identifierHtml .= '
            <div class="row mb-2">
                <div class="col-md-4"><small>Identifier</small></div>
                <div class="col-md-8">
                    <small>
                        '.escMR($identifier['system'] ?? '-').'<br>
                        <span class="text-muted">'.escMR($identifier['value'] ?? '-').'</span>
                    </small>
                </div>
            </div>
        ';
    }

    if ($identifierHtml === '') {
        $identifierHtml = rowMR('Identifier', '-');
    }

    // ADDITIONAL INSTRUCTION
    $additionalHtml = '';

    foreach (($dosage['additionalInstruction'] ?? []) as $additional) {
        $coding = $additional['coding'][0] ?? [];

        $additionalHtml .= '
            <div class="row mb-2">
                <div class="col-md-4">
                    <small>Additional Instruction</small>
                </div>
                <div class="col-md-8">
                    <small>
                        '.escMR($coding['code'] ?? '-').' -
                        '.escMR($coding['display'] ?? '-').'<br>
                        <span class="text-muted">'.escMR($coding['system'] ?? '-').'</span>
                    </small>
                </div>
            </div>
        ';
    }

    // CONTAINED MEDICATION
    $containedHtml = '';

    foreach (($data['contained'] ?? []) as $contained) {
        if (($contained['resourceType'] ?? '') !== 'Medication') continue;

        $containedId = trim((string)($contained['id'] ?? ''));

        $kfa = $contained['code']['coding'][0] ?? [];

        $form = $contained['form']['coding'][0] ?? [];

        $containedHtml .= '
            <div class="border border-secondary border-opacity-50 rounded-3 p-3 mt-2">
                '.rowMR('Contained Medication ID', $containedId).'
                '.rowMR(
                    'KFA',
                    trim(
                        ($kfa['code'] ?? '').' - '.($kfa['display'] ?? ''),
                        ' -'
                    )
                ).'
                '.rowMR(
                    'KFA System',
                    $kfa['system'] ?? ''
                ).'
                '.rowMR(
                    'Sediaan',
                    trim(
                        ($form['code'] ?? '').' - '.($form['display'] ?? ''),
                        ' -'
                    )
                ).'
                '.rowMR(
                    'Sediaan System',
                    $form['system'] ?? ''
                ).'
            </div>
        ';
    }

    if ($containedHtml === '') {
        $containedHtml = '
            <div class="alert alert-secondary mb-0">
                <small>Tidak ada contained Medication.</small>
            </div>
        ';
    }

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

            '.rowMR('Resource Type', $data['resourceType'] ?? '').'
            '.rowMR('MedicationRequest ID', $resource_id).'
            '.rowMR('Status', $status).'
            '.rowMR('Intent', $intent).'
            '.rowMR('Priority', $priority).'
            '.rowMR('Authored On', $authoredOn).'

            <hr>

            '.$identifierHtml.'

            <hr>

            <div class="row mb-2">
                <div class="col-md-12">
                    <small><b>Pasien & Encounter</b></small>
                </div>
            </div>

            '.rowMR('Subject Reference', $subject_reference).'
            '.rowMR('Subject Display', $subject_display).'
            '.rowMR('Encounter Reference', $encounter_reference).'
            '.rowMR('Encounter Display', $encounter_display).'

            <hr>

            <div class="row mb-2">
                <div class="col-md-12">
                    <small><b>Requester</b></small>
                </div>
            </div>

            '.rowMR('Requester Reference', $requester_reference).'
            '.rowMR('Requester Display', $requester_display).'

            <hr>

            <div class="row mb-2">
                <div class="col-md-12">
                    <small><b>Medication</b></small>
                </div>
            </div>

            '.rowMR('Medication Reference', $medication_reference).'
            '.rowMR('Medication Display', $medication_display).'

            '.$containedHtml.'

            <hr>

            <div class="row mb-2">
                <div class="col-md-12">
                    <small><b>Reason</b></small>
                </div>
            </div>

            '.rowMR('Reason Reference', $reason_reference).'
            '.rowMR('Reason Display', $reason_display).'

            <hr>

            <div class="row mb-2">
                <div class="col-md-12">
                    <small><b>Dosage Instruction</b></small>
                </div>
            </div>

            '.rowMR('Sequence', $sequence).'
            '.rowMR('Dosage Text', $dosage_text).'
            '.rowMR('Patient Instruction', $patient_instruction).'
            '.rowMR(
                'Frequency',
                $frequency !== ''
                    ? $frequency.' kali / '.$period.' '.$periodUnit
                    : ''
            ).'

            '.$additionalHtml.'

            '.rowMR(
                'Route',
                trim(
                    $route_code.' - '.$route_display,
                    ' -'
                )
            ).'

            '.rowMR('Route System', $route_system).'

            '.rowMR(
                'Method',
                trim(
                    $method_code.' - '.$method_display,
                    ' -'
                )
            ).'

            '.rowMR('Method System', $method_system).'

            '.rowMR(
                'Dose',
                trim(
                    $dose_value.' '.$dose_unit.
                    ' ['.$dose_code.']',
                    ' []'
                )
            ).'

            '.rowMR('Dose System', $dose_system).'

            <hr>

            <div class="row mb-2">
                <div class="col-md-12">
                    <small><b>Dispense Request</b></small>
                </div>
            </div>

            '.rowMR(
                'Quantity',
                trim(
                    $dispense_value.' '.$dispense_unit.
                    ' ['.$dispense_code.']',
                    ' []'
                )
            ).'

            '.rowMR('Quantity System', $dispense_system).'

            '.rowMR(
                'Expected Supply Duration',
                trim(
                    $duration_value.' '.$duration_unit.
                    ' ['.$duration_code.']',
                    ' []'
                )
            ).'

            '.rowMR('Duration System', $duration_system).'

            <hr>

            <details>
                <summary class="text-primary" style="cursor:pointer;">
                    <small>Tampilkan JSON Response</small>
                </summary>

                <pre class="bg-light border rounded p-3 mt-2 mb-0"
                    style="max-height:500px; overflow:auto; font-size:12px;">'.escMR($jsonPretty).'</pre>
            </details>

        </div>
    ';

    responseMR(
        'success',
        'Detail Medication Request berhasil ditemukan.',
        $html
    );
?>