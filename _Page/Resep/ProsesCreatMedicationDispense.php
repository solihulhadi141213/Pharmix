<?php
    // CONNECTION, HELPER & SESSION
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    date_default_timezone_set('Asia/Jakarta');
    header('Content-Type: application/json; charset=utf-8');

    function responseDispense(string $status, string $message, array $data = []): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'data'    => $data
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    function escDispense($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    function operationOutcomeDispense(array $data): string {
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

    function referenceIdDispense($reference): string {
        $reference = trim((string)$reference);

        if ($reference === '') return '';

        $parts = explode('/', $reference);
        return trim((string)end($parts));
    }

    function localToUtcDispense($datetime): string {
        $datetime = trim((string)$datetime);

        if ($datetime === '') {
            throw new Exception('Tanggal dan waktu tidak boleh kosong.');
        }

        $dt = DateTime::createFromFormat(
            'Y-m-d\TH:i',
            $datetime,
            new DateTimeZone('Asia/Jakarta')
        );

        if (!$dt) {
            throw new Exception('Format tanggal dan waktu tidak valid.');
        }

        $dt->setTimezone(new DateTimeZone('UTC'));

        return $dt->format('Y-m-d\TH:i:sP');
    }

    function httpPostFHIR(
        string $url,
        string $token,
        array $payload
    ): array {
        $json = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {
            throw new Exception('Gagal membentuk payload JSON.');
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => $json,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer '.$token,
                'Content-Type: application/json',
                'Accept: application/fhir+json'
            ],
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);

            throw new Exception('Koneksi SATUSEHAT gagal: '.$error);
        }

        $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $data = json_decode($response, true);

        if (
            json_last_error() !== JSON_ERROR_NONE ||
            !is_array($data)
        ) {
            throw new Exception(
                'Response SATUSEHAT bukan JSON valid. HTTP Code: '.$httpCode
            );
        }

        if (
            $httpCode < 200 ||
            $httpCode >= 300 ||
            ($data['resourceType'] ?? '') === 'OperationOutcome'
        ) {
            throw new Exception(
                operationOutcomeDispense($data)
            );
        }

        return $data;
    }

    // VALIDASI SESSION & METHOD
    if (empty($SessionIdAkses)) {
        responseDispense(
            'error',
            'Sesi akses telah berakhir. Silakan login ulang.'
        );
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseDispense(
            'error',
            'Metode request tidak valid.'
        );
    }

    // PARAMETER FORM
    $MedicationRequestId = trim(
        (string)($_POST['MedicationRequestId'] ?? '')
    );

    $dispense_status = trim(
        (string)($_POST['dispense_status'] ?? '')
    );

    $quantity_value = (float)(
        $_POST['quantity_value'] ?? 0
    );

    $days_supply_value = (int)(
        $_POST['days_supply_value'] ?? 0
    );

    $when_prepared = trim(
        (string)($_POST['when_prepared'] ?? '')
    );

    $when_handed_over = trim(
        (string)($_POST['when_handed_over'] ?? '')
    );

    $dosage_text = trim(
        (string)($_POST['dosage_text'] ?? '')
    );

    $frequency = (int)(
        $_POST['frequency'] ?? 0
    );

    $period = (int)(
        $_POST['period'] ?? 0
    );

    $dose_value = (float)(
        $_POST['dose_value'] ?? 0
    );

    // VALIDASI FORM
    if ($MedicationRequestId === '') {
        responseDispense(
            'error',
            'ID item resep tidak boleh kosong.'
        );
    }

    /*
     * ENUM medication_dispense lokal saat ini hanya:
     * preparation, in-progress, completed, stopped, cancelled
     */
    $statusAllowed = [
        'preparation',
        'in-progress',
        'completed',
        'stopped',
        'cancelled'
    ];

    if (!in_array($dispense_status, $statusAllowed, true)) {
        responseDispense(
            'error',
            'Status Medication Dispense tidak valid atau belum didukung database lokal.'
        );
    }

    if ($quantity_value <= 0) {
        responseDispense(
            'error',
            'Jumlah obat yang diserahkan harus lebih dari 0.'
        );
    }

    if ($days_supply_value < 1) {
        responseDispense(
            'error',
            'Days Supply harus lebih dari 0.'
        );
    }

    if ($frequency < 1) {
        responseDispense(
            'error',
            'Frequency harus lebih dari 0.'
        );
    }

    if ($period < 1) {
        responseDispense(
            'error',
            'Period harus lebih dari 0.'
        );
    }

    if ($dose_value <= 0) {
        responseDispense(
            'error',
            'Dosis per pemakaian harus lebih dari 0.'
        );
    }

    // FORMAT WAKTU
    try {
        $whenPreparedUtc   = localToUtcDispense($when_prepared);
        $whenHandedOverUtc = localToUtcDispense($when_handed_over);

        if (
            strtotime($when_handed_over) <
            strtotime($when_prepared)
        ) {
            responseDispense(
                'error',
                'Waktu penyerahan tidak boleh lebih awal dari waktu penyiapan.'
            );
        }

    } catch (Throwable $e) {
        responseDispense(
            'error',
            escDispense($e->getMessage())
        );
    }

    // AMBIL DATA RESEP + GROUP + PATIENT + ENCOUNTER + MEDICATION
    $stmt = $Conn->prepare("
        SELECT
            mr.MedicationRequestId,
            mr.id_medication_request_group,
            mr.id_medication_request,
            mr.id_index_medication,
            mr.name_medication,

            mr.dosage_inst_period_unit,
            mr.dose_unit,
            mr.dose_code,
            mr.dose_system,
            mr.route_display,
            mr.route_code,
            mr.route_system,

            mr.dispense_unit,
            mr.dispense_code,
            mr.dispense_sys,

            mr.supply_duration_unit,
            mr.supply_duration_code,
            mr.supply_duration_sys,

            mrg.nama_pasien,
            mrg.apoteker_id,
            mrg.apoteker_code,
            mrg.apoteker_nama,
            mrg.apoteker_ihs,

            a.id_ihs AS patient_ihs,

            k.id_encounter,

            med.id_medication,
            med.medication_code,
            med.medication_name,
            med.medication_category,
            med.kfa_code,
            med.kfa_display,
            med.sediaan_code,
            med.sediaan_display,
            med.racikan_code,
            med.racikan_display,
            med.manufacturer_id,
            med.manufacturer_name,
            med.ingredient

        FROM medication_request mr

        INNER JOIN medication_request_group mrg
            ON mrg.id_medication_request_group =
               mr.id_medication_request_group

        LEFT JOIN anggota a
            ON a.id_anggota = mrg.id_anggota

        LEFT JOIN kunjungan k
            ON k.id_kunjungan = mrg.id_kunjungan

        LEFT JOIN medication med
            ON med.id_index_medication =
               mr.id_index_medication

        WHERE mr.MedicationRequestId = ?
        LIMIT 1
    ");

    if (!$stmt) {
        responseDispense(
            'error',
            'Gagal mempersiapkan data resep.<br>'.
            escDispense($Conn->error)
        );
    }

    $stmt->bind_param(
        "s",
        $MedicationRequestId
    );

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();

        responseDispense(
            'error',
            'Gagal membuka data resep.<br>'.
            escDispense($error)
        );
    }

    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$data) {
        responseDispense(
            'error',
            'Item resep tidak ditemukan.'
        );
    }

    // CEK DUPLIKASI DISPENSE
    $stmt = $Conn->prepare("
        SELECT
            kode_medication_dispense,
            id_medication_dispense
        FROM medication_dispense
        WHERE MedicationRequestId = ?
        LIMIT 1
    ");

    if (!$stmt) {
        responseDispense(
            'error',
            'Gagal memeriksa data Medication Dispense.'
        );
    }

    $stmt->bind_param(
        "s",
        $MedicationRequestId
    );

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();

        responseDispense(
            'error',
            'Gagal memeriksa Medication Dispense.<br>'.
            escDispense($error)
        );
    }

    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existing) {
        responseDispense(
            'error',
            'Item resep ini sudah memiliki Medication Dispense.'
        );
    }

    // MAPPING DATABASE
    $id_medication_request_group = (int)(
        $data['id_medication_request_group'] ?? 0
    );

    $id_medication_request = trim(
        (string)($data['id_medication_request'] ?? '')
    );

    $id_index_medication = (int)(
        $data['id_index_medication'] ?? 0
    );

    $id_medication = trim(
        (string)($data['id_medication'] ?? '')
    );

    $medication_code = trim(
        (string)($data['medication_code'] ?? '')
    );

    $medication_name = trim(
        (string)(
            $data['medication_name']
            ?? $data['name_medication']
            ?? ''
        )
    );

    $patient_ihs = trim(
        (string)($data['patient_ihs'] ?? '')
    );

    $patient_name = trim(
        (string)($data['nama_pasien'] ?? '')
    );

    $id_encounter = trim(
        (string)($data['id_encounter'] ?? '')
    );

    $apoteker_ihs = trim(
        (string)($data['apoteker_ihs'] ?? '')
    );

    $apoteker_nama = trim(
        (string)($data['apoteker_nama'] ?? '')
    );

    // REFERENSI MEDICATION
    $kfa_code = trim(
        (string)($data['kfa_code'] ?? '')
    );

    $kfa_display = trim(
        (string)($data['kfa_display'] ?? '')
    );

    $sediaan_code = trim(
        (string)($data['sediaan_code'] ?? '')
    );

    $sediaan_display = trim(
        (string)($data['sediaan_display'] ?? '')
    );

    $racikan_code = trim(
        (string)($data['racikan_code'] ?? '')
    );

    $racikan_display = trim(
        (string)($data['racikan_display'] ?? '')
    );

    $manufacturer_id = trim(
        (string)($data['manufacturer_id'] ?? '')
    );

    $ingredientRaw = trim(
        (string)($data['ingredient'] ?? '')
    );

    // SATUAN & DOSAGE DARI DATABASE
    $period_unit = trim(
        (string)($data['dosage_inst_period_unit'] ?? '')
    );

    $dose_unit = trim(
        (string)($data['dose_unit'] ?? '')
    );

    $dose_code = trim(
        (string)($data['dose_code'] ?? '')
    );

    $dose_system = trim(
        (string)($data['dose_system'] ?? '')
    );

    $route_display = trim(
        (string)($data['route_display'] ?? '')
    );

    $route_code = trim(
        (string)($data['route_code'] ?? '')
    );

    $route_system = trim(
        (string)($data['route_system'] ?? '')
    );

    $quantity_unit = trim(
        (string)($data['dispense_unit'] ?? '')
    );

    $quantity_code = trim(
        (string)($data['dispense_code'] ?? '')
    );

    $quantity_system = trim(
        (string)($data['dispense_sys'] ?? '')
    );

    $days_supply_unit = trim(
        (string)($data['supply_duration_unit'] ?? '')
    );

    $days_supply_code = trim(
        (string)($data['supply_duration_code'] ?? '')
    );

    $days_supply_system = trim(
        (string)($data['supply_duration_sys'] ?? '')
    );

    // VALIDASI REFERENSI WAJIB
    $missing = [];

    if ($id_medication_request_group < 1) {
        $missing[] = 'ID Group Resep';
    }

    if ($id_medication_request === '') {
        $missing[] = 'MedicationRequest ID SATUSEHAT';
    }

    if ($id_index_medication < 1) {
        $missing[] = 'Index Medication Lokal';
    }

    if ($patient_ihs === '') {
        $missing[] = 'Patient IHS';
    }

    if ($apoteker_ihs === '') {
        $missing[] = 'Practitioner IHS Apoteker';
    }

    if ($apoteker_nama === '') {
        $missing[] = 'Nama Apoteker';
    }

    if ($quantity_code === '') {
        $missing[] = 'Quantity Code';
    }

    if ($quantity_system === '') {
        $missing[] = 'Quantity System';
    }

    if ($days_supply_code === '') {
        $missing[] = 'Days Supply Code';
    }

    if ($days_supply_system === '') {
        $missing[] = 'Days Supply System';
    }

    if ($dose_code === '') {
        $missing[] = 'Dose Code';
    }

    if ($dose_system === '') {
        $missing[] = 'Dose System';
    }

    if ($route_code === '') {
        $missing[] = 'Route Code';
    }

    if ($route_system === '') {
        $missing[] = 'Route System';
    }

    if ($period_unit === '') {
        $missing[] = 'Period Unit';
    }

    if (
        $id_medication === '' &&
        $kfa_code === '' &&
        !in_array($racikan_code, ['SD','EP'], true)
    ) {
        $missing[] = 'ID Medication / KFA';
    }

    if (!empty($missing)) {
        responseDispense(
            'error',
            'Data belum lengkap:<br>• '.
            implode(
                '<br>• ',
                array_map('escDispense', $missing)
            )
        );
    }

    /*
     * FALLBACK PATIENT / ENCOUNTER DARI MedicationRequest SATUSEHAT
     *
     * Patient/Encounter lokal mungkin NULL untuk resep NRN eksternal.
     */
    $stmt = $Conn->prepare("
        SELECT
            url_connection_satu_sehat,
            organization_id
        FROM connection_satu_sehat
        WHERE status_connection_satu_sehat = 1
        LIMIT 1
    ");

    if (!$stmt) {
        responseDispense(
            'error',
            'Gagal membuka konfigurasi SATUSEHAT.'
        );
    }

    if (!$stmt->execute()) {
        $stmt->close();

        responseDispense(
            'error',
            'Gagal membaca konfigurasi SATUSEHAT.'
        );
    }

    $config = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$config) {
        responseDispense(
            'error',
            'Konfigurasi SATUSEHAT aktif tidak ditemukan.'
        );
    }

    $base_url = rtrim(
        trim(
            (string)(
                $config['url_connection_satu_sehat']
                ?? ''
            )
        ),
        '/'
    );

    $organization_id = trim(
        (string)(
            $config['organization_id']
            ?? ''
        )
    );

    if ($base_url === '') {
        responseDispense(
            'error',
            'URL SATUSEHAT belum dikonfigurasi.'
        );
    }

    if ($organization_id === '') {
        responseDispense(
            'error',
            'Organization IHS ID belum dikonfigurasi.'
        );
    }

    // TOKEN
    $tokenResult = generateTokenSatuSehat($Conn);

    if (
        empty($tokenResult) ||
        ($tokenResult['status'] ?? '') !== 'success' ||
        empty($tokenResult['token'])
    ) {
        responseDispense(
            'error',
            'Gagal membuat token SATUSEHAT.<br>'.
            escDispense(
                $tokenResult['message'] ?? ''
            )
        );
    }

    $token = trim(
        (string)$tokenResult['token']
    );

    /*
     * FALLBACK SUBJECT & ENCOUNTER
     * berdasarkan MedicationRequest server SATUSEHAT
     */
    if ($patient_ihs === '' || $id_encounter === '') {

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL =>
                $base_url.
                '/fhir-r4/v1/MedicationRequest/'.
                rawurlencode($id_medication_request),

            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_CONNECTTIMEOUT => 10,

            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer '.$token,
                'Accept: application/fhir+json'
            ],

            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $responseMr = curl_exec($curl);

        if ($responseMr !== false) {
            $httpMr = (int)curl_getinfo(
                $curl,
                CURLINFO_HTTP_CODE
            );

            if ($httpMr >= 200 && $httpMr < 300) {
                $remoteMr = json_decode(
                    $responseMr,
                    true
                );

                if (
                    is_array($remoteMr) &&
                    ($remoteMr['resourceType'] ?? '') === 'MedicationRequest'
                ) {
                    if ($patient_ihs === '') {
                        $patient_ihs = referenceIdDispense(
                            $remoteMr['subject']['reference']
                            ?? ''
                        );

                        if ($patient_name === '') {
                            $patient_name = trim(
                                (string)(
                                    $remoteMr['subject']['display']
                                    ?? ''
                                )
                            );
                        }
                    }

                    if ($id_encounter === '') {
                        $id_encounter = referenceIdDispense(
                            $remoteMr['encounter']['reference']
                            ?? ''
                        );
                    }
                }
            }
        }

        curl_close($curl);
    }

    if ($patient_ihs === '') {
        responseDispense(
            'error',
            'Patient IHS tidak ditemukan.'
        );
    }

    if ($id_encounter === '') {
        responseDispense(
            'error',
            'Encounter ID tidak ditemukan.'
        );
    }

    /*
     * GENERATE KODE DISPENSE LOKAL
     */
    try {
        $kode_medication_dispense =
            'MD-'.
            date('YmdHis').
            '-'.
            strtoupper(bin2hex(random_bytes(3)));

    } catch (Throwable $e) {
        responseDispense(
            'error',
            'Gagal membuat kode Medication Dispense.'
        );
    }

    // ID DISPENSE GROUP
    $kode_dispense_group =
        'MDG-'.$id_medication_request_group;

    /*
     * ========================================================
     * JIKA MEDICATION BELUM PUNYA ID SATUSEHAT
     * BUAT RESOURCE MEDICATION TERLEBIH DAHULU
     * ========================================================
     */
    $medicationCreatedNow = false;

    if ($id_medication === '') {

        $payloadMedication = [
            'resourceType' => 'Medication',
            'status'       => 'active'
        ];

        /*
         * MedicationType
         * NC = Non-compound
         * SD = Gives of such doses
         * EP = Divide into equal parts
         */
        if ($racikan_code !== '') {
            $payloadMedication['extension'] = [
                [
                    'url' =>
                        'https://fhir.kemkes.go.id/r4/StructureDefinition/MedicationType',

                    'valueCodeableConcept' => [
                        'coding' => [
                            [
                                'system' =>
                                    'http://terminology.kemkes.go.id/CodeSystem/medication-type',
                                'code'    => $racikan_code,
                                'display' => $racikan_display
                            ]
                        ]
                    ]
                ]
            ];
        }

        // NON RACIKAN
        if ($racikan_code === 'NC' || $racikan_code === '') {

            if ($medication_code === '') {
                responseDispense(
                    'error',
                    'Kode medication lokal belum tersedia.'
                );
            }

            if ($kfa_code === '') {
                responseDispense(
                    'error',
                    'Kode KFA belum tersedia.'
                );
            }

            $payloadMedication['identifier'] = [
                [
                    'use'    => 'official',
                    'system' =>
                        'http://sys-ids.kemkes.go.id/medication/'.
                        $organization_id,
                    'value'  => $medication_code
                ]
            ];

            $payloadMedication['code'] = [
                'coding' => [
                    [
                        'system'  =>
                            'http://sys-ids.kemkes.go.id/kfa',
                        'code'    => $kfa_code,
                        'display' => $kfa_display
                    ]
                ]
            ];
        }

        // SEDIAAN
        if ($sediaan_code !== '') {
            $payloadMedication['form'] = [
                'coding' => [
                    [
                        'system' =>
                            'http://terminology.kemkes.go.id/CodeSystem/medication-form',
                        'code'    => $sediaan_code,
                        'display' => $sediaan_display
                    ]
                ]
            ];
        }

        // MANUFACTURER
        if ($manufacturer_id !== '') {
            $payloadMedication['manufacturer'] = [
                'reference' =>
                    'Organization/'.$manufacturer_id
            ];
        }

        // INGREDIENT RACIKAN
        if (
            in_array($racikan_code, ['SD','EP'], true)
        ) {
            $ingredients = json_decode(
                $ingredientRaw,
                true
            );

            if (
                json_last_error() !== JSON_ERROR_NONE ||
                !is_array($ingredients) ||
                empty($ingredients)
            ) {
                responseDispense(
                    'error',
                    'Ingredient medication racikan tidak valid atau kosong.'
                );
            }

            $payloadMedication['ingredient'] = [];

            foreach ($ingredients as $index => $ingredient) {

                $kodeKfa = trim(
                    (string)(
                        $ingredient['kode_kfa']
                        ?? ''
                    )
                );

                $namaKfa = trim(
                    (string)(
                        $ingredient['nama_kfa']
                        ?? ''
                    )
                );

                $jumlahNumerator = (float)(
                    $ingredient['jumlah_numerator']
                    ?? 0
                );

                $kodeNumerator = trim(
                    (string)(
                        $ingredient['kode_numerator']
                        ?? ''
                    )
                );

                $jumlahDenominator = (float)(
                    $ingredient['jumlah_denominator']
                    ?? 0
                );

                $kodeDenominator = trim(
                    (string)(
                        $ingredient['kode_denominator']
                        ?? ''
                    )
                );

                if (
                    $kodeKfa === '' ||
                    $jumlahNumerator <= 0 ||
                    $kodeNumerator === '' ||
                    $jumlahDenominator <= 0 ||
                    $kodeDenominator === ''
                ) {
                    responseDispense(
                        'error',
                        'Data ingredient ke-'.
                        ($index + 1).
                        ' belum lengkap.'
                    );
                }

                /*
                 * Numerator lazimnya UCUM untuk mg, mL, dll.
                 * Jika kode bentuk seperti TAB/CAP, pakai OrderableDrugForm.
                 */
                $numeratorSystem =
                    in_array(
                        $kodeNumerator,
                        ['TAB','CAP','DROP','CRM','OINT','TOPCRM','TOPOINT'],
                        true
                    )
                    ? 'http://terminology.hl7.org/CodeSystem/v3-orderableDrugForm'
                    : 'http://unitsofmeasure.org';

                $denominatorSystem =
                    in_array(
                        $kodeDenominator,
                        ['TAB','CAP','DROP','CRM','OINT','TOPCRM','TOPOINT'],
                        true
                    )
                    ? 'http://terminology.hl7.org/CodeSystem/v3-orderableDrugForm'
                    : 'http://unitsofmeasure.org';

                $payloadMedication['ingredient'][] = [
                    'itemCodeableConcept' => [
                        'coding' => [
                            [
                                'system' =>
                                    'http://sys-ids.kemkes.go.id/kfa',
                                'code'    => $kodeKfa,
                                'display' => $namaKfa
                            ]
                        ]
                    ],

                    'isActive' => true,

                    'strength' => [
                        'numerator' => [
                            'value'  => $jumlahNumerator,
                            'system' => $numeratorSystem,
                            'code'   => $kodeNumerator
                        ],

                        'denominator' => [
                            'value'  => $jumlahDenominator,
                            'system' => $denominatorSystem,
                            'code'   => $kodeDenominator
                        ]
                    ]
                ];
            }
        }

        try {
            $medicationResponse = httpPostFHIR(
                $base_url.'/fhir-r4/v1/Medication',
                $token,
                $payloadMedication
            );

        } catch (Throwable $e) {
            responseDispense(
                'error',
                'Pengiriman Medication untuk proses dispense gagal.<br>'.
                escDispense($e->getMessage())
            );
        }

        $id_medication = trim(
            (string)(
                $medicationResponse['id']
                ?? ''
            )
        );

        if ($id_medication === '') {
            responseDispense(
                'error',
                'SATUSEHAT tidak mengembalikan ID Medication.'
            );
        }

        $medicationCreatedNow = true;
    }

    /*
     * ========================================================
     * PAYLOAD MEDICATION DISPENSE
     * ========================================================
     */
    $payloadDispense = [
        'resourceType' => 'MedicationDispense',

        'identifier' => [
            [
                'use'    => 'official',
                'system' => 'http://sys-ids.kemkes.go.id/prescription/'.$organization_id,
                'value'  => $kode_dispense_group
            ],
            [
                'use'    => 'official',
                'system' => 'http://sys-ids.kemkes.go.id/prescription-item/'.$organization_id,
                'value'  => $kode_medication_dispense
            ]
        ],

        'status' => $dispense_status,

        'medicationReference' => [
            'reference' =>
                'Medication/'.$id_medication,
            'display' =>
                $medication_name
        ],

        'subject' => [
            'reference' =>
                'Patient/'.$patient_ihs,
            'display' =>
                $patient_name
        ],

        'context' => [
            'reference' =>
                'Encounter/'.$id_encounter
        ],

        'performer' => [
            [
                'actor' => [
                    'reference' =>
                        'Practitioner/'.$apoteker_ihs,
                    'display' =>
                        $apoteker_nama
                ]
            ]
        ],

        'authorizingPrescription' => [
            [
                'reference' =>
                    'MedicationRequest/'.
                    $id_medication_request
            ]
        ],

        'quantity' => [
            'value'  => $quantity_value,
            'unit'   =>
                $quantity_unit !== ''
                    ? $quantity_unit
                    : $quantity_code,
            'system' => $quantity_system,
            'code'   => $quantity_code
        ],

        'daysSupply' => [
            'value'  => $days_supply_value,
            'unit'   =>
                $days_supply_unit !== ''
                    ? $days_supply_unit
                    : $days_supply_code,
            'system' => $days_supply_system,
            'code'   => $days_supply_code
        ],

        'whenPrepared'   =>
            $whenPreparedUtc,

        'whenHandedOver' =>
            $whenHandedOverUtc
    ];

    // DOSAGE
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
                            'system' =>
                                'http://terminology.hl7.org/CodeSystem/dose-rate-type',
                            'code'    => 'ordered',
                            'display' => 'Ordered'
                        ]
                    ]
                ],

                'doseQuantity' => [
                    'value'  => $dose_value,
                    'unit'   =>
                        $dose_unit !== ''
                            ? $dose_unit
                            : $dose_code,
                    'system' => $dose_system,
                    'code'   => $dose_code
                ]
            ]
        ]
    ];

    if ($dosage_text !== '') {
        $dosageInstruction['text'] =
            $dosage_text;

        $dosageInstruction['patientInstruction'] =
            $dosage_text;
    }

    $payloadDispense['dosageInstruction'] = [
        $dosageInstruction
    ];

    /*
     * Tidak ada substitusi pada form saat ini.
     */
    $payloadDispense['substitution'] = [
        'wasSubstituted' => false
    ];

    /*
     * ========================================================
     * KIRIM MEDICATION DISPENSE
     * ========================================================
     */
    try {
        $dispenseResponse = httpPostFHIR(
            $base_url.'/fhir-r4/v1/MedicationDispense',
            $token,
            $payloadDispense
        );

    } catch (Throwable $e) {

        /*
         * Sesuai permintaan:
         * jika MedicationDispense gagal,
         * TIDAK ADA data yang disimpan ke database lokal.
         *
         * Catatan: bila Medication baru saja berhasil dibuat
         * di SATUSEHAT, resource Medication tersebut tetap ada
         * di server SATUSEHAT karena API eksternal tidak bisa rollback.
         */
        responseDispense(
            'error',
            'Pengiriman Medication Dispense ke SATUSEHAT gagal.<br>'.
            escDispense($e->getMessage()),
            [
                'id_medication_created' =>
                    $medicationCreatedNow
                        ? $id_medication
                        : null
            ]
        );
    }

    // ID MEDICATION DISPENSE SATUSEHAT
    $id_medication_dispense = trim(
        (string)(
            $dispenseResponse['id']
            ?? ''
        )
    );

    if ($id_medication_dispense === '') {
        responseDispense(
            'error',
            'SATUSEHAT tidak mengembalikan ID MedicationDispense.'
        );
    }

    /*
     * ========================================================
     * SATUSEHAT SUKSES
     * BARU SIMPAN DATABASE LOKAL
     * ========================================================
     */
    $Conn->begin_transaction();

    try {

        /*
         * Jika Medication baru dibuat saat dispense,
         * simpan ID tersebut ke tabel medication.
         */
        if ($medicationCreatedNow) {

            $stmt = $Conn->prepare("
                UPDATE medication
                SET id_medication = ?
                WHERE id_index_medication = ?
                AND (
                    id_medication IS NULL
                    OR TRIM(id_medication) = ''
                )
            ");

            if (!$stmt) {
                throw new Exception(
                    'Gagal mempersiapkan update ID Medication.'
                );
            }

            $stmt->bind_param(
                "si",
                $id_medication,
                $id_index_medication
            );

            if (!$stmt->execute()) {
                $error = $stmt->error;
                $stmt->close();

                throw new Exception(
                    'Gagal menyimpan ID Medication. Keterangan: '.
                    $error
                );
            }

            $stmt->close();
        }

        /*
         * Cek ulang dispense agar tidak double insert.
         */
        $stmt = $Conn->prepare("
            SELECT kode_medication_dispense
            FROM medication_dispense
            WHERE MedicationRequestId = ?
            LIMIT 1
            FOR UPDATE
        ");

        if (!$stmt) {
            throw new Exception(
                'Gagal mempersiapkan pengecekan Medication Dispense.'
            );
        }

        $stmt->bind_param(
            "s",
            $MedicationRequestId
        );

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();

            throw new Exception(
                'Gagal memeriksa Medication Dispense. Keterangan: '.
                $error
            );
        }

        $duplicate = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($duplicate) {
            throw new Exception(
                'Medication Dispense sudah tersimpan pada database lokal.'
            );
        }

        /*
         * INSERT medication_dispense
         *
         * Struktur tabel saat ini:
         * - kode_medication_dispense
         * - id_medication_dispense
         * - MedicationRequestId
         * - id_medication_request_group
         * - apoteker_id_ihs
         * - apoteker_nama
         * - quantity_value
         * - quantity_unit
         * - quantity_code
         * - quantity_system
         * - status
         */
        $stmt = $Conn->prepare("
            INSERT INTO medication_dispense (
                kode_medication_dispense,
                id_medication_dispense,
                MedicationRequestId,
                id_medication_request_group,
                apoteker_id_ihs,
                apoteker_nama,
                quantity_value,
                quantity_unit,
                quantity_code,
                quantity_system,
                status
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
        ");

        if (!$stmt) {
            throw new Exception(
                'Gagal mempersiapkan penyimpanan Medication Dispense.'
            );
        }

        $stmt->bind_param(
            "sssissdssss",
            $kode_medication_dispense,
            $id_medication_dispense,
            $MedicationRequestId,
            $id_medication_request_group,
            $apoteker_ihs,
            $apoteker_nama,
            $quantity_value,
            $quantity_unit,
            $quantity_code,
            $quantity_system,
            $dispense_status
        );

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();

            throw new Exception(
                'Gagal menyimpan Medication Dispense. Keterangan: '.
                $error
            );
        }

        $stmt->close();

        // COMMIT
        $Conn->commit();

    } catch (Throwable $e) {

        $Conn->rollback();

        if (
            isset($stmt) &&
            $stmt instanceof mysqli_stmt
        ) {
            try {
                $stmt->close();
            } catch (Throwable $ignored) {
            }
        }

        /*
         * SATUSEHAT sudah berhasil.
         * Tidak bisa rollback resource eksternal.
         */
        responseDispense(
            'error',
            'Medication Dispense berhasil dibuat di SATUSEHAT dengan ID '.
            escDispense($id_medication_dispense).
            ', tetapi penyimpanan database lokal gagal.<br>'.
            escDispense($e->getMessage()),
            [
                'id_medication' =>
                    $id_medication,

                'id_medication_dispense' =>
                    $id_medication_dispense
            ]
        );
    }

    // RESPONSE SUCCESS
    responseDispense(
        'success',
        'Medication Dispense berhasil dikirim ke SATUSEHAT dan disimpan.',
        [
            'kode_medication_dispense' =>
                $kode_medication_dispense,

            'id_medication_dispense' =>
                $id_medication_dispense,

            'MedicationRequestId' =>
                $MedicationRequestId,

            'id_medication_request' =>
                $id_medication_request,

            'id_medication' =>
                $id_medication,

            'quantity_value' =>
                $quantity_value,

            'status' =>
                $dispense_status
        ]
    );
?>