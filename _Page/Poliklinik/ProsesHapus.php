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
    function responsePoliklinikHapus(string $status, string $message, array $metadata = []): void {
        echo json_encode([
            'status'   => $status,
            'message'  => $message,
            'metadata' => $metadata
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // FUNCTION AMBIL PESAN ERROR FHIR
    function getFhirErrorMessageHapus(array $data): string {
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
        responsePoliklinikHapus('error', 'Sesi akses telah berakhir. Silakan login ulang.');
    }

    // VALIDASI METHOD
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responsePoliklinikHapus('error', 'Metode request tidak valid.');
    }

    // VALIDASI POLYCLINIC ID
    $polyclinicId = filter_var(
        $_POST['polyclinicId'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($polyclinicId === false || $polyclinicId === null) {
        responsePoliklinikHapus('error', 'ID poliklinik tidak valid.');
    }

    // BUKA DATA POLIKLINIK
    $stmtPolyclinic = $Conn->prepare("SELECT polyclinicId, satuSehatCode, polyclinicCode, polyclinicName, polyclinicStatus FROM polyclinic WHERE polyclinicId = ? LIMIT 1");
    if ($stmtPolyclinic === false) {
        responsePoliklinikHapus('error', 'Gagal mempersiapkan data poliklinik.');
    }
    $stmtPolyclinic->bind_param('i', $polyclinicId);
    if (!$stmtPolyclinic->execute()) {
        $stmtPolyclinic->close();
        responsePoliklinikHapus('error', 'Gagal membuka data poliklinik.');
    }
    $resultPolyclinic = $stmtPolyclinic->get_result();
    $dataPolyclinic = $resultPolyclinic ? $resultPolyclinic->fetch_assoc() : null;
    $stmtPolyclinic->close();

    // VALIDASI DATA
    if (!$dataPolyclinic) {
        responsePoliklinikHapus('error', 'Data poliklinik tidak ditemukan.');
    }

    // VARIABLE
    $satuSehatCode  = trim((string) ($dataPolyclinic['satuSehatCode'] ?? ''));
    $polyclinicCode = trim((string) ($dataPolyclinic['polyclinicCode'] ?? ''));
    $polyclinicName = trim((string) ($dataPolyclinic['polyclinicName'] ?? ''));

    // STATUS PROSES SATUSEHAT
    $satusehatUpdated = false;

    // JIKA MEMILIKI ID LOCATION SATUSEHAT
    if ($satuSehatCode !== '') {
        // BUKA KONFIGURASI SATUSEHAT
        $stmtSetting = $Conn->prepare("SELECT url_connection_satu_sehat, organization_id FROM connection_satu_sehat WHERE status_connection_satu_sehat = 1 LIMIT 1");
        if ($stmtSetting === false) {
            responsePoliklinikHapus('error', 'Gagal membuka konfigurasi SATUSEHAT.');
        }
        if (!$stmtSetting->execute()) {
            $stmtSetting->close();
            responsePoliklinikHapus('error', 'Gagal membaca konfigurasi SATUSEHAT.');
        }
        $settingResult = $stmtSetting->get_result();
        $setting = $settingResult ? $settingResult->fetch_assoc() : null;
        $stmtSetting->close();

        // VALIDASI SETTING
        if (!$setting) {
            responsePoliklinikHapus('error', 'Konfigurasi SATUSEHAT aktif tidak ditemukan.');
        }

        $baseurlSatusehat = rtrim(trim((string) ($setting['url_connection_satu_sehat'] ?? '')), '/');
        if ($baseurlSatusehat === '') {
            responsePoliklinikHapus('error', 'URL koneksi SATUSEHAT tidak tersedia.');
        }

        // GENERATE TOKEN SATUSEHAT
        $tokenResult = generateTokenSatuSehat($Conn);
        if (($tokenResult['status'] ?? 'error') !== 'success') {
            $messageToken = $tokenResult['message'] ?? 'Tidak diketahui';
            responsePoliklinikHapus('error', 'Gagal membuat token SATUSEHAT.<br>' . htmlspecialchars($messageToken, ENT_QUOTES, 'UTF-8'));
        }

        $token = trim((string) ($tokenResult['token'] ?? ''));
        if ($token === '') {
            responsePoliklinikHapus('error', 'Token SATUSEHAT tidak tersedia.');
        }

        // GET DETAIL LOCATION SATUSEHAT
        $urlLocation = $baseurlSatusehat . '/fhir-r4/v1/Location/' . rawurlencode($satuSehatCode);

        // CURL GET LOCATION
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $urlLocation,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token
            ],
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $getResponse = curl_exec($curl);

        if ($getResponse === false) {
            $curlError = curl_error($curl);
            curl_close($curl);
            responsePoliklinikHapus('error', 'Gagal mengambil Location SATUSEHAT.<br>' . htmlspecialchars($curlError, ENT_QUOTES, 'UTF-8'));
        }

        $getHttpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $locationData = json_decode($getResponse, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($locationData)) {
            responsePoliklinikHapus('error', 'Response detail Location SATUSEHAT bukan JSON yang valid.');
        }

        if (($locationData['resourceType'] ?? '') === 'OperationOutcome') {
            responsePoliklinikHapus('error', 'Gagal mengambil Location SATUSEHAT.<br>' . getFhirErrorMessageHapus($locationData));
        }

        if ($getHttpCode < 200 || $getHttpCode >= 300) {
            responsePoliklinikHapus('error', 'Gagal mengambil Location SATUSEHAT.<br>HTTP Code : ' . $getHttpCode);
        }

        if (($locationData['resourceType'] ?? '') !== 'Location') {
            responsePoliklinikHapus('error', 'Resource SATUSEHAT yang diterima bukan Location.');
        }

        $responseLocationId = trim((string) ($locationData['id'] ?? ''));
        if ($responseLocationId === '') {
            responsePoliklinikHapus('error', 'ID Location tidak ditemukan pada response SATUSEHAT.');
        }

        if ($responseLocationId !== $satuSehatCode) {
            responsePoliklinikHapus('error', 'ID Location SATUSEHAT tidak sesuai.');
        }

        $currentStatus = trim((string) ($locationData['status'] ?? ''));

        // JIKA SUDAH INACTIVE TIDAK PERLU UPDATE
        if ($currentStatus === 'inactive') {
            $satusehatUpdated = true;
        } else {
            // UBAH STATUS MENJADI INACTIVE
            $locationData['status'] = 'inactive';

            if (isset($locationData['meta'])) {
                unset($locationData['meta']);
            }

            $payloadJson = json_encode($locationData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($payloadJson === false) {
                responsePoliklinikHapus('error', 'Gagal membentuk payload Location SATUSEHAT.');
            }

            // CURL PUT
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL            => $urlLocation,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER         => false,
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CUSTOMREQUEST  => 'PUT',
                CURLOPT_POSTFIELDS     => $payloadJson,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $token,
                    'Content-Type: application/json'
                ],
                CURLOPT_SSL_VERIFYPEER => true
            ]);

            $satusehatResponse = curl_exec($curl);

            if ($satusehatResponse === false) {
                $curlError = curl_error($curl);
                curl_close($curl);
                responsePoliklinikHapus('error', 'Gagal menonaktifkan Location SATUSEHAT.<br>' . htmlspecialchars($curlError, ENT_QUOTES, 'UTF-8'));
            }

            $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            $satusehatData = json_decode($satusehatResponse, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($satusehatData)) {
                responsePoliklinikHapus('error', 'Response SATUSEHAT bukan JSON yang valid.');
            }

            if (($satusehatData['resourceType'] ?? '') === 'OperationOutcome') {
                responsePoliklinikHapus('error', 'Gagal menonaktifkan Location SATUSEHAT.<br>HTTP Code : ' . $httpCode . '<br>' . getFhirErrorMessageHapus($satusehatData));
            }

            if ($httpCode < 200 || $httpCode >= 300) {
                responsePoliklinikHapus('error', 'Gagal menonaktifkan Location SATUSEHAT.<br>HTTP Code : ' . $httpCode);
            }

            if (($satusehatData['resourceType'] ?? '') !== 'Location') {
                responsePoliklinikHapus('error', 'Response SATUSEHAT bukan resource Location.');
            }

            $responseLocationId = trim((string) ($satusehatData['id'] ?? ''));
            if ($responseLocationId !== $satuSehatCode) {
                responsePoliklinikHapus('error', 'ID Location response SATUSEHAT tidak sesuai.');
            }

            $responseStatus = trim((string) ($satusehatData['status'] ?? ''));
            if ($responseStatus !== 'inactive') {
                responsePoliklinikHapus('error', 'Location SATUSEHAT belum berhasil dinonaktifkan.');
            }

            $satusehatUpdated = true;
        }
    }

    // MULAI TRANSACTION DATABASE
    $Conn->begin_transaction();

    try {
        // DELETE POLYCLINIC
        $stmtDelete = $Conn->prepare("DELETE FROM polyclinic WHERE polyclinicId = ? LIMIT 1");
        if ($stmtDelete === false) {
            throw new Exception('Gagal mempersiapkan penghapusan poliklinik.');
        }

        $stmtDelete->bind_param('i', $polyclinicId);
        if (!$stmtDelete->execute()) {
            $errorDelete = $stmtDelete->error;
            $stmtDelete->close();
            throw new Exception('Poliklinik gagal dihapus. ' . $errorDelete);
        }

        if ($stmtDelete->affected_rows < 1) {
            $stmtDelete->close();
            throw new Exception('Data poliklinik tidak ditemukan atau sudah dihapus.');
        }

        $stmtDelete->close();

        // COMMIT
        $Conn->commit();

        $message = $satusehatUpdated ? 'Poliklinik berhasil dihapus dan Location SATUSEHAT berhasil dinonaktifkan.' : 'Poliklinik berhasil dihapus.';

        // RESPONSE SUCCESS
        responsePoliklinikHapus(
            'success',
            $message,
            [
                'polyclinicId'     => $polyclinicId,
                'polyclinicCode'   => $polyclinicCode,
                'polyclinicName'   => $polyclinicName,
                'satuSehatCode'    => $satuSehatCode,
                'satusehatUpdated' => $satusehatUpdated
            ]
        );
    } catch (Throwable $e) {
        // ROLLBACK DATABASE
        $Conn->rollback();
        responsePoliklinikHapus('error', $e->getMessage());
    }
?>