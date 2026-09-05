<?php
    // Koneksi, Function Dan Session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Format Response
    header('Content-Type: application/json');

    // Validasi Session
    if (empty($SessionIdAkses)) {
        echo json_encode(['results' => [], 'pagination' => ['more' => false]]);
        exit;
    }

    // Validasi Parameter
    if (empty($_GET['keyword'])) {
        echo json_encode(['results' => [], 'pagination' => ['more' => false]]);
        exit;
    }

    // Sanitasi Input
    $keyword = validateAndSanitizeInput($_GET['keyword']);

    // Token SATUSEHAT
    $tokenResult = generateTokenSatuSehat($Conn);
    if (empty($tokenResult) ||$tokenResult['status'] !== 'success' ||empty($tokenResult['token'])) {
        echo json_encode(['results' => [], 'pagination' => ['more' => false]]);
        exit;
    }
    $token = $tokenResult['token'];

    // Ambil Base URL
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

    // URL API Organization
    $url = "$base_url/fhir-r4/v1/Organization?name=" . urlencode($keyword);

   // CURL
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

    // Error Handling CURL
    if ($curl_error || $http_code !== 200) {
        echo json_encode(['results' => [], 'pagination' => ['more' => false]]);
        exit;
    }

    // Decode JSON
    $data = json_decode($response, true);
    if (!$data ||!is_array($data) ||isset($data['issue'])) {
        echo json_encode(['results' => [], 'pagination' => ['more' => false]]);
        exit;
    }

    // Ambil Metadata 'total'
    $total = (int) ($data['total'] ?? 0);

    if ($total === 0) {
        echo json_encode(['results' => [], 'pagination' => ['more' => false]]);
        exit;
    }

    // Ambil metadata entry
    $entry = $data['entry'];

   // Mapping Result
    $results = [];

    foreach ($entry as $row) {
        $resource = $row['resource'];
        $id       = $resource['id'];
        $name     = $resource['name'];

        $results[] = [
            'id'   => $id . '|' . $name,
            'text' => $name
        ];
    }

    // ===============================
    // Output JSON (FINAL)
    // ===============================
    echo json_encode([
        'results' => $results,
        'pagination' => [
            'more' => false
        ]
    ]);


?>