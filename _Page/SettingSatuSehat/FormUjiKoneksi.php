<?php
    // koneksi dan session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    function formatTimestampMs($ms) {
        return date('d/m/Y H:i:s', substr($ms, 0, 10));
    }

    function maskString($str, $start = 6, $end = 4) {
        if (strlen($str) <= ($start + $end)) return $str;
        return substr($str, 0, $start) . '****' . substr($str, -$end);
    }

    // Zona Waktu
    date_default_timezone_set("Asia/Jakarta");

    // =======================
    // VALIDASI SESSION
    // =======================
    if (empty($SessionIdAkses)) {
        echo '
            <div class="alert alert-danger text-center">
                <small>Sesi Akses Sudah Berakhir! Silahkan Login Ulang.</small>
            </div>
        ';
        exit;
    }

    // =======================
    // VALIDASI INPUT
    // =======================
    if (empty($_POST['id_connection_satu_sehat'])) {
        echo '
            <div class="alert alert-danger text-center">
                <small>Koneksi Satu Sehat Tidak Boleh Kosong!</small>
            </div>
        ';
        exit;
    }

    $id_connection_satu_sehat = validateAndSanitizeInput($_POST['id_connection_satu_sehat']);

    // =======================
    // AMBIL DATA KONEKSI
    // =======================
    $Qry = $Conn->prepare("SELECT * FROM connection_satu_sehat WHERE id_connection_satu_sehat = ?");
    $Qry->bind_param("i", $id_connection_satu_sehat);

    if (!$Qry->execute()) {
        echo '
            <div class="alert alert-danger">
                <small>Error DB: '.$Conn->error.'</small>
            </div>
        ';
        exit;
    }

    $Result = $Qry->get_result();
    $Data   = $Result->fetch_assoc();
    $Qry->close();

    if (!$Data) {
        echo '
            <div class="alert alert-danger">
                <small>Data koneksi Satu Sehat tidak ditemukan.</small>
            </div>
        ';
        exit;
    }

    // =======================
    // VARIABEL
    // =======================
    $url_connection_satu_sehat = rtrim($Data['url_connection_satu_sehat'], '/');
    $organization_id           = $Data['organization_id'];
    $client_key                = $Data['client_key'];
    $secret_key                = $Data['secret_key'];

    // =======================
    // REQUEST TOKEN (cURL)
    // =======================
    $payload = http_build_query([
        'grant_type'    => 'client_credentials',
        'client_id'     => $client_key,
        'client_secret' => $secret_key
    ]);

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL            => $url_connection_satu_sehat . '/oauth2/v1/accesstoken?grant_type=client_credentials',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS      => 'client_id='.$client_key.'&client_secret='.$secret_key.'',
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/x-www-form-urlencoded'
        ],
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT        => 10,

        // DEV ONLY
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);
    $response     = curl_exec($curl);
    $curl_error   = curl_error($curl);
    $http_code    = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    // =======================
    // DEBUGGING CURL
    // =======================
    if ($response === false) {
        echo '
            <div class="alert alert-danger">
                <small>
                    <b>Gagal menghubungi API Satu Sehat</b><br>
                    Error: '.$curl_error.'
                </small>
            </div>
        ';
        exit;
    }

    if ($http_code !== 200) {
        echo '
            <div class="alert alert-danger">
                <small>
                    <b>HTTP Error</b><br>
                    Status Code: '.$http_code.'
                </small>
            </div>
        ';
        exit;
    }

    // =======================
    // VALIDASI JSON
    // =======================
    $arry = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo '
            <div class="alert alert-danger">
                <small>Response API bukan JSON valid.</small>
            </div>
        ';
        exit;
    }

    // =======================
    // VALIDASI RESPONSE API
    // =======================
    if (!isset($arry['status'])) {
        echo '
            <div class="alert alert-danger">
                <small>Format response API tidak dikenali.</small>
            </div>
        ';
        exit;
    }

    if ($arry['status'] !== 'approved') {
        echo '
            <div class="alert alert-danger">
                <small>'.$arry['status'].'</small>
            </div>
        ';
        exit;
    }

    // =======================
    // DATA TOKEN
    // =======================
    $api_product_list_json = $arry['api_product_list_json'];

    $display = [
        'Status'                => $arry['status'],
        'Organization'          => $arry['organization_name'],
        'API Environment'       => implode(', ', $arry['api_product_list_json']),
        'Token Type'            => $arry['token_type'],
        'Expires In'            => $arry['expires_in'].' detik',
        'Issued At'             => formatTimestampMs($arry['issued_at']),
        'Refresh Token Count'   => $arry['refresh_count'],
        'Developer Email'       => $arry['developer.email'],
        'Client ID'             => maskString($arry['client_id']),
        'Access Token'          => maskString($arry['access_token'])
    ];

    foreach ($display as $label => $value) {
        echo '
            <div class="row mb-1">
                <div class="col-4">
                    <small class="text-muted">'.$label.'</small>
                </div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7">
                    <small class="text-break">'.$value.'</small>
                </div>
            </div>
        ';
    }
