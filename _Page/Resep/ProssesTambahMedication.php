<?php
    // CONNECTION, HELPER & SESSION
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    date_default_timezone_set('Asia/Jakarta');
    header('Content-Type: application/json; charset=utf-8');

    function sendResponse(string $status, string $message, array $data = []): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'data'    => $data
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    function getOperationOutcome(array $data): string {
        if (($data['resourceType'] ?? '') !== 'OperationOutcome' || empty($data['issue'])) {
            return 'SATUSEHAT mengembalikan response error.';
        }

        $messages = [];
        foreach ($data['issue'] as $issue) {
            $message = $issue['details']['text'] ?? $issue['diagnostics'] ?? $issue['code'] ?? '';
            if ($message !== '') {
                $messages[] = $message;
            }
        }

        return !empty($messages) ? implode(' | ', $messages) : 'SATUSEHAT mengembalikan OperationOutcome.';
    }

    // VALIDASI SESSION & METHOD
    if (empty($SessionIdAkses)) {
        sendResponse('error', 'Sesi akses telah berakhir. Silakan login ulang.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse('error', 'Metode request tidak valid.');
    }

    // TANGKAP DATA FORM
    $MedicationRequestId = trim((string)($_POST['MedicationRequestId'] ?? ''));
    $medication_code     = trim((string)($_POST['medication_code'] ?? ''));
    $medication_name     = trim((string)($_POST['medication_name'] ?? ''));
    $medication_category = trim((string)($_POST['medication_category'] ?? ''));
    $kfa_code            = trim((string)($_POST['kfa_code'] ?? ''));
    $kfa_display         = trim((string)($_POST['kfa_display'] ?? ''));
    $sediaan_code        = trim((string)($_POST['sediaan_code'] ?? ''));
    $sediaan_display     = trim((string)($_POST['sediaan_display'] ?? ''));
    $racikan_code        = trim((string)($_POST['racikan_code'] ?? ''));
    $racikan_display     = trim((string)($_POST['racikan_display'] ?? ''));
    $manufacturer_id     = trim((string)($_POST['manufacturer_id'] ?? ''));
    $manufacturer_name   = trim((string)($_POST['manufacturer_name'] ?? ''));
    $ingredient_raw      = trim((string)($_POST['ingredient'] ?? ''));

    // NORMALISASI SEDIAAN
    if (str_contains($sediaan_code, '|')) {
        $parts = explode('|', $sediaan_code, 2);
        $sediaan_code = trim($parts[0] ?? '');

        if ($sediaan_display === '') {
            $sediaan_display = trim($parts[1] ?? '');
        }
    }

    // VALIDASI WAJIB
    if ($MedicationRequestId === '') {
        sendResponse('error', 'ID item resep tidak boleh kosong.');
    }

    if ($medication_code === '') {
        sendResponse('error', 'Medication Code tidak boleh kosong.');
    }

    if ($medication_name === '') {
        sendResponse('error', 'Medication Name tidak boleh kosong.');
    }

    if (!in_array($medication_category, ['Obat', 'Alkes', 'Lainnya'], true)) {
        sendResponse('error', 'Medication Category tidak valid.');
    }

    if ($medication_category === 'Obat' && !in_array($racikan_code, ['NC', 'SD', 'EP'], true)) {
        sendResponse('error', 'Kode racikan tidak valid.');
    }

    // AMBIL ITEM RESEP
    $stmt = $Conn->prepare("
        SELECT
            MedicationRequestId,
            id_index_medication,
            name_medication,
            racikan_code,
            racikan_display,
            ingredient
        FROM medication_request
        WHERE MedicationRequestId = ?
        LIMIT 1
    ");

    if (!$stmt) {
        sendResponse('error', 'Gagal mempersiapkan data item resep.');
    }

    $stmt->bind_param("s", $MedicationRequestId);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        sendResponse('error', 'Gagal membuka item resep.<br>Keterangan : '.htmlspecialchars($error, ENT_QUOTES, 'UTF-8'));
    }

    $dataRequest = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$dataRequest) {
        sendResponse('error', 'Item resep tidak ditemukan.');
    }

    if (!empty($dataRequest['id_index_medication'])) {
        sendResponse('error', 'Item resep sudah terhubung dengan data Medication.');
    }

    // CEK DUPLIKASI MEDICATION CODE
    $stmt = $Conn->prepare("
        SELECT id_index_medication
        FROM medication
        WHERE medication_code = ?
        LIMIT 1
    ");

    if (!$stmt) {
        sendResponse('error', 'Gagal memeriksa Medication Code.');
    }

    $stmt->bind_param("s", $medication_code);
    $stmt->execute();

    $existingMedication = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existingMedication) {
        sendResponse('error', 'Medication Code sudah digunakan.');
    }

    // RACIKAN DISPLAY
    if ($racikan_code === 'NC') {
        $racikan_display = 'Non-compound';
    } elseif ($racikan_code === 'SD') {
        $racikan_display = 'Gives of such doses';
    } elseif ($racikan_code === 'EP') {
        $racikan_display = 'Divide into equal parts';
    }

    // VALIDASI KFA NON RACIKAN
    if ($medication_category === 'Obat' && $racikan_code === 'NC') {
        if ($kfa_code === '' || $kfa_display === '') {
            sendResponse('error', 'Obat non-racikan wajib memiliki KFA Code dan KFA Display.');
        }
    }

    // INGREDIENT
    $ingredientArray = [];

    if ($medication_category === 'Obat' && in_array($racikan_code, ['SD', 'EP'], true)) {
        if ($ingredient_raw === '') {
            sendResponse('error', 'Medication racikan wajib memiliki ingredient.');
        }

        $ingredientArray = json_decode($ingredient_raw, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($ingredientArray)) {
            sendResponse('error', 'Format JSON ingredient tidak valid.');
        }

        if (empty($ingredientArray)) {
            sendResponse('error', 'Medication racikan wajib memiliki minimal satu ingredient.');
        }

        foreach ($ingredientArray as $index => $item) {
            $kode_kfa           = trim((string)($item['kode_kfa'] ?? ''));
            $nama_kfa           = trim((string)($item['nama_kfa'] ?? ''));
            $kode_numerator     = trim((string)($item['kode_numerator'] ?? ''));
            $jumlah_numerator   = trim((string)($item['jumlah_numerator'] ?? ''));
            $kode_denominator   = trim((string)($item['kode_denominator'] ?? ''));
            $jumlah_denominator = trim((string)($item['jumlah_denominator'] ?? ''));

            if ($kode_kfa === '' || $nama_kfa === '') {
                sendResponse('error', 'Ingredient ke-'.($index + 1).' belum memiliki KFA yang lengkap.');
            }

            if ($kode_numerator === '' || $jumlah_numerator === '' || !is_numeric($jumlah_numerator)) {
                sendResponse('error', 'Numerator ingredient ke-'.($index + 1).' tidak valid.');
            }

            if ($kode_denominator === '' || $jumlah_denominator === '' || !is_numeric($jumlah_denominator)) {
                sendResponse('error', 'Denominator ingredient ke-'.($index + 1).' tidak valid.');
            }
        }
    } else {
        $ingredient_raw = null;
    }

    // KONFIGURASI SATUSEHAT
    $status_connection = 1;

    $stmt = $Conn->prepare("
        SELECT
            url_connection_satu_sehat,
            organization_id
        FROM connection_satu_sehat
        WHERE status_connection_satu_sehat = ?
        LIMIT 1
    ");

    if (!$stmt) {
        sendResponse('error', 'Gagal membuka konfigurasi SATUSEHAT.');
    }

    $stmt->bind_param("i", $status_connection);

    if (!$stmt->execute()) {
        $stmt->close();
        sendResponse('error', 'Gagal membaca konfigurasi SATUSEHAT.');
    }

    $config = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$config) {
        sendResponse('error', 'Konfigurasi SATUSEHAT aktif tidak ditemukan.');
    }

    $base_url = rtrim(trim((string)($config['url_connection_satu_sehat'] ?? '')), '/');
    $organization_id = trim((string)($config['organization_id'] ?? ''));

    if ($base_url === '') {
        sendResponse('error', 'URL koneksi SATUSEHAT belum dikonfigurasi.');
    }

    if ($organization_id === '') {
        sendResponse('error', 'Organization IHS ID belum dikonfigurasi.');
    }

    // GENERATE TOKEN SATUSEHAT
    $tokenResult = generateTokenSatuSehat($Conn);

    if (
        empty($tokenResult) ||
        ($tokenResult['status'] ?? '') !== 'success' ||
        empty($tokenResult['token'])
    ) {
        sendResponse(
            'error',
            'Gagal membuat token SATUSEHAT.<br>'.
            htmlspecialchars((string)($tokenResult['message'] ?? ''), ENT_QUOTES, 'UTF-8')
        );
    }

    $token = $tokenResult['token'];

    // PAYLOAD DASAR MEDICATION
    $payload = [
        'resourceType' => 'Medication',
        'identifier' => [
            [
                'use'    => 'official',
                'system' => 'http://sys-ids.kemkes.go.id/medication/'.$organization_id,
                'value'  => $medication_code
            ]
        ],
        'status' => 'active'
    ];

    // MEDICATION TYPE
    if ($medication_category === 'Obat') {
        $payload['extension'] = [
            [
                'url' => 'https://fhir.kemkes.go.id/r4/StructureDefinition/MedicationType',
                'valueCodeableConcept' => [
                    'coding' => [
                        [
                            'system'  => 'http://terminology.kemkes.go.id/CodeSystem/medication-type',
                            'code'    => $racikan_code,
                            'display' => $racikan_display
                        ]
                    ]
                ]
            ]
        ];
    }

    // KFA
    if ($kfa_code !== '' && $kfa_display !== '') {
        $payload['code'] = [
            'coding' => [
                [
                    'system'  => 'http://sys-ids.kemkes.go.id/kfa',
                    'code'    => $kfa_code,
                    'display' => $kfa_display
                ]
            ],
            'text' => $medication_name
        ];
    }

    // BENTUK SEDIAAN
    if ($sediaan_code !== '' && $sediaan_display !== '') {
        $payload['form'] = [
            'coding' => [
                [
                    'system'  => 'http://terminology.kemkes.go.id/CodeSystem/medication-form',
                    'code'    => $sediaan_code,
                    'display' => $sediaan_display
                ]
            ]
        ];
    }

    // MANUFACTURER
    if ($manufacturer_id !== '') {
        $payload['manufacturer'] = [
            'reference' => 'Organization/'.$manufacturer_id
        ];

        if ($manufacturer_name !== '') {
            $payload['manufacturer']['display'] = $manufacturer_name;
        }
    }

    // INGREDIENT RACIKAN
    if ($medication_category === 'Obat' && in_array($racikan_code, ['SD', 'EP'], true)) {
        $payload['ingredient'] = [];

        foreach ($ingredientArray as $item) {
            $numeratorSystem = $racikan_code === 'SD'
                ? 'http://unitsofmeasure.org'
                : 'http://terminology.hl7.org/CodeSystem/v3-orderableDrugForm';

            $payload['ingredient'][] = [
                'itemCodeableConcept' => [
                    'coding' => [
                        [
                            'system'  => 'http://sys-ids.kemkes.go.id/kfa',
                            'code'    => trim((string)$item['kode_kfa']),
                            'display' => trim((string)$item['nama_kfa'])
                        ]
                    ]
                ],
                'isActive' => true,
                'strength' => [
                    'numerator' => [
                        'value'  => (float)$item['jumlah_numerator'],
                        'system' => $numeratorSystem,
                        'code'   => trim((string)$item['kode_numerator'])
                    ],
                    'denominator' => [
                        'value'  => (float)$item['jumlah_denominator'],
                        'system' => 'http://terminology.hl7.org/CodeSystem/v3-orderableDrugForm',
                        'code'   => trim((string)$item['kode_denominator'])
                    ]
                ]
            ];
        }
    }

    // ENCODE PAYLOAD
    $payloadJson = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    if ($payloadJson === false) {
        sendResponse('error', 'Gagal membentuk payload Medication.');
    }

    // POST MEDICATION KE SATUSEHAT
    $url = $base_url.'/fhir-r4/v1/Medication';

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

        sendResponse(
            'error',
            'Gagal mengirim Medication ke SATUSEHAT.<br>'.
            htmlspecialchars($curlError, ENT_QUOTES, 'UTF-8')
        );
    }

    $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    // DECODE RESPONSE SATUSEHAT
    $satusehatData = json_decode($satusehatResponse, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($satusehatData)) {
        sendResponse(
            'error',
            'SATUSEHAT mengembalikan response yang tidak valid.<br>HTTP Code : '.$httpCode
        );
    }

    if (
        $httpCode < 200 ||
        $httpCode >= 300 ||
        ($satusehatData['resourceType'] ?? '') === 'OperationOutcome'
    ) {
        sendResponse(
            'error',
            'Pengiriman Medication ke SATUSEHAT gagal.<br>'.
            htmlspecialchars(
                getOperationOutcome($satusehatData),
                ENT_QUOTES,
                'UTF-8'
            )
        );
    }

    // ID MEDICATION DARI SATUSEHAT
    $id_medication = trim((string)($satusehatData['id'] ?? ''));

    if ($id_medication === '') {
        sendResponse('error', 'SATUSEHAT tidak mengembalikan ID Medication.');
    }

    // SIMPAN DATABASE LOKAL
    $Conn->begin_transaction();

    try {
        $kfa_code_db          = $kfa_code !== '' ? $kfa_code : null;
        $kfa_display_db       = $kfa_display !== '' ? $kfa_display : null;
        $sediaan_code_db      = $sediaan_code !== '' ? $sediaan_code : null;
        $sediaan_display_db   = $sediaan_display !== '' ? $sediaan_display : null;
        $racikan_code_db      = $racikan_code !== '' ? $racikan_code : null;
        $racikan_display_db   = $racikan_display !== '' ? $racikan_display : null;
        $manufacturer_id_db   = $manufacturer_id !== '' ? $manufacturer_id : null;
        $manufacturer_name_db = $manufacturer_name !== '' ? $manufacturer_name : null;
        $ingredient_db        = !empty($ingredient_raw) ? $ingredient_raw : null;

        // INSERT MEDICATION
        $stmt = $Conn->prepare("
            INSERT INTO medication (
                id_medication,
                medication_code,
                medication_name,
                medication_category,
                kfa_code,
                kfa_display,
                sediaan_code,
                sediaan_display,
                racikan_code,
                racikan_display,
                manufacturer_id,
                manufacturer_name,
                ingredient
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            throw new Exception('Gagal mempersiapkan penyimpanan medication.');
        }

        $stmt->bind_param(
            "sssssssssssss",
            $id_medication,
            $medication_code,
            $medication_name,
            $medication_category,
            $kfa_code_db,
            $kfa_display_db,
            $sediaan_code_db,
            $sediaan_display_db,
            $racikan_code_db,
            $racikan_display_db,
            $manufacturer_id_db,
            $manufacturer_name_db,
            $ingredient_db
        );

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();

            throw new Exception(
                'Gagal menyimpan medication.<br>Keterangan : '.$error
            );
        }

        $id_index_medication = (int)$Conn->insert_id;
        $stmt->close();

        if ($id_index_medication < 1) {
            throw new Exception('ID Index Medication gagal dibuat.');
        }

        // UPDATE ITEM RESEP
        $stmt = $Conn->prepare("
            UPDATE medication_request
            SET id_index_medication = ?
            WHERE MedicationRequestId = ?
            AND id_index_medication IS NULL
        ");

        if (!$stmt) {
            throw new Exception('Gagal mempersiapkan update item resep.');
        }

        $stmt->bind_param(
            "is",
            $id_index_medication,
            $MedicationRequestId
        );

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();

            throw new Exception(
                'Gagal menghubungkan Medication dengan item resep.<br>Keterangan : '.$error
            );
        }

        if ($stmt->affected_rows !== 1) {
            $stmt->close();
            throw new Exception('Item resep tidak berhasil dihubungkan dengan Medication.');
        }

        $stmt->close();

        // COMMIT
        $Conn->commit();

        sendResponse(
            'success',
            'Medication berhasil dikirim ke SATUSEHAT dan disimpan.',
            [
                'MedicationRequestId' => $MedicationRequestId,
                'id_index_medication' => $id_index_medication,
                'id_medication'       => $id_medication,
                'medication_code'     => $medication_code,
                'medication_name'     => $medication_name
            ]
        );

    } catch (Throwable $e) {
        $Conn->rollback();

        if (isset($stmt) && $stmt instanceof mysqli_stmt) {
            $stmt->close();
        }

        sendResponse(
            'error',
            'Medication berhasil dibuat di SATUSEHAT dengan ID '.$id_medication.
            ', tetapi penyimpanan database lokal gagal.<br>'.
            htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'),
            [
                'id_medication' => $id_medication
            ]
        );
    }
?>