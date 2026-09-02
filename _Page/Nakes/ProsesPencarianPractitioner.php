<?php
    header('Content-Type: application/json; charset=utf-8');

    // INCLUDE
    include __DIR__ . "/../../_Config/Connection.php";
    include __DIR__ . "/../../_Config/GlobalFunction.php";
    include __DIR__ . "/../../_Config/Session.php";

    date_default_timezone_set('Asia/Jakarta');

    // FUNCTION RESPONSE
    function responsePractitioner(string $status, string $message, string $html = ''): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'html'    => $html
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // FUNCTION AMBIL PESAN ERROR FHIR
    function getFhirErrorPractitioner(array $data): string {
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
        responsePractitioner('error', 'Sesi akses telah berakhir. Silakan login ulang.');
    }

    // VALIDASI METHOD
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responsePractitioner('error', 'Metode request tidak valid.');
    }

    // TANGKAP NIK
    $NikNakes = preg_replace('/\D/', '', trim((string)($_POST['NikNakes'] ?? '')));

    if ($NikNakes === '') {
        responsePractitioner('error', 'NIK tenaga kesehatan tidak boleh kosong.');
    }
    if (strlen($NikNakes) !== 16) {
        responsePractitioner('error', 'NIK tenaga kesehatan harus terdiri dari 16 digit.');
    }

    // BUKA KONFIGURASI SATUSEHAT
    $stmtSetting = $Conn->prepare("SELECT url_connection_satu_sehat FROM connection_satu_sehat WHERE status_connection_satu_sehat = 1 LIMIT 1");
    if ($stmtSetting === false) {
        responsePractitioner('error', 'Gagal membuka konfigurasi SATUSEHAT.');
    }

    if (!$stmtSetting->execute()) {
        $stmtSetting->close();
        responsePractitioner('error', 'Gagal membaca konfigurasi SATUSEHAT.');
    }

    $settingResult = $stmtSetting->get_result();
    $setting = $settingResult ? $settingResult->fetch_assoc() : null;
    $stmtSetting->close();

    if (!$setting) {
        responsePractitioner('error', 'Konfigurasi SATUSEHAT aktif tidak ditemukan.');
    }

    $baseurlSatusehat = rtrim(trim((string)($setting['url_connection_satu_sehat'] ?? '')), '/');
    if ($baseurlSatusehat === '') {
        responsePractitioner('error', 'URL koneksi SATUSEHAT tidak tersedia.');
    }

    // GENERATE TOKEN SATUSEHAT
    $tokenResult = generateTokenSatuSehat($Conn);
    if (($tokenResult['status'] ?? 'error') !== 'success') {
        $messageToken = $tokenResult['message'] ?? 'Tidak diketahui';
        responsePractitioner('error', 'Gagal membuat token SATUSEHAT.<br>'.htmlspecialchars($messageToken, ENT_QUOTES, 'UTF-8'));
    }

    $token = trim((string)($tokenResult['token'] ?? ''));
    if ($token === '') {
        responsePractitioner('error', 'Token SATUSEHAT tidak tersedia.');
    }

    // PARAMETER PENCARIAN NIK
    $identifier = 'https://fhir.kemkes.go.id/id/nik|'.$NikNakes;
    $urlPractitioner = $baseurlSatusehat.'/fhir-r4/v1/Practitioner?'.http_build_query([
        'identifier' => $identifier
    ]);

    // CURL GET PRACTITIONER
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => $urlPractitioner,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => false,
        CURLOPT_ENCODING       => '',
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CUSTOMREQUEST  => 'GET',
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer '.$token
        ],
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $satusehatResponse = curl_exec($curl);

    if ($satusehatResponse === false) {
        $curlError = curl_error($curl);
        curl_close($curl);
        responsePractitioner('error', 'Gagal terhubung dengan SATUSEHAT.<br>'.htmlspecialchars($curlError, ENT_QUOTES, 'UTF-8'));
    }

    $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    // DECODE RESPONSE
    $satusehatData = json_decode($satusehatResponse, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($satusehatData)) {
        responsePractitioner('error', 'Response SATUSEHAT bukan JSON yang valid.');
    }

    if (($satusehatData['resourceType'] ?? '') === 'OperationOutcome') {
        responsePractitioner('error', 'Pencarian Practitioner gagal.<br>'.getFhirErrorPractitioner($satusehatData));
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        responsePractitioner('error', 'Pencarian Practitioner gagal.<br>HTTP Code : '.$httpCode);
    }

    if (($satusehatData['resourceType'] ?? '') !== 'Bundle') {
        responsePractitioner('error', 'Response SATUSEHAT bukan resource Bundle.');
    }

    // CEK HASIL
    $entries = $satusehatData['entry'] ?? [];

    if (empty($entries) || !is_array($entries)) {
        responsePractitioner(
            'success',
            'Practitioner tidak ditemukan.',
            '
                <div class="alert alert-warning text-center mb-0">
                    <h1 class="bi bi-exclamation-circle"></h1>
                    <small>
                        Practitioner dengan NIK tersebut tidak ditemukan di SATUSEHAT.
                    </small>
                </div>
            '
        );
    }

    // BANGUN LIST PRACTITIONER
    $html = '<div class="list-group">';
    $jumlahData = 0;

    foreach ($entries as $entry) {
        $resource = $entry['resource'] ?? [];
        if (($resource['resourceType'] ?? '') !== 'Practitioner') {
            continue;
        }

        $idPractitioner = trim((string)($resource['id'] ?? ''));
        if ($idPractitioner === '') {
            continue;
        }

        // NAMA PRACTITIONER
        $namaPractitioner = '-';
        if (!empty($resource['name']) && is_array($resource['name'])) {
            foreach ($resource['name'] as $name) {
                if (!empty($name['text'])) {
                    $namaPractitioner = trim($name['text']);
                    break;
                }

                $nama = [];
                if (!empty($name['prefix']) && is_array($name['prefix'])) {
                    $nama = array_merge($nama, $name['prefix']);
                }
                if (!empty($name['given']) && is_array($name['given'])) {
                    $nama = array_merge($nama, $name['given']);
                }
                if (!empty($name['family'])) {
                    $nama[] = $name['family'];
                }

                if (!empty($nama)) {
                    $namaPractitioner = implode(' ', $nama);
                    break;
                }
            }
        }

        // NIK PRACTITIONER
        $nikPractitioner = '';
        if (!empty($resource['identifier']) && is_array($resource['identifier'])) {
            foreach ($resource['identifier'] as $identifier) {
                $system = trim((string)($identifier['system'] ?? ''));
                $value  = trim((string)($identifier['value'] ?? ''));

                if ($system === 'https://fhir.kemkes.go.id/id/nik' && $value !== '') {
                    $nikPractitioner = $value;
                    break;
                }
            }
        }

        // SENSOR NIK
        if ($nikPractitioner !== '') {
            $nikDisplay = strlen($nikPractitioner) > 3
                ? substr($nikPractitioner, 0, -3).'***'
                : $nikPractitioner;
        } else {
            $nikDisplay = '-';
        }

        // GENDER
        $genderRaw = strtolower(trim((string)($resource['gender'] ?? '')));
        if ($genderRaw === 'male') {
            $gender = 'Laki-laki';
        } elseif ($genderRaw === 'female') {
            $gender = 'Perempuan';
        } else {
            $gender = '-';
        }

        // TANGGAL LAHIR
        $birthDate = trim((string)($resource['birthDate'] ?? ''));
        if ($birthDate !== '') {
            $timestamp = strtotime($birthDate);
            $birthDate = $timestamp !== false ? date('d/m/Y', $timestamp) : $birthDate;
        } else {
            $birthDate = '-';
        }

        // STATUS
        $active = $resource['active'] ?? null;
        if ($active === true) {
            $status = 'Active';
        } elseif ($active === false) {
            $status = 'Inactive';
        } else {
            $status = '-';
        }

        // ESCAPE OUTPUT
        $idEsc        = htmlspecialchars($idPractitioner, ENT_QUOTES, 'UTF-8');
        $namaEsc      = htmlspecialchars($namaPractitioner, ENT_QUOTES, 'UTF-8');
        $nikEsc       = htmlspecialchars($nikDisplay, ENT_QUOTES, 'UTF-8');
        $genderEsc    = htmlspecialchars($gender, ENT_QUOTES, 'UTF-8');
        $birthDateEsc = htmlspecialchars($birthDate, ENT_QUOTES, 'UTF-8');
        $statusEsc    = htmlspecialchars($status, ENT_QUOTES, 'UTF-8');

        // LIST PRACTITIONER
        $html .= '
            <button type="button" class="list-group-item list-group-item-action PilihPractitioner" data-id="'.$idEsc.'" data-name="'.$namaEsc.'">
                <div class="d-flex w-100 justify-content-between">
                    <strong>'.$namaEsc.'</strong>
                    <small>'.$statusEsc.'</small>
                </div>
                <div class="mt-1">
                    <small class="text-muted"><b>ID :</b> '.$idEsc.'</small>
                </div>
                <div>
                    <small class="text-muted"><b>NIK :</b> '.$nikEsc.'</small>
                </div>
                <div>
                    <small class="text-muted"><b>Jenis Kelamin :</b> '.$genderEsc.'</small>
                </div>
                <div>
                    <small class="text-muted"><b>Tanggal Lahir :</b> '.$birthDateEsc.'</small>
                </div>
            </button>
        ';

        $jumlahData++;
    }

    $html .= '</div>';

    // JIKA TIDAK ADA PRACTITIONER VALID
    if ($jumlahData === 0) {
        $html = '
            <div class="alert alert-warning text-center">
                <h1 class="bi bi-exclamation-circle"></h1>
                <small>Data Practitioner tidak ditemukan.</small>
            </div>
        ';
    }

    // RESPONSE SUCCESS
    responsePractitioner(
        'success',
        $jumlahData.' Practitioner ditemukan.',
        $html
    );

    responsePractitioner('success', 'Pencarian Practitioner berhasil.', $html);
?>