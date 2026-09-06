<?php
    // CONNECTION, HELPER & SESSION
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    date_default_timezone_set('Asia/Jakarta');
    header('Content-Type: application/json; charset=utf-8');

    function sendResponseMR(string $status, string $message, array $data = []): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'data'    => $data
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    function operationOutcomeMR(array $data): string {
        if (($data['resourceType'] ?? '') !== 'OperationOutcome' || empty($data['issue'])) {
            return 'SATUSEHAT mengembalikan response error.';
        }

        $messages = [];
        foreach ($data['issue'] as $issue) {
            $message = $issue['details']['text'] ?? $issue['diagnostics'] ?? $issue['code'] ?? '';
            if ($message !== '') $messages[] = $message;
        }

        return !empty($messages)
            ? implode(' | ', $messages)
            : 'SATUSEHAT mengembalikan OperationOutcome.';
    }

    function normalizeCodeMR($value): string {
        $value = trim((string)($value ?? ''));

        if (str_contains($value, '|')) {
            $parts = explode('|', $value, 2);
            return trim($parts[0] ?? '');
        }

        return $value;
    }

    // VALIDASI SESSION & METHOD
    if (empty($SessionIdAkses)) {
        sendResponseMR('error', 'Sesi akses telah berakhir. Silakan login ulang.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponseMR('error', 'Metode request tidak valid.');
    }

    // HANYA ID YANG DIPERCAYA DARI FORM
    $MedicationRequestId = trim((string)($_POST['MedicationRequestId'] ?? ''));

    if ($MedicationRequestId === '') {
        sendResponseMR('error', 'ID Medication Request tidak boleh kosong.');
    }

    // AMBIL SELURUH DATA YANG DIPERLUKAN
    $stmt = $Conn->prepare("
        SELECT
            mr.MedicationRequestId,
            mr.id_medication_request_group,
            mr.id_medication_request,
            mr.intent,
            mr.id_index_medication,
            mr.name_medication,
            mr.status,
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
            mrg.priority,
            mrg.datetime_creat,
            mrg.dokter_ihs,
            mrg.dokter_nama,
            mrg.reason_code,
            mrg.reason_display,
            mrg.reason_system,

            a.id_ihs AS patient_ihs,
            a.nama AS patient_name,

            k.id_encounter,

            med.id_medication,
            med.medication_name

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
        sendResponseMR('error', 'Gagal mempersiapkan data Medication Request.');
    }

    $stmt->bind_param("s", $MedicationRequestId);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();

        sendResponseMR(
            'error',
            'Gagal membuka data Medication Request.<br>Keterangan : '.
            htmlspecialchars($error, ENT_QUOTES, 'UTF-8')
        );
    }

    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$data) {
        sendResponseMR('error', 'Data Medication Request tidak ditemukan.');
    }

    // CEGAH PENGIRIMAN GANDA
    if (!empty($data['id_medication_request'])) {
        sendResponseMR(
            'error',
            'Medication Request sudah pernah dikirim ke SATUSEHAT dengan ID '.$data['id_medication_request'].'.'
        );
    }

    // MAPPING
    $status          = trim((string)($data['status'] ?? ''));
    $intent          = trim((string)($data['intent'] ?? ''));
    $priority        = trim((string)($data['priority'] ?? ''));
    $id_medication   = trim((string)($data['id_medication'] ?? ''));
    $medication_name = trim((string)($data['medication_name'] ?? $data['name_medication'] ?? ''));

    $patient_ihs  = trim((string)($data['patient_ihs'] ?? ''));
    $patient_name = trim((string)($data['patient_name'] ?? ''));
    $id_encounter = trim((string)($data['id_encounter'] ?? ''));

    $dokter_ihs  = trim((string)($data['dokter_ihs'] ?? ''));
    $dokter_nama = trim((string)($data['dokter_nama'] ?? ''));

    $reason_code    = trim((string)($data['reason_code'] ?? ''));
    $reason_display = trim((string)($data['reason_display'] ?? ''));
    $reason_system  = trim((string)($data['reason_system'] ?? ''));

    $dosage_text = trim((string)($data['dosage_inst_text'] ?? ''));
    $frequency   = (int)($data['dosage_inst_frequency'] ?? 0);
    $period      = (int)($data['dosage_inst_period'] ?? 0);
    $period_unit = trim((string)($data['dosage_inst_period_unit'] ?? ''));

    $dose_value  = (float)($data['dose_value'] ?? 0);
    $dose_unit   = trim((string)($data['dose_unit'] ?? ''));
    $dose_code   = trim((string)($data['dose_code'] ?? ''));
    $dose_system = trim((string)($data['dose_system'] ?? ''));

    $route_display = trim((string)($data['route_display'] ?? ''));
    $route_code    = normalizeCodeMR($data['route_code'] ?? '');
    $route_system  = trim((string)($data['route_system'] ?? ''));

    $dispense_value  = (float)($data['dispense_value'] ?? 0);
    $dispense_unit   = trim((string)($data['dispense_unit'] ?? ''));
    $dispense_code   = trim((string)($data['dispense_code'] ?? ''));
    $dispense_system = trim((string)($data['dispense_sys'] ?? ''));

    $supply_value  = (float)($data['supply_duration_value'] ?? 0);
    $supply_unit   = trim((string)($data['supply_duration_unit'] ?? ''));
    $supply_code   = normalizeCodeMR($data['supply_duration_code'] ?? '');
    $supply_system = trim((string)($data['supply_duration_sys'] ?? ''));

    if (str_contains($dose_code, '|')) {
        [$dose_code_raw, $dose_display_raw] = array_pad(explode('|', $dose_code, 2), 2, '');

        $dose_code = trim($dose_code_raw);

        if ($dose_unit === '') {
            $dose_unit = trim($dose_display_raw);
        }
    }

    if (str_contains($dispense_code, '|')) {
        [$dispense_code_raw, $dispense_display_raw] = array_pad(explode('|', $dispense_code, 2), 2, '');

        $dispense_code = trim($dispense_code_raw);

        if ($dispense_unit === '') {
            $dispense_unit = trim($dispense_display_raw);
        }
    }

    // VALIDASI DATA WAJIB
    $missing = [];

    if ($status === '') $missing[] = 'Status';
    if ($intent === '') $missing[] = 'Intent';
    if ($priority === '') $missing[] = 'Priority';

    if ($id_medication === '') $missing[] = 'ID Medication SATUSEHAT';
    if ($patient_ihs === '') $missing[] = 'Patient IHS ID';
    if ($id_encounter === '') $missing[] = 'Encounter ID';
    if ($dokter_ihs === '') $missing[] = 'Practitioner IHS Dokter';

    if ($frequency < 1) $missing[] = 'Frequency';
    if ($period < 1) $missing[] = 'Period';
    if ($period_unit === '') $missing[] = 'Period Unit';

    if ($dose_value <= 0) $missing[] = 'Dose Value';
    if ($dose_code === '') $missing[] = 'Dose Code';
    if ($dose_system === '') $missing[] = 'Dose System';

    if ($route_code === '') $missing[] = 'Route Code';
    if ($route_system === '') $missing[] = 'Route System';

    if ($dispense_value <= 0) $missing[] = 'Dispense Value';
    if ($dispense_code === '') $missing[] = 'Dispense Code';
    if ($dispense_system === '') $missing[] = 'Dispense System';

    if ($supply_value <= 0) $missing[] = 'Supply Duration Value';
    if ($supply_code === '') $missing[] = 'Supply Duration Code';
    if ($supply_system === '') $missing[] = 'Supply Duration System';

    if (!empty($missing)) {
        sendResponseMR(
            'error',
            'Data Medication Request belum lengkap.<br>'.
            implode('<br>', array_map(
                fn($item) => '• '.htmlspecialchars($item, ENT_QUOTES, 'UTF-8'),
                $missing
            ))
        );
    }

    // VALIDASI ENUM FHIR
    if (!in_array($status, ['active','on-hold','completed','stopped','cancelled','entered-in-error'], true)) {
        sendResponseMR('error', 'Status Medication Request tidak valid.');
    }

    if (!in_array($intent, ['order','plan','proposal'], true)) {
        sendResponseMR('error', 'Intent Medication Request tidak valid.');
    }

    if (!in_array($priority, ['routine','urgent','asap','stat'], true)) {
        sendResponseMR('error', 'Priority Medication Request tidak valid.');
    }

    // AUTHORED ON -> UTC +00:00
    $datetime_creat = trim((string)($data['datetime_creat'] ?? ''));

    if ($datetime_creat === '' || strtotime($datetime_creat) === false) {
        sendResponseMR('error', 'Tanggal dan waktu resep tidak valid.');
    }

    try {
        $authoredOn = new DateTime(
            $datetime_creat,
            new DateTimeZone('Asia/Jakarta')
        );

        $authoredOn->setTimezone(
            new DateTimeZone('UTC')
        );

        $authored_on = $authoredOn->format('Y-m-d\TH:i:sP');

    } catch (Throwable $e) {
        sendResponseMR('error', 'Gagal membentuk authoredOn.');
    }

    // KONFIGURASI SATUSEHAT
    $stmt = $Conn->prepare("
        SELECT
            url_connection_satu_sehat,
            organization_id
        FROM connection_satu_sehat
        WHERE status_connection_satu_sehat = 1
        LIMIT 1
    ");

    if (!$stmt) {
        sendResponseMR('error', 'Gagal membuka konfigurasi SATUSEHAT.');
    }

    if (!$stmt->execute()) {
        $stmt->close();
        sendResponseMR('error', 'Gagal membaca konfigurasi SATUSEHAT.');
    }

    $config = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$config) {
        sendResponseMR('error', 'Konfigurasi SATUSEHAT aktif tidak ditemukan.');
    }

    $base_url = rtrim(
        trim((string)($config['url_connection_satu_sehat'] ?? '')),
        '/'
    );

    $organization_id = trim(
        (string)($config['organization_id'] ?? '')
    );

    if ($base_url === '') {
        sendResponseMR('error', 'URL SATUSEHAT belum dikonfigurasi.');
    }

    if ($organization_id === '') {
        sendResponseMR('error', 'Organization IHS ID belum dikonfigurasi.');
    }

    // TOKEN
    $tokenResult = generateTokenSatuSehat($Conn);

    if (
        empty($tokenResult) ||
        ($tokenResult['status'] ?? '') !== 'success' ||
        empty($tokenResult['token'])
    ) {
        sendResponseMR(
            'error',
            'Gagal membuat token SATUSEHAT.<br>'.
            htmlspecialchars(
                (string)($tokenResult['message'] ?? ''),
                ENT_QUOTES,
                'UTF-8'
            )
        );
    }

    $token = trim((string)$tokenResult['token']);

    // PAYLOAD DASAR
    $payload = [
        'resourceType' => 'MedicationRequest',

        'identifier' => [
            [
                'use'    => 'official',
                'system' => 'http://sys-ids.kemkes.go.id/prescription-item/'.$organization_id,
                'value'  => $MedicationRequestId
            ]
        ],

        'status'   => $status,
        'intent'   => $intent,
        'priority' => $priority,

        'medicationReference' => [
            'reference' => 'Medication/'.$id_medication,
            'display'   => $medication_name
        ],

        'subject' => [
            'reference' => 'Patient/'.$patient_ihs,
            'display'   => $patient_name
        ],

        'encounter' => [
            'reference' => 'Encounter/'.$id_encounter
        ],

        'authoredOn' => $authored_on,

        'requester' => [
            'reference' => 'Practitioner/'.$dokter_ihs,
            'display'   => $dokter_nama
        ]
    ];

    // REASON CODE
    if ($reason_code !== '') {
        if ($reason_system === '') {
            $reason_system = 'http://hl7.org/fhir/sid/icd-10';
        }

        $payload['reasonCode'] = [
            [
                'coding' => [
                    [
                        'system'  => $reason_system,
                        'code'    => $reason_code,
                        'display' => $reason_display
                    ]
                ]
            ]
        ];
    }

    // DOSAGE INSTRUCTION
    $dosageInstruction = [
        'sequence' => 1,

        'timing' => [
            'repeat' => [
                'frequency'  => $frequency,
                'period'     => $period,
                'periodUnit' => $period_unit
            ]
        ],

        'route' => [
            'coding' => [
                [
                    'system'  => $route_system,
                    'code'    => $route_code,
                    'display' => $route_display
                ]
            ]
        ],

        'doseAndRate' => [
            [
                'type' => [
                    'coding' => [
                        [
                            'system'  => 'http://terminology.hl7.org/CodeSystem/dose-rate-type',
                            'code'    => 'ordered',
                            'display' => 'Ordered'
                        ]
                    ]
                ],

                'doseQuantity' => [
                    'value'  => $dose_value,
                    'unit'   => $dose_unit,
                    'system' => $dose_system,
                    'code'   => $dose_code
                ]
            ]
        ]
    ];

    if ($dosage_text !== '') {
        $dosageInstruction['text'] = $dosage_text;
        $dosageInstruction['patientInstruction'] = $dosage_text;
    }

    $payload['dosageInstruction'] = [
        $dosageInstruction
    ];

    // DISPENSE REQUEST
    $payload['dispenseRequest'] = [
        'quantity' => [
            'value'  => $dispense_value,
            'unit'   => $dispense_unit,
            'system' => $dispense_system,
            'code'   => $dispense_code
        ],

        'expectedSupplyDuration' => [
            'value'  => $supply_value,
            'unit'   => $supply_unit !== '' ? $supply_unit : $supply_code,
            'system' => $supply_system,
            'code'   => $supply_code
        ]
    ];

    // SUBSTITUTION
    $payload['substitution'] = [
        'allowedBoolean' => false
    ];

    // ENCODE PAYLOAD
    $payloadJson = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    if ($payloadJson === false) {
        sendResponseMR('error', 'Gagal membentuk payload Medication Request.');
    }

    // KIRIM KE SATUSEHAT
    $url = $base_url.'/fhir-r4/v1/MedicationRequest';

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => $payloadJson,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer '.$token,
            'Content-Type: application/json',
            'Accept: application/fhir+json'
        ],
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $satusehatResponse = curl_exec($curl);

    if ($satusehatResponse === false) {
        $curlError = curl_error($curl);
        curl_close($curl);

        sendResponseMR(
            'error',
            'Gagal mengirim Medication Request ke SATUSEHAT.<br>'.
            htmlspecialchars($curlError, ENT_QUOTES, 'UTF-8')
        );
    }

    $httpCode = (int)curl_getinfo(
        $curl,
        CURLINFO_HTTP_CODE
    );

    curl_close($curl);

    // DECODE RESPONSE
    $satusehatData = json_decode(
        $satusehatResponse,
        true
    );

    if (
        json_last_error() !== JSON_ERROR_NONE ||
        !is_array($satusehatData)
    ) {
        sendResponseMR(
            'error',
            'SATUSEHAT mengembalikan response yang tidak valid.<br>HTTP Code : '.$httpCode
        );
    }

    // ERROR SATUSEHAT
    if (
        $httpCode < 200 ||
        $httpCode >= 300 ||
        ($satusehatData['resourceType'] ?? '') === 'OperationOutcome'
    ) {
        sendResponseMR(
            'error',
            'Pengiriman Medication Request ke SATUSEHAT gagal.<br>'.
            htmlspecialchars(
                operationOutcomeMR($satusehatData),
                ENT_QUOTES,
                'UTF-8'
            )
        );
    }

    // ID RESOURCE
    $id_medication_request = trim(
        (string)($satusehatData['id'] ?? '')
    );

    if ($id_medication_request === '') {
        sendResponseMR(
            'error',
            'SATUSEHAT tidak mengembalikan ID Medication Request.'
        );
    }

    // UPDATE DATABASE LOKAL
    $stmt = $Conn->prepare("
        UPDATE medication_request
        SET id_medication_request = ?
        WHERE MedicationRequestId = ?
        AND id_medication_request IS NULL
    ");

    if (!$stmt) {
        sendResponseMR(
            'error',
            'Medication Request berhasil dibuat di SATUSEHAT dengan ID '.
            htmlspecialchars($id_medication_request, ENT_QUOTES, 'UTF-8').
            ', tetapi gagal mempersiapkan update database lokal.',
            [
                'id_medication_request' => $id_medication_request
            ]
        );
    }

    $stmt->bind_param(
        "ss",
        $id_medication_request,
        $MedicationRequestId
    );

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();

        sendResponseMR(
            'error',
            'Medication Request berhasil dibuat di SATUSEHAT dengan ID '.
            htmlspecialchars($id_medication_request, ENT_QUOTES, 'UTF-8').
            ', tetapi database lokal gagal diperbarui.<br>Keterangan : '.
            htmlspecialchars($error, ENT_QUOTES, 'UTF-8'),
            [
                'id_medication_request' => $id_medication_request
            ]
        );
    }

    if ($stmt->affected_rows !== 1) {
        $stmt->close();

        sendResponseMR(
            'error',
            'Medication Request berhasil dibuat di SATUSEHAT dengan ID '.
            htmlspecialchars($id_medication_request, ENT_QUOTES, 'UTF-8').
            ', tetapi ID tersebut tidak berhasil disimpan pada item resep lokal.',
            [
                'id_medication_request' => $id_medication_request
            ]
        );
    }

    $stmt->close();

    // SUCCESS
    sendResponseMR(
        'success',
        'Medication Request berhasil dikirim ke SATUSEHAT.',
        [
            'MedicationRequestId'   => $MedicationRequestId,
            'id_medication_request' => $id_medication_request,
            'id_medication'         => $id_medication,
            'id_encounter'          => $id_encounter
        ]
    );
?>