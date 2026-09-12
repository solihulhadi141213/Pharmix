<?php
require_once __DIR__.'/../../_Config/Connection.php';
require_once __DIR__.'/../../_Config/GlobalFunction.php';
require_once __DIR__.'/../../_Config/Session.php';
require_once __DIR__.'/ConditionEditHelper.php';
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Asia/Jakarta');
if (empty($SessionIdAkses)) conditionResponse('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') conditionResponse('error', 'Metode request tidak valid.');
try {
    include __DIR__.'/../../_Config/FungsiAkses.php';
    if (empty($SessionNama)) conditionResponse('error', 'Data pengguna tidak ditemukan. Silakan login ulang.');
    $input = [];
    foreach (['id_diagnosis', 'medicalPersonelId', 'icd_code', 'diagnosis_text', 'case_status', 'certainty_status'] as $key) $input[$key] = conditionInput($key);
    $result = editConditionDiagnosis($Conn, $input, (int) $SessionIdAkses, $SessionNama);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    error_log('ProsesEditCondition: gagal memproses permintaan.');
    conditionResponse('error', 'Perubahan diagnosis belum dapat diproses.');
}
