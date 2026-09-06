<?php
    // CONNECTION, HELPER & SESSION
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

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

    function rowDR(string $label, $value): string {
        $value = trim((string)($value ?? ''));

        return '
            <div class="row mb-2">
                <div class="col-md-4">
                    <small>'.$label.'</small>
                </div>
                <div class="col-md-8">
                    <small>'.escDR($value !== '' ? $value : '-').'</small>
                </div>
            </div>
        ';
    }

    function operationOutcomeDR(array $data): string {
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
        responseDR('error', 'Sesi akses telah berakhir. Silakan login ulang.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseDR('error', 'Metode request tidak valid.');
    }

    // ID DOCUMENT REFERENCE
    $id_document_reference = trim(
        (string)($_POST['id_document_reference'] ?? '')
    );

    if ($id_document_reference === '') {
        responseDR('error', 'ID DocumentReference tidak boleh kosong.');
    }

    // KONFIGURASI SATUSEHAT
    $stmt = $Conn->prepare("
        SELECT url_connection_satu_sehat
        FROM connection_satu_sehat
        WHERE status_connection_satu_sehat = 1
        LIMIT 1
    ");

    if (!$stmt) {
        responseDR('error', 'Gagal membuka konfigurasi SATUSEHAT.');
    }

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();

        responseDR(
            'error',
            'Gagal membaca konfigurasi SATUSEHAT.<br>'.escDR($error)
        );
    }

    $config = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$config) {
        responseDR('error', 'Konfigurasi SATUSEHAT aktif tidak ditemukan.');
    }

    $base_url = rtrim(
        trim((string)($config['url_connection_satu_sehat'] ?? '')),
        '/'
    );

    if ($base_url === '') {
        responseDR('error', 'URL SATUSEHAT belum dikonfigurasi.');
    }

    // TOKEN SATUSEHAT
    $tokenResult = generateTokenSatuSehat($Conn);

    if (
        empty($tokenResult) ||
        ($tokenResult['status'] ?? '') !== 'success' ||
        empty($tokenResult['token'])
    ) {
        responseDR(
            'error',
            'Gagal membuat token SATUSEHAT.<br>'.
            escDR($tokenResult['message'] ?? '')
        );
    }

    $token = trim((string)$tokenResult['token']);

    // GET DOCUMENT REFERENCE BY ID
    $url = $base_url
        .'/fhir-r4/v1/DocumentReference/'
        .rawurlencode($id_document_reference);

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

        responseDR(
            'error',
            'Gagal mengambil DocumentReference dari SATUSEHAT.<br>'.escDR($error)
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
        responseDR(
            'error',
            'Response SATUSEHAT tidak valid.<br>HTTP Code : '.$httpCode
        );
    }

    if (
        $httpCode < 200 ||
        $httpCode >= 300 ||
        ($data['resourceType'] ?? '') === 'OperationOutcome'
    ) {
        responseDR(
            'error',
            'Gagal mengambil DocumentReference.<br>'.
            escDR(operationOutcomeDR($data))
        );
    }

    if (($data['resourceType'] ?? '') !== 'DocumentReference') {
        responseDR('error', 'Resource yang diterima bukan DocumentReference.');
    }

    // INFORMASI DASAR
    $resource_id = trim((string)($data['id'] ?? ''));
    $status      = trim((string)($data['status'] ?? ''));
    $doc_status  = trim((string)($data['docStatus'] ?? ''));
    $date        = trim((string)($data['date'] ?? ''));
    $description = trim((string)($data['description'] ?? ''));

    // MASTER IDENTIFIER / NRN
    $master_system = trim(
        (string)($data['masterIdentifier']['system'] ?? '')
    );

    $master_value = trim(
        (string)($data['masterIdentifier']['value'] ?? '')
    );

    // TYPE
    $typeCoding = $data['type']['coding'][0] ?? [];

    $type_code = trim((string)($typeCoding['code'] ?? ''));
    $type_display = trim((string)($typeCoding['display'] ?? ''));
    $type_system = trim((string)($typeCoding['system'] ?? ''));

    // SUBJECT
    $subject_reference = trim(
        (string)($data['subject']['reference'] ?? '')
    );

    $subject_display = trim(
        (string)($data['subject']['display'] ?? '')
    );

    // AUTHOR
    $author_reference = '';
    $author_display   = '';

    if (!empty($data['author'][0])) {
        $author_reference = trim(
            (string)($data['author'][0]['reference'] ?? '')
        );

        $author_display = trim(
            (string)($data['author'][0]['display'] ?? '')
        );
    }

    // CUSTODIAN
    $custodian_reference = trim(
        (string)($data['custodian']['reference'] ?? '')
    );

    $custodian_display = trim(
        (string)($data['custodian']['display'] ?? '')
    );

    // ENCOUNTER
    $encounter_reference = '';

    if (!empty($data['context']['encounter'][0]['reference'])) {
        $encounter_reference = trim(
            (string)$data['context']['encounter'][0]['reference']
        );
    }

    // IDENTIFIERS
    $identifierHtml = '';

    foreach (($data['identifier'] ?? []) as $identifier) {
        $identifierHtml .= '
            <div class="row mb-2">
                <div class="col-md-4">
                    <small>Identifier</small>
                </div>
                <div class="col-md-8">
                    <small>
                        '.escDR($identifier['value'] ?? '-').'<br>
                        <span class="text-muted">
                            '.escDR($identifier['system'] ?? '-').'
                        </span>
                    </small>
                </div>
            </div>
        ';
    }

    if ($identifierHtml === '') {
        $identifierHtml = rowDR('Identifier', '-');
    }

    // CATEGORY
    $categoryHtml = '';

    foreach (($data['category'] ?? []) as $category) {
        foreach (($category['coding'] ?? []) as $coding) {
            $categoryHtml .= '
                <div class="row mb-2">
                    <div class="col-md-4">
                        <small>Category</small>
                    </div>
                    <div class="col-md-8">
                        <small>
                            '.escDR($coding['code'] ?? '-').' -
                            '.escDR($coding['display'] ?? '-').'<br>
                            <span class="text-muted">
                                '.escDR($coding['system'] ?? '-').'
                            </span>
                        </small>
                    </div>
                </div>
            ';
        }
    }

    if ($categoryHtml === '') {
        $categoryHtml = rowDR('Category', '-');
    }

    // CONTENT
    $contentHtml = '';

    foreach (($data['content'] ?? []) as $content) {
        $attachment = $content['attachment'] ?? [];
        $format     = $content['format'] ?? [];

        $title = trim((string)($attachment['title'] ?? ''));
        $urlAttachment = trim((string)($attachment['url'] ?? ''));

        $contentHtml .= '
            <div class="row mb-3">
                <div class="col-md-4">
                    <small>
                        '.escDR($format['display'] ?? 'Content').'
                    </small>
                </div>
                <div class="col-md-8">
                    <small>
                        '.escDR($title !== '' ? $title : '-').'
                    </small>';

        if ($urlAttachment !== '') {
            $contentHtml .= '
                    <br>
                    <a href="'.escDR($urlAttachment).'"
                       target="_blank"
                       rel="noopener noreferrer">
                        <small>'.escDR($urlAttachment).'</small>
                    </a>
            ';
        }

        $contentHtml .= '
                    <br>
                    <small class="text-muted">
                        '.escDR($format['code'] ?? '-').' |
                        '.escDR($format['system'] ?? '-').'
                    </small>
                </div>
            </div>
        ';
    }

    if ($contentHtml === '') {
        $contentHtml = rowDR('Content', '-');
    }

    // RELATED RESOURCE
    $relatedHtml = '';

    foreach (($data['context']['related'] ?? []) as $related) {
        $reference = trim((string)($related['reference'] ?? ''));
        $display   = trim((string)($related['display'] ?? ''));

        $relatedHtml .= '
            <div class="row mb-2">
                <div class="col-md-4">
                    <small>Related Resource</small>
                </div>
                <div class="col-md-8">
                    <small>'.escDR($reference !== '' ? $reference : '-').'</small>';

        if ($display !== '') {
            $relatedHtml .= '
                    <br>
                    <small class="text-muted">'.escDR($display).'</small>
            ';
        }

        $relatedHtml .= '
                </div>
            </div>
        ';
    }

    if ($relatedHtml === '') {
        $relatedHtml = rowDR('Related Resource', '-');
    }

    // FORMAT TANGGAL
    $dateDisplay = $date;

    if ($date !== '' && strtotime($date) !== false) {
        try {
            $dt = new DateTime($date);
            $dt->setTimezone(new DateTimeZone('Asia/Jakarta'));
            $dateDisplay = $dt->format('d-m-Y H:i:s');
        } catch (Throwable $e) {
            $dateDisplay = $date;
        }
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
                <div class="col-md-8">
                    <h6 class="mb-1">
                        <i class="bi bi-file-earmark-medical"></i>
                        Informasi DocumentReference
                    </h6>
                    <small class="text-muted">
                        Data diperoleh langsung dari SATUSEHAT.
                    </small>
                </div>

                <div class="col-md-4 text-md-end">
                    <span class="badge bg-secondary">
                        '.escDR($status !== '' ? $status : '-').'
                    </span>
                </div>
            </div>

            '.rowDR('Resource Type', $data['resourceType'] ?? '').'
            '.rowDR('DocumentReference ID', $resource_id).'
            '.rowDR('Status', $status).'
            '.rowDR('Document Status', $doc_status).'
            '.rowDR('Tanggal', $dateDisplay).'
            '.rowDR('Description', $description).'

            <hr>

            <div class="row mb-2">
                <div class="col-md-12">
                    <small><b>Nomor Resep Nasional / Master Identifier</b></small>
                </div>
            </div>

            '.rowDR('Master Identifier', $master_value).'
            '.rowDR('Master System', $master_system).'

            <hr>

            <div class="row mb-2">
                <div class="col-md-12">
                    <small><b>Jenis Dokumen</b></small>
                </div>
            </div>

            '.rowDR(
                'Type',
                trim($type_code.' - '.$type_display, ' -')
            ).'

            '.rowDR('Type System', $type_system).'

            '.$categoryHtml.'

            <hr>

            <div class="row mb-2">
                <div class="col-md-12">
                    <small><b>Identifier</b></small>
                </div>
            </div>

            '.$identifierHtml.'

            <hr>

            <div class="row mb-2">
                <div class="col-md-12">
                    <small><b>Pasien & Encounter</b></small>
                </div>
            </div>

            '.rowDR('Subject Reference', $subject_reference).'
            '.rowDR('Subject Display', $subject_display).'
            '.rowDR('Encounter Reference', $encounter_reference).'

            <hr>

            <div class="row mb-2">
                <div class="col-md-12">
                    <small><b>Author & Custodian</b></small>
                </div>
            </div>

            '.rowDR('Author Reference', $author_reference).'
            '.rowDR('Author Display', $author_display).'
            '.rowDR('Custodian Reference', $custodian_reference).'
            '.rowDR('Custodian Display', $custodian_display).'

            <hr>

            <div class="row mb-2">
                <div class="col-md-12">
                    <small><b>Konten Dokumen</b></small>
                </div>
            </div>

            '.$contentHtml.'

            <hr>

            <div class="row mb-2">
                <div class="col-md-12">
                    <small><b>Resource Terkait</b></small>
                </div>
            </div>

            '.$relatedHtml.'

            <hr>

            <details>
                <summary class="text-primary" style="cursor:pointer;">
                    <small>Tampilkan JSON Response</small>
                </summary>

                <pre class="bg-light border rounded p-3 mt-2 mb-0"
                    style="max-height:500px; overflow:auto; font-size:12px;">'.escDR($jsonPretty).'</pre>
            </details>

        </div>
    ';

    responseDR(
        'success',
        'Detail DocumentReference berhasil ditemukan.',
        $html
    );
?>