<?php
    // HEADER
    header('Content-Type: application/json; charset=utf-8');

    // INCLUDE
    include __DIR__ . "/../../_Config/Connection.php";
    include __DIR__ . "/../../_Config/GlobalFunction.php";
    include __DIR__ . "/../../_Config/Session.php";
    include __DIR__ . "/../../_Config/FungsiAkses.php";

    date_default_timezone_set('Asia/Jakarta');

    // FUNCTION RESPONSE
    function responsePoliklinikTambah(string $status, string $message, array $metadata = []): void {
        echo json_encode([
            'status'   => $status,
            'message'  => $message,
            'metadata' => $metadata
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // FUNCTION AMBIL PESAN ERROR FHIR
    function getFhirErrorMessage(array $data): string {
        if (($data['resourceType'] ?? '') === 'OperationOutcome' && !empty($data['issue']) && is_array($data['issue'])) {
            $messages = [];
            foreach ($data['issue'] as $issue) {
                $message = $issue['details']['text'] ?? $issue['diagnostics'] ?? $issue['code'] ?? '';
                if ($message !== '') {
                    $messages[] = $message;
                }
            }
            if (!empty($messages)) {
                return implode('<br>', $messages);
            }
        }
        return 'SATUSEHAT mengembalikan response yang tidak diketahui.';
    }

    // VALIDASI SESSION
    if (empty($SessionIdAkses)) {
        responsePoliklinikTambah('error', 'Sesi akses telah berakhir. Silakan login ulang.');
    }

    // VALIDASI METHOD
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responsePoliklinikTambah('error', 'Metode request tidak valid.');
    }

    // TANGKAP INPUT
    $polyclinicCode   = trim((string) ($_POST['polyclinicCode'] ?? ''));
    $polyclinicName   = trim((string) ($_POST['polyclinicName'] ?? ''));
    $polyclinicStatus = trim((string) ($_POST['polyclinicStatus'] ?? ''));
    $satuSehatCode    = trim((string) ($_POST['satuSehatCode'] ?? ''));

    // Checkbox. Jika dicentang browser akan mengirim value = 1.
    $creatIdLocation = (isset($_POST['creat_id_location']) && (string) $_POST['creat_id_location'] === '1');

    // VALIDASI INPUT
    if ($polyclinicCode === '') {
        responsePoliklinikTambah('error', 'Kode poliklinik tidak boleh kosong.');
    }
    if ($polyclinicName === '') {
        responsePoliklinikTambah('error', 'Nama poliklinik tidak boleh kosong.');
    }
    if ($polyclinicStatus === '') {
        responsePoliklinikTambah('error', 'Status poliklinik tidak boleh kosong.');
    }
    if (!in_array($polyclinicStatus, ['Active', 'Inactive'], true)) {
        responsePoliklinikTambah('error', 'Status poliklinik tidak valid.');
    }

    // VALIDASI FORMAT KODE POLIKLINIK
    if (!preg_match('/^PLY-[0-9]{8}$/', $polyclinicCode)) {
        responsePoliklinikTambah('error', 'Format kode poliklinik tidak valid. Contoh: PLY-12345678');
    }

    // CEK DUPLIKASI KODE POLIKLINIK
    $stmtDuplicate = $Conn->prepare("SELECT polyclinicId FROM polyclinic WHERE polyclinicCode = ? LIMIT 1");
    if ($stmtDuplicate === false) {
        responsePoliklinikTambah('error', 'Gagal mempersiapkan validasi kode poliklinik.');
    }
    $stmtDuplicate->bind_param('s', $polyclinicCode);
    if (!$stmtDuplicate->execute()) {
        $stmtDuplicate->close();
        responsePoliklinikTambah('error', 'Gagal memvalidasi kode poliklinik.');
    }
    $duplicateResult = $stmtDuplicate->get_result();
    $isDuplicate = ($duplicateResult !== false && $duplicateResult->num_rows > 0);
    $stmtDuplicate->close();

    if ($isDuplicate) {
        responsePoliklinikTambah('error', 'Kode poliklinik sudah terdaftar.');
    }

    // JIKA CHECKBOX BUAT LOCATION DIPILIH
    if ($creatIdLocation) {
        $satuSehatCode = '';

        // BUKA KONFIGURASI SATUSEHAT
        $stmtSetting = $Conn->prepare("SELECT url_connection_satu_sehat, organization_id FROM connection_satu_sehat WHERE status_connection_satu_sehat = 1 LIMIT 1");
        if ($stmtSetting === false) {
            responsePoliklinikTambah('error', 'Gagal membuka konfigurasi SATUSEHAT.');
        }
        if (!$stmtSetting->execute()) {
            $stmtSetting->close();
            responsePoliklinikTambah('error', 'Gagal membaca konfigurasi SATUSEHAT.');
        }
        $settingResult = $stmtSetting->get_result();
        $setting = $settingResult ? $settingResult->fetch_assoc() : null;
        $stmtSetting->close();

        // VALIDASI SETTING
        if (!$setting) {
            responsePoliklinikTambah('error', 'Konfigurasi SATUSEHAT aktif tidak ditemukan.');
        }

        $baseurlSatusehat = rtrim(trim((string) ($setting['url_connection_satu_sehat'] ?? '')), '/');
        $organizationIhs = trim((string) ($setting['organization_id'] ?? ''));

        if ($baseurlSatusehat === '') {
            responsePoliklinikTambah('error', 'URL koneksi SATUSEHAT tidak tersedia.');
        }
        if ($organizationIhs === '') {
            responsePoliklinikTambah('error', 'ID Organization SATUSEHAT belum dikonfigurasi.');
        }

        // GENERATE TOKEN SATUSEHAT
        $tokenResult = generateTokenSatuSehat($Conn);
        if (($tokenResult['status'] ?? 'error') !== 'success') {
            $messageToken = $tokenResult['message'] ?? 'Tidak diketahui';
            responsePoliklinikTambah('error', 'Gagal membuat token SATUSEHAT.<br>' . htmlspecialchars($messageToken, ENT_QUOTES, 'UTF-8'));
        }

        $token = trim((string) ($tokenResult['token'] ?? ''));
        if ($token === '') {
            responsePoliklinikTambah('error', 'Token SATUSEHAT tidak tersedia.');
        }

        // KONVERSI STATUS
        $locationStatus = ($polyclinicStatus === 'Active') ? 'active' : 'inactive';

        // PAYLOAD LOCATION
        $payloadLocation = [
            'resourceType' => 'Location',
            'identifier' => [
                [
                    'use' => 'official',
                    'system' => 'http://sys-ids.kemkes.go.id/location/' . $organizationIhs,
                    'value' => $polyclinicCode
                ]
            ],
            'status' => $locationStatus,
            'name' => $polyclinicName,
            'physicalType' => [
                'coding' => [
                    [
                        'system' => 'http://terminology.hl7.org/CodeSystem/location-physical-type',
                        'code'    => 'ro',
                        'display' => 'Room'
                    ]
                ]
            ],
            'managingOrganization' => [
                'reference' => 'Organization/' . $organizationIhs
            ]
        ];

        // JSON ENCODE PAYLOAD
        $payloadJson = json_encode($payloadLocation, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payloadJson === false) {
            responsePoliklinikTambah('error', 'Gagal membentuk payload Location SATUSEHAT.');
        }

        // URL SATUSEHAT
        $urlLocation = $baseurlSatusehat . '/fhir-r4/v1/Location';

        // CURL POST LOCATION
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $urlLocation,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $payloadJson,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
                'Accept: application/fhir+json'
            ],
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        // EXECUTE CURL
        $satusehatResponse = curl_exec($curl);

        // CURL ERROR
        if ($satusehatResponse === false) {
            $curlError = curl_error($curl);
            curl_close($curl);
            responsePoliklinikTambah('error', 'Gagal terhubung dengan SATUSEHAT.<br>' . htmlspecialchars($curlError, ENT_QUOTES, 'UTF-8'));
        }

        // HTTP CODE
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        // DECODE RESPONSE
        $satusehatData = json_decode($satusehatResponse, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($satusehatData)) {
            responsePoliklinikTambah('error', 'Response SATUSEHAT bukan JSON yang valid.');
        }

        // CEK OPERATION OUTCOME
        if (($satusehatData['resourceType'] ?? '') === 'OperationOutcome') {
            responsePoliklinikTambah('error', 'Gagal membuat Location SATUSEHAT.<br>' . getFhirErrorMessage($satusehatData));
        }

        // CEK HTTP STATUS
        if ($httpCode < 200 || $httpCode >= 300) {
            responsePoliklinikTambah('error', 'Gagal membuat Location SATUSEHAT.<br>HTTP Code : ' . $httpCode);
        }

        // VALIDASI RESOURCE TYPE
        if (($satusehatData['resourceType'] ?? '') !== 'Location') {
            responsePoliklinikTambah('error', 'Response SATUSEHAT bukan resource Location.');
        }

        // AMBIL ID LOCATION
        $locationId = trim((string) ($satusehatData['id'] ?? ''));
        if ($locationId === '') {
            responsePoliklinikTambah('error', 'Location berhasil dikirim tetapi ID Location tidak ditemukan pada response SATUSEHAT.');
        }

        $satuSehatCode = $locationId;
    }

    // NILAI NULL JIKA TIDAK ADA ID LOCATION
    $satuSehatValue = ($satuSehatCode === '') ? null : $satuSehatCode;

    // DATA AUDIT
    $now = date('Y-m-d H:i:s');
    $accessId = (int) $SessionIdAkses;
    $accessName = trim((string) ($SessionNama ?? ''));

    // MULAI TRANSACTION DATABASE
    $Conn->begin_transaction();

    try {
        // INSERT POLYCLINIC
        $stmtInsert = $Conn->prepare("
            INSERT INTO polyclinic (
                satuSehatCode,
                polyclinicCode,
                polyclinicName,
                polyclinicStatus,
                creat_at,
                creat_by_id,
                creat_by_name,
                update_at,
                update_by_id,
                update_by_name
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if ($stmtInsert === false) {
            throw new Exception('Gagal mempersiapkan penyimpanan poliklinik.');
        }

        $stmtInsert->bind_param(
            'sssssissis',
            $satuSehatValue,
            $polyclinicCode,
            $polyclinicName,
            $polyclinicStatus,
            $now,
            $accessId,
            $accessName,
            $now,
            $accessId,
            $accessName
        );

        if (!$stmtInsert->execute()) {
            $errorInsert = $stmtInsert->error;
            $stmtInsert->close();
            throw new Exception('Poliklinik gagal disimpan. ' . $errorInsert);
        }

        $polyclinicId = $stmtInsert->insert_id;
        $stmtInsert->close();

        // COMMIT
        $Conn->commit();

        // RESPONSE SUCCESS
        responsePoliklinikTambah(
            'success',
            $creatIdLocation ? 'Poliklinik dan Location SATUSEHAT berhasil dibuat.' : 'Poliklinik berhasil disimpan.',
            [
                'polyclinicId'    => $polyclinicId,
                'satuSehatCode'   => $satuSehatCode,
                'locationCreated' => $creatIdLocation
            ]
        );
    } catch (Throwable $e) {
        // ROLLBACK DATABASE
        $Conn->rollback();
        responsePoliklinikTambah('error', $e->getMessage());
    }
?>