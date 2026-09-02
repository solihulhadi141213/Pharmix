<?php
    header('Content-Type: application/json; charset=utf-8');

    // INCLUDE
    include __DIR__ . "/../../_Config/Connection.php";
    include __DIR__ . "/../../_Config/GlobalFunction.php";
    include __DIR__ . "/../../_Config/Session.php";

    date_default_timezone_set('Asia/Jakarta');

    // FUNCTION RESPONSE
    function responsePractitionerDetail(string $status, string $message, string $html = ''): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'html'    => $html
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // FUNCTION VALUE
    function practitionerValue($value): string {
        if ($value === null || trim((string)$value) === '') {
            return '-';
        }
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    // FUNCTION ERROR FHIR
    function practitionerFhirError(array $data): string {
        if (($data['resourceType'] ?? '') !== 'OperationOutcome' || empty($data['issue'])) {
            return 'SATUSEHAT mengembalikan response yang tidak diketahui.';
        }

        $messages = [];
        foreach ($data['issue'] as $issue) {
            $message = $issue['details']['text'] ?? $issue['diagnostics'] ?? $issue['code'] ?? '';
            if ($message !== '') {
                $messages[] = $message;
            }
        }

        return !empty($messages)
            ? implode('<br>', array_map(function($item) {
                return htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
            }, $messages))
            : 'Terjadi kesalahan pada SATUSEHAT.';
    }

    // VALIDASI SESSION
    if (empty($SessionIdAkses)) {
        responsePractitionerDetail('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
    }

    // VALIDASI METHOD
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responsePractitionerDetail('error', 'Metode request tidak valid.');
    }

    // TANGKAP ID PRACTITIONER
    $idPractitioner = trim((string)($_POST['id_practitioner'] ?? ''));

    if ($idPractitioner === '') {
        responsePractitionerDetail('error', 'ID Practitioner tidak boleh kosong.');
    }

    // VALIDASI KARAKTER ID FHIR
    if (!preg_match('/^[A-Za-z0-9\-\.]{1,64}$/', $idPractitioner)) {
        responsePractitionerDetail('error', 'Format ID Practitioner tidak valid.');
    }

    // BUKA KONFIGURASI SATUSEHAT
    $stmtSetting = $Conn->prepare("
        SELECT url_connection_satu_sehat
        FROM connection_satu_sehat
        WHERE status_connection_satu_sehat = 1
        LIMIT 1
    ");

    if ($stmtSetting === false) {
        responsePractitionerDetail('error', 'Gagal membuka konfigurasi SATUSEHAT.');
    }

    if (!$stmtSetting->execute()) {
        $stmtSetting->close();
        responsePractitionerDetail('error', 'Gagal membaca konfigurasi SATUSEHAT.');
    }

    $resultSetting = $stmtSetting->get_result();
    $setting = $resultSetting ? $resultSetting->fetch_assoc() : null;
    $stmtSetting->close();

    if (!$setting) {
        responsePractitionerDetail('error', 'Konfigurasi SATUSEHAT aktif tidak ditemukan.');
    }

    $baseurlSatusehat = rtrim(trim((string)($setting['url_connection_satu_sehat'] ?? '')), '/');

    if ($baseurlSatusehat === '') {
        responsePractitionerDetail('error', 'URL koneksi SATUSEHAT tidak tersedia.');
    }

    // GENERATE TOKEN SATUSEHAT
    $tokenResult = generateTokenSatuSehat($Conn);

    if (($tokenResult['status'] ?? 'error') !== 'success') {
        $messageToken = $tokenResult['message'] ?? 'Tidak diketahui';
        responsePractitionerDetail(
            'error',
            'Gagal membuat token SATUSEHAT.<br>Pesan : '.htmlspecialchars($messageToken, ENT_QUOTES, 'UTF-8')
        );
    }

    $token = trim((string)($tokenResult['token'] ?? ''));

    if ($token === '') {
        responsePractitionerDetail('error', 'Token SATUSEHAT tidak tersedia.');
    }

    // URL DETAIL PRACTITIONER
    $urlPractitioner = $baseurlSatusehat.'/fhir-r4/v1/Practitioner/'.rawurlencode($idPractitioner);

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
            'Authorization: Bearer '.$token,
            'Accept: application/fhir+json'
        ],
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $curlResponse = curl_exec($curl);

    // CURL ERROR
    if ($curlResponse === false) {
        $curlError = curl_error($curl);
        curl_close($curl);

        responsePractitionerDetail(
            'error',
            'Terjadi kesalahan koneksi ke SATUSEHAT.<br>CURL Error : '.htmlspecialchars($curlError, ENT_QUOTES, 'UTF-8')
        );
    }

    $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    // DECODE RESPONSE
    $data = json_decode($curlResponse, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        responsePractitionerDetail('error', 'Response SATUSEHAT bukan JSON yang valid.');
    }

    // VALIDASI OPERATION OUTCOME
    if (($data['resourceType'] ?? '') === 'OperationOutcome') {
        responsePractitionerDetail(
            'error',
            'Terjadi kesalahan dari SATUSEHAT.<br>'.practitionerFhirError($data)
        );
    }

    // VALIDASI HTTP CODE
    if ($httpCode < 200 || $httpCode >= 300) {
        responsePractitionerDetail('error', 'SATUSEHAT mengembalikan HTTP Code '.$httpCode.'.');
    }

    // VALIDASI RESOURCE
    if (($data['resourceType'] ?? '') !== 'Practitioner') {
        responsePractitionerDetail('error', 'Format response Practitioner SATUSEHAT tidak sesuai.');
    }

    // ID PRACTITIONER
    $practitionerId = trim((string)($data['id'] ?? $idPractitioner));

    // STATUS
    $active = $data['active'] ?? null;
    if ($active === true) {
        $statusBadge = '<span class="badge bg-success">Active</span>';
    } elseif ($active === false) {
        $statusBadge = '<span class="badge bg-danger">Inactive</span>';
    } else {
        $statusBadge = '<span class="badge bg-secondary">-</span>';
    }

    // NAMA
    $namaPractitioner = '-';

    if (!empty($data['name']) && is_array($data['name'])) {
        foreach ($data['name'] as $name) {
            if (!empty($name['text'])) {
                $namaPractitioner = trim((string)$name['text']);
                break;
            }

            $parts = [];

            if (!empty($name['prefix']) && is_array($name['prefix'])) {
                $parts = array_merge($parts, $name['prefix']);
            }

            if (!empty($name['given']) && is_array($name['given'])) {
                $parts = array_merge($parts, $name['given']);
            }

            if (!empty($name['family'])) {
                $parts[] = $name['family'];
            }

            if (!empty($parts)) {
                $namaPractitioner = implode(' ', $parts);
                break;
            }
        }
    }

    // IDENTIFIER / NIK
    $nik = '-';
    $identifierHtml = '';

    if (!empty($data['identifier']) && is_array($data['identifier'])) {
        foreach ($data['identifier'] as $identifier) {
            $system = trim((string)($identifier['system'] ?? ''));
            $value  = trim((string)($identifier['value'] ?? ''));

            if ($system === 'https://fhir.kemkes.go.id/id/nik' && $value !== '') {
                $nik = strlen($value) > 3 ? substr($value, 0, -3).'***' : $value;
            }

            if ($value !== '') {
                $identifierHtml .= '
                    <div class="mb-2">
                        <small class="text-muted d-block">'.practitionerValue($system).'</small>
                        <small>'.practitionerValue($value).'</small>
                    </div>
                ';
            }
        }
    }

    if ($identifierHtml === '') {
        $identifierHtml = '<small class="text-muted">-</small>';
    }

    // GENDER
    $genderRaw = strtolower(trim((string)($data['gender'] ?? '')));

    if ($genderRaw === 'male') {
        $gender = 'Laki-laki';
    } elseif ($genderRaw === 'female') {
        $gender = 'Perempuan';
    } else {
        $gender = '-';
    }

    // TANGGAL LAHIR
    $birthDate = trim((string)($data['birthDate'] ?? ''));

    if ($birthDate !== '') {
        $timestamp = strtotime($birthDate);
        $birthDate = $timestamp !== false ? date('d/m/Y', $timestamp) : $birthDate;
    } else {
        $birthDate = '-';
    }

    // TELECOM
    $telecomHtml = '';

    if (!empty($data['telecom']) && is_array($data['telecom'])) {
        foreach ($data['telecom'] as $telecom) {
            $system = trim((string)($telecom['system'] ?? ''));
            $value  = trim((string)($telecom['value'] ?? ''));
            $use    = trim((string)($telecom['use'] ?? ''));

            if ($value === '') {
                continue;
            }

            if ($system === 'phone') {
                $label = 'Telepon';
            } elseif ($system === 'email') {
                $label = 'Email';
            } elseif ($system === 'fax') {
                $label = 'Fax';
            } else {
                $label = ucfirst($system);
            }

            if ($use !== '') {
                $label .= ' ('.$use.')';
            }

            $telecomHtml .= '
                <div class="row mb-2">
                    <div class="col-5"><small>'.practitionerValue($label).'</small></div>
                    <div class="col-1"><small>:</small></div>
                    <div class="col-6"><small class="text-muted">'.practitionerValue($value).'</small></div>
                </div>
            ';
        }
    }

    if ($telecomHtml === '') {
        $telecomHtml = '
            <div class="row mb-2">
                <div class="col-5"><small>Kontak</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6"><small class="text-muted">-</small></div>
            </div>
        ';
    }

    // ADDRESS
    $addressHtml = '';

    if (!empty($data['address']) && is_array($data['address'])) {
        foreach ($data['address'] as $address) {
            $addressParts = [];

            if (!empty($address['line']) && is_array($address['line'])) {
                foreach ($address['line'] as $line) {
                    if (trim((string)$line) !== '') {
                        $addressParts[] = trim((string)$line);
                    }
                }
            }

            foreach (['city', 'district', 'state', 'postalCode', 'country'] as $field) {
                if (!empty($address[$field])) {
                    $addressParts[] = trim((string)$address[$field]);
                }
            }

            if (!empty($addressParts)) {
                $addressHtml .= '
                    <div class="mb-2">
                        <small class="text-muted">'.practitionerValue(implode(', ', $addressParts)).'</small>
                    </div>
                ';
            }
        }
    }

    if ($addressHtml === '') {
        $addressHtml = '<small class="text-muted">-</small>';
    }

    // QUALIFICATION
    $qualificationHtml = '';

    if (!empty($data['qualification']) && is_array($data['qualification'])) {
        foreach ($data['qualification'] as $qualification) {
            $qualificationName = '';

            if (!empty($qualification['code']['text'])) {
                $qualificationName = trim((string)$qualification['code']['text']);
            } elseif (!empty($qualification['code']['coding']) && is_array($qualification['code']['coding'])) {
                foreach ($qualification['code']['coding'] as $coding) {
                    if (!empty($coding['display'])) {
                        $qualificationName = trim((string)$coding['display']);
                        break;
                    }
                    if (!empty($coding['code'])) {
                        $qualificationName = trim((string)$coding['code']);
                        break;
                    }
                }
            }

            $issuer = trim((string)($qualification['issuer']['display'] ?? $qualification['issuer']['reference'] ?? ''));

            if ($qualificationName !== '') {
                $qualificationHtml .= '
                    <div class="mb-2">
                        <small><b>'.practitionerValue($qualificationName).'</b></small>
                ';

                if ($issuer !== '') {
                    $qualificationHtml .= '
                        <br>
                        <small class="text-muted">Penerbit : '.practitionerValue($issuer).'</small>
                    ';
                }

                $qualificationHtml .= '</div>';
            }
        }
    }

    if ($qualificationHtml === '') {
        $qualificationHtml = '<small class="text-muted">-</small>';
    }

    // META
    $versionId   = $data['meta']['versionId'] ?? '-';
    $lastUpdated = $data['meta']['lastUpdated'] ?? '-';

    if ($lastUpdated !== '-') {
        $timestamp = strtotime($lastUpdated);
        if ($timestamp !== false) {
            $lastUpdated = date('d/m/Y H:i:s', $timestamp);
        }
    }

    // GENERATE HTML
    $html = '
        <div class="container-fluid px-0">
            <div class="row mb-2">
                <div class="col-5"><small>ID Practitioner</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6"><small class="text-muted text-break">'.practitionerValue($practitionerId).'</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><small>Nama</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6"><small class="text-muted">'.practitionerValue($namaPractitioner).'</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><small>NIK</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6"><small class="text-muted">'.practitionerValue($nik).'</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><small>Last Updated</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6"><small class="text-muted">'.practitionerValue($lastUpdated).'</small></div>
            </div>
        </div>
    ';

    responsePractitionerDetail(
        'success',
        'Detail Practitioner berhasil dimuat.',
        $html
    );
?>