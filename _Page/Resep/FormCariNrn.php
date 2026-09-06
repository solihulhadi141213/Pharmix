<?php
    // CONNECTION, HELPER & SESSION
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');

    function responseNrn(string $status, string $message, string $html = ''): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'html'    => $html
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    function escNrn($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    function rowNrn(string $label, $value): string {
        $value = trim((string)($value ?? ''));

        return '
            <div class="row mb-2">
                <div class="col-md-4">
                    <small>'.$label.'</small>
                </div>
                <div class="col-md-8">
                    <input type="text" class="form-control form-control-sm"
                        value="'.escNrn($value !== '' ? $value : '-').'" readonly>
                </div>
            </div>
        ';
    }

    // VALIDASI SESSION & METHOD
    if (empty($SessionIdAkses)) {
        responseNrn('error', 'Sesi akses telah berakhir. Silakan login ulang.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseNrn('error', 'Metode request tidak valid.');
    }

    // ID DOCUMENT REFERENCE
    $id_document_reference = trim((string)($_POST['id_document_reference'] ?? ''));

    if ($id_document_reference === '') {
        responseNrn('error', 'ID DocumentReference tidak boleh kosong.');
    }

    // CEK DOCUMENT REFERENCE DI DATABASE
    $stmt = $Conn->prepare("
        SELECT
            id_medication_request_group,
            id_document_reference,
            no_resep_nasional,
            nama_pasien
        FROM medication_request_group
        WHERE id_document_reference = ?
        LIMIT 1
    ");

    if (!$stmt) {
        responseNrn('error', 'Gagal mempersiapkan data resep.');
    }

    $stmt->bind_param("s", $id_document_reference);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();

        responseNrn(
            'error',
            'Gagal membuka data resep.<br>Keterangan : '.
            escNrn($error)
        );
    }

    $group = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$group) {
        responseNrn('error', 'DocumentReference tidak ditemukan pada database lokal.');
    }

    $id_medication_request_group = (int)$group['id_medication_request_group'];
    $nama_pasien = trim((string)($group['nama_pasien'] ?? ''));

    // Jika NRN sudah tersimpan
    if (!empty($group['no_resep_nasional'])) {
        responseNrn(
            'error',
            'Nomor Resep Nasional sudah tersimpan: '.
            escNrn($group['no_resep_nasional'])
        );
    }

    // KONFIGURASI SATUSEHAT
    $stmt = $Conn->prepare("
        SELECT url_connection_satu_sehat
        FROM connection_satu_sehat
        WHERE status_connection_satu_sehat = 1
        LIMIT 1
    ");

    if (!$stmt) {
        responseNrn('error', 'Gagal membuka konfigurasi SATUSEHAT.');
    }

    if (!$stmt->execute()) {
        $stmt->close();
        responseNrn('error', 'Gagal membaca konfigurasi SATUSEHAT.');
    }

    $config = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$config) {
        responseNrn('error', 'Konfigurasi SATUSEHAT aktif tidak ditemukan.');
    }

    $base_url = rtrim(
        trim((string)($config['url_connection_satu_sehat'] ?? '')),
        '/'
    );

    if ($base_url === '') {
        responseNrn('error', 'URL SATUSEHAT belum dikonfigurasi.');
    }

    // TOKEN
    $tokenResult = generateTokenSatuSehat($Conn);

    if (
        empty($tokenResult) ||
        ($tokenResult['status'] ?? '') !== 'success' ||
        empty($tokenResult['token'])
    ) {
        responseNrn(
            'error',
            'Gagal membuat token SATUSEHAT.<br>'.
            escNrn($tokenResult['message'] ?? '')
        );
    }

    $token = trim((string)$tokenResult['token']);

    // GET DOCUMENT REFERENCE BY ID
    $url = $base_url.
        '/fhir-r4/v1/DocumentReference/'.
        rawurlencode($id_document_reference);

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

        responseNrn(
            'error',
            'Gagal mengambil DocumentReference dari SATUSEHAT.<br>'.
            escNrn($error)
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
        responseNrn(
            'error',
            'Response SATUSEHAT tidak valid.<br>HTTP Code : '.$httpCode
        );
    }

    // OPERATION OUTCOME
    if (
        $httpCode < 200 ||
        $httpCode >= 300 ||
        ($data['resourceType'] ?? '') === 'OperationOutcome'
    ) {
        $message = '';

        if (!empty($data['issue'])) {
            foreach ($data['issue'] as $issue) {
                $msg =
                    $issue['details']['text'] ??
                    $issue['diagnostics'] ??
                    $issue['code'] ??
                    '';

                if ($msg !== '') {
                    $message .= ($message !== '' ? ' | ' : '').$msg;
                }
            }
        }

        responseNrn(
            'error',
            'Gagal mengambil DocumentReference dari SATUSEHAT.<br>'.
            escNrn($message !== '' ? $message : 'HTTP Code '.$httpCode)
        );
    }

    if (($data['resourceType'] ?? '') !== 'DocumentReference') {
        responseNrn('error', 'Resource yang diterima bukan DocumentReference.');
    }

    // AMBIL NRN
    $masterIdentifier = $data['masterIdentifier'] ?? [];

    $master_system = trim((string)($masterIdentifier['system'] ?? ''));
    $nrn           = trim((string)($masterIdentifier['value'] ?? ''));

    if ($nrn === '') {
        responseNrn(
            'error',
            'Nomor Resep Nasional belum tersedia pada DocumentReference SATUSEHAT.'
        );
    }

    if (
        $master_system !== '' &&
        $master_system !== 'http://sys-ids.kemkes.go.id/prescription/national'
    ) {
        responseNrn(
            'error',
            'Master Identifier ditemukan tetapi bukan Nomor Resep Nasional.'
        );
    }

    // DETAIL DOCUMENT REFERENCE
    $document_id = trim((string)($data['id'] ?? ''));
    $status      = trim((string)($data['status'] ?? ''));
    $doc_status  = trim((string)($data['docStatus'] ?? ''));
    $date        = trim((string)($data['date'] ?? ''));
    $description = trim((string)($data['description'] ?? ''));

    // SUBJECT
    $subject_reference = trim((string)($data['subject']['reference'] ?? ''));
    $subject_display   = trim((string)($data['subject']['display'] ?? ''));

    // AUTHOR
    $author_reference = '';
    $author_display   = '';

    if (!empty($data['author'][0])) {
        $author_reference = trim((string)($data['author'][0]['reference'] ?? ''));
        $author_display   = trim((string)($data['author'][0]['display'] ?? ''));
    }

    // CUSTODIAN
    $custodian_reference = trim((string)($data['custodian']['reference'] ?? ''));
    $custodian_display   = trim((string)($data['custodian']['display'] ?? ''));

    // ENCOUNTER
    $encounter_reference = '';

    if (!empty($data['context']['encounter'][0]['reference'])) {
        $encounter_reference = trim(
            (string)$data['context']['encounter'][0]['reference']
        );
    }

    // TYPE
    $type_code    = '';
    $type_display = '';

    if (!empty($data['type']['coding'][0])) {
        $type_code    = trim((string)($data['type']['coding'][0]['code'] ?? ''));
        $type_display = trim((string)($data['type']['coding'][0]['display'] ?? ''));
    }

    // HTML
    $html = '
        <input type="hidden"
            name="id_medication_request_group"
            value="'.$id_medication_request_group.'">

        <input type="hidden"
            name="id_document_reference"
            value="'.escNrn($id_document_reference).'">

        <input type="hidden"
            name="no_resep_nasional"
            value="'.escNrn($nrn).'">

        <div class="alert alert-success">
            <small>
                <i class="bi bi-check-circle"></i>
                Nomor Resep Nasional berhasil ditemukan.
            </small>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <small><b>Nomor Resep Nasional</b></small>
            </div>
            <div class="card-body">
                <div class="text-center py-2">
                    <h4 class="mb-1">'.escNrn($nrn).'</h4>
                    <small class="text-muted">
                        '.escNrn($master_system).'
                    </small>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <small><b>Detail DocumentReference</b></small>
            </div>
            <div class="card-body">
                '.rowNrn('DocumentReference ID', $document_id).'
                '.rowNrn('Status', $status).'
                '.rowNrn('Doc Status', $doc_status).'
                '.rowNrn('Type', trim($type_code.' - '.$type_display, ' -')).'
                '.rowNrn('Tanggal', $date).'
                '.rowNrn('Description', $description).'
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <small><b>Pasien</b></small>
            </div>
            <div class="card-body">
                '.rowNrn('Pasien Lokal', $nama_pasien).'
                '.rowNrn('Subject Reference', $subject_reference).'
                '.rowNrn('Subject Display', $subject_display).'
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <small><b>Author & Fasyankes</b></small>
            </div>
            <div class="card-body">
                '.rowNrn('Author Reference', $author_reference).'
                '.rowNrn('Author Display', $author_display).'
                '.rowNrn('Custodian Reference', $custodian_reference).'
                '.rowNrn('Custodian Display', $custodian_display).'
            </div>
        </div>

        <div class="card mb-0">
            <div class="card-header">
                <small><b>Encounter</b></small>
            </div>
            <div class="card-body">
                '.rowNrn('Encounter Reference', $encounter_reference).'
            </div>
        </div>
    ';

    responseNrn(
        'success',
        'Nomor Resep Nasional berhasil ditemukan.',
        $html
    );
?>