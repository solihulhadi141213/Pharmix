<?php
/**
 * ============================================================
 * PENCARIAN KFA (VERSI BENAR & STABIL)
 * ============================================================
 */

include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";

date_default_timezone_set("Asia/Jakarta");
header('Content-Type: application/json');

/* ============================================================
 * INPUT
 * ============================================================ */
$keyword = trim($_GET['q'] ?? '');

if (strlen($keyword) < 3) {
    echo json_encode(['results' => []]);
    exit;
}

/* ============================================================
 * TOKEN SATUSEHAT
 * ============================================================ */
$tokenResult = generateTokenSatuSehat($Conn);
if (
    empty($tokenResult) ||
    $tokenResult['status'] !== 'success' ||
    empty($tokenResult['token'])
) {
    echo json_encode(['results' => []]);
    exit;
}
$token = $tokenResult['token'];

/* ============================================================
 * BASE URL SATUSEHAT
 * ============================================================ */
$stmt = $Conn->prepare("
    SELECT url_connection_satu_sehat
    FROM connection_satu_sehat
    WHERE status_connection_satu_sehat = 1
    LIMIT 1
");
$stmt->execute();
$config = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (empty($config['url_connection_satu_sehat'])) {
    echo json_encode(['results' => []]);
    exit;
}

$baseUrl = rtrim($config['url_connection_satu_sehat'], '/');

/* ============================================================
 * REQUEST KFA
 * ============================================================ */
$query = http_build_query([
    'page'         => 1,
    'size'         => 20,
    'product_type' => 'farmasi',
    'keyword'      => $keyword
]);

$url = $baseUrl . '/kfa-v2/products/all?' . $query;

/* ============================================================
 * CURL
 * ============================================================ */
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL            => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ],
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($httpCode !== 200 || empty($response)) {
    echo json_encode(['results' => []]);
    exit;
}

/* ============================================================
 * PARSE RESPONSE (INI KUNCI UTAMA)
 * ============================================================ */
$json = json_decode($response, true);
$items = $json['items']['data'] ?? [];

/* ============================================================
 * FORMAT UNTUK SELECT2
 * ============================================================ */
$results = [];

foreach ($items as $row) {

    if (empty($row['kfa_code']) || empty($row['name'])) {
        continue;
    }

    $dosage = $row['dosage_form']['name'] ?? '';
    $template = $row['product_template']['display_name'] ?? '';

    $results[] = [
        'id'   => $row['kfa_code'],
        'text' => $row['name'],
        'meta' => [
            'template_kfa' => $row['product_template']['kfa_code'] ?? null,
            'dosage_form'  => $dosage,
            'state'        => $row['state'] ?? null,
            'active'       => $row['active'] ?? null
        ]
    ];
}

/* ============================================================
 * OUTPUT
 * ============================================================ */
echo json_encode([
    'results' => $results
]);
exit;
