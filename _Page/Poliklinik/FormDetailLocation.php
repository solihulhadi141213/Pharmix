<?php
    // HEADER JSON
    header('Content-Type: application/json; charset=utf-8');

    // INCLUDE
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // DEFAULT RESPONSE
    $response = [
        "status"  => "error",
        "message" => "Terjadi kesalahan.",
        "html"    => ""
    ];

    // VALIDASI SESSION
    if (empty($SessionIdAkses)) {
        $response["message"] = "Sesi akses sudah berakhir. Silakan login ulang.";
        echo json_encode($response);
        exit;
    }

    // VALIDASI METHOD
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response["message"] = "Metode request tidak valid.";
        echo json_encode($response);
        exit;
    }

    // VALIDASI ID LOCATION
    $satuSehatCode = trim($_POST['satuSehatCode'] ?? '');
    if ($satuSehatCode === '') {
        $response["message"] = "ID Location tidak boleh kosong.";
        echo json_encode($response);
        exit;
    }

    /*
     * ID resource SATUSEHAT pada umumnya berupa UUID.
     * Hindari karakter yang tidak semestinya masuk ke URL.
     */
    if (!preg_match('/^[A-Za-z0-9\-\.]+$/', $satuSehatCode)) {
        $response["message"] = "Format ID Location tidak valid.";
        echo json_encode($response);
        exit;
    }

    // BUKA KONFIGURASI SATUSEHAT
    $stmt = mysqli_prepare(
        $Conn,
        "SELECT url_connection_satu_sehat FROM connection_satu_sehat WHERE status_connection_satu_sehat = 1 LIMIT 1"
    );
    if (!$stmt) {
        $response["message"] = "Gagal membuka konfigurasi SATUSEHAT.";
        echo json_encode($response);
        exit;
    }
    mysqli_stmt_execute($stmt);
    $result  = mysqli_stmt_get_result($stmt);
    $setting = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // VALIDASI URL
    $baseurlSatusehat = rtrim(trim($setting['url_connection_satu_sehat'] ?? ''), '/');
    if ($baseurlSatusehat === '') {
        $response["message"] = "URL koneksi SATUSEHAT tidak tersedia.";
        echo json_encode($response);
        exit;
    }

    // GENERATE TOKEN
    $tokenResult = generateTokenSatuSehat($Conn);
    if (($tokenResult['status'] ?? 'error') !== 'success') {
        $messageToken = $tokenResult['message'] ?? 'Tidak diketahui';
        $response["message"] = "Gagal membuat token SATUSEHAT.<br>" . htmlspecialchars($messageToken);
        echo json_encode($response);
        exit;
    }

    $token = trim($tokenResult['token'] ?? '');
    if ($token === '') {
        $response["message"] = "Token SATUSEHAT tidak tersedia.";
        echo json_encode($response);
        exit;
    }

    // URL DETAIL LOCATION
    $url = $baseurlSatusehat . '/fhir-r4/v1/Location/' . rawurlencode($satuSehatCode);

    // CURL
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => false,
        CURLOPT_ENCODING       => '',
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CUSTOMREQUEST  => 'GET',
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Accept: application/fhir+json'
        ],
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    $curlResponse = curl_exec($curl);

    // CURL ERROR
    if ($curlResponse === false) {
        $curlError = curl_error($curl);
        curl_close($curl);
        $response["message"] = "Gagal terhubung dengan SATUSEHAT.<br>" . htmlspecialchars($curlError);
        echo json_encode($response);
        exit;
    }

    // HTTP CODE
    $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    // DECODE RESPONSE
    $data = json_decode($curlResponse, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        $response["message"] = "Response SATUSEHAT bukan JSON yang valid.";
        echo json_encode($response);
        exit;
    }

    // OPERATION OUTCOME
    if (($data['resourceType'] ?? '') === 'OperationOutcome') {
        $errorMessage = "Terjadi kesalahan dari SATUSEHAT.";
        if (!empty($data['issue']) && is_array($data['issue'])) {
            $messages = [];
            foreach ($data['issue'] as $issue) {
                $msg = $issue['details']['text'] ?? $issue['diagnostics'] ?? '';
                if ($msg !== '') {
                    $messages[] = $msg;
                }
            }
            if (!empty($messages)) {
                $errorMessage .= "<br>" . implode("<br>", array_map('htmlspecialchars', $messages));
            }
        }
        $response["message"] = $errorMessage;
        echo json_encode($response);
        exit;
    }

    // VALIDASI HTTP CODE
    if ($httpCode < 200 || $httpCode >= 300) {
        $response["message"] = "SATUSEHAT mengembalikan HTTP Code " . $httpCode;
        echo json_encode($response);
        exit;
    }

    // VALIDASI RESOURCE
    if (($data['resourceType'] ?? '') !== 'Location') {
        $response["message"] = "Resource yang diterima bukan Location.";
        echo json_encode($response);
        exit;
    }

    // AMBIL DATA DASAR
    $id          = trim($data['id'] ?? '');
    $name        = trim($data['name'] ?? '');
    $status      = trim($data['status'] ?? '');
    $description = trim($data['description'] ?? '');
    $mode        = trim($data['mode'] ?? '');

    // IDENTIFIER
    $identifierUse    = '';
    $identifierSystem = '';
    $identifierValue  = '';
    if (!empty($data['identifier']) && is_array($data['identifier'])) {
        $identifier = $data['identifier'][0] ?? [];
        $identifierUse    = trim($identifier['use'] ?? '');
        $identifierSystem = trim($identifier['system'] ?? '');
        $identifierValue  = trim($identifier['value'] ?? '');
    }

    // ALIAS
    $aliases = [];
    if (!empty($data['alias']) && is_array($data['alias'])) {
        foreach ($data['alias'] as $alias) {
            $alias = trim((string) $alias);
            if ($alias !== '') {
                $aliases[] = $alias;
            }
        }
    }
    $aliasText = !empty($aliases) ? implode(', ', $aliases) : '-';

    // PHYSICAL TYPE
    $physicalTypeCode   = '';
    $physicalTypeDisplay = '';
    if (!empty($data['physicalType']['coding']) && is_array($data['physicalType']['coding'])) {
        $physicalCoding      = $data['physicalType']['coding'][0] ?? [];
        $physicalTypeCode    = trim($physicalCoding['code'] ?? '');
        $physicalTypeDisplay = trim($physicalCoding['display'] ?? '');
    }

    // LOCATION TYPE
    $locationTypes = [];
    if (!empty($data['type']) && is_array($data['type'])) {
        foreach ($data['type'] as $type) {
            if (empty($type['coding']) || !is_array($type['coding'])) {
                continue;
            }
            foreach ($type['coding'] as $coding) {
                $code    = trim($coding['code'] ?? '');
                $display = trim($coding['display'] ?? '');
                if ($code !== '' || $display !== '') {
                    $locationTypes[] = [
                        'code'    => $code,
                        'display' => $display
                    ];
                }
            }
        }
    }

    // MANAGING ORGANIZATION
    $organizationReference = trim($data['managingOrganization']['reference'] ?? '');
    $organizationDisplay   = trim($data['managingOrganization']['display'] ?? '');

    // PART OF
    $partOfReference = trim($data['partOf']['reference'] ?? '');
    $partOfDisplay   = trim($data['partOf']['display'] ?? '');

    // ADDRESS
    $addressParts = [];
    if (!empty($data['address']['line']) && is_array($data['address']['line'])) {
        foreach ($data['address']['line'] as $line) {
            $line = trim((string) $line);
            if ($line !== '') {
                $addressParts[] = $line;
            }
        }
    }
    if (!empty($data['address']['city'])) {
        $addressParts[] = trim($data['address']['city']);
    }
    if (!empty($data['address']['district'])) {
        $addressParts[] = trim($data['address']['district']);
    }
    if (!empty($data['address']['state'])) {
        $addressParts[] = trim($data['address']['state']);
    }
    if (!empty($data['address']['postalCode'])) {
        $addressParts[] = trim($data['address']['postalCode']);
    }
    if (!empty($data['address']['country'])) {
        $addressParts[] = trim($data['address']['country']);
    }
    $addressText = !empty($addressParts) ? implode(', ', array_filter($addressParts)) : '-';

    // TELECOM
    $telecomHtml = '';
    if (!empty($data['telecom']) && is_array($data['telecom'])) {
        foreach ($data['telecom'] as $telecom) {
            $system = trim($telecom['system'] ?? '');
            $value  = trim($telecom['value'] ?? '');
            $use    = trim($telecom['use'] ?? '');
            if ($value === '') {
                continue;
            }
            $telecomHtml .= '
                <div>
                    <small>
                        ' . htmlspecialchars(ucfirst($system)) . ' : ' . htmlspecialchars($value) . '
                        ' . ($use !== '' ? '(' . htmlspecialchars($use) . ')' : '') . '
                    </small>
                </div>
            ';
        }
    }
    if ($telecomHtml === '') {
        $telecomHtml = '<small>-</small>';
    }

    // HOURS OF OPERATION
    $hoursHtml = '';
    if (!empty($data['hoursOfOperation']) && is_array($data['hoursOfOperation'])) {
        foreach ($data['hoursOfOperation'] as $hour) {
            $days = '-';
            if (!empty($hour['daysOfWeek']) && is_array($hour['daysOfWeek'])) {
                $days = implode(', ', $hour['daysOfWeek']);
            }
            $allDay = isset($hour['allDay']) ? ($hour['allDay'] ? 'Ya' : 'Tidak') : '-';
            $openingTime = trim($hour['openingTime'] ?? '');
            $closingTime = trim($hour['closingTime'] ?? '');

            $hoursHtml .= '
                <div class="mb-1">
                    <small>
                        <b>' . htmlspecialchars($days) . '</b><br>
            ';
            if ($allDay === 'Ya') {
                $hoursHtml .= '24 Jam';
            } else {
                if ($openingTime !== '' || $closingTime !== '') {
                    $hoursHtml .= htmlspecialchars($openingTime) . ' - ' . htmlspecialchars($closingTime);
                } else {
                    $hoursHtml .= '-';
                }
            }
            $hoursHtml .= '
                    </small>
                </div>
            ';
        }
    }
    if ($hoursHtml === '') {
        $hoursHtml = '<small>-</small>';
    }

    // AVAILABILITY EXCEPTIONS
    $availabilityExceptions = trim($data['availabilityExceptions'] ?? '');

    // STATUS BADGE
    $statusClass = 'secondary';
    if ($status === 'active') {
        $statusClass = 'success';
    } elseif ($status === 'inactive') {
        $statusClass = 'danger';
    } elseif ($status === 'suspended') {
        $statusClass = 'warning';
    }

    // LOCATION TYPE HTML
    $locationTypeHtml = '-';
    if (!empty($locationTypes)) {
        $locationTypeHtml = '';
        foreach ($locationTypes as $type) {
            $locationTypeHtml .= '
                <div>
                    <small>
                        ' . htmlspecialchars($type['display'] !== '' ? $type['display'] : $type['code']) . '
                        ' . ($type['code'] !== '' && $type['display'] !== '' ? '(' . htmlspecialchars($type['code']) . ')' : '') . '
                    </small>
                </div>
            ';
        }
    }

    // HTML DETAIL
    $html = '
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <tbody>
                    <tr>
                        <td width="35%"><small><b>ID Location</b></small></td>
                        <td><small class="text-primary">' . htmlspecialchars($id) . '</small></td>
                    </tr>
                    <tr>
                        <td><small><b>Nama</b></small></td>
                        <td><small>' . htmlspecialchars($name !== '' ? $name : '-') . '</small></td>
                    </tr>
                    <tr>
                        <td><small><b>Status</b></small></td>
                        <td><span class="badge bg-' . $statusClass . '">' . htmlspecialchars($status !== '' ? ucfirst($status) : '-') . '</span></td>
                    </tr>
                    <tr>
                        <td><small><b>Identifier</b></small></td>
                        <td><small>' . htmlspecialchars($identifierValue !== '' ? $identifierValue : '-') . '</small></td>
                    </tr>
                    <tr>
                        <td><small><b>Identifier System</b></small></td>
                        <td><small class="text-muted">' . htmlspecialchars($identifierSystem !== '' ? $identifierSystem : '-') . '</small></td>
                    </tr>
                    <tr>
                        <td><small><b>Identifier Use</b></small></td>
                        <td><small>' . htmlspecialchars($identifierUse !== '' ? $identifierUse : '-') . '</small></td>
                    </tr>
                    <tr>
                        <td><small><b>Alias</b></small></td>
                        <td><small>' . htmlspecialchars($aliasText) . '</small></td>
                    </tr>
                    <tr>
                        <td><small><b>Deskripsi</b></small></td>
                        <td><small>' . htmlspecialchars($description !== '' ? $description : '-') . '</small></td>
                    </tr>
                    <tr>
                        <td><small><b>Mode</b></small></td>
                        <td><small>' . htmlspecialchars($mode !== '' ? $mode : '-') . '</small></td>
                    </tr>
                    <tr>
                        <td><small><b>Tipe Lokasi</b></small></td>
                        <td>' . $locationTypeHtml . '</td>
                    </tr>
                    <tr>
                        <td><small><b>Physical Type</b></small></td>
                        <td><small>' . htmlspecialchars($physicalTypeDisplay !== '' ? $physicalTypeDisplay : ($physicalTypeCode !== '' ? $physicalTypeCode : '-')) . ($physicalTypeCode !== '' && $physicalTypeDisplay !== '' ? '(' . htmlspecialchars($physicalTypeCode) . ')' : '') . '</small></td>
                    </tr>
                    <tr>
                        <td><small><b>Managing Organization</b></small></td>
                        <td><small>' . htmlspecialchars($organizationDisplay !== '' ? $organizationDisplay : ($organizationReference !== '' ? $organizationReference : '-')) . '</small></td>
                    </tr>
                    <tr>
                        <td><small><b>Part Of</b></small></td>
                        <td><small>' . htmlspecialchars($partOfDisplay !== '' ? $partOfDisplay : ($partOfReference !== '' ? $partOfReference : '-')) . '</small></td>
                    </tr>
                    <tr>
                        <td><small><b>Alamat</b></small></td>
                        <td><small>' . htmlspecialchars($addressText) . '</small></td>
                    </tr>
                    <tr>
                        <td><small><b>Telecom</b></small></td>
                        <td>' . $telecomHtml . '</td>
                    </tr>
                    <tr>
                        <td><small><b>Jam Operasional</b></small></td>
                        <td>' . $hoursHtml . '</td>
                    </tr>
                    <tr>
                        <td><small><b>Pengecualian Operasional</b></small></td>
                        <td><small>' . htmlspecialchars($availabilityExceptions !== '' ? $availabilityExceptions : '-') . '</small></td>
                    </tr>
                </tbody>
            </table>
        </div>
    ';

    // RESPONSE SUCCESS
    $response = [
        "status"  => "success",
        "message" => "Detail Location berhasil ditampilkan.",
        "html"    => $html
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
?>