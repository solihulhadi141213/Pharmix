<?php
    // Koneksi, Function Dan Session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');

    // Output Select2
    function outputKfa(array $results = [], bool $more = false): void {
        echo json_encode([
            'results' => $results,
            'pagination' => [
                'more' => $more
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // Validasi Session
    if (empty($SessionIdAkses)) {
        outputKfa();
    }

    // Parameter
    $keyword = trim((string)($_GET['keyword'] ?? ''));
    $medication_category = trim((string)($_GET['medication_category'] ?? ''));

    if ($keyword === '' || $medication_category === '') {
        outputKfa();
    }

    $keyword = validateAndSanitizeInput($keyword);
    $medication_category = validateAndSanitizeInput($medication_category);

    // Pagination
    $page = (!empty($_GET['page']) && is_numeric($_GET['page']))
        ? max(1, (int)$_GET['page'])
        : 1;

    $limit = 50;

    // Jenis pencarian KFA
    $product_type = 'farmasi';

    // Token SATUSEHAT
    $tokenResult = generateTokenSatuSehat($Conn);

    if (
        empty($tokenResult) ||
        ($tokenResult['status'] ?? '') !== 'success' ||
        empty($tokenResult['token'])
    ) {
        outputKfa();
    }

    $token = trim((string)$tokenResult['token']);

    // Konfigurasi SATUSEHAT
    $status_connection = 1;

    $stmt = $Conn->prepare("
        SELECT url_connection_satu_sehat
        FROM connection_satu_sehat
        WHERE status_connection_satu_sehat = ?
        LIMIT 1
    ");

    if (!$stmt) {
        outputKfa();
    }

    $stmt->bind_param("i", $status_connection);

    if (!$stmt->execute()) {
        $stmt->close();
        outputKfa();
    }

    $config = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (empty($config['url_connection_satu_sehat'])) {
        outputKfa();
    }

    $base_url = rtrim(
        trim((string)$config['url_connection_satu_sehat']),
        '/'
    );

    // URL API KFA V2
    $url = $base_url.'/kfa-v2/products/all'
        .'?page='.$page
        .'&size='.$limit
        .'&product_type='.urlencode($product_type)
        .'&keyword='.urlencode($keyword);

    // Request API KFA
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer '.$token,
            'Accept: application/json'
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);

    $response = curl_exec($curl);
    $http_code = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($curl);

    curl_close($curl);

    // Validasi Response CURL
    if (
        $response === false ||
        $curl_error !== '' ||
        $http_code !== 200
    ) {
        outputKfa();
    }

    // Decode Response
    $data = json_decode($response, true);

    if (
        json_last_error() !== JSON_ERROR_NONE ||
        !is_array($data) ||
        isset($data['issue'])
    ) {
        outputKfa();
    }

    // Metadata Pagination
    $total = (int)($data['total'] ?? 0);
    $currentPage = (int)($data['page'] ?? $page);
    $size = (int)($data['size'] ?? $limit);
    $items = $data['items']['data'] ?? [];

    if (
        $total < 1 ||
        !is_array($items) ||
        empty($items)
    ) {
        outputKfa();
    }

    $totalPage = $size > 0
        ? (int)ceil($total / $size)
        : 1;

    $more = $currentPage < $totalPage;

    // Mapping Result Select2
    $results = [];

    foreach ($items as $row) {

        $kfa_code = trim((string)($row['kfa_code'] ?? ''));
        $kfa_display = trim((string)($row['name'] ?? ''));

        if ($kfa_code === '' || $kfa_display === '') {
            continue;
        }

        /*
         * Berdasarkan response KFA v2:
         *
         * "manufacturer": "KIMIA FARMA TBK"
         *
         * Manufacturer berbentuk STRING.
         */
        $manufacturer_name = trim(
            (string)($row['manufacturer'] ?? '')
        );

        /*
         * API KFA products/all tidak menjamin adanya
         * Organization ID SATUSEHAT manufacturer.
         *
         * Field tetap disediakan pada response Select2
         * agar frontend tetap konsisten.
         */
        $manufacturer_id = '';

        /*
         * Jika suatu response KFA menyediakan identifier
         * manufacturer, ambil nilainya.
         */
        if (!empty($row['manufacturer_id'])) {
            $manufacturer_id = trim(
                (string)$row['manufacturer_id']
            );
        }

        // Informasi tambahan
        $nama_dagang = trim(
            (string)($row['nama_dagang'] ?? '')
        );

        $nie = trim(
            (string)($row['nie'] ?? '')
        );

        $registrar = trim(
            (string)($row['registrar'] ?? '')
        );

        $dosage_form_code = '';
        $dosage_form_name = '';

        if (
            !empty($row['dosage_form']) &&
            is_array($row['dosage_form'])
        ) {
            $dosage_form_code = trim(
                (string)($row['dosage_form']['code'] ?? '')
            );

            $dosage_form_name = trim(
                (string)($row['dosage_form']['name'] ?? '')
            );
        }

        $results[] = [
            'id' => $kfa_code,
            'text' => $kfa_code.' - '.$kfa_display,

            'kfa_code' => $kfa_code,
            'kfa_display' => $kfa_display,

            'manufacturer_id' => $manufacturer_id,
            'manufacturer_name' => $manufacturer_name,

            'nama_dagang' => $nama_dagang,
            'nie' => $nie,
            'registrar' => $registrar,

            'dosage_form_code' => $dosage_form_code,
            'dosage_form_name' => $dosage_form_name
        ];
    }

    outputKfa($results, $more);
?>