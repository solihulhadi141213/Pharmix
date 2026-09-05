<?php
    // Koneksi, Function Dan Session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Format Response
    header('Content-Type: application/json');

    // ===============================
    // Validasi Session
    // ===============================
    if (empty($SessionIdAkses)) {
        echo json_encode(['results' => [], 'pagination' => ['more' => false]]);
        exit;
    }

    // ===============================
    // Validasi Parameter
    // ===============================
    if (empty($_GET['keyword']) || empty($_GET['medication_category'])) {
        echo json_encode(['results' => [], 'pagination' => ['more' => false]]);
        exit;
    }

    // ===============================
    // Sanitasi Input
    // ===============================
    $keyword             = validateAndSanitizeInput($_GET['keyword']);
    $medication_category = validateAndSanitizeInput($_GET['medication_category']);

    // ===============================
    // Tentukan Kategori API
    // ===============================
    $kategori_pencarian = 'farmasi';

    // ===============================
    // Pagination
    // ===============================
    $page  = (!empty($_GET['page']) && is_numeric($_GET['page'])) ? (int) $_GET['page'] : 1;
    $limit = 50;

    // ===============================
    // Token SATUSEHAT
    // ===============================
    $tokenResult = generateTokenSatuSehat($Conn);
    if (
        empty($tokenResult) ||
        $tokenResult['status'] !== 'success' ||
        empty($tokenResult['token'])
    ) {
        echo json_encode(['results' => [], 'pagination' => ['more' => false]]);
        exit;
    }
    $token = $tokenResult['token'];

    // ===============================
    // Ambil Base URL
    // ===============================
    $status_connection = 1;
    $stmt = $Conn->prepare("SELECT url_connection_satu_sehat FROM connection_satu_sehat WHERE status_connection_satu_sehat = ? LIMIT 1");
    $stmt->bind_param("i", $status_connection);
    $stmt->execute();
    $config = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (empty($config['url_connection_satu_sehat'])) {
        echo json_encode(['results' => [], 'pagination' => ['more' => false]]);
        exit;
    }

    $base_url = rtrim($config['url_connection_satu_sehat'], '/');

    // ===============================
    // URL API KFA
    // ===============================
    $url = "$base_url/kfa-v2/products/all"
        . "?page=$page"
        . "&size=$limit"
        . "&product_type=$kategori_pencarian"
        . "&keyword=" . urlencode($keyword);

    // ===============================
    // CURL
    // ===============================
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => $url,
        CURLOPT_CUSTOMREQUEST  => 'GET',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false, // aktifkan TRUE di production
        CURLOPT_SSL_VERIFYHOST => false
    ]);

    $response   = curl_exec($curl);
    $http_code  = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($curl);
    curl_close($curl);

    // ===============================
    // Error Handling CURL
    // ===============================
    if ($curl_error || $http_code !== 200) {
        echo json_encode(['results' => [], 'pagination' => ['more' => false]]);
        exit;
    }

    // ===============================
    // Decode JSON
    // ===============================
    $data = json_decode($response, true);
    if (
        !$data ||
        !is_array($data) ||
        isset($data['issue'])
    ) {
        echo json_encode(['results' => [], 'pagination' => ['more' => false]]);
        exit;
    }

    // ===============================
    // Ambil Metadata
    // ===============================
    $total       = (int) ($data['total'] ?? 0);
    $currentPage = (int) ($data['page'] ?? 1);
    $size        = (int) ($data['size'] ?? $limit);
    $items       = $data['items']['data'] ?? [];

    if ($total === 0 || empty($items)) {
        echo json_encode(['results' => [], 'pagination' => ['more' => false]]);
        exit;
    }

    // ===============================
    // Hitung Pagination
    // ===============================
    $totalPage = (int) ceil($total / $size);
    $more      = ($currentPage < $totalPage);

    // ===============================
    // Mapping Result
    // ===============================
    $results = [];

    foreach ($items as $row) {
        if (empty($row['kfa_code']) || empty($row['name'])) {
            continue;
        }

        $results[] = [
            'id'   => $row['kfa_code'] . '|' . $row['name'],
            'text' => $row['kfa_code'] . ' - ' . $row['name']
        ];
    }

    // ===============================
    // Output JSON (FINAL)
    // ===============================
    echo json_encode([
        'results' => $results,
        'pagination' => [
            'more' => $more
        ]
    ]);


?>