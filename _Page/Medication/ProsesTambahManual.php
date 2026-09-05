<?php
    //--------------------------------------
    // Connection, Helper, Session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    include "../../_Config/SettingGeneral.php";

    date_default_timezone_set("Asia/Jakarta");
    header('Content-Type: application/json');

    //--------------------------------------
    // 1. VALIDASI SESSION
    if (empty($SessionIdAkses)) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Sesi Anda telah berakhir. Silakan login kembali.'
        ]);
        exit;
    }

    //--------------------------------------
    // 2. VALIDASI DATA WAJIB (BAHASA RAMAH USER)
    if (empty($_POST['medication_category'])) {
        echo json_encode(['status'=>'error','message'=>'Kategori data wajib dipilih.']);
        exit;
    }

    if (empty($_POST['medication_name'])) {
        echo json_encode(['status'=>'error','message'=>'Nama atau merek obat/alkes tidak boleh kosong.']);
        exit;
    }

    if (empty($_POST['sediaan'])) {
        echo json_encode(['status'=>'error','message'=>'Sediaan obat wajib dipilih.']);
        exit;
    }

    $randome_code = GenerateKodeBarang(8);

    // ======================================================
    // 3. VARIABEL WAJIB + SANITASI
    // ======================================================
    $medication_category = validateAndSanitizeInput($_POST['medication_category']);
    $medication_code     = "MED-$randome_code";
    $medication_name     = validateAndSanitizeInput($_POST['medication_name']);
    $sediaan_raw         = validateAndSanitizeInput($_POST['sediaan']);

    // ======================================================
    // 4. VARIABEL TIDAK WAJIB
    // ======================================================
    $racikan_code      = $_POST['racikan_code'] ?? '';
    $kfa_raw           = $_POST['kfa'] ?? '';
    $manufaktur_raw    = $_POST['manufaktur'] ?? '';
    $payload_ingridient= $_POST['payload_ingridient'] ?? '';

    // ======================================================
    // 5. EXPLODE VARIABEL KOMPOSIT
    // ======================================================

    // SEDIAAN
    list($sediaan_code, $sediaan_display) = explode('|', $sediaan_raw);

    // KFA
    $kfa_code = $kfa_display = '';
    if (!empty($kfa_raw)) {
        list($kfa_code, $kfa_display) = explode('|', $kfa_raw);
    }

    // MANUFACTURER
    $manufacturer_id = $manufacturer_name = '';
    if (!empty($manufaktur_raw)) {
        list($manufacturer_id, $manufacturer_name) = explode('|', $manufaktur_raw);
    }

    // RACIKAN
    switch ($racikan_code) {
        case 'NC': $racikan_display = 'Non-compound'; break;
        case 'SD': $racikan_display = 'Gives of such doses'; break;
        case 'EP': $racikan_display = 'Divide into equal parts'; break;
        default  : $racikan_display = '';
    }

    // ======================================================
    // 6. BANGUN DATA INGREDIENT
    // ======================================================
    $ingredients = [];

    if (!empty($_POST['payload_ingridient']) && is_array($_POST['payload_ingridient'])) {

        foreach ($_POST['payload_ingridient'] as $item) {

            // item masih berupa string JSON → decode satu per satu
            $row = json_decode($item, true);

            if (!is_array($row)) {
                continue;
            }

            $ingredients[] = [
                'kode_kfa'           => $row['kode_kfa'] ?? '',
                'nama_kfa'           => $row['nama_kfa'] ?? '',
                'jumlah_numerator'   => $row['jumlah_numerator'] ?? '',
                'kode_numerator'     => $row['kode_numerator'] ?? '',
                'nama_numerator'     => $row['nama_numerator'] ?? '',
                'jumlah_denominator' => $row['jumlah_denominator'] ?? '',
                'kode_denominator'   => $row['kode_denominator'] ?? '',
                'nama_denominator'   => $row['nama_denominator'] ?? ''
            ];
        }
    }
    $ingredients_json = json_encode($ingredients);

    // ======================================================
    // 7. VALIDASI DUPLIKAT KODE LOKAL
    // ======================================================
    $cek = GetDetailData($Conn, 'medication', 'medication_code', $medication_code, 'medication_code');
    if (!empty($cek)) {
        echo json_encode([
            'status'=>'error',
            'message'=>'Kode lokal tersebut sudah terdaftar.'
        ]);
        exit;
    }

    // ======================================================
    // 8. BEGIN TRANSACTION
    // ======================================================
    $Conn->begin_transaction();

    try {

        // ==================================================
        // SIMPAN DATA MEDICATION (AWAL, id_medication KOSONG)
        // ==================================================
        $stmt = $Conn->prepare("
            INSERT INTO medication (
                medication_code, medication_name, medication_category,
                kfa_code, kfa_display,
                sediaan_code, sediaan_display,
                racikan_code, racikan_display,
                manufacturer_id, manufacturer_name, ingredient
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
        ");

        $stmt->bind_param(
            "ssssssssssss",
            $medication_code, $medication_name, $medication_category,
            $kfa_code, $kfa_display,
            $sediaan_code, $sediaan_display,
            $racikan_code, $racikan_display,
            $manufacturer_id, $manufacturer_name,$ingredients_json
        );

        if (!$stmt->execute()) {
            throw new Exception('Gagal menyimpan data medication.');
        }
        $stmt->close();

        // ==================================================
        // 9. KIRIM KE SATUSEHAT JIKA DIPILIH
        // ==================================================
        
        // TOKEN (FUNCTION SUDAH ADA)
        $tokenResult = generateTokenSatuSehat($Conn);
        if ($tokenResult['status'] !== 'success') {
            throw new Exception($tokenResult['message']);
        }
        $token = $tokenResult['token'];

        // KONFIGURASI SATUSEHAT
        $q = $Conn->query("SELECT url_connection_satu_sehat, organization_id 
                        FROM connection_satu_sehat 
                        WHERE status_connection_satu_sehat = 1 LIMIT 1");
        $cfg = $q->fetch_assoc();
        if (!$cfg) {
            throw new Exception('Koneksi SATUSEHAT tidak tersedia.');
        }

        $url = rtrim($cfg['url_connection_satu_sehat'],'/').'/fhir-r4/v1/Medication';

        // PAYLOAD FHIR
        $payload = [
            'resourceType' => 'Medication',
            'status'       => 'active',
            'meta' => [
                'profile' => [
                    'https://fhir.kemkes.go.id/r4/StructureDefinition/Medication'
                ]
            ],
            'identifier' => [[
                'system' => 'http://sys-ids.kemkes.go.id/medication/'.$cfg['organization_id'],
                'use'    => 'official',
                'value'  => $medication_code
            ]],
            'code' => [
                'coding' => [[
                    'system'  => 'http://sys-ids.kemkes.go.id/kfa',
                    'code'    => $kfa_code,
                    'display' => $kfa_display
                ]]
            ],
            'form' => [
                'coding' => [[
                    'system'  => 'http://terminology.kemkes.go.id/CodeSystem/medication-form',
                    'code'    => $sediaan_code,
                    'display' => $sediaan_display
                ]]
            ]
        ];

        if (!empty($manufacturer_id)) {
            $payload['manufacturer'] = [
                'reference' => 'Organization/'.$manufacturer_id,
                'display'   => 'Organization/'.$manufacturer_id
            ];
        }

        if (!empty($ingredients)) {

            $payload['ingredient'] = [];

            foreach ($ingredients as $ing) {

                $ingredient = [
                    'itemCodeableConcept' => [
                        'coding' => [[
                            'system'  => 'http://sys-ids.kemkes.go.id/kfa',
                            'code'    => $ing['kode_kfa'],
                            'display' => $ing['nama_kfa']
                        ]]
                    ],
                    'isActive' => true
                ];

                // STRENGTH (OPSIONAL)
                if (!empty($ing['jumlah_numerator'])) {

                    $ingredient['strength'] = [
                        'numerator' => [
                            'value'  => (float)$ing['jumlah_numerator'],
                            'system' => 'http://unitsofmeasure.org',
                            'code'   => $ing['kode_numerator']
                        ]
                    ];

                    if (!empty($ing['jumlah_denominator'])) {
                        $ingredient['strength']['denominator'] = [
                            'value'  => (float)$ing['jumlah_denominator'],
                            'system' => 'http://terminology.hl7.org/CodeSystem/v3-orderableDrugForm',
                            'code'   => $ing['kode_denominator']
                        ];
                    }
                }

                $payload['ingredient'][] = $ingredient;
            }
        }

        if (!empty($racikan_code)) {
            $payload['extension'] = [[
                'url' => 'https://fhir.kemkes.go.id/r4/StructureDefinition/MedicationType',
                'valueCodeableConcept' => [
                    'coding' => [[
                        'system'  => 'http://terminology.kemkes.go.id/CodeSystem/medication-type',
                        'code'    => $racikan_code,
                        'display' => $racikan_display
                    ]]
                ]
            ]];
        }

        // CURL
        $ch = curl_init($url);
        curl_setopt_array($ch,[
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer $token",
                "Content-Type: application/json"
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $resp = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $respArr = json_decode($resp, true);

        // ================================
        // ERROR RESPONSE DARI SATUSEHAT
        // ================================
        if ($http !== 201) {

            // ROLLBACK KARENA GAGAL KIRIM
            $Conn->rollback();
            
            // Dapatkan error dari curl jika ada
            $curl_error = '';
            if ($http === 0) {
                $curl_error = curl_error($ch);
            }
            curl_close($ch);
            
            // Siapkan pesan error
            $error_message = "Gagal mengirim Medication ke SATUSEHAT";
            
            if ($http !== 0) {
                $error_message .= " (HTTP $http)";
            } else {
                $error_message .= " (HTTP 0 - Koneksi gagal)";
                
                if (!empty($curl_error)) {
                    $error_message .= ": " . $curl_error;
                }
            }
            
            // Siapkan array untuk response
            $response_data = [];
            
            // Coba decode JSON response
            if (!empty($resp)) {
                $respArr = json_decode($resp, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $response_data = $respArr;
                } else {
                    // Jika bukan JSON, simpan sebagai string
                    $response_data = [
                        'raw_response' => $resp,
                        'note' => 'Response bukan format JSON'
                    ];
                }
            }
            
            // Jika HTTP 0, tambahkan informasi koneksi
            if ($http === 0) {
                $response_data['curl_error'] = $curl_error;
                $response_data['http_status'] = 0;
                $response_data['note'] = 'Permintaan tidak sampai ke server SatuSehat. Cek koneksi internet, firewall, atau konfigurasi CURL.';
            }
            
            // Tambahkan informasi URL yang diakses
            $response_data['request_url'] = $url;
            $response_data['request_method'] = 'POST';
            
            // Siapkan output yang akan ditampilkan di frontend
            $output = [
                'status'       => 'error',
                'message'      => $error_message,
                'http_code'    => $http,
                'response'     => $response_data,
                'payload'      => $payload,
                'debug_info'   => [
                    'url' => $url,
                    'organization_id' => $cfg['organization_id'] ?? '',
                    'token_available' => !empty($token) ? 'Ya' : 'Tidak',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'curl_error' => $curl_error ?? ''
                ]
            ];
            
            // Jika ingin melihat semua data (termasuk token), tambahkan ini:
            // HATI-HATI: Jangan tampilkan token di production!
            // $output['debug_info']['token_first_10_chars'] = substr($token, 0, 10) . '...';
            
            echo json_encode($output, JSON_PRETTY_PRINT);
            exit;
        }

        // UPDATE id_medication
        $id_medication = $respArr['id'];
        $Conn->query("UPDATE medication 
                    SET id_medication='$id_medication' 
                    WHERE medication_code='$medication_code'");

        // ==================================================
        // COMMIT
        // ==================================================
        $Conn->commit();

        echo json_encode([
            'status'=>'success',
            'message'=>'Data medication berhasil disimpan.',
            'id_medication'=>$id_medication
        ]);
        exit;

    } catch (Exception $e) {

        // ==================================================
        // ROLLBACK
        // ==================================================
        $Conn->rollback();

        echo json_encode([
            'status'=>'error',
            'message'=>$e->getMessage()
        ]);
        exit;
    }

?>