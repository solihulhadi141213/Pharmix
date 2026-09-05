<?php
    $JmlHalaman = 0;
    $page = 1;
    /**
     * ============================================================
     * PENCARIAN KFA
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
            <tr>
                <td colspan="4" class="text-center">
                    <span class="text-danger">Sesi Akses Sudah Berakhir! Silahkan Login Ulang!</span>
                </td>
            </tr>
            <script>
                $("#page_info_kfa").html("0 / 0");
                $("#prev_button_kfa").prop("disabled", true);
                $("#next_button_kfa").prop("disabled", true);
            </script>
        ';
        exit;
    }

    /* ============================================================
    * VALIDASI INPUT
    * ============================================================ */
    if(empty($_POST['versi_pencarian'])){
        echo '
            <tr>
                <td colspan="4" class="text-center">
                    <span class="text-danger">Versi Pencarian Tidak Boleh Kosong!</span>
                </td>
            </tr>
            <script>
                $("#page_info_kfa").html("0 / 0");
                $("#prev_button_kfa").prop("disabled", true);
                $("#next_button_kfa").prop("disabled", true);
            </script>
        ';
        exit;
    }
    if(empty($_POST['kategori_pencarian'])){
        echo '
            <tr>
                <td colspan="4" class="text-center">
                    <span class="text-danger">Kategori Pencarian Tidak Boleh Kosong!</span>
                </td>
            </tr>
            <script>
                $("#page_info_kfa").html("0 / 0");
                $("#prev_button_kfa").prop("disabled", true);
                $("#next_button_kfa").prop("disabled", true);
            </script>
        ';
        exit;
    }
    if(empty($_POST['keyword_pencarian'])){
        echo '
            <tr>
                <td colspan="4" class="text-center">
                    <span class="text-danger">Keyword Pencarian Tidak Boleh Kosong!</span>
                </td>
            </tr>
            <script>
                $("#page_info_kfa").html("0 / 0");
                $("#prev_button_kfa").prop("disabled", true);
                $("#next_button_kfa").prop("disabled", true);
            </script>
        ';
        exit;
    }
    if(empty($_POST['page'])){
        echo '
            <tr>
                <td colspan="4" class="text-center">
                    <span class="text-danger">Posisi Halaman Tidak Boleh Kosong!</span>
                </td>
            </tr>
            <script>
                $("#page_info_kfa").html("0 / 0");
                $("#prev_button_kfa").prop("disabled", true);
                $("#next_button_kfa").prop("disabled", true);
            </script>
        ';
        exit;
    }

    $versi_pencarian    = validateAndSanitizeInput($_POST['versi_pencarian']);
    $kategori_pencarian = validateAndSanitizeInput($_POST['kategori_pencarian']);
    $keyword_pencarian  = validateAndSanitizeInput($_POST['keyword_pencarian']);
    $page               = validateAndSanitizeInput($_POST['page']);
    $limit             = 10;

    /* ============================================================
    * TOKEN SATUSEHAT
    * ============================================================ */
    $tokenResult = generateTokenSatuSehat($Conn);
    if (empty($tokenResult) || $tokenResult['status'] !== 'success' || empty($tokenResult['token'])) {
        echo '
            <tr>
                <td colspan="4" class="text-center">
                    <span class="text-danger">Gagal Mendapatkan Token Satu Sehat!</span>
                </td>
            </tr>
            <script>
                $("#page_info_kfa").html("0 / 0");
                $("#prev_button_kfa").prop("disabled", true);
                $("#next_button_kfa").prop("disabled", true);
            </script>
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
            <tr>
                <td colspan="4" class="text-center">
                    <span class="text-danger">Konfigurasi Koneksi Dengan Satu Sehat Tidak Ditemukan!</span>
                </td>
            </tr>
            <script>
                $("#page_info_kfa").html("0 / 0");
                $("#prev_button_kfa").prop("disabled", true);
                $("#next_button_kfa").prop("disabled", true);
            </script>
        ';
        exit;
    }

    // Menentukan URL
    if($versi_pencarian=="V1"){$sub_url = "kfa";}
    if($versi_pencarian=="V2"){$sub_url = "kfa-v2";}
    if($versi_pencarian=="V3"){$sub_url = "kfa-v3";}

    $base_url = rtrim($config['url_connection_satu_sehat'], '/');
    $url      = "$base_url/$sub_url/products/all?page=$page&size=$limit&product_type=$kategori_pencarian&keyword=$keyword_pencarian";

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
            <tr>
                <td colspan="4" class="text-center">
                    <span class="text-danger">CURL Error: ' . htmlspecialchars($curl_error) . '</span>
                </td>
            </tr>
            <script>
                $("#page_info_kfa").html("0 / 0");
                $("#prev_button_kfa").prop("disabled", true);
                $("#next_button_kfa").prop("disabled", true);
            </script>
        ';
        exit;
    }

    if ($http_code !== 200) {
        echo '
            <tr>
                <td colspan="4" class="text-center">
                    <span class="text-danger">Gagal mengambil data (HTTP ' . $http_code . ').</span>
                </td>
            </tr>
            <script>
                $("#page_info_kfa").html("0 / 0");
                $("#prev_button_kfa").prop("disabled", true);
                $("#next_button_kfa").prop("disabled", true);
            </script>
        ';
        exit;
    }

    /* ============================================================
    * PARSE RESPONSE JSON
    * ============================================================ */
    $data = json_decode($response, true);

    if (!$data ||!is_array($data) ||isset($data['issue'])) {
        echo '
            <tr>
                <td colspan="4" class="text-center">
                    <span class="text-danger">Response Server Tidak Valid</span>
                </td>
            </tr>
            <script>
                $("#page_info_kfa").html("0 / 0");
                $("#prev_button_kfa").prop("disabled", true);
                $("#next_button_kfa").prop("disabled", true);
            </script>
        ';
        exit;
    }

    // Buat Variabel
    $total       = $data['total'];
    $curent_page = $data['page'];
    $size        = $data['size'];
    $items       = $data['items'];
    $JmlHalaman = ceil($total/$size); 

    // Tampilkan Data
    if(empty($data['total'])){
        echo '
            <tr>
                <td colspan="4" class="text-center">
                    <span class="text-danger">Data Tidak Ditemukan</span>
                </td>
            </tr>
        ';
    }else{
        $items_data = $items['data'];
        $no         = 1;
        foreach ($items_data as $items_list){
            echo '
                <tr>
                    <td class="text-center">'.$no.'</td>
                    <td>'.$items_list['name'].'</td>
                    <td>'.$items_list['kfa_code'].'</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-secondary btn-floating modal_tambah_medication_kfa"  data-id="'.$items_list['kfa_code'].'">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </td>
                </tr>
            ';
            $no++;
        }
    }
?>
<script>
    //Creat Javascript Variabel
    var page_count=<?php echo $JmlHalaman; ?>;
    var curent_page=<?php echo $page; ?>;
    
    //Put Into Pagging Element
    $('#page_info_kfa').html(''+curent_page+' / '+page_count+'');
    
    //Set Pagging Button
    if(curent_page==1){
        $('#prev_button_kfa').prop('disabled', true);
    }else{
        $('#prev_button_kfa').prop('disabled', false);
    }
    if(page_count<=curent_page){
        $('#next_button_kfa').prop('disabled', true);
    }else{
        $('#next_button_kfa').prop('disabled', false);
    }
</script>
