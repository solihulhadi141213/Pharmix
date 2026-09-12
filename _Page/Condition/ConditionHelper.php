<?php
// Shared validation and prepared queries for the condition endpoints.
function conditionResponse($status, $message, $data = []) {
    echo json_encode(array_merge(['status' => $status, 'message' => $message], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

function conditionInput($key) {
    return isset($_POST[$key]) && is_string($_POST[$key]) ? trim($_POST[$key]) : '';
}

function conditionQuery($Conn, $sql, $types = '', $params = []) {
    $stmt = $Conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Prepare condition query failed.');
    }
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException('Execute condition query failed.');
    }
    return $stmt;
}

function conditionRow($Conn, $sql, $types, $params) {
    $stmt = conditionQuery($Conn, $sql, $types, $params);
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row;
}

function conditionVisit($Conn) {
    $id = filter_var(conditionInput('id_kunjungan'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $category = conditionInput('category');
    if (!$id) {
        conditionResponse('error', 'ID kunjungan wajib diisi dan harus valid.');
    }
    if (!in_array($category, ['Admission', 'Provisional', 'Primary', 'Secondary', 'Working', 'Differential', 'Final'], true)) {
        conditionResponse('error', 'Kategori diagnosis wajib diisi dan harus valid.');
    }
    $patient = conditionRow($Conn, 'SELECT a.id_pasien, a.nama FROM kunjungan k INNER JOIN anggota a ON a.id_anggota = k.id_anggota WHERE k.id_kunjungan = ? LIMIT 1', 'i', [$id]);
    if (!$patient || trim((string) $patient['id_pasien']) === '') {
        conditionResponse('error', 'Kunjungan atau nomor RM pasien tidak ditemukan.');
    }
    return [$id, $category, $patient];
}

function conditionUuid() {
    $bytes = random_bytes(16);
    $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
    $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
}
