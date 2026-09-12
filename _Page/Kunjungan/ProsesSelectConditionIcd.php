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
    $stmt = conditionQuery($Conn, "SELECT kode, long_des FROM icd WHERE icd = 'ICD10' AND (kode LIKE ? OR long_des LIKE ? OR short_des LIKE ?) ORDER BY kode, id_icd LIMIT 11 OFFSET ?", 'sssi', [$keyword, $keyword, $keyword, $offset]);
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $more = count($rows) > 10;
    $results = [];
    foreach (array_slice($rows, 0, 10) as $row) {
        $results[] = ['id' => $row['kode'], 'text' => $row['kode'].' - '.$row['long_des'], 'description' => $row['long_des']];
    }
    echo json_encode(['results' => $results, 'pagination' => ['more' => $more]], JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    error_log('ProsesSelectConditionIcd: '.$error->getMessage());
    http_response_code(500);
    conditionResponse('error', 'Gagal memuat pilihan.', ['results' => [], 'pagination' => ['more' => false]]);
}

