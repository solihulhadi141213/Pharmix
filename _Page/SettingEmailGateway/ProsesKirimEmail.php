<?php
    // Koneksi
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    include "../../_Config/SettingEmail.php";

    // Validasi Session
    if(empty($SessionIdAkses)){
        exit("ERROR : Sesi akses sudah berakhir, silahkan login ulang.");
    }

    // Ambil Data
    $nama_tujuan  = $_POST['nama_tujuan'] ?? '';
    $email_tujuan = $_POST['email_tujuan'] ?? '';
    $subjek       = $_POST['subjek'] ?? '';
    $pesan        = $_POST['pesan'] ?? '';

    // Validasi
    if(empty($nama_tujuan)){
        exit("ERROR : Nama penerima tidak boleh kosong.");
    }

    if(empty($email_tujuan)){
        exit("ERROR : Email tujuan tidak boleh kosong.");
    }

    if(!filter_var($email_tujuan,FILTER_VALIDATE_EMAIL)){
        exit("ERROR : Format email tujuan tidak valid.");
    }

    if(empty($subjek)){
        exit("ERROR : Subjek tidak boleh kosong.");
    }

    if(empty($pesan)){
        exit("ERROR : Isi pesan tidak boleh kosong.");
    }

    // Sanitasi
    $nama_tujuan  = validateAndSanitizeInput($nama_tujuan);
    $email_tujuan = validateAndSanitizeInput($email_tujuan);
    $subjek       = validateAndSanitizeInput($subjek);
    $pesan        = validateAndSanitizeInput($pesan);

    // Validasi Setting Gateway
    if(
        empty($url_service) ||
        empty($email_gateway) ||
        empty($password_gateway) ||
        empty($url_provider)
    ){
        exit("ERROR : Pengaturan Email Gateway belum lengkap.");
    }

    // Susun Payload
    $payload = [
        "subjek"               => $subjek,
        "email_asal"           => $email_gateway,
        "password_email_asal"  => $password_gateway,
        "url_provider"         => $url_provider,
        "nama_pengirim"        => $nama_pengirim,
        "email_tujuan"         => $email_tujuan,
        "nama_tujuan"          => $nama_tujuan,
        "pesan"                => $pesan,
        "port"                 => $port_gateway
    ];

    // Log Awal
    $log  = "=== TEST EMAIL GATEWAY ===\n";
    $log .= "URL Service : ".$url_service."\n";
    $log .= "SMTP Host   : ".$url_provider."\n";
    $log .= "Port        : ".$port_gateway."\n";
    $log .= "Pengirim    : ".$email_gateway."\n";
    $log .= "Penerima    : ".$email_tujuan."\n";
    $log .= "----------------------------------------\n";

    // CURL
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url_service,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10
    ]);

    $response = curl_exec($ch);
    $error    = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    // Error CURL
    if(!empty($error)){

        $log .= "STATUS      : GAGAL\n";
        $log .= "HTTP CODE   : ".$httpCode."\n";
        $log .= "CURL ERROR  : ".$error."\n";

        exit($log);
    }

    // Decode Response
    $result = json_decode($response,true);

    $log .= "STATUS      : RESPONSE DITERIMA\n";
    $log .= "HTTP CODE   : ".$httpCode."\n";

    if(is_array($result)){

        if(!empty($result['code'])){
            $log .= "CODE        : ".$result['code']."\n";
        }

        if(!empty($result['pesan'])){
            $log .= "PESAN       : ".$result['pesan']."\n";
        }

        $log .= "----------------------------------------\n";
        $log .= "RAW RESPONSE:\n";
        $log .= json_encode(
            $result,
            JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE
        );

    }else{

        $log .= "RAW RESPONSE:\n";
        $log .= $response;
    }

    echo $log;
?>