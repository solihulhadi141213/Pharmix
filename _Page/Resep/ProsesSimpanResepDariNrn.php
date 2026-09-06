<?php
    // CONNECTION, HELPER, SESSION & AKSES
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    include "../../_Config/FungsiAkses.php";

    date_default_timezone_set('Asia/Jakarta');
    header('Content-Type: application/json; charset=utf-8');

    function responseImport(string $status, string $message, array $data = []): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'data'    => $data
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    function getRefId($reference): string {
        $reference = trim((string)$reference);
        if ($reference === '' || str_starts_with($reference, '#')) return '';

        $parts = explode('/', $reference);
        return trim((string)end($parts));
    }

    function getContainedId($reference): string {
        $reference = trim((string)$reference);
        return str_starts_with($reference, '#')
            ? substr($reference, 1)
            : '';
    }

    function utcToLocalMysql($datetime): string {
        $datetime = trim((string)$datetime);

        if ($datetime === '') {
            return date('Y-m-d H:i:s');
        }

        try {
            $dt = new DateTime($datetime);
            $dt->setTimezone(new DateTimeZone('Asia/Jakarta'));
            return $dt->format('Y-m-d H:i:s');
        } catch (Throwable $e) {
            return date('Y-m-d H:i:s');
        }
    }

    function operationOutcomeImport(array $data): string {
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
        responseImport('error', 'Sesi akses telah berakhir. Silakan login ulang.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseImport('error', 'Metode request tidak valid.');
    }

    // PARAMETER DARI FORM
    $nrn = strtoupper(trim((string)($_POST['nomor_resep_nasional'] ?? '')));
    $id_document_reference_form = trim((string)($_POST['id_document_reference'] ?? ''));

    if ($nrn === '') {
        responseImport('error', 'Nomor Resep Nasional tidak boleh kosong.');
    }

    if ($id_document_reference_form === '') {
        responseImport('error', 'ID DocumentReference tidak boleh kosong.');
    }

    // CEK DUPLIKASI NRN LOKAL
    $stmt = $Conn->prepare("
        SELECT id_medication_request_group
        FROM medication_request_group
        WHERE no_resep_nasional = ?
        LIMIT 1
    ");

    if (!$stmt) {
        responseImport('error', 'Gagal mempersiapkan pemeriksaan NRN.');
    }

    $stmt->bind_param("s", $nrn);
    $stmt->execute();

    $existingNrn = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existingNrn) {
        responseImport(
            'error',
            'Nomor Resep Nasional sudah tersimpan pada database lokal.'
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
        responseImport('error', 'Gagal membuka konfigurasi SATUSEHAT.');
    }

    if (!$stmt->execute()) {
        $stmt->close();
        responseImport('error', 'Gagal membaca konfigurasi SATUSEHAT.');
    }

    $config = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$config) {
        responseImport('error', 'Konfigurasi SATUSEHAT aktif tidak ditemukan.');
    }

    $base_url = rtrim(
        trim((string)($config['url_connection_satu_sehat'] ?? '')),
        '/'
    );

    if ($base_url === '') {
        responseImport('error', 'URL SATUSEHAT belum dikonfigurasi.');
    }

    // TOKEN
    $tokenResult = generateTokenSatuSehat($Conn);

    if (
        empty($tokenResult) ||
        ($tokenResult['status'] ?? '') !== 'success' ||
        empty($tokenResult['token'])
    ) {
        responseImport(
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

    // GET DOCUMENT REFERENCE BY NRN + RELATED RESOURCE
    $identifier = 'http://sys-ids.kemkes.go.id/prescription/national|'.$nrn;

    $url = $base_url
        .'/fhir-r4/v1/DocumentReference/'
        .'?_include=DocumentReference%3Arelated'
        .'&identifier='.urlencode($identifier);

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

        responseImport(
            'error',
            'Gagal mengambil resep dari SATUSEHAT.<br>'.
            htmlspecialchars($error, ENT_QUOTES, 'UTF-8')
        );
    }

    $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    $bundle = json_decode($response, true);

    if (
        json_last_error() !== JSON_ERROR_NONE ||
        !is_array($bundle)
    ) {
        responseImport(
            'error',
            'Response SATUSEHAT tidak valid.<br>HTTP Code : '.$httpCode
        );
    }

    if (
        $httpCode < 200 ||
        $httpCode >= 300 ||
        ($bundle['resourceType'] ?? '') === 'OperationOutcome'
    ) {
        responseImport(
            'error',
            'Pencarian resep ke SATUSEHAT gagal.<br>'.
            htmlspecialchars(
                operationOutcomeImport($bundle),
                ENT_QUOTES,
                'UTF-8'
            )
        );
    }

    if (
        ($bundle['resourceType'] ?? '') !== 'Bundle' ||
        empty($bundle['entry'])
    ) {
        responseImport(
            'error',
            'Resep dengan NRN '.$nrn.' tidak ditemukan.'
        );
    }

    // PISAHKAN RESOURCE
    $documentReference  = null;
    $medicationRequests = [];
    $medicationsBundle  = [];

    foreach ($bundle['entry'] as $entry) {
        $resource = $entry['resource'] ?? [];

        if (!is_array($resource)) continue;

        $resourceType = $resource['resourceType'] ?? '';

        if ($resourceType === 'DocumentReference' && $documentReference === null) {
            $documentReference = $resource;
        }

        if ($resourceType === 'MedicationRequest') {
            $medicationRequests[] = $resource;
        }

        if ($resourceType === 'Medication' && !empty($resource['id'])) {
            $medicationsBundle[$resource['id']] = $resource;
        }
    }

    if (!$documentReference) {
        responseImport('error', 'DocumentReference tidak ditemukan pada response.');
    }

    if (empty($medicationRequests)) {
        responseImport('error', 'MedicationRequest tidak ditemukan pada resep.');
    }

    // VALIDASI DOCUMENT REFERENCE
    $id_document_reference = trim(
        (string)($documentReference['id'] ?? '')
    );

    $nrnResponse = trim(
        (string)($documentReference['masterIdentifier']['value'] ?? '')
    );

    if ($id_document_reference === '') {
        responseImport('error', 'ID DocumentReference tidak ditemukan.');
    }

    if ($id_document_reference !== $id_document_reference_form) {
        responseImport(
            'error',
            'ID DocumentReference dari form tidak sesuai dengan SATUSEHAT.'
        );
    }

    if ($nrnResponse === '') {
        responseImport('error', 'NRN tidak tersedia pada DocumentReference.');
    }

    if ($nrnResponse !== $nrn) {
        responseImport(
            'error',
            'NRN response SATUSEHAT tidak sesuai dengan NRN yang dicari.'
        );
    }

    // PATIENT
    $patientReference = trim(
        (string)($documentReference['subject']['reference'] ?? '')
    );

    $patient_ihs = getRefId($patientReference);

    $nama_pasien = trim(
        (string)($documentReference['subject']['display'] ?? '')
    );

    if ($patient_ihs === '') {
        responseImport('error', 'Patient IHS tidak ditemukan pada resep.');
    }

    if ($nama_pasien === '') {
        $nama_pasien = 'Pasien SATUSEHAT';
    }

    // MAPPING PASIEN LOKAL
    $id_anggota = null;

    $stmt = $Conn->prepare("
        SELECT id_anggota
        FROM anggota
        WHERE id_ihs = ?
        LIMIT 1
    ");

    if ($stmt) {
        $stmt->bind_param("s", $patient_ihs);
        $stmt->execute();

        $patientLocal = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($patientLocal) {
            $id_anggota = (int)$patientLocal['id_anggota'];
        }
    }

    // ENCOUNTER
    $encounterReference = trim(
        (string)($documentReference['context']['encounter'][0]['reference'] ?? '')
    );

    $id_encounter = getRefId($encounterReference);
    $id_kunjungan = null;

    if ($id_encounter !== '') {
        $stmt = $Conn->prepare("
            SELECT id_kunjungan
            FROM kunjungan
            WHERE id_encounter = ?
            LIMIT 1
        ");

        if ($stmt) {
            $stmt->bind_param("s", $id_encounter);
            $stmt->execute();

            $encounterLocal = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($encounterLocal) {
                $id_kunjungan = (int)$encounterLocal['id_kunjungan'];
            }
        }
    }

    // DOKTER DARI DOCUMENT REFERENCE
    $dokterReference = trim(
        (string)($documentReference['author'][0]['reference'] ?? '')
    );

    $dokter_ihs = getRefId($dokterReference);

    $dokter_nama = trim(
        (string)($documentReference['author'][0]['display'] ?? '')
    );

    // FALLBACK KE REQUESTER MR PERTAMA
    if ($dokter_ihs === '') {
        $dokterReference = trim(
            (string)($medicationRequests[0]['requester']['reference'] ?? '')
        );

        $dokter_ihs = getRefId($dokterReference);
    }

    if ($dokter_nama === '') {
        $dokter_nama = trim(
            (string)($medicationRequests[0]['requester']['display'] ?? '')
        );
    }

    if ($dokter_nama === '') {
        $dokter_nama = 'Dokter Eksternal';
    }

    // MAPPING DOKTER LOKAL
    $dokter_id   = null;
    $dokter_code = null;

    if ($dokter_ihs !== '') {
        $stmt = $Conn->prepare("
            SELECT
                medicalPersonelId,
                medicalPersonelCode
            FROM medical_personel
            WHERE id_practitioner = ?
            LIMIT 1
        ");

        if ($stmt) {
            $stmt->bind_param("s", $dokter_ihs);
            $stmt->execute();

            $doctorLocal = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($doctorLocal) {
                $dokter_id   = (int)$doctorLocal['medicalPersonelId'];
                $dokter_code = trim(
                    (string)($doctorLocal['medicalPersonelCode'] ?? '')
                );

                if ($dokter_code === '') {
                    $dokter_code = null;
                }
            }
        }
    }

    // SUMBER RESEP
    $sumber_resep = trim(
        (string)($documentReference['custodian']['display'] ?? '')
    );

    if ($sumber_resep === '') {
        $sumber_resep = 'SATUSEHAT';
    }

    // PRIORITY
    $priority = trim(
        (string)($medicationRequests[0]['priority'] ?? 'routine')
    );

    if (!in_array($priority, ['routine','urgent','asap','stat'], true)) {
        $priority = 'routine';
    }

    // TANGGAL RESEP
    $datetime_creat = utcToLocalMysql(
        $medicationRequests[0]['authoredOn']
        ?? $documentReference['date']
        ?? ''
    );

    // REASON
    $reason_code    = null;
    $reason_display = null;
    $reason_system  = null;

    // Response contoh menggunakan reasonReference, belum resolve Condition
    if (!empty($medicationRequests[0]['reasonReference'][0]['display'])) {
        $reason_display = trim(
            (string)$medicationRequests[0]['reasonReference'][0]['display']
        );
    }

    // METADATA
    $now          = date('Y-m-d H:i:s');
    $creator_id   = (int)$SessionIdAkses;
    $creator_name = trim((string)($SessionNama ?? ''));

    if ($creator_name === '') {
        $creator_name = 'System';
    }

    /*
     * ===========================================================
     * TRANSACTION DATABASE
     * ===========================================================
     */
    $Conn->begin_transaction();

    try {

        // CEK DUPLIKASI LAGI DI DALAM TRANSACTION
        $stmt = $Conn->prepare("
            SELECT id_medication_request_group
            FROM medication_request_group
            WHERE no_resep_nasional = ?
            LIMIT 1
            FOR UPDATE
        ");

        if (!$stmt) {
            throw new Exception('Gagal mempersiapkan pengecekan duplikasi NRN.');
        }

        $stmt->bind_param("s", $nrn);
        $stmt->execute();

        $duplicate = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($duplicate) {
            throw new Exception('NRN sudah tersimpan pada database lokal.');
        }

        // INSERT MEDICATION REQUEST GROUP
        $status_resep   = 'Verified';
        $kategori_resep = 'Masuk';

        $stmt = $Conn->prepare("
            INSERT INTO medication_request_group (
                id_anggota,
                id_kunjungan,
                nama_pasien,
                priority,
                datetime_creat,
                dokter_id,
                dokter_code,
                dokter_ihs,
                dokter_nama,
                reason_code,
                reason_display,
                reason_system,
                apoteker_id,
                apoteker_code,
                apoteker_nama,
                apoteker_ihs,
                kategori_resep,
                sumber_resep,
                status_resep,
                id_document_reference,
                no_resep_nasional,
                creat_at,
                creat_by_id,
                creat_by_name,
                update_at,
                update_by_id,
                update_by_name
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                NULL, NULL, NULL, NULL,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
        ");

        if (!$stmt) {
            throw new Exception(
                'Gagal mempersiapkan penyimpanan header resep. Keterangan: '.$Conn->error
            );
        }

        $stmt->bind_param(
            "iisssissssssssssssissis",
            $id_anggota,
            $id_kunjungan,
            $nama_pasien,
            $priority,
            $datetime_creat,
            $dokter_id,
            $dokter_code,
            $dokter_ihs,
            $dokter_nama,
            $reason_code,
            $reason_display,
            $reason_system,
            $kategori_resep,
            $sumber_resep,
            $status_resep,
            $id_document_reference,
            $nrn,
            $now,
            $creator_id,
            $creator_name,
            $now,
            $creator_id,
            $creator_name
        );

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();

            throw new Exception(
                'Gagal menyimpan header resep. Keterangan: '.$error
            );
        }

        $id_medication_request_group = (int)$Conn->insert_id;
        $stmt->close();

        if ($id_medication_request_group < 1) {
            throw new Exception('ID resep lokal gagal dibuat.');
        }

        // PROSES SETIAP MEDICATION REQUEST
        $jumlahInserted = 0;

        foreach ($medicationRequests as $index => $mr) {

            $id_medication_request = trim(
                (string)($mr['id'] ?? '')
            );

            if ($id_medication_request === '') {
                throw new Exception(
                    'MedicationRequest item ke-'.($index + 1).' tidak memiliki ID SATUSEHAT.'
                );
            }

            // CEK MR SUDAH PERNAH DIIMPORT
            $stmt = $Conn->prepare("
                SELECT MedicationRequestId
                FROM medication_request
                WHERE id_medication_request = ?
                LIMIT 1
            ");

            if (!$stmt) {
                throw new Exception('Gagal memeriksa duplikasi MedicationRequest.');
            }

            $stmt->bind_param("s", $id_medication_request);
            $stmt->execute();

            $duplicateMr = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($duplicateMr) {
                throw new Exception(
                    'MedicationRequest '.$id_medication_request.
                    ' sudah tersimpan pada database lokal.'
                );
            }

            // MEDICATION REFERENCE
            $medRef = trim(
                (string)($mr['medicationReference']['reference'] ?? '')
            );

            $medDisplay = trim(
                (string)($mr['medicationReference']['display'] ?? '')
            );

            $id_medication = '';
            $containedId    = '';
            $medResource    = null;

            // STANDALONE Medication/{id}
            if (str_starts_with($medRef, 'Medication/')) {
                $id_medication = getRefId($medRef);

                if (
                    $id_medication !== '' &&
                    isset($medicationsBundle[$id_medication])
                ) {
                    $medResource = $medicationsBundle[$id_medication];
                }
            }

            // CONTAINED #xxxx
            if (str_starts_with($medRef, '#')) {
                $containedId = getContainedId($medRef);

                foreach (($mr['contained'] ?? []) as $contained) {
                    if (
                        ($contained['resourceType'] ?? '') === 'Medication' &&
                        ($contained['id'] ?? '') === $containedId
                    ) {
                        $medResource = $contained;
                        break;
                    }
                }
            }

            // DEFAULT DATA MEDICATION
            $medication_name     = $medDisplay;
            $medication_category = 'Obat';

            $kfa_code          = null;
            $kfa_display       = null;
            $sediaan_code      = null;
            $sediaan_display   = null;
            $racikan_code      = null;
            $racikan_display   = null;
            $manufacturer_id   = null;
            $manufacturer_name = null;
            $ingredient_json   = null;

            // AMBIL DATA RESOURCE MEDICATION
            if (is_array($medResource)) {

                // CODE / KFA
                $coding = $medResource['code']['coding'][0] ?? [];

                if (($coding['system'] ?? '') === 'http://sys-ids.kemkes.go.id/kfa') {
                    $kfa_code = trim((string)($coding['code'] ?? ''));
                    $kfa_display = trim((string)($coding['display'] ?? ''));

                    if ($kfa_code === '') $kfa_code = null;
                    if ($kfa_display === '') $kfa_display = null;
                }

                if ($medication_name === '' && $kfa_display !== null) {
                    $medication_name = $kfa_display;
                }

                // FORM
                $formCoding = $medResource['form']['coding'][0] ?? [];

                $sediaan_code = trim(
                    (string)($formCoding['code'] ?? '')
                );

                $sediaan_display = trim(
                    (string)($formCoding['display'] ?? '')
                );

                if ($sediaan_code === '') $sediaan_code = null;
                if ($sediaan_display === '') $sediaan_display = null;

                // MEDICATION TYPE
                foreach (($medResource['extension'] ?? []) as $extension) {
                    if (
                        ($extension['url'] ?? '') ===
                        'https://fhir.kemkes.go.id/r4/StructureDefinition/MedicationType'
                    ) {
                        $typeCoding = $extension['valueCodeableConcept']['coding'][0] ?? [];

                        $racikan_code = trim(
                            (string)($typeCoding['code'] ?? '')
                        );

                        $racikan_display = trim(
                            (string)($typeCoding['display'] ?? '')
                        );

                        if ($racikan_code === '') $racikan_code = null;
                        if ($racikan_display === '') $racikan_display = null;

                        break;
                    }
                }

                // MANUFACTURER
                $manufacturerReference = trim(
                    (string)($medResource['manufacturer']['reference'] ?? '')
                );

                if ($manufacturerReference !== '') {
                    $manufacturer_id = getRefId($manufacturerReference);

                    if ($manufacturer_id === '') {
                        $manufacturer_id = null;
                    }
                }

                $manufacturer_name = trim(
                    (string)($medResource['manufacturer']['display'] ?? '')
                );

                if ($manufacturer_name === '') {
                    $manufacturer_name = null;
                }

                // INGREDIENT
                $ingredientArray = [];

                foreach (($medResource['ingredient'] ?? []) as $ing) {
                    $ingredientCoding =
                        $ing['itemCodeableConcept']['coding'][0] ?? [];

                    $numerator =
                        $ing['strength']['numerator'] ?? [];

                    $denominator =
                        $ing['strength']['denominator'] ?? [];

                    $ingredientArray[] = [
                        'kode_kfa' => trim(
                            (string)($ingredientCoding['code'] ?? '')
                        ),
                        'nama_kfa' => trim(
                            (string)($ingredientCoding['display'] ?? '')
                        ),
                        'jumlah_numerator' => (string)(
                            $numerator['value'] ?? ''
                        ),
                        'kode_numerator' => trim(
                            (string)($numerator['code'] ?? '')
                        ),
                        'nama_numerator' => trim(
                            (string)(
                                $numerator['unit']
                                ?? $numerator['code']
                                ?? ''
                            )
                        ),
                        'jumlah_denominator' => (string)(
                            $denominator['value'] ?? ''
                        ),
                        'kode_denominator' => trim(
                            (string)($denominator['code'] ?? '')
                        ),
                        'nama_denominator' => trim(
                            (string)(
                                $denominator['unit']
                                ?? $denominator['code']
                                ?? ''
                            )
                        )
                    ];
                }

                if (!empty($ingredientArray)) {
                    $ingredient_json = json_encode(
                        $ingredientArray,
                        JSON_UNESCAPED_UNICODE |
                        JSON_UNESCAPED_SLASHES
                    );
                }
            }

            if ($medication_name === '') {
                $medication_name = 'Medication dari SATUSEHAT';
            }

            /*
             * MAPPING MEDICATION LOKAL
             *
             * Prioritas:
             * 1. id_medication SATUSEHAT
             * 2. KFA yang sama untuk non-racikan
             * 3. Buat medication baru
             */
            $id_index_medication = 0;

            if ($id_medication !== '') {
                $stmt = $Conn->prepare("
                    SELECT id_index_medication
                    FROM medication
                    WHERE id_medication = ?
                    LIMIT 1
                ");

                if ($stmt) {
                    $stmt->bind_param("s", $id_medication);
                    $stmt->execute();

                    $medLocal = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                    if ($medLocal) {
                        $id_index_medication = (int)$medLocal['id_index_medication'];
                    }
                }
            }

            // MAP BY KFA UNTUK NON RACIKAN
            if (
                $id_index_medication < 1 &&
                $kfa_code !== null &&
                ($racikan_code === null || $racikan_code === 'NC')
            ) {
                $stmt = $Conn->prepare("
                    SELECT id_index_medication
                    FROM medication
                    WHERE kfa_code = ?
                    AND (
                        racikan_code = 'NC'
                        OR racikan_code IS NULL
                        OR racikan_code = ''
                    )
                    LIMIT 1
                ");

                if ($stmt) {
                    $stmt->bind_param("s", $kfa_code);
                    $stmt->execute();

                    $medLocal = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                    if ($medLocal) {
                        $id_index_medication = (int)$medLocal['id_index_medication'];
                    }
                }
            }

            // INSERT MEDICATION BARU
            if ($id_index_medication < 1) {

                // KODE LOKAL
                do {
                    $medication_code = 'MED-'.GenerateKodeBarang(8);

                    $stmt = $Conn->prepare("
                        SELECT id_index_medication
                        FROM medication
                        WHERE medication_code = ?
                        LIMIT 1
                    ");

                    if (!$stmt) {
                        throw new Exception(
                            'Gagal memeriksa kode medication lokal.'
                        );
                    }

                    $stmt->bind_param("s", $medication_code);
                    $stmt->execute();

                    $codeExists = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                } while ($codeExists);

                $id_medication_db = $id_medication !== ''
                    ? $id_medication
                    : null;

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
                    throw new Exception(
                        'Gagal mempersiapkan penyimpanan medication item ke-'.
                        ($index + 1).'.'
                    );
                }

                $stmt->bind_param(
                    "sssssssssssss",
                    $id_medication_db,
                    $medication_code,
                    $medication_name,
                    $medication_category,
                    $kfa_code,
                    $kfa_display,
                    $sediaan_code,
                    $sediaan_display,
                    $racikan_code,
                    $racikan_display,
                    $manufacturer_id,
                    $manufacturer_name,
                    $ingredient_json
                );

                if (!$stmt->execute()) {
                    $error = $stmt->error;
                    $stmt->close();

                    throw new Exception(
                        'Gagal menyimpan medication item ke-'.
                        ($index + 1).'. Keterangan: '.$error
                    );
                }

                $id_index_medication = (int)$Conn->insert_id;
                $stmt->close();
            }

            // DATA MEDICATION REQUEST
            $intent = trim((string)($mr['intent'] ?? 'order'));
            $status = trim((string)($mr['status'] ?? 'active'));

            if (!in_array($intent, ['order','plan','proposal'], true)) {
                $intent = 'order';
            }

            if (!in_array(
                $status,
                [
                    'active',
                    'on-hold',
                    'completed',
                    'stopped',
                    'cancelled',
                    'entered-in-error'
                ],
                true
            )) {
                $status = 'active';
            }

            $dosage = $mr['dosageInstruction'][0] ?? [];
            $repeat = $dosage['timing']['repeat'] ?? [];

            $dosage_inst_text = trim(
                (string)(
                    $dosage['patientInstruction']
                    ?? $dosage['text']
                    ?? ''
                )
            );

            $frequency = (int)($repeat['frequency'] ?? 0);
            $period    = (int)($repeat['period'] ?? 0);

            $period_unit = trim(
                (string)($repeat['periodUnit'] ?? '')
            );

            $dose = $dosage['doseAndRate'][0]['doseQuantity'] ?? [];

            $dose_value  = (float)($dose['value'] ?? 0);
            $dose_unit   = trim((string)($dose['unit'] ?? ''));
            $dose_code   = trim((string)($dose['code'] ?? ''));
            $dose_system = trim((string)($dose['system'] ?? ''));

            $routeCoding = $dosage['route']['coding'][0] ?? [];

            $route_display = trim(
                (string)($routeCoding['display'] ?? '')
            );

            $route_code = trim(
                (string)($routeCoding['code'] ?? '')
            );

            $route_system = trim(
                (string)($routeCoding['system'] ?? '')
            );

            $dispense = $mr['dispenseRequest']['quantity'] ?? [];

            $dispense_value = (float)(
                $dispense['value'] ?? 0
            );

            $dispense_unit = trim(
                (string)($dispense['unit'] ?? '')
            );

            $dispense_code = trim(
                (string)($dispense['code'] ?? '')
            );

            $dispense_sys = trim(
                (string)($dispense['system'] ?? '')
            );

            $supply = $mr['dispenseRequest']['expectedSupplyDuration'] ?? [];

            $supply_duration_value = (int)(
                $supply['value'] ?? 0
            );

            $supply_duration_unit = trim(
                (string)($supply['unit'] ?? '')
            );

            $supply_duration_code = trim(
                (string)($supply['code'] ?? '')
            );

            $supply_duration_sys = trim(
                (string)($supply['system'] ?? '')
            );

            // RACIKAN MR
            $mr_racikan_code    = $racikan_code;
            $mr_racikan_display = $racikan_display;

            // INGREDIENT MR
            $mr_ingredient = $ingredient_json;

            // ID ITEM LOKAL
            $MedicationRequestId = GenerateKodeBarang(12);

            // Pastikan unik
            do {
                $MedicationRequestId = 'MR-'.GenerateKodeBarang(10);

                $stmt = $Conn->prepare("
                    SELECT MedicationRequestId
                    FROM medication_request
                    WHERE MedicationRequestId = ?
                    LIMIT 1
                ");

                if (!$stmt) {
                    throw new Exception(
                        'Gagal memeriksa ID item resep.'
                    );
                }

                $stmt->bind_param("s", $MedicationRequestId);
                $stmt->execute();

                $mrCodeExists = $stmt->get_result()->fetch_assoc();
                $stmt->close();

            } while ($mrCodeExists);

            // VALIDASI FIELD NOT NULL DATABASE
            if ($frequency < 1) $frequency = 1;
            if ($period < 1) $period = 1;
            if ($period_unit === '') $period_unit = 'd';

            if ($dose_value <= 0) $dose_value = 1;

            if ($dose_unit === '') {
                $dose_unit = $dose_code !== ''
                    ? $dose_code
                    : '-';
            }

            if ($route_display === '') {
                $route_display = '-';
            }

            if ($dispense_value <= 0) {
                $dispense_value = 1;
            }

            if ($dispense_unit === '') {
                $dispense_unit = $dispense_code !== ''
                    ? $dispense_code
                    : '-';
            }

            if ($supply_duration_value < 1) {
                $supply_duration_value = 1;
            }

            if ($supply_duration_unit === '') {
                $supply_duration_unit = $supply_duration_code !== ''
                    ? $supply_duration_code
                    : 'days';
            }

            // INSERT MEDICATION REQUEST
            $stmt = $Conn->prepare("
                INSERT INTO medication_request (
                    MedicationRequestId,
                    id_medication_request_group,
                    id_medication_request,
                    intent,
                    id_index_medication,
                    name_medication,
                    status,
                    dosage_inst_text,
                    dosage_inst_frequency,
                    dosage_inst_period,
                    dosage_inst_period_unit,
                    dose_value,
                    dose_unit,
                    dose_code,
                    dose_system,
                    route_display,
                    route_code,
                    route_system,
                    dispense_value,
                    dispense_unit,
                    dispense_code,
                    dispense_sys,
                    supply_duration_value,
                    supply_duration_unit,
                    supply_duration_code,
                    supply_duration_sys,
                    racikan_code,
                    racikan_display,
                    ingredient
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?
                )
            ");

            if (!$stmt) {
                throw new Exception(
                    'Gagal mempersiapkan item resep ke-'.($index + 1).'.'
                );
            }

            $stmt->bind_param(
                "sississsiisdssssssdsssissssss",
                $MedicationRequestId,
                $id_medication_request_group,
                $id_medication_request,
                $intent,
                $id_index_medication,
                $medication_name,
                $status,
                $dosage_inst_text,
                $frequency,
                $period,
                $period_unit,
                $dose_value,
                $dose_unit,
                $dose_code,
                $dose_system,
                $route_display,
                $route_code,
                $route_system,
                $dispense_value,
                $dispense_unit,
                $dispense_code,
                $dispense_sys,
                $supply_duration_value,
                $supply_duration_unit,
                $supply_duration_code,
                $supply_duration_sys,
                $mr_racikan_code,
                $mr_racikan_display,
                $mr_ingredient
            );

            if (!$stmt->execute()) {
                $error = $stmt->error;
                $stmt->close();

                throw new Exception(
                    'Gagal menyimpan item resep ke-'.
                    ($index + 1).'. Keterangan: '.$error
                );
            }

            $stmt->close();
            $jumlahInserted++;
        }

        // COMMIT
        $Conn->commit();

        responseImport(
            'success',
            'Resep dengan NRN '.$nrn.' berhasil disimpan.',
            [
                'id_medication_request_group' => $id_medication_request_group,
                'no_resep_nasional'           => $nrn,
                'id_document_reference'       => $id_document_reference,
                'jumlah_item'                 => $jumlahInserted,
                'patient_ihs'                 => $patient_ihs,
                'id_anggota'                  => $id_anggota,
                'id_encounter'                => $id_encounter,
                'id_kunjungan'                => $id_kunjungan
            ]
        );

    } catch (Throwable $e) {

        $Conn->rollback();

        if (isset($stmt) && $stmt instanceof mysqli_stmt) {
            try {
                $stmt->close();
            } catch (Throwable $ignored) {
            }
        }

        responseImport(
            'error',
            htmlspecialchars(
                $e->getMessage(),
                ENT_QUOTES,
                'UTF-8'
            )
        );
    }
?>