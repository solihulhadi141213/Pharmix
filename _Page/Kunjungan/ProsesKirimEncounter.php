<?php
    // Konfigurasi & Inisialisasi
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Header JSON
    header('Content-Type: application/json; charset=utf-8');

    // Function Response JSON
    function responseJson($status, $message, $data = []) {
        echo json_encode(
            array_merge(['status' => $status, 'message' => $message], $data),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        exit;
    }

    // Function Ambil Error FHIR
    function getFhirErrorMessage($data) {
        if (!is_array($data)) {
            return 'Response SATUSEHAT tidak dapat dibaca.';
        }

        if (isset($data['resourceType']) && $data['resourceType'] === 'OperationOutcome') {
            $messages = [];
            if (isset($data['issue']) && is_array($data['issue'])) {
                foreach ($data['issue'] as $issue) {
                    $message = '';
                    if (!empty($issue['details']['text'])) {
                        $message = $issue['details']['text'];
                    } elseif (!empty($issue['diagnostics'])) {
                        $message = $issue['diagnostics'];
                    } elseif (!empty($issue['code'])) {
                        $message = $issue['code'];
                    }
                    if ($message !== '') {
                        $messages[] = $message;
                    }
                }
            }
            if (!empty($messages)) {
                return implode('<br>', array_unique($messages));
            }
        }

        if (!empty($data['message'])) {
            return $data['message'];
        }

        return 'SATUSEHAT menolak pengiriman Encounter.';
    }

    // Validasi Session
    if (empty($SessionIdAkses)) {
        responseJson('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
    }

    // Validasi Method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseJson('error', 'Metode request tidak valid.');
    }

    // Validasi ID Kunjungan
    $id_kunjungan = trim($_POST['id_kunjungan'] ?? '');
    if ($id_kunjungan === '' || !ctype_digit($id_kunjungan) || (int) $id_kunjungan <= 0) {
        responseJson('error', 'ID Kunjungan tidak valid.');
    }
    $id_kunjungan = (int) $id_kunjungan;

    // Ambil Data Kunjungan
    $query = "
        SELECT 
            k.id_kunjungan, k.id_anggota, k.id_encounter, k.tanggal_kunjungan, k.priority, k.keluhan, k.jenis_kunjungan, k.status,
            k.id_dokter_penerima, k.kode_dokter_penerima, k.nama_dokter_penerima,
            k.id_dpjp, k.kode_dpjp, k.nama_dpjp,
            k.id_poli, k.kode_poli, k.nama_poli,
            k.kelas_inap, k.ruang_inap,
            a.id_pasien AS rm_pasien, a.id_ihs AS id_ihs_pasien, a.nama AS nama_pasien,
            dokter_penerima.id_practitioner AS id_practitioner_penerima,
            dokter_penerima.medicalPersonelCode AS kode_practitioner_penerima,
            dokter_penerima.medicalPersonelName AS nama_practitioner_penerima,
            dpjp.id_practitioner AS id_practitioner_dpjp,
            dpjp.medicalPersonelCode AS kode_practitioner_dpjp,
            dpjp.medicalPersonelName AS nama_practitioner_dpjp,
            poli.satuSehatCode AS id_location,
            poli.polyclinicCode AS kode_poliklinik,
            poli.polyclinicName AS nama_poliklinik
        FROM kunjungan AS k
        LEFT JOIN anggota AS a ON k.id_anggota = a.id_anggota
        LEFT JOIN medical_personel AS dokter_penerima ON k.id_dokter_penerima = dokter_penerima.medicalPersonelId
        LEFT JOIN medical_personel AS dpjp ON k.id_dpjp = dpjp.medicalPersonelId
        LEFT JOIN polyclinic AS poli ON k.id_poli = poli.polyclinicId
        WHERE k.id_kunjungan = ?
        LIMIT 1
    ";

    // Prepare & Execute Query Kunjungan
    $stmt = mysqli_prepare($Conn, $query);
    if (!$stmt) {
        responseJson('error', 'Terjadi kesalahan pada saat menyiapkan data kunjungan.');
    }

    mysqli_stmt_bind_param($stmt, 'i', $id_kunjungan);
    if (!mysqli_stmt_execute($stmt)) {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        responseJson('error', 'Gagal membuka data kunjungan.<br>Pesan : ' . $error);
    }

    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // Validasi Data Kunjungan
    if (!$data) {
        responseJson('error', 'Data kunjungan tidak ditemukan.');
    }

    // Cek ID Encounter
    $id_encounter_lama = trim($data['id_encounter'] ?? '');
    if ($id_encounter_lama !== '') {
        responseJson('error', 'Encounter sudah pernah dikirim ke SATUSEHAT.', ['id_encounter' => $id_encounter_lama]);
    }

    // Data Pasien
    $id_ihs_pasien = trim($data['id_ihs_pasien'] ?? '');
    $nama_pasien = trim($data['nama_pasien'] ?? '');

    // Data Kunjungan
    $tanggal_kunjungan = trim($data['tanggal_kunjungan'] ?? '');
    $jenis_kunjungan = strtoupper(trim($data['jenis_kunjungan'] ?? ''));
    $priority = trim($data['priority'] ?? '');
    $keluhan = trim($data['keluhan'] ?? '');
    $status = trim($data['status'] ?? '');

    // Dokter Penerima
    $id_practitioner_penerima = trim($data['id_practitioner_penerima'] ?? '');
    $nama_dokter_penerima = trim($data['nama_practitioner_penerima'] ?? $data['nama_dokter_penerima'] ?? '');

    // DPJP
    $id_practitioner_dpjp = trim($data['id_practitioner_dpjp'] ?? '');
    $nama_dpjp = trim($data['nama_practitioner_dpjp'] ?? $data['nama_dpjp'] ?? '');

    // Location
    $id_location = trim($data['id_location'] ?? '');
    $nama_poli = trim($data['nama_poliklinik'] ?? $data['nama_poli'] ?? '');

    // Validasi Data Wajib & Relasi SATUSEHAT
    $errors = [];
    if ($id_ihs_pasien === '') $errors[] = 'ID IHS pasien belum tersedia.';
    if ($nama_pasien === '') $errors[] = 'Nama pasien tidak tersedia.';
    if ($tanggal_kunjungan === '') $errors[] = 'Tanggal kunjungan belum tersedia.';
    if ($jenis_kunjungan === '') $errors[] = 'Jenis kunjungan belum tersedia.';
    if ($status === '') $errors[] = 'Status Encounter belum tersedia.';

    // Validasi Dokter Penerima
    if (empty($data['id_dokter_penerima'])) {
        $errors[] = 'Dokter penerima belum dipilih.';
    } elseif ($id_practitioner_penerima === '') {
        $errors[] = 'Dokter penerima belum memiliki ID Practitioner SATUSEHAT.';
    }

    // Validasi DPJP
    if (empty($data['id_dpjp'])) {
        $errors[] = 'Dokter DPJP belum dipilih.';
    } elseif ($id_practitioner_dpjp === '') {
        $errors[] = 'Dokter DPJP belum memiliki ID Practitioner SATUSEHAT.';
    }

    // Validasi Poliklinik
    if (empty($data['id_poli'])) {
        $errors[] = 'Poliklinik belum dipilih.';
    } elseif ($id_location === '') {
        $errors[] = 'Poliklinik belum memiliki ID Location SATUSEHAT.';
    }

    // Validasi Status & Class
    $allowedStatus = ['planned', 'arrived', 'triaged', 'in-progress', 'onleave', 'finished', 'cancelled', 'entered-in-error', 'unknown'];
    if ($status !== '' && !in_array($status, $allowedStatus, true)) {
        $errors[] = 'Status Encounter tidak valid.';
    }

    $allowedClass = ['AMB', 'IMP', 'EMER'];
    if ($jenis_kunjungan !== '' && !in_array($jenis_kunjungan, $allowedClass, true)) {
        $errors[] = 'Jenis kunjungan tidak valid.';
    }

    if (!empty($errors)) {
        responseJson('error', implode('<br>', $errors));
    }

    // Encounter Class Mapping
    switch ($jenis_kunjungan) {
        case 'AMB':
            $classCode = 'AMB';
            $classDisplay = 'ambulatory';
            break;
        case 'IMP':
            $classCode = 'IMP';
            $classDisplay = 'inpatient encounter';
            break;
        case 'EMER':
            $classCode = 'EMER';
            $classDisplay = 'emergency';
            break;
        default:
            responseJson('error', 'Jenis kunjungan tidak dikenali.');
    }

    // Priority Mapping
    switch ($priority) {
        case 'Emergency':
            $priorityCode = 'EM';
            $priorityDisplay = 'emergency';
            break;
        case 'Urgent':
            $priorityCode = 'UR';
            $priorityDisplay = 'urgent';
            break;
        default:
            $priorityCode = 'R';
            $priorityDisplay = 'routine';
            break;
    }

    // Format Datetime
    try {
        $dateEncounter = new DateTime($tanggal_kunjungan, new DateTimeZone('Asia/Jakarta'));
        $periodStart = $dateEncounter->format('Y-m-d\TH:i:sP');
    } catch (Throwable $e) {
        responseJson('error', 'Format tanggal kunjungan tidak valid.');
    }

    // Buka Konfigurasi SATUSEHAT Aktif
    $stmtSetting = mysqli_prepare($Conn, "SELECT * FROM connection_satu_sehat WHERE status_connection_satu_sehat = 1 LIMIT 1");
    if (!$stmtSetting || !mysqli_stmt_execute($stmtSetting)) {
        if ($stmtSetting) mysqli_stmt_close($stmtSetting);
        responseJson('error', 'Terjadi kesalahan pada saat membuka pengaturan koneksi SATUSEHAT.');
    }

    $resultSetting = mysqli_stmt_get_result($stmtSetting);
    $setting = mysqli_fetch_assoc($resultSetting);
    mysqli_stmt_close($stmtSetting);

    if (!$setting) {
        responseJson('error', 'Konfigurasi SATUSEHAT aktif tidak ditemukan.');
    }

    // Base URL
    $baseurl_satusehat = rtrim(trim($setting['url_connection_satu_sehat'] ?? ''), '/');
    if ($baseurl_satusehat === '') {
        responseJson('error', 'URL koneksi SATUSEHAT belum dikonfigurasi.');
    }

    // Organization ID
    $organizationId = trim($setting['organization_id'] ?? $setting['organizationId'] ?? $setting['id_organization'] ?? $setting['id_organization_satu_sehat'] ?? '');
    if ($organizationId === '') {
        responseJson('error', 'Organization ID SATUSEHAT belum tersedia pada pengaturan koneksi.');
    }

    // Generate Token
    $tokenResult = generateTokenSatuSehat($Conn);
    if (($tokenResult['status'] ?? 'error') !== 'success') {
        $messageToken = $tokenResult['message'] ?? 'Tidak diketahui';
        responseJson('error', 'Terjadi kesalahan pada saat generate token SATUSEHAT.<br>Pesan : ' . $messageToken);
    }

    $token = trim($tokenResult['token'] ?? '');
    if ($token === '') {
        responseJson('error', 'Token SATUSEHAT tidak ditemukan.');
    }

    // Identifier Encounter
    $identifierSystem = 'http://sys-ids.kemkes.go.id/encounter/' . $organizationId;
    $identifierValue = 'KUNJUNGAN-' . $id_kunjungan;

    // Participant
    $participant = [];

    // Dokter Penerima
    if ($id_practitioner_penerima !== '') {
        $participant[] = [
            'individual' => [
                'reference' => 'Practitioner/' . $id_practitioner_penerima,
                'display' => $nama_dokter_penerima
            ]
        ];
    }

    // DPJP
    if ($id_practitioner_dpjp !== '') {
        if ($id_practitioner_dpjp !== $id_practitioner_penerima) {
            $participant[] = [
                'type' => [
                    [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/v3-ParticipationType',
                                'code' => 'ATND',
                                'display' => 'attender'
                            ]
                        ]
                    ]
                ],
                'individual' => [
                    'reference' => 'Practitioner/' . $id_practitioner_dpjp,
                    'display' => $nama_dpjp
                ]
            ];
        } else {
            if (isset($participant[0])) {
                $participant[0]['type'] = [
                    [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/v3-ParticipationType',
                                'code' => 'ATND',
                                'display' => 'attender'
                            ]
                        ]
                    ]
                ];
            }
        }
    }

    // Status History
    $statusHistory = [];
    switch ($status) {
        case 'planned':
            $statusHistory[] = ['status' => 'planned', 'period' => ['start' => $periodStart]];
            break;
        case 'arrived':
            $statusHistory[] = ['status' => 'arrived', 'period' => ['start' => $periodStart]];
            break;
        case 'triaged':
            $statusHistory[] = ['status' => 'arrived', 'period' => ['start' => $periodStart]];
            $statusHistory[] = ['status' => 'triaged', 'period' => ['start' => $periodStart]];
            break;
        case 'in-progress':
            $statusHistory[] = ['status' => 'arrived', 'period' => ['start' => $periodStart]];
            $statusHistory[] = ['status' => 'in-progress', 'period' => ['start' => $periodStart]];
            break;
        case 'finished':
            $statusHistory[] = ['status' => 'arrived', 'period' => ['start' => $periodStart]];
            $statusHistory[] = ['status' => 'in-progress', 'period' => ['start' => $periodStart]];
            $statusHistory[] = ['status' => 'finished', 'period' => ['start' => $periodStart]];
            break;
        case 'cancelled':
            $statusHistory[] = ['status' => 'cancelled', 'period' => ['start' => $periodStart]];
            break;
        default:
            $statusHistory[] = ['status' => $status, 'period' => ['start' => $periodStart]];
            break;
    }

    // Payload Encounter
    $payload = [
        'resourceType' => 'Encounter',
        'identifier' => [
            [
                'system' => $identifierSystem,
                'value' => $identifierValue
            ]
        ],
        'status' => $status,
        'statusHistory' => $statusHistory,
        'class' => [
            'system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode',
            'code' => $classCode,
            'display' => $classDisplay
        ],
        'priority' => [
            'coding' => [
                [
                    'system' => 'http://terminology.hl7.org/CodeSystem/v3-ActPriority',
                    'code' => $priorityCode,
                    'display' => $priorityDisplay
                ]
            ],
            'text' => $priority
        ],
        'subject' => [
            'reference' => 'Patient/' . $id_ihs_pasien,
            'display' => $nama_pasien
        ],
        'participant' => $participant,
        'period' => [
            'start' => $periodStart
        ],
        'location' => [
            [
                'location' => [
                    'reference' => 'Location/' . $id_location,
                    'display' => $nama_poli
                ]
            ]
        ],
        'serviceProvider' => [
            'reference' => 'Organization/' . $organizationId
        ]
    ];

    // Reason / Keluhan
    if ($keluhan !== '') {
        $payload['reasonCode'] = [
            [
                'text' => $keluhan
            ]
        ];
    }

    // Convert Payload ke JSON
    $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($payloadJson === false) {
        responseJson('error', 'Gagal membuat payload Encounter.<br>Pesan : ' . json_last_error_msg());
    }

    // URL Encounter
    $url = $baseurl_satusehat . '/fhir-r4/v1/Encounter';

    // CURL Setup & Eksekusi
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => 0,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $payloadJson,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/fhir+json',
            'Accept: application/fhir+json'
        ]
    ]);

    $response = curl_exec($curl);

    // CURL Error Handling
    if (curl_errno($curl)) {
        $errorCurl = curl_error($curl);
        curl_close($curl);
        responseJson('error', 'CURL Error : ' . $errorCurl);
    }

    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    // Decode Response
    $responseData = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        responseJson('error', 'Response SATUSEHAT bukan JSON yang valid.', ['http_code' => $httpCode]);
    }

    // Validasi Operation Outcome & HTTP Code
    if (isset($responseData['resourceType']) && $responseData['resourceType'] === 'OperationOutcome') {
        $message = getFhirErrorMessage($responseData);
        responseJson('error', $message, ['http_code' => $httpCode]);
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        $message = getFhirErrorMessage($responseData);
        responseJson('error', $message, ['http_code' => $httpCode]);
    }

    // Validasi Resource Type
    if (($responseData['resourceType'] ?? '') !== 'Encounter') {
        responseJson('error', 'Response SATUSEHAT bukan resource Encounter.', ['http_code' => $httpCode]);
    }

    // Ambil ID Encounter SATUSEHAT
    $id_encounter = trim($responseData['id'] ?? '');
    if ($id_encounter === '') {
        responseJson('error', 'Encounter berhasil dikirim tetapi ID Encounter tidak ditemukan pada response SATUSEHAT.', ['http_code' => $httpCode]);
    }

    // Simpan ID Encounter ke Database
    $queryUpdate = "
        UPDATE kunjungan 
        SET id_encounter = ?, update_at = ?, update_by_id = ?, update_by_name = ?
        WHERE id_kunjungan = ? 
        AND (id_encounter IS NULL OR id_encounter = '')
    ";

    $now = date('Y-m-d H:i:s');
    $updateById = (int) $SessionIdAkses;
    $updateByName = $SessionNamaAkses ?? $SessionNama ?? 'System';

    $stmtUpdate = mysqli_prepare($Conn, $queryUpdate);
    if (!$stmtUpdate) {
        responseJson('error', 'Encounter berhasil dikirim ke SATUSEHAT tetapi gagal menyiapkan penyimpanan ID Encounter.', [
            'id_encounter' => $id_encounter,
            'http_code' => $httpCode
        ]);
    }

    mysqli_stmt_bind_param($stmtUpdate, 'ssisi', $id_encounter, $now, $updateById, $updateByName, $id_kunjungan);

    if (!mysqli_stmt_execute($stmtUpdate)) {
        $errorUpdate = mysqli_stmt_error($stmtUpdate);
        mysqli_stmt_close($stmtUpdate);
        responseJson('error', 'Encounter berhasil dikirim ke SATUSEHAT tetapi ID Encounter gagal disimpan ke database.<br>Pesan : ' . $errorUpdate, [
            'id_encounter' => $id_encounter,
            'http_code' => $httpCode
        ]);
    }

    $affectedRows = mysqli_stmt_affected_rows($stmtUpdate);
    mysqli_stmt_close($stmtUpdate);

    if ($affectedRows < 1) {
        responseJson('error', 'Encounter berhasil dikirim ke SATUSEHAT tetapi ID Encounter gagal diperbarui pada data kunjungan.', [
            'id_encounter' => $id_encounter,
            'http_code' => $httpCode
        ]);
    }

    // Response Success
    responseJson('success', 'Encounter berhasil dikirim ke SATUSEHAT.', [
        'id_kunjungan' => $id_kunjungan,
        'id_encounter' => $id_encounter,
        'http_code' => $httpCode
    ]);
?>