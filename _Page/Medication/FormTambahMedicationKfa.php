<?php
    /**
     * ============================================================
     * FORM TAMBAH MEDICATION DARI KFA
     * ============================================================
     */

    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    date_default_timezone_set("Asia/Jakarta");

    /* ============================================================
    * VALIDASI SESSION
    * ============================================================ */
    if (empty($SessionIdAkses)) {
        echo '
            <div class="alert alert-danger">
                Sesi Akses Sudah Berakhir! Silahkan Login Ulang!
            </div>
        ';
        exit;
    }

    /* ============================================================
    * VALIDASI INPUT
    * ============================================================ */
    if(empty($_POST['kfa_code'])){
        echo '
            <div class="alert alert-danger">
                Kode KFA Tidak Boleh Kosong!
            </div>
        ';
        exit;
    }

    $kfa_code    = validateAndSanitizeInput($_POST['kfa_code']);
    /* ============================================================
    * TOKEN SATUSEHAT
    * ============================================================ */
    $tokenResult = generateTokenSatuSehat($Conn);
    if (empty($tokenResult) || $tokenResult['status'] !== 'success' || empty($tokenResult['token'])) {
        echo '
            <div class="alert alert-danger">
                Gagal Mendapatkan Token Satu Sehat!
            </div>
        ';
        exit;
    }

    $token = $tokenResult['token'];

    /* ============================================================
    * KONFIGURASI KONEKSI SATUSEHAT
    * ============================================================ */
    $status_connection = 1;
    $stmt = $Conn->prepare("
        SELECT url_connection_satu_sehat
        FROM connection_satu_sehat
        WHERE status_connection_satu_sehat = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $status_connection);
    $stmt->execute();
    $config = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (empty($config['url_connection_satu_sehat'])) {
        echo '
            <div class="alert alert-danger">
                Konfigurasi Koneksi Dengan Satu Sehat Tidak Ditemukan!
            </div>
        ';
        exit;
    }

    $base_url = rtrim($config['url_connection_satu_sehat'], '/');
    $url      = "$base_url/kfa-v2/products?identifier=kfa&code=$kfa_code";

    /* ============================================================
    * CURL REQUEST
    * ============================================================ */
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($curl);
    curl_close($curl);

    if ($curl_error) {
        echo '
            <div class="alert alert-danger">
                CURL Error: ' . htmlspecialchars($curl_error) . '
            </div>
        ';
        exit;
    }

    if ($http_code !== 200) {
        echo '
            <div class="alert alert-danger">
                Gagal mengambil data (HTTP ' . $http_code . ').
            </div>
        ';
        exit;
    }

    /* ============================================================
    * PARSE RESPONSE JSON
    * ============================================================ */
    $data = json_decode($response, true);

    if (!$data ||!is_array($data) ||isset($data['issue'])) {
        echo '
            <div class="alert alert-danger">
                Response Server Tidak Valid
            </div>
        ';
        exit;
    }

    // Tampilkan Data
    if(empty($data['result'])){
        echo '
            <div class="alert alert-danger">
               Data Tidak Ditemukan
            </div>
        ';
    }else{
        // Buat Variabel 'result'
        $result = $data['result'];

        // Buka Atribut Data
        $name         = $result['name'];
        $kfa_code     = $result['kfa_code'];
        $manufacturer = $result['manufacturer'];
        $farmalkes_type = $result['farmalkes_type'];
        $farmalkes_type_code = $farmalkes_type['code'];
        $farmalkes_type_name = $farmalkes_type['name'];
        $farmalkes_type_group = $farmalkes_type['group'];

        // Kategori
        $kategori = $result['farmalkes_type']['name'];
        if($kategori=="Obat"){
            $select_obat    = "selected";
            $select_alkes   = "";
            $select_lainnya = "";
        }else{
            if($kategori=="Alkes"){
                $select_obat    = "";
                $select_alkes   = "selected";
                $select_lainnya = "";
            }else{
                if($kategori=="Lainnya"){
                    $select_obat    = "";
                    $select_alkes   = "";
                    $select_lainnya = "selected";
                }else{
                    $select_obat    = "";
                    $select_alkes   = "";
                    $select_lainnya = "";
                }
            }
        }

        echo '
            <div class="row mb-2 mt-2">
                <div class="col-5"><small><b>A. Informasi Umum</b></small></div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><label for="medication_code"><small>Kode</small></label></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <div class="input-group">
                        <input type="text" name="medication_code" id="medication_code" class="form-control">
                        <a href="javascript:void(0)" class="input-group-text generate_kode_lokal" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Generate Kode Lokal">
                            <i class="bi bi-repeat"></i>
                        </a>
                    </div>
                    <small>Kode lokal obat/alkes yang di gunakan pada faskes</small>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><label for="medication_name"><small>Nama/Merek</small></label></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <input type="text" name="medication_name" id="medication_name" class="form-control" value="'.$result['nama_dagang'].'">
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><label for="medication_category"><small>Kategori</small></label></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <select name="medication_category" id="medication_category" class="form-control">
                        <option value="">Pilih Kategori</option>
                        <option '.$select_obat.' value="Obat">Obat</option>
                        <option '.$select_alkes.' value="Alkes">Alkes</option>
                        <option '.$select_lainnya.' value="Lainnya">Lainnya</option>
                    </select>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><label for="racikan_code"><small>Obat Racikan</small></label></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <select name="racikan_code" id="racikan_code" class="form-control">
                        <option value="NC">Non-compound</option>
                        <option value="C">Compound</option>
                    </select>
                </div>
            </div>
            <div class="row mb-2 mt-2">
                <div class="col-5"><small><b>B. Kamus Farmasi Dan Alat Kesehatan (KFA)</b></small></div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><label for="kfa_code"><small>KFA Code</small></label></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <input type="text" name="kfa_code" id="kfa_code" class="form-control" value="'.$result['kfa_code'].'">
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><label for="kfa_display"><small>KFA Display</small></label></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <input type="text" name="kfa_display" id="kfa_display" class="form-control" value="'.$result['name'].'">
                </div>
            </div>
            <div class="row mb-2 mt-2">
                <div class="col-5"><small><b>C. Informasi Sediaan</b></small></div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><label for="sediaan_code"><small><i>Code</i></small></label></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <input type="text" name="sediaan_code" id="sediaan_code" class="form-control" value="'.$result['dosage_form']['code'].'">
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><label for="sediaan_display"><small><i>Display</i></small></label></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <input type="text" name="sediaan_display" id="sediaan_display" class="form-control" value="'.$result['dosage_form']['name'].'">
                </div>
            </div>
            <div class="row mb-2 mt-2">
                <div class="col-5"><small><b>D. Manufaktur</b></small></div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><label for="manufacturer_id"><small><i>ID Manufacturer</i></small></label></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <input type="text" name="manufacturer_id" id="manufacturer_id" class="form-control" value="">
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><label for="manufacturer_name"><small><i>Manufacturer Name</i></small></label></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">
                    <input type="text" name="manufacturer_name" id="manufacturer_name" class="form-control" value="'.$result['manufacturer'].'">
                </div>
            </div>
        ';
    }
?>

