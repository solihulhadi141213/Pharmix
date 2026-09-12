<?php
require_once __DIR__.'/../../_Config/Connection.php';
require_once __DIR__.'/../../_Config/GlobalFunction.php';
require_once __DIR__.'/../../_Config/Session.php';
require_once __DIR__.'/SatuSehatCondition.php';
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Asia/Jakarta');
if (empty($SessionIdAkses)) conditionResponse('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') conditionResponse('error', 'Metode request tidak valid.');
try {
    include __DIR__.'/../../_Config/FungsiAkses.php';
    if (empty($SessionNama)) conditionResponse('error', 'Data pengguna tidak ditemukan. Silakan login ulang.');
    $diagnosis = conditionSatuSehatDiagnosis($Conn, conditionInput('diagnosis_code'));
    if (!$diagnosis) conditionResponse('error', 'Kode diagnosis wajib diisi dan harus merujuk ke satu data diagnosis yang valid.');
    // All payload fields and eligibility are read again on the server, under a lock.
    $result = syncConditionSatuSehat($Conn, (int) $diagnosis['id_diagnosis'], (int) $SessionIdAkses, $SessionNama);
    conditionResponse($result['status'] === 'skipped' ? 'error' : $result['status'], $result['message'], [
        'id_diagnosis' => (int) $diagnosis['id_diagnosis'],
        'id_kunjungan' => (int) $diagnosis['id_kunjungan'],
        'id_condition' => $result['id_condition'] ?? null,
        'satusehat' => $result
    ]);
} catch (Throwable $error) {
    error_log('ProsesKirimCondition: gagal memproses pengiriman.');
    conditionResponse('error', 'Pengiriman Condition belum dapat diproses. Silakan muat ulang preview.');
}
