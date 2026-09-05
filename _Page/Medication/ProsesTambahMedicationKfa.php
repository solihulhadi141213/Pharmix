<?php
    // KONEKSI, SESSION, GLOBAL FUNCTION
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    include "../../_Config/SettingGeneral.php";

    date_default_timezone_set("Asia/Jakarta");

    // RESPONSE HEADER
    header('Content-Type: application/json');

    // inisiasi Function
    function getPost($key, $default = ''){
        if (!isset($_POST[$key])) {
            return $default;
        }

        $value = $_POST[$key];

        if (is_array($value)) {
            return array_map('trim', $value);
        }

        $value = trim($value);
        $value = str_replace(["\r", "\n"], ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return $value;
    }

    // VALIDASI SESSION
    if (empty($SessionIdAkses)) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Sesi akses telah berakhir. Silakan login ulang.'
        ]);
        exit;
    }

    // DATA WAJIB
    if(empty($_POST['medication_code'])){
        echo json_encode([
            'status'  => 'error',
            'message' => 'Kode Lokal Tidak Boleh Kosong.'
        ]);
        exit;
    }

    if(empty($_POST['medication_name'])){
        echo json_encode([
            'status'  => 'error',
            'message' => 'Nama Obat/Alkes Tidak Boleh Kosong.'
        ]);
        exit;
    }
    if(empty($_POST['medication_category'])){
        echo json_encode([
            'status'  => 'error',
            'message' => 'Kategori Obat/Alkes Tidak Boleh Kosong.'
        ]);
        exit;
    }
    if(empty($_POST['racikan_code'])){
        echo json_encode([
            'status'  => 'error',
            'message' => 'Kategori Racikan Tidak Boleh Kosong.'
        ]);
        exit;
    }
   
    if(empty($_POST['kfa_code'])){
        echo json_encode([
            'status'  => 'error',
            'message' => 'Kode KFA Tidak Boleh Kosong.'
        ]);
        exit;
    }
    if(empty($_POST['kfa_display'])){
        echo json_encode([
            'status'  => 'error',
            'message' => 'Nama KFA Tidak Boleh Kosong.'
        ]);
        exit;
    }

    if(empty($_POST['sediaan_code'])){
        echo json_encode([
            'status'  => 'error',
            'message' => 'Kode Sediaan Tidak Boleh Kosong.'
        ]);
        exit;
    }

    if(empty($_POST['sediaan_display'])){
        echo json_encode([
            'status'  => 'error',
            'message' => 'Nama Sediaan Tidak Boleh Kosong.'
        ]);
        exit;
    }

    // BUAT VARIABELNYA
    $medication_code     = validateAndSanitizeInput($_POST['medication_code']);
    $medication_name     = validateAndSanitizeInput($_POST['medication_name']);
    $medication_category = validateAndSanitizeInput($_POST['medication_category']);
    $racikan_code        = validateAndSanitizeInput($_POST['racikan_code']);
    $kfa_code            = validateAndSanitizeInput($_POST['kfa_code']);
    $kfa_display         = validateAndSanitizeInput($_POST['kfa_display']);
    $sediaan_code        = validateAndSanitizeInput($_POST['sediaan_code']);
    $sediaan_display     = validateAndSanitizeInput($_POST['sediaan_display']);

    // Validasi Kode Lokal Tidak Boleh Duplikat
    $validasi_duplikat = GetDetailData($Conn, 'medication', 'medication_code', $medication_code, 'medication_code');
    if(!empty($validasi_duplikat)){
        echo json_encode([
            'status'  => 'error',
            'message' => 'Kode lokal <i>medication</i> tersebut sudah terdaftar'
        ]);
        exit;
    }

    if(empty($_POST['manufacturer_id'])){
       $manufacturer_id = "";
    }else{
        $manufacturer_id     = validateAndSanitizeInput($_POST['manufacturer_id']);
    }
    if(empty($_POST['manufacturer_name'])){
        $manufacturer_name = "";
    }else{
        $manufacturer_name     = validateAndSanitizeInput($_POST['manufacturer_name']);
    }

    // Racikan
    if($racikan_code=="NC"){
        $nama_racikan = "Non-compound";
    }else{
        $nama_racikan = "compound";
    }

    // MEMBUAT TOKEN SATU SEHAT
    $tokenResult = generateTokenSatuSehat($Conn);

    if ($tokenResult['status'] !== 'success') {
        echo json_encode([
            'status'  => 'error',
            'message' => $tokenResult['message']
        ]);
        exit;
    }

    $token = $tokenResult['token'];

    // AMBIL KONFIGURASI SATUSEHAT AKTIF
    $status_active = 1;
    $stmt = $Conn->prepare("SELECT url_connection_satu_sehat, organization_id FROM connection_satu_sehat WHERE status_connection_satu_sehat = ?
    ");
    $stmt->bind_param("i", $status_active);
    $stmt->execute();
    $result = $stmt->get_result();
    $config = $result->fetch_assoc();
    $stmt->close();

    if (!$config) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Koneksi SATUSEHAT tidak ditemukan.'
        ]);
        exit;
    }

    $organization_id = $config['organization_id'];

    // BUAT URL
    $url_api         = rtrim($config['url_connection_satu_sehat'], '/');
    $url_medication = $url_api . '/fhir-r4/v1/Medication';

    // SUSUN PAYLOAD Medication (FHIR R4)
    $payload = [
        'resourceType' => 'Medication',
        'status'       => 'active',
        'meta' => [
            'profile' => [
                'https://fhir.kemkes.go.id/r4/StructureDefinition/Medication',
            ]
        ],
        'identifier' => [[
            'system' => 'http://sys-ids.kemkes.go.id/medication/'.$organization_id.'',
            'use'    => 'official',
            'value'  => ''.$medication_code.'',
        ]],

        'code' => [
            'coding' => [[
                'system'  => 'http://sys-ids.kemkes.go.id/kfa',
                'code'    => ''.$kfa_code.'',
                'display' => ''.$kfa_display.''
            ]]
        ],

        'form' => [
            'coding' => [[
                'system'  => 'http://terminology.kemkes.go.id/CodeSystem/medication-form',
                'code'    => ''.$sediaan_code.'',
                'display' => ''.$sediaan_display.''
            ]]
        ],
        'extension' => [[
            'url' => 'https://fhir.kemkes.go.id/r4/StructureDefinition/MedicationType',
            'valueCodeableConcept' => [
                'coding' => [[
                    'system'  => 'http://terminology.kemkes.go.id/CodeSystem/medication-type',
                    'code'    => ''.$racikan_code.'',
                    'display' => ''.$nama_racikan.''
                ]]
            ]
        ]],
    ];

    // ENCODE JSON
    $payload_json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Gagal membuat JSON payload.'
        ]);
        exit;
    }

    // KIRIM KE SATUSEHAT
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url_medication,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $payload_json,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($curl);
    curl_close($curl);

   // HANDLE ERROR CURL
    if ($curl_error) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'cURL Error: ' . $curl_error
        ]);
        exit;
    }

   // DECODE RESPONSE
    $result = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Response bukan JSON valid.',
            'response_raw' => substr($response, 0, 300)
        ]);
        exit;
    }

    // VALIDASI RESPONSE SATUSEHAT
    if ($http_code !== 201) {
        $msg = 'Gagal mengirim Medication ke SATUSEHAT';

        if (($result['resourceType'] ?? '') === 'OperationOutcome') {
            $msg = $result['issue'][0]['details']['text']
                ?? $result['issue'][0]['diagnostics']
                ?? $msg;
        }

        echo json_encode([
            'status'    => 'error',
            'payload'   => $payload_json,
            'message'   => $msg,
            'http_code' => $http_code
        ]);
        exit;
    }

    // SIMPAN ID MEDICATION KE DATABASE
    $id_medication = $result['id'] ?? null;

    if ($id_medication) {
        $stmt = $Conn->prepare("INSERT INTO medication (
            id_medication, 
            medication_code, 
            medication_name, 
            medication_category, 
            kfa_code,
            kfa_display,
            sediaan_code,
            sediaan_display,
            racikan_code,
            racikan_display,
            manufacturer_id,
            manufacturer_name
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )");
        $stmt->bind_param("ssssssssssss", 
            $id_medication, 
            $medication_code, 
            $medication_name, 
            $medication_category, 
            $kfa_code,
            $kfa_display,
            $sediaan_code,
            $sediaan_display,
            $racikan_code,
            $nama_racikan,
            $manufacturer_id,
            $manufacturer_name
        );
        $Input = $stmt->execute();
        $stmt->close();
    }

    // ======================================================
    // RESPONSE SUKSES
    // ======================================================
    echo json_encode([
        'status'         => 'success',
        'message'        => 'Medication berhasil dikirim ke SATUSEHAT',
        'payload'        => $payload_json,
        'id_medication' => $id_medication
    ]);
    exit;
?>