<?php
    // CONNECTION, HELPER, SESSION & AKSES
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    include "../../_Config/FungsiAkses.php";

    date_default_timezone_set('Asia/Jakarta');
    header('Content-Type: application/json; charset=utf-8');

    function responseDR(string $status, string $message, array $data = []): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'data'    => $data
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    function operationOutcomeDR(array $data): string {
        if (($data['resourceType'] ?? '') !== 'OperationOutcome' || empty($data['issue'])) {
            return 'SATUSEHAT mengembalikan response error.';
        }

        $messages = [];

        foreach ($data['issue'] as $issue) {
            $message =
                $issue['details']['text'] ??
                $issue['diagnostics'] ??
                $issue['code'] ??
                '';

            if ($message !== '') {
                $messages[] = $message;
            }
        }

        return !empty($messages)
            ? implode(' | ', $messages)
            : 'SATUSEHAT mengembalikan OperationOutcome.';
    }

    function validUrlDR(string $url): bool {
        return $url !== '' && filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    // VALIDASI SESSION & METHOD
    if (empty($SessionIdAkses)) {
        responseDR('error', 'Sesi akses telah berakhir. Silakan login ulang.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseDR('error', 'Metode request tidak valid.');
    }

    // DATA FORM
    $id_medication_request_group = (int)($_POST['id_medication_request_group'] ?? 0);
    $coverage_type    = trim((string)($_POST['coverage_type'] ?? ''));
    $nomor_sep        = trim((string)($_POST['nomor_sep'] ?? ''));
    $description      = trim((string)($_POST['description'] ?? ''));
    $homepage_url     = trim((string)($_POST['homepage_url'] ?? ''));
    $emergency_url    = trim((string)($_POST['emergency_url'] ?? ''));
    $confirmation_url = trim((string)($_POST['confirmation_url'] ?? ''));

    // VALIDASI FORM
    if ($id_medication_request_group < 1) {
        responseDR('error', 'ID resep tidak valid.');
    }

    $coverageAllowed = [
        'BPJS-K',
        'Biaya-Sendiri',
        'Biaya-Perusahaan',
        'Asuransi-Swasta'
    ];

    if (!in_array($coverage_type, $coverageAllowed, true)) {
        responseDR('error', 'Jenis pembiayaan tidak valid.');
    }

    if ($coverage_type === 'BPJS-K' && $nomor_sep === '') {
        responseDR('error', 'Nomor SEP wajib diisi untuk pembiayaan BPJS Kesehatan.');
    }

    if ($homepage_url === '') {
        responseDR('error', 'Homepage fasyankes tidak boleh kosong.');
    }

    if (!validUrlDR($homepage_url)) {
        responseDR('error', 'Format Homepage Fasyankes tidak valid.');
    }

    if ($emergency_url !== '' && !validUrlDR($emergency_url)) {
        responseDR('error', 'Format Link Kontak Emergensi tidak valid.');
    }

    if ($confirmation_url !== '' && !validUrlDR($confirmation_url)) {
        responseDR('error', 'Format Link Konfirmasi Resep tidak valid.');
    }

    // AMBIL GROUP + PASIEN + ENCOUNTER
    $stmt = $Conn->prepare("
        SELECT
            mrg.id_medication_request_group,
            mrg.id_document_reference,
            mrg.nama_pasien,
            mrg.datetime_creat,
            mrg.dokter_ihs,
            mrg.dokter_nama,
            mrg.id_anggota,
            mrg.id_kunjungan,

            a.id_ihs AS patient_ihs,
            a.nama AS patient_name,

            k.id_encounter

        FROM medication_request_group mrg

        LEFT JOIN anggota a
            ON a.id_anggota = mrg.id_anggota

        LEFT JOIN kunjungan k
            ON k.id_kunjungan = mrg.id_kunjungan

        WHERE mrg.id_medication_request_group = ?
        LIMIT 1
    ");

    if (!$stmt) {
        responseDR('error', 'Gagal mempersiapkan data resep.');
    }

    $stmt->bind_param("i", $id_medication_request_group);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();

        responseDR(
            'error',
            'Gagal membuka data resep.<br>Keterangan : '.
            htmlspecialchars($error, ENT_QUOTES, 'UTF-8')
        );
    }

    $group = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$group) {
        responseDR('error', 'Data resep tidak ditemukan.');
    }

    // CEGAH PENGIRIMAN GANDA
    if (!empty($group['id_document_reference'])) {
        responseDR(
            'error',
            'DocumentReference sudah pernah dikirim ke SATUSEHAT dengan ID '.
            htmlspecialchars(
                $group['id_document_reference'],
                ENT_QUOTES,
                'UTF-8'
            ).'.'
        );
    }

    // MAPPING
    $patient_ihs = trim((string)($group['patient_ihs'] ?? ''));
    $patient_name = trim((string)(
        $group['patient_name'] ??
        $group['nama_pasien'] ??
        ''
    ));

    $id_encounter = trim((string)($group['id_encounter'] ?? ''));
    $dokter_ihs   = trim((string)($group['dokter_ihs'] ?? ''));
    $dokter_nama  = trim((string)($group['dokter_nama'] ?? ''));

    // VALIDASI DATA UTAMA
    $missing = [];

    if ($patient_ihs === '') {
        $missing[] = 'Patient IHS ID';
    }

    if ($id_encounter === '') {
        $missing[] = 'Encounter ID';
    }

    if ($dokter_ihs === '') {
        $missing[] = 'Practitioner IHS Dokter';
    }

    if (!empty($missing)) {
        responseDR(
            'error',
            'Data belum lengkap:<br>• '.
            implode('<br>• ', $missing)
        );
    }

    // AMBIL SELURUH MEDICATION REQUEST
    $stmt = $Conn->prepare("
        SELECT
            MedicationRequestId,
            id_medication_request,
            name_medication
        FROM medication_request
        WHERE id_medication_request_group = ?
        ORDER BY MedicationRequestId ASC
    ");

    if (!$stmt) {
        responseDR('error', 'Gagal mempersiapkan item Medication Request.');
    }

    $stmt->bind_param("i", $id_medication_request_group);

    if (!$stmt->execute()) {
        $stmt->close();
        responseDR('error', 'Gagal membuka item Medication Request.');
    }

    $resultMR = $stmt->get_result();

    $jumlah_item = 0;
    $medicationReferences = [];
    $belumTerkirim = [];

    while ($item = $resultMR->fetch_assoc()) {
        $jumlah_item++;

        $localId = trim((string)($item['MedicationRequestId'] ?? ''));
        $satusehatId = trim((string)($item['id_medication_request'] ?? ''));

        if ($satusehatId === '') {
            $belumTerkirim[] = $localId;
            continue;
        }

        $medicationReferences[] = [
            'reference' => 'MedicationRequest/'.$satusehatId,
            'display'   => trim((string)($item['name_medication'] ?? ''))
        ];
    }

    $stmt->close();

    if ($jumlah_item < 1) {
        responseDR('error', 'Resep belum memiliki item Medication Request.');
    }

    if (!empty($belumTerkirim)) {
        responseDR(
            'error',
            'Masih terdapat Medication Request yang belum dikirim ke SATUSEHAT:<br>• '.
            implode('<br>• ', array_map(
                fn($id) => htmlspecialchars($id, ENT_QUOTES, 'UTF-8'),
                $belumTerkirim
            ))
        );
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
        responseDR('error', 'Gagal membuka konfigurasi SATUSEHAT.');
    }

    if (!$stmt->execute()) {
        $stmt->close();
        responseDR('error', 'Gagal membaca konfigurasi SATUSEHAT.');
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

    $organization_id = trim(
        (string)($config['organization_id'] ?? '')
    );

    if ($base_url === '') {
        responseDR('error', 'URL SATUSEHAT belum dikonfigurasi.');
    }

    if ($organization_id === '') {
        responseDR('error', 'Organization IHS ID belum dikonfigurasi.');
    }

    // TOKEN
    $tokenResult = generateTokenSatuSehat($Conn);

    if (
        empty($tokenResult) ||
        ($tokenResult['status'] ?? '') !== 'success' ||
        empty($tokenResult['token'])
    ) {
        responseDR(
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

    // IDENTIFIER
    $identifier = [
        [
            'system' => 'http://terminology.kemkes.go.id/CodeSystem/coverage-type',
            'value'  => $coverage_type
        ]
    ];

    // SEP BPJS
    if ($coverage_type === 'BPJS-K') {
        $identifier[] = [
            'system' => 'http://sys-ids.kemkes.go.id/claim-number/'.$organization_id,
            'value'  => $nomor_sep
        ];
    }

    // CONTENT DOCUMENT
    $content = [
        [
            'attachment' => [
                'title' => 'Homepage Fasyankes',
                'url'   => $homepage_url
            ],
            'format' => [
                'system'  => 'http://terminology.kemkes.go.id/CodeSystem/documentformat',
                'code'    => 'DF000001',
                'display' => 'Homepage Fasyankes'
            ]
        ]
    ];

    // LINK EMERGENSI
    if ($emergency_url !== '') {
        $content[] = [
            'attachment' => [
                'title' => 'Link Kontak Emergensi',
                'url'   => $emergency_url
            ],
            'format' => [
                'system'  => 'http://terminology.kemkes.go.id/CodeSystem/documentformat',
                'code'    => 'DF000002',
                'display' => 'Link Kontak Emergensi'
            ]
        ];
    }

    // LINK KONFIRMASI
    if ($confirmation_url !== '') {
        $content[] = [
            'attachment' => [
                'title' => 'Link Kontak Konfirmasi Resep',
                'url'   => $confirmation_url
            ],
            'format' => [
                'system'  => 'http://terminology.kemkes.go.id/CodeSystem/documentformat',
                'code'    => 'DF000003',
                'display' => 'Link Kontak Konfirmasi Resep'
            ]
        ];
    }

    // PAYLOAD
    $payload = [
        'resourceType' => 'DocumentReference',

        'identifier' => $identifier,

        'status' => 'current',

        'subject' => [
            'reference' => 'Patient/'.$patient_ihs,
            'display'   => $patient_name
        ],

        'author' => [
            [
                'reference' => 'Practitioner/'.$dokter_ihs,
                'display'   => $dokter_nama
            ]
        ],

        'custodian' => [
            'reference' => 'Organization/'.$organization_id
        ],

        'content' => $content,

        'context' => [
            'encounter' => [
                [
                    'reference' => 'Encounter/'.$id_encounter
                ]
            ],
            'related' => $medicationReferences
        ]
    ];

    // DESCRIPTION OPTIONAL
    if ($description !== '') {
        $payload['description'] = $description;
    }

    // WAKTU DOKUMEN
    $datetimeCreat = trim((string)($group['datetime_creat'] ?? ''));

    if ($datetimeCreat !== '' && strtotime($datetimeCreat) !== false) {
        try {
            $documentDate = new DateTime(
                $datetimeCreat,
                new DateTimeZone('Asia/Jakarta')
            );

            $documentDate->setTimezone(
                new DateTimeZone('UTC')
            );

            $payload['date'] = $documentDate->format('Y-m-d\TH:i:sP');

        } catch (Throwable $e) {
            responseDR('error', 'Tanggal resep tidak dapat dikonversi.');
        }
    }

    // ENCODE PAYLOAD
    $payloadJson = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    if ($payloadJson === false) {
        responseDR('error', 'Gagal membentuk payload DocumentReference.');
    }

    // KIRIM KE SATUSEHAT
    $url = $base_url.'/fhir-r4/v1/DocumentReference';

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

        responseDR(
            'error',
            'Gagal mengirim DocumentReference ke SATUSEHAT.<br>'.
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
        responseDR(
            'error',
            'SATUSEHAT mengembalikan response yang tidak valid.<br>'.
            'HTTP Code : '.$httpCode
        );
    }

    // SATUSEHAT ERROR
    if (
        $httpCode < 200 ||
        $httpCode >= 300 ||
        ($satusehatData['resourceType'] ?? '') === 'OperationOutcome'
    ) {
        responseDR(
            'error',
            'Pengiriman DocumentReference ke SATUSEHAT gagal.<br>'.
            htmlspecialchars(
                operationOutcomeDR($satusehatData),
                ENT_QUOTES,
                'UTF-8'
            )
        );
    }

    // ID DOCUMENT REFERENCE
    $id_document_reference = trim(
        (string)($satusehatData['id'] ?? '')
    );

    if ($id_document_reference === '') {
        responseDR(
            'error',
            'SATUSEHAT tidak mengembalikan ID DocumentReference.'
        );
    }

    // UPDATE DATABASE
    $update_at      = date('Y-m-d H:i:s');
    $update_by_id   = (int)$SessionIdAkses;
    $update_by_name = trim((string)($SessionNama ?? ''));

    if ($update_by_name === '') {
        $update_by_name = 'System';
    }

    $stmt = $Conn->prepare("
        UPDATE medication_request_group
        SET
            id_document_reference = ?,
            update_at             = ?,
            update_by_id          = ?,
            update_by_name        = ?
        WHERE id_medication_request_group = ?
        AND (
            id_document_reference IS NULL
            OR TRIM(id_document_reference) = ''
        )
    ");

    if (!$stmt) {
        responseDR(
            'error',
            'DocumentReference berhasil dibuat di SATUSEHAT dengan ID '.
            htmlspecialchars($id_document_reference, ENT_QUOTES, 'UTF-8').
            ', tetapi gagal mempersiapkan update database lokal.',
            [
                'id_document_reference' => $id_document_reference
            ]
        );
    }

    $stmt->bind_param(
        "ssisi",
        $id_document_reference,
        $update_at,
        $update_by_id,
        $update_by_name,
        $id_medication_request_group
    );

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();

        responseDR(
            'error',
            'DocumentReference berhasil dibuat di SATUSEHAT dengan ID '.
            htmlspecialchars($id_document_reference, ENT_QUOTES, 'UTF-8').
            ', tetapi database lokal gagal diperbarui.<br>'.
            'Keterangan : '.
            htmlspecialchars($error, ENT_QUOTES, 'UTF-8'),
            [
                'id_document_reference' => $id_document_reference
            ]
        );
    }

    if ($stmt->affected_rows !== 1) {
        $stmt->close();

        responseDR(
            'error',
            'DocumentReference berhasil dibuat di SATUSEHAT dengan ID '.
            htmlspecialchars($id_document_reference, ENT_QUOTES, 'UTF-8').
            ', tetapi ID tersebut tidak berhasil disimpan pada resep lokal.',
            [
                'id_document_reference' => $id_document_reference
            ]
        );
    }

    $stmt->close();

    // SUCCESS
    responseDR(
        'success',
        'Document Reference berhasil dikirim ke SATUSEHAT.',
        [
            'id_medication_request_group' => $id_medication_request_group,
            'id_document_reference'       => $id_document_reference
        ]
    );
?>