<?php
require_once __DIR__.'/../../_Config/Connection.php';
require_once __DIR__.'/../../_Config/GlobalFunction.php';
require_once __DIR__.'/../../_Config/Session.php';
require_once __DIR__.'/../Condition/ConditionHelper.php';
header('Content-Type: application/json; charset=utf-8');
if (empty($SessionIdAkses)) {
    http_response_code(401);
    conditionResponse('error', 'Sesi akses sudah berakhir. Silakan login ulang.', ['results' => [], 'pagination' => ['more' => false]]);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    conditionResponse('error', 'Metode request tidak valid.', ['results' => [], 'pagination' => ['more' => false]]);
}
$page = filter_var(conditionInput('page'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 214748364]]);
$page = $page ?: 1;
$offset = ($page - 1) * 10;
$keyword = '%'.conditionInput('keyword').'%';
try {
    $stmt = conditionQuery($Conn, "SELECT medicalPersonelId, medicalPersonelCode, medicalPersonelName FROM medical_personel WHERE medicalPersonelStatus = 'Active' AND (medicalPersonelCode LIKE ? OR medicalPersonelName LIKE ?) ORDER BY medicalPersonelName, medicalPersonelId LIMIT 11 OFFSET ?", 'ssi', [$keyword, $keyword, $offset]);
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $more = count($rows) > 10;
    $results = [];
    foreach (array_slice($rows, 0, 10) as $row) {
        $results[] = ['id' => $row['medicalPersonelId'], 'text' => $row['medicalPersonelCode'].' - '.$row['medicalPersonelName'], 'name' => $row['medicalPersonelName']];
    }
    echo json_encode(['results' => $results, 'pagination' => ['more' => $more]], JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    error_log('ProsesSelectConditionMedicalPersonel: '.$error->getMessage());
    http_response_code(500);
    conditionResponse('error', 'Gagal memuat pilihan.', ['results' => [], 'pagination' => ['more' => false]]);
}

