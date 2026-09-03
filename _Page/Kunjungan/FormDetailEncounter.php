<?php
    // Konfigurasi & Inisialisasi
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');

    // Helper
    function responseJson($status, $message, $data = []) {
        echo json_encode(
            array_merge(['status' => $status, 'message' => $message], $data),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        exit;
    }

    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    function formatFhirDate($value) {
        if (empty($value)) return '-';

        try {
            $date = new DateTime($value);
            $date->setTimezone(new DateTimeZone('Asia/Jakarta'));
            return $date->format('d/m/Y H:i:s');
        } catch (Throwable $e) {
            return $value;
        }
    }

    function getFhirErrorMessage($data) {
        if (!is_array($data)) return 'Response SATUSEHAT tidak dapat dibaca.';

        if (($data['resourceType'] ?? '') === 'OperationOutcome') {
            $messages = [];
            foreach (($data['issue'] ?? []) as $issue) {
                $message = $issue['details']['text'] ?? $issue['diagnostics'] ?? $issue['code'] ?? '';
                if ($message !== '') $messages[] = $message;
            }
            if ($messages) return implode('<br>', array_unique($messages));
        }

        return $data['message'] ?? 'Gagal mengambil data Encounter dari SATUSEHAT.';
    }

    // Validasi Session
    if (empty($SessionIdAkses)) {
        responseJson('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
    }

    // Validasi Method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseJson('error', 'Metode request tidak valid.');
    }

    // Validasi ID Encounter
    $id_encounter = trim($_POST['id_encounter'] ?? '');
    if ($id_encounter === '') {
        responseJson('error', 'ID Encounter tidak boleh kosong.');
    }

    // Konfigurasi SATUSEHAT
    $stmt = mysqli_prepare($Conn, "SELECT url_connection_satu_sehat FROM connection_satu_sehat WHERE status_connection_satu_sehat = 1 LIMIT 1");
    if (!$stmt || !mysqli_stmt_execute($stmt)) {
        if ($stmt) mysqli_stmt_close($stmt);
        responseJson('error', 'Terjadi kesalahan saat membuka pengaturan SATUSEHAT.');
    }

    $result = mysqli_stmt_get_result($stmt);
    $setting = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    $baseurl_satusehat = rtrim(trim($setting['url_connection_satu_sehat'] ?? ''), '/');
    if ($baseurl_satusehat === '') {
        responseJson('error', 'URL koneksi SATUSEHAT belum dikonfigurasi.');
    }

    // Generate Token
    $tokenResult = generateTokenSatuSehat($Conn);
    if (($tokenResult['status'] ?? 'error') !== 'success') {
        responseJson('error', 'Gagal generate token SATUSEHAT.<br>Pesan : '.($tokenResult['message'] ?? '-'));
    }

    $token = trim($tokenResult['token'] ?? '');
    if ($token === '') {
        responseJson('error', 'Token SATUSEHAT tidak ditemukan.');
    }

    // URL Encounter
    $url = $baseurl_satusehat.'/fhir-r4/v1/Encounter/'.rawurlencode($id_encounter);

    // CURL
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer '.$token,
            'Accept: application/fhir+json'
        ]
    ]);

    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        $error = curl_error($curl);
        curl_close($curl);
        responseJson('error', 'CURL Error : '.$error);
    }

    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    // Decode Response
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        responseJson('error', 'Response SATUSEHAT bukan JSON yang valid.');
    }

    // Validasi Error
    if (($data['resourceType'] ?? '') === 'OperationOutcome' || $httpCode < 200 || $httpCode >= 300) {
        responseJson('error', getFhirErrorMessage($data), ['http_code' => $httpCode]);
    }

    if (($data['resourceType'] ?? '') !== 'Encounter') {
        responseJson('error', 'Resource yang diterima bukan Encounter.');
    }

    // Data Utama
    $id        = $data['id'] ?? '-';
    $status    = $data['status'] ?? '-';
    $class     = $data['class']['display'] ?? $data['class']['code'] ?? '-';
    $classCode = $data['class']['code'] ?? '-';

    $patientRef  = $data['subject']['reference'] ?? '-';
    $patientName = $data['subject']['display'] ?? '-';

    $periodStart = formatFhirDate($data['period']['start'] ?? '');
    $periodEnd   = formatFhirDate($data['period']['end'] ?? '');

    $priorityCode = $data['priority']['coding'][0]['code'] ?? '-';
    $priorityName = $data['priority']['coding'][0]['display'] ?? $data['priority']['text'] ?? '-';

    $organizationRef = $data['serviceProvider']['reference'] ?? '-';
    $organizationName = $data['serviceProvider']['display'] ?? '-';

    $identifierSystem = $data['identifier'][0]['system'] ?? '-';
    $identifierValue  = $data['identifier'][0]['value'] ?? '-';

    // Location
    $locations = [];
    foreach (($data['location'] ?? []) as $location) {
        $locations[] = [
            'reference' => $location['location']['reference'] ?? '-',
            'display'   => $location['location']['display'] ?? '-'
        ];
    }

    // Participant
    $participants = [];
    foreach (($data['participant'] ?? []) as $participant) {
        $participants[] = [
            'reference' => $participant['individual']['reference'] ?? '-',
            'display'   => $participant['individual']['display'] ?? '-',
            'type'      => $participant['type'][0]['coding'][0]['display']
                        ?? $participant['type'][0]['coding'][0]['code']
                        ?? '-'
        ];
    }

    // Status History
    $statusHistory = [];
    foreach (($data['statusHistory'] ?? []) as $history) {
        $statusHistory[] = [
            'status' => $history['status'] ?? '-',
            'start'  => formatFhirDate($history['period']['start'] ?? ''),
            'end'    => formatFhirDate($history['period']['end'] ?? '')
        ];
    }

    // Reason
    $reasons = [];
    foreach (($data['reasonCode'] ?? []) as $reason) {
        $reasons[] = $reason['text'] ?? $reason['coding'][0]['display'] ?? '-';
    }
    $reasonText = $reasons ? implode(', ', $reasons) : '-';

    // Badge Status
    $statusBadge = [
        'planned'          => 'secondary',
        'arrived'          => 'info',
        'triaged'          => 'warning',
        'in-progress'      => 'primary',
        'onleave'          => 'warning',
        'finished'         => 'success',
        'cancelled'        => 'danger',
        'entered-in-error' => 'danger',
        'unknown'          => 'secondary'
    ];

    $badge = $statusBadge[$status] ?? 'secondary';

    // HTML Detail
    $html = '
        <div class="row mb-2">
            <div class="col-12"><b>A. Informasi Encounter</b></div>
        </div>

        <div class="row mb-2">
            <div class="col-5"><small>ID Encounter</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.e($id).'</small></div>
        </div>

        <div class="row mb-2">
            <div class="col-5"><small>Status</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6">
                <span class="badge bg-'.$badge.'">'.e($status).'</span>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-5"><small>Class</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.e($class).' ('.e($classCode).')</small></div>
        </div>

        <div class="row mb-2">
            <div class="col-5"><small>Priority</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.e($priorityName).' ('.e($priorityCode).')</small></div>
        </div>

        <div class="row mb-2">
            <div class="col-5"><small>Periode Mulai</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.e($periodStart).'</small></div>
        </div>

        <div class="row mb-2">
            <div class="col-5"><small>Periode Selesai</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.e($periodEnd).'</small></div>
        </div>

        <div class="row mb-3">
            <div class="col-5"><small>Keluhan / Reason</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.e($reasonText).'</small></div>
        </div>

        <div class="row mb-2">
            <div class="col-12"><b>B. Informasi Pasien</b></div>
        </div>

        <div class="row mb-2">
            <div class="col-5"><small>Nama Pasien</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.e($patientName).'</small></div>
        </div>

        <div class="row mb-3">
            <div class="col-5"><small>Patient Reference</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.e($patientRef).'</small></div>
        </div>

        <div class="row mb-2">
            <div class="col-12"><b>C. Identifier</b></div>
        </div>

        <div class="row mb-2">
            <div class="col-5"><small>System</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.e($identifierSystem).'</small></div>
        </div>

        <div class="row mb-3">
            <div class="col-5"><small>Value</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.e($identifierValue).'</small></div>
        </div>
    ';

    // Participant
    $html .= '
        <div class="row mb-2">
            <div class="col-12"><b>D. Participant</b></div>
        </div>
    ';

    if ($participants) {
        foreach ($participants as $no => $participant) {
            $html .= '
                <div class="row mb-2">
                    <div class="col-5"><small>Participant '.($no + 1).'</small></div>
                    <div class="col-1"><small>:</small></div>
                    <div class="col-6">
                        <small>
                            '.e($participant['display']).'<br>
                            <span class="text-muted">'.e($participant['reference']).'</span>
                            '.($participant['type'] !== '-' ? '<br><span class="text-muted">'.e($participant['type']).'</span>' : '').'
                        </small>
                    </div>
                </div>
            ';
        }
    } else {
        $html .= '
            <div class="row mb-3">
                <div class="col-5"><small>Participant</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6"><small>-</small></div>
            </div>
        ';
    }

    // Location
    $html .= '
        <div class="row mt-3 mb-2">
            <div class="col-12"><b>E. Lokasi Pelayanan</b></div>
        </div>
    ';

    if ($locations) {
        foreach ($locations as $location) {
            $html .= '
                <div class="row mb-2">
                    <div class="col-5"><small>Location</small></div>
                    <div class="col-1"><small>:</small></div>
                    <div class="col-6">
                        <small>
                            '.e($location['display']).'<br>
                            <span class="text-muted">'.e($location['reference']).'</span>
                        </small>
                    </div>
                </div>
            ';
        }
    } else {
        $html .= '
            <div class="row mb-2">
                <div class="col-5"><small>Location</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6"><small>-</small></div>
            </div>
        ';
    }

    // Organization
    $html .= '
        <div class="row mt-3 mb-2">
            <div class="col-12"><b>F. Service Provider</b></div>
        </div>

        <div class="row mb-2">
            <div class="col-5"><small>Organization</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6">
                <small>
                    '.e($organizationName).'<br>
                    <span class="text-muted">'.e($organizationRef).'</span>
                </small>
            </div>
        </div>
    ';

    // Status History
    $html .= '
        <div class="row mt-3 mb-2">
            <div class="col-12"><b>G. Status History</b></div>
        </div>
    ';

    if ($statusHistory) {
        foreach ($statusHistory as $history) {
            $html .= '
                <div class="row mb-2">
                    <div class="col-5"><small>'.e($history['status']).'</small></div>
                    <div class="col-1"><small>:</small></div>
                    <div class="col-6">
                        <small>
                            '.e($history['start']).'
                            '.($history['end'] !== '-' ? ' - '.e($history['end']) : '').'
                        </small>
                    </div>
                </div>
            ';
        }
    } else {
        $html .= '
            <div class="row mb-2">
                <div class="col-5"><small>Status History</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6"><small>-</small></div>
            </div>
        ';
    }

    responseJson('success', 'Detail Encounter berhasil dimuat.', [
        'html'         => $html,
        'id_encounter' => $id
    ]);
?>