<?php
    // Header JSON
    header('Content-Type: application/json');

    // Connection, Function And Session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Validasi Session
    if (empty($SessionIdAkses)) {
        echo json_encode(["status"  => "Error", "message" => "Sesi Akses Sudah Berakhir! Silahkan Login Ulang."]);
        exit;
    }

    // Validasi nik
    if(empty($_POST['nik'])){
        echo json_encode(["status"  => "Error", "message" => "NIK Tidak Boleh Kosong"]);
        exit;
    }
    
    // Creat Variable
    $nik = validateAndSanitizeInput($_POST['nik']);

    // Open Configuration SATUSEHAT Active
    $stmt = mysqli_prepare($Conn,"SELECT url_connection_satu_sehat FROM connection_satu_sehat WHERE status_connection_satu_sehat = 1 LIMIT 1");
    if (!$stmt) {
        echo json_encode(["status"  => "Error", "message" => "Terjadi Kesalahan Pada Saat Membuka Pengaturan Koneksi SATUSEHAT"]);
        exit;
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $setting = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // Validation Configuration Info
    $baseurl_satusehat = rtrim(trim($setting['url_connection_satu_sehat'] ?? ''), '/');
    if ($baseurl_satusehat === '') {
        echo json_encode(["status"  => "Error", "message" => "Terjadi Kesalahan Pada Saat Membuka Pengaturan Koneksi SATUSEHAT"]);
        exit;
    }

    // Generate Token
    $tokenResult = generateTokenSatuSehat($Conn);
    if (($tokenResult['status'] ?? 'error') !== 'success') {
        $message = $tokenResult['message'];
        echo json_encode(["status"  => "Error", "message" => "Terjadi kessalahan pada saat generate token SATUSEHAT.<br> Pesan : $message"]);
        exit;
    }

    // Validate Token NULL
    $token = $tokenResult['token'] ?? '';
    if ($token === '') {
        echo json_encode(["status"  => "Error", "message" => "Terjadi kessalahan pada saat generate token SATUSEHAT.<br> Pesan : $message"]);
        exit;
    }

    // Buat URL
    $url = "$baseurl_satusehat/fhir-r4/v1/Patient?identifier=https://fhir.kemkes.go.id/id/nik|$nik";

    // Mulai CURL
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => ''.$url.'',
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => 0,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,

    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer '.$token.''
    ),
    ));
    $response = curl_exec($curl);

    // Error CURL
    if(curl_errno($curl)){
        $error_curl = curl_error($curl);
        echo json_encode(["status"  => "Error", "message" => "CURL Error : $error_curl <br> Response : $response"]);
        exit;
    }

    // Decode JSON
    $data = json_decode($response, true);

    // Validasi JSON Response
    if(json_last_error() !== JSON_ERROR_NONE){
        echo json_encode(["status"  => "Error", "message" => "Response : $url"]);
        exit;
    }

    // Validasi Error FHIR
    if(isset($data['resourceType']) && $data['resourceType'] === 'OperationOutcome'){
        echo json_encode(["status"  => "Error", "message" => $data]);
        exit;
    }

    // Response Berhasil
    // Validasi entry tersedia
    if (!isset($data['entry']) || !is_array($data['entry']) || count($data['entry']) == 0) {
        echo json_encode([
            "status"  => "Error",
            "message" => "Data pasien tidak ditemukan di SATUSEHAT"
        ]);
        exit;
    }

    // Ambil resource patient pertama
    $resource = $data['entry'][0]['resource'] ?? [];

    // Ambil ID SATUSEHAT
    $id = $resource['id'] ?? '';

    // Ambil Nama Pasien
    $name = '';

    if (
        isset($resource['name'][0]['text']) &&
        !empty($resource['name'][0]['text'])
    ) {
        $name = $resource['name'][0]['text'];
    } elseif (
        isset($resource['name'][0]['given'][0])
    ) {
        $name = $resource['name'][0]['given'][0];
    }

    // Ambil NIK dari identifier
    $nik_pasien = '';

    if (isset($resource['identifier']) && is_array($resource['identifier'])) {

        foreach ($resource['identifier'] as $identifier) {

            $system = $identifier['system'] ?? '';
            $value  = $identifier['value'] ?? '';

            if ($system == 'https://fhir.kemkes.go.id/id/nik') {
                $nik_pasien = $value;
                break;
            }
        }
    }

    // Metadata Response
    $metadata = [
        "id"   => $id,
        "name" => $name,
        "nik"  => $nik_pasien,
    ];

    // Response Success
    echo json_encode([
        "status"   => "success",
        "message"  => "Data Berhasil Ditemukan",
        "metadata" => $metadata
    ]);
    exit;
?>