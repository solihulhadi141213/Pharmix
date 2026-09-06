<?php
    // CONNECTION, HELPER & SESSION
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    date_default_timezone_set('Asia/Jakarta');
    header('Content-Type: application/json; charset=utf-8');

    function responseNRN(string $status, string $message, string $html = ''): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'html'    => $html
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    function escNRN($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    function rowNRN(string $label, $value): string {
        $value = trim((string)($value ?? ''));

        return '
            <div class="row mb-2">
                <div class="col-md-4">
                    <small>'.$label.'</small>
                </div>
                <div class="col-md-8">
                    <small>'.escNRN($value !== '' ? $value : '-').'</small>
                </div>
            </div>
        ';
    }

    function operationOutcomeNRN(array $data): string {
        if (($data['resourceType'] ?? '') !== 'OperationOutcome' || empty($data['issue'])) {
            return 'SATUSEHAT mengembalikan response error.';
        }

        $messages = [];

        foreach ($data['issue'] as $issue) {
            $message = $issue['details']['text']
                ?? $issue['diagnostics']
                ?? $issue['code']
                ?? '';

            if ($message !== '') $messages[] = $message;
        }

        return !empty($messages)
            ? implode(' | ', $messages)
            : 'SATUSEHAT mengembalikan OperationOutcome.';
    }

    // VALIDASI SESSION & METHOD
    if (empty($SessionIdAkses)) {
        responseNRN('error', 'Sesi akses telah berakhir. Silakan login ulang.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseNRN('error', 'Metode request tidak valid.');
    }

    // NRN
    $no_resep_nasional = strtoupper(
        trim((string)($_POST['no_resep_nasional'] ?? ''))
    );

    if ($no_resep_nasional === '') {
        responseNRN('error', 'Nomor Resep Nasional tidak boleh kosong.');
    }

    // KONFIGURASI SATUSEHAT
    $stmt = $Conn->prepare("
        SELECT url_connection_satu_sehat
        FROM connection_satu_sehat
        WHERE status_connection_satu_sehat = 1
        LIMIT 1
    ");

    if (!$stmt) {
        responseNRN('error', 'Gagal membuka konfigurasi SATUSEHAT.');
    }

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();

        responseNRN(
            'error',
            'Gagal membaca konfigurasi SATUSEHAT.<br>'.escNRN($error)
        );
    }

    $config = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$config) {
        responseNRN('error', 'Konfigurasi SATUSEHAT aktif tidak ditemukan.');
    }

    $base_url = rtrim(
        trim((string)($config['url_connection_satu_sehat'] ?? '')),
        '/'
    );

    if ($base_url === '') {
        responseNRN('error', 'URL SATUSEHAT belum dikonfigurasi.');
    }

    // TOKEN SATUSEHAT FHIR
    $tokenResult = generateTokenSatuSehat($Conn);

    if (
        empty($tokenResult) ||
        ($tokenResult['status'] ?? '') !== 'success' ||
        empty($tokenResult['token'])
    ) {
        responseNRN(
            'error',
            'Gagal membuat token SATUSEHAT.<br>'.
            escNRN($tokenResult['message'] ?? '')
        );
    }

    $token = trim((string)$tokenResult['token']);

    // CARI DOCUMENT REFERENCE BERDASARKAN NRN
    $identifier =
        'http://sys-ids.kemkes.go.id/prescription/national|'.
        $no_resep_nasional;

    $url = $base_url
        .'/fhir-r4/v1/DocumentReference'
        .'?identifier='.urlencode($identifier)
        .'&_include=DocumentReference:related';

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

        responseNRN(
            'error',
            'Gagal mengambil data NRN dari SATUSEHAT.<br>'.escNRN($error)
        );
    }

    $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    // DECODE
    $bundle = json_decode($response, true);

    if (
        json_last_error() !== JSON_ERROR_NONE ||
        !is_array($bundle)
    ) {
        responseNRN(
            'error',
            'Response SATUSEHAT tidak valid.<br>HTTP Code : '.$httpCode
        );
    }

    if (
        $httpCode < 200 ||
        $httpCode >= 300 ||
        ($bundle['resourceType'] ?? '') === 'OperationOutcome'
    ) {
        responseNRN(
            'error',
            'Gagal mengambil detail NRN.<br>'.
            escNRN(operationOutcomeNRN($bundle))
        );
    }

    if (
        ($bundle['resourceType'] ?? '') !== 'Bundle' ||
        empty($bundle['entry'])
    ) {
        responseNRN(
            'error',
            'Data resep dengan NRN <b>'.escNRN($no_resep_nasional).'</b> tidak ditemukan.'
        );
    }

    // PISAHKAN RESOURCE
    $documentReference  = null;
    $medicationRequests = [];
    $observations       = [];

    foreach ($bundle['entry'] as $entry) {
        $resource = $entry['resource'] ?? [];

        if (!is_array($resource)) continue;

        $type = $resource['resourceType'] ?? '';

        if ($type === 'DocumentReference' && $documentReference === null) {
            $documentReference = $resource;
        }

        if ($type === 'MedicationRequest') {
            $medicationRequests[] = $resource;
        }

        if ($type === 'Observation') {
            $observations[] = $resource;
        }
    }

    if (!$documentReference) {
        responseNRN('error', 'DocumentReference resep tidak ditemukan.');
    }

    // MASTER IDENTIFIER / NRN
    $nrn = trim(
        (string)($documentReference['masterIdentifier']['value'] ?? '')
    );

    $nrnSystem = trim(
        (string)($documentReference['masterIdentifier']['system'] ?? '')
    );

    if ($nrn === '') {
        $nrn = $no_resep_nasional;
    }

    // INFORMASI DOCUMENT
    $documentId = trim(
        (string)($documentReference['id'] ?? '')
    );

    $status = trim(
        (string)($documentReference['status'] ?? '')
    );

    $docStatus = trim(
        (string)($documentReference['docStatus'] ?? '')
    );

    $date = trim(
        (string)($documentReference['date'] ?? '')
    );

    $description = trim(
        (string)($documentReference['description'] ?? '')
    );

    // TYPE
    $typeCoding = $documentReference['type']['coding'][0] ?? [];

    $typeCode = trim(
        (string)($typeCoding['code'] ?? '')
    );

    $typeDisplay = trim(
        (string)($typeCoding['display'] ?? '')
    );

    $typeSystem = trim(
        (string)($typeCoding['system'] ?? '')
    );

    // PATIENT
    $patientReference = trim(
        (string)($documentReference['subject']['reference'] ?? '')
    );

    $patientDisplay = trim(
        (string)($documentReference['subject']['display'] ?? '')
    );

    // AUTHOR
    $authorReference = trim(
        (string)($documentReference['author'][0]['reference'] ?? '')
    );

    $authorDisplay = trim(
        (string)($documentReference['author'][0]['display'] ?? '')
    );

    // CUSTODIAN
    $custodianReference = trim(
        (string)($documentReference['custodian']['reference'] ?? '')
    );

    $custodianDisplay = trim(
        (string)($documentReference['custodian']['display'] ?? '')
    );

    // ENCOUNTER
    $encounterReference = trim(
        (string)($documentReference['context']['encounter'][0]['reference'] ?? '')
    );

    // IDENTIFIERS
    $identifierHtml = '';

    foreach (($documentReference['identifier'] ?? []) as $identifierItem) {
        $identifierHtml .= '
            <div class="row mb-2">
                <div class="col-md-4">
                    <small>Identifier</small>
                </div>
                <div class="col-md-8">
                    <small>
                        '.escNRN($identifierItem['value'] ?? '-').'<br>
                        <span class="text-muted">
                            '.escNRN($identifierItem['system'] ?? '-').'
                        </span>
                    </small>
                </div>
            </div>
        ';
    }

    if ($identifierHtml === '') {
        $identifierHtml = rowNRN('Identifier', '-');
    }

    // CONTENT
    $contentHtml = '';

    foreach (($documentReference['content'] ?? []) as $content) {
        $attachment = $content['attachment'] ?? [];
        $format     = $content['format'] ?? [];

        $contentHtml .= '
            <div class="row mb-2">
                <div class="col-md-4">
                    <small>'.escNRN($format['display'] ?? 'Content').'</small>
                </div>
                <div class="col-md-8">
                    <small>
                        '.escNRN($attachment['title'] ?? '-').'<br>
                        <span class="text-muted">
                            '.escNRN($attachment['url'] ?? '-').'
                        </span>
                    </small>
                </div>
            </div>
        ';
    }

    if ($contentHtml === '') {
        $contentHtml = rowNRN('Content', '-');
    }

    // ITEM MEDICATION REQUEST
    $itemHtml = '';

    foreach ($medicationRequests as $index => $mr) {
        $mrId = trim((string)($mr['id'] ?? ''));

        $medicationName = trim(
            (string)($mr['medicationReference']['display'] ?? '')
        );

        if ($medicationName === '' && !empty($mr['contained'])) {
            foreach ($mr['contained'] as $contained) {
                if (($contained['resourceType'] ?? '') !== 'Medication') continue;

                $medicationName = trim(
                    (string)($contained['code']['coding'][0]['display'] ?? '')
                );

                if ($medicationName !== '') break;
            }
        }

        $instruction = trim(
            (string)(
                $mr['dosageInstruction'][0]['patientInstruction']
                ?? $mr['dosageInstruction'][0]['text']
                ?? ''
            )
        );

        $dispense = $mr['dispenseRequest']['quantity'] ?? [];

        $qty = trim(
            (string)($dispense['value'] ?? '')
            .' '.
            (string)($dispense['unit'] ?? '')
        );

        $itemHtml .= '
            <div class="row py-2 '.($index > 0 ? 'border-top' : '').'">
                <div class="col-md-1 text-center">
                    <small>'.($index + 1).'</small>
                </div>

                <div class="col-md-5">
                    <small>'.escNRN($medicationName !== '' ? $medicationName : '-').'</small><br>
                    <small class="text-muted">'.escNRN($mrId).'</small>
                </div>

                <div class="col-md-2">
                    <small>'.escNRN(trim($qty)).'</small>
                </div>

                <div class="col-md-4">
                    <small>'.escNRN($instruction !== '' ? $instruction : '-').'</small>
                </div>
            </div>
        ';
    }

    if ($itemHtml === '') {
        $itemHtml = '
            <div class="row">
                <div class="col-md-12 text-center py-3">
                    <small class="text-muted">Tidak ada MedicationRequest.</small>
                </div>
            </div>
        ';
    }

    // JSON
    $jsonPretty = json_encode(
        $bundle,
        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    // HTML
    $html = '
        <div class="border border-secondary border-opacity-50 rounded-3 p-3">

            <div class="row mb-3">
                <div class="col-md-8">
                    <h6 class="mb-1">
                        <i class="bi bi-prescription2"></i>
                        Nomor Resep Nasional
                    </h6>
                    <small class="text-muted">
                        Data diperoleh dari DocumentReference SATUSEHAT.
                    </small>
                </div>

                <div class="col-md-4 text-md-end">
                    <span class="badge bg-success">
                        '.count($medicationRequests).' Item Resep
                    </span>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <small>NRN</small>
                </div>
                <div class="col-md-8">
                    <h5 class="mb-0 text-primary">
                        '.escNRN($nrn).'
                    </h5>
                    <small class="text-muted">
                        '.escNRN($nrnSystem).'
                    </small>
                </div>
            </div>

            <hr>

            '.rowNRN('DocumentReference ID', $documentId).'
            '.rowNRN('Status', $status).'
            '.rowNRN('Document Status', $docStatus).'
            '.rowNRN('Tanggal', $date).'
            '.rowNRN(
                'Type',
                trim($typeCode.' - '.$typeDisplay, ' -')
            ).'
            '.rowNRN('Type System', $typeSystem).'
            '.rowNRN('Description', $description).'

            <hr>

            '.$identifierHtml.'

            <hr>

            <div class="row mb-2">
                <div class="col-md-12">
                    <small><b>Pasien</b></small>
                </div>
            </div>

            '.rowNRN('Patient Reference', $patientReference).'
            '.rowNRN('Nama Pasien', $patientDisplay).'
            '.rowNRN('Encounter', $encounterReference).'

            <hr>

            <div class="row mb-2">
                <div class="col-md-12">
                    <small><b>Dokter & Fasyankes</b></small>
                </div>
            </div>

            '.rowNRN('Author Reference', $authorReference).'
            '.rowNRN('Nama Dokter', $authorDisplay).'
            '.rowNRN('Custodian Reference', $custodianReference).'
            '.rowNRN('Nama Fasyankes', $custodianDisplay).'

            <hr>

            <div class="row mb-2">
                <div class="col-md-12">
                    <small><b>Informasi Dokumen</b></small>
                </div>
            </div>

            '.$contentHtml.'

            <hr>

            <div class="row mb-2">
                <div class="col-md-8">
                    <small><b>Medication Request</b></small>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge bg-secondary">
                        '.count($medicationRequests).' Item
                    </span>
                </div>
            </div>

            <div class="row border-bottom pb-2">
                <div class="col-md-1 text-center"><small>No</small></div>
                <div class="col-md-5"><small>Medication</small></div>
                <div class="col-md-2"><small>Jumlah</small></div>
                <div class="col-md-4"><small>Instruksi</small></div>
            </div>

            '.$itemHtml.'

            '.(!empty($observations) ? '
                <div class="row mt-3">
                    <div class="col-md-12">
                        <small class="text-muted">
                            Terdapat '.count($observations).' Observation terkait.
                        </small>
                    </div>
                </div>
            ' : '').'

            <hr>

            <details>
                <summary class="text-primary" style="cursor:pointer;">
                    <small>Tampilkan JSON Response</small>
                </summary>

                <pre class="bg-light border rounded p-3 mt-2 mb-0"
                    style="max-height:500px; overflow:auto; font-size:12px;">'.escNRN($jsonPretty).'</pre>
            </details>

        </div>
    ';

    responseNRN(
        'success',
        'Detail Nomor Resep Nasional berhasil ditemukan.',
        $html
    );
?>