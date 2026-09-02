<?php
    // HEADER JSON
    header('Content-Type: application/json; charset=utf-8');

    // CONNECTION, FUNCTION, SESSION
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

    // VALIDASI NAMA LOKASI
    $nama_lokasi = trim($_POST['nama_lokasi'] ?? '');
    if ($nama_lokasi === '') {
        $response["message"] = "Nama lokasi tidak boleh kosong.";
        echo json_encode($response);
        exit;
    }
    $nama_lokasi = validateAndSanitizeInput($nama_lokasi);

    // BUKA KONFIGURASI SATUSEHAT AKTIF
    $stmt = mysqli_prepare(
        $Conn,
        "SELECT url_connection_satu_sehat FROM connection_satu_sehat WHERE status_connection_satu_sehat = 1 LIMIT 1"
    );
    if (!$stmt) {
        $response["message"] = "Terjadi kesalahan pada saat membuka pengaturan koneksi SATUSEHAT.";
        echo json_encode($response);
        exit;
    }
    mysqli_stmt_execute($stmt);
    $result  = mysqli_stmt_get_result($stmt);
    $setting = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // VALIDASI KONFIGURASI
    $baseurl_satusehat = rtrim(trim($setting['url_connection_satu_sehat'] ?? ''), '/');
    if ($baseurl_satusehat === '') {
        $response["message"] = "URL koneksi SATUSEHAT tidak ditemukan.";
        echo json_encode($response);
        exit;
    }

    // GENERATE TOKEN SATUSEHAT
    $tokenResult = generateTokenSatuSehat($Conn);
    if (($tokenResult['status'] ?? 'error') !== 'success') {
        $message = $tokenResult['message'] ?? 'Tidak diketahui';
        $response["message"] = "Terjadi kesalahan pada saat generate token SATUSEHAT.<br>Pesan : " . htmlspecialchars($message);
        echo json_encode($response);
        exit;
    }

    // VALIDASI TOKEN
    $token = trim($tokenResult['token'] ?? '');
    if ($token === '') {
        $response["message"] = "Token SATUSEHAT tidak tersedia.";
        echo json_encode($response);
        exit;
    }

    // BUAT URL PENCARIAN LOCATION
    $query = http_build_query(['name' => $nama_lokasi]);
    $url = $baseurl_satusehat . '/fhir-r4/v1/Location?' . $query;

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
        $response["message"] = "Terjadi kesalahan koneksi ke SATUSEHAT.<br>CURL Error : " . htmlspecialchars($curlError);
        echo json_encode($response);
        exit;
    }

    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    // DECODE JSON
    $data = json_decode($curlResponse, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response["message"] = "Response SATUSEHAT bukan JSON yang valid.";
        echo json_encode($response);
        exit;
    }

    // VALIDASI HTTP STATUS
    if ($httpCode < 200 || $httpCode >= 300) {
        $errorMessage = "SATUSEHAT mengembalikan HTTP Code " . $httpCode;
        if (isset($data['resourceType']) && $data['resourceType'] === 'OperationOutcome') {
            if (!empty($data['issue'][0]['details']['text'])) {
                $errorMessage .= '<br>' . htmlspecialchars($data['issue'][0]['details']['text']);
            } elseif (!empty($data['issue'][0]['diagnostics'])) {
                $errorMessage .= '<br>' . htmlspecialchars($data['issue'][0]['diagnostics']);
            }
        }
        $response["message"] = $errorMessage;
        echo json_encode($response);
        exit;
    }

    // VALIDASI OPERATION OUTCOME
    if (isset($data['resourceType']) && $data['resourceType'] === 'OperationOutcome') {
        $errorMessage = "Terjadi kesalahan dari SATUSEHAT.";
        if (!empty($data['issue'][0]['details']['text'])) {
            $errorMessage .= '<br>' . htmlspecialchars($data['issue'][0]['details']['text']);
        } elseif (!empty($data['issue'][0]['diagnostics'])) {
            $errorMessage .= '<br>' . htmlspecialchars($data['issue'][0]['diagnostics']);
        }
        $response["message"] = $errorMessage;
        echo json_encode($response);
        exit;
    }

    // VALIDASI RESOURCE BUNDLE
    if (($data['resourceType'] ?? '') !== 'Bundle') {
        $response["message"] = "Format response Location SATUSEHAT tidak sesuai.";
        echo json_encode($response);
        exit;
    }

    // VALIDASI ENTRY
    if (empty($data['entry']) || !is_array($data['entry'])) {
        $response["status"]  = "success";
        $response["message"] = "Data Location tidak ditemukan.";
        $response["html"] = '
            <div class="alert alert-warning text-center">
                <h1 class="bi bi-exclamation-circle"></h1>
                <small>Location dengan nama <b>' . htmlspecialchars($nama_lokasi) . '</b> tidak ditemukan di SATUSEHAT.</small>
            </div>
        ';
        echo json_encode($response);
        exit;
    }

    // GENERATE HTML
    $html = '<div class="list-group">';
    $jumlahData = 0;

    foreach ($data['entry'] as $entry) {
        $resource = $entry['resource'] ?? [];
        if (($resource['resourceType'] ?? '') !== 'Location') {
            continue;
        }

        $id_location = trim($resource['id'] ?? '');
        if ($id_location === '') {
            continue;
        }

        $nama_location = trim($resource['name'] ?? '');
        if ($nama_location === '') {
            $nama_location = '-';
        }

        $status_location = trim($resource['status'] ?? '-');

        $identifier_system = '';
        $identifier_value  = '';
        if (isset($resource['identifier']) && is_array($resource['identifier'])) {
            foreach ($resource['identifier'] as $identifier) {
                $system = trim($identifier['system'] ?? '');
                $value  = trim($identifier['value'] ?? '');
                if ($value !== '') {
                    $identifier_system = $system;
                    $identifier_value  = $value;
                    break;
                }
            }
        }

        $organization_reference = trim($resource['managingOrganization']['reference'] ?? '');
        $organization_display = trim($resource['managingOrganization']['display'] ?? '');

        $alamat = '';
        if (isset($resource['address']['line']) && is_array($resource['address']['line'])) {
            $alamat = implode(', ', array_filter($resource['address']['line']));
        }
        if (!empty($resource['address']['city'])) {
            if ($alamat !== '') {
                $alamat .= ', ';
            }
            $alamat .= $resource['address']['city'];
        }

        $html .= '
            <button type="button" class="list-group-item list-group-item-action PilihLocation" data-id="' . htmlspecialchars($id_location, ENT_QUOTES, 'UTF-8') . '" data-name="' . htmlspecialchars($nama_location, ENT_QUOTES, 'UTF-8') . '">
                <div class="d-flex w-100 justify-content-between">
                    <strong>' . htmlspecialchars($nama_location) . '</strong>
                    <small>' . htmlspecialchars($status_location) . '</small>
                </div>
                <div class="mt-1">
                    <small class="text-muted"><b>ID :</b> ' . htmlspecialchars($id_location) . '</small>
                </div>
        ';

        if ($identifier_value !== '') {
            $html .= '<div><small class="text-muted"><b>Identifier :</b> ' . htmlspecialchars($identifier_value) . '</small></div>';
        }

        if ($organization_display !== '') {
            $html .= '<div><small class="text-muted"><b>Org :</b> ' . htmlspecialchars($organization_display) . '</small></div>';
        } elseif ($organization_reference !== '') {
            $html .= '<div><small class="text-muted"><b>Org :</b> ' . htmlspecialchars($organization_reference) . '</small></div>';
        }

        if ($alamat !== '') {
            $html .= '<div><small class="text-muted"><b>Alamat :</b> ' . htmlspecialchars($alamat) . '</small></div>';
        }

        $html .= '</button>';
        $jumlahData++;
    }

    $html .= '</div>';

    // JIKA SEMUA ENTRY BUKAN LOCATION
    if ($jumlahData === 0) {
        $html = '
            <div class="alert alert-warning text-center">
                <h1 class="bi bi-exclamation-circle"></h1>
                <small>Data Location tidak ditemukan.</small>
            </div>
        ';
    }

    // RESPONSE SUCCESS
    $response = [
        "status"  => "success",
        "message" => $jumlahData . " Location ditemukan.",
        "html"    => $html
    ];

    echo json_encode($response);
    exit;
?>