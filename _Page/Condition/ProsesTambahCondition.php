<?php
require_once __DIR__.'/../../_Config/Connection.php';
require_once __DIR__.'/../../_Config/GlobalFunction.php';
require_once __DIR__.'/../../_Config/Session.php';
require_once __DIR__.'/ConditionHelper.php';
require_once __DIR__.'/SatuSehatCondition.php';
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Asia/Jakarta');
if (empty($SessionIdAkses)) {
    conditionResponse('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    conditionResponse('error', 'Metode request tidak valid.');
}
try {
    include __DIR__.'/../../_Config/FungsiAkses.php';
    if (empty($SessionNama)) {
        conditionResponse('error', 'Data pengguna tidak ditemukan. Silakan login ulang.');
    }
    list($id_kunjungan, $category, $patient) = conditionVisit($Conn);
    $personelId = filter_var(conditionInput('medicalPersonelId'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $icdCode = conditionInput('icd_code');
    $caseStatus = conditionInput('case_status');
    $certaintyStatus = conditionInput('certainty_status');
    $diagnosisText = conditionInput('diagnosis_text');
    if (!$personelId || $icdCode === '') {
        conditionResponse('error', 'Tenaga medis dan kode ICD10 wajib dipilih.');
    }
    if (!in_array($caseStatus, ['Baru', 'Lama', 'Kambuh', 'Kronis'], true) || !in_array($certaintyStatus, ['Provisional', 'Final'], true)) {
        conditionResponse('error', 'Status kasus dan kepastian diagnosis wajib diisi dengan pilihan yang valid.');
    }
    if (strlen($diagnosisText) > 65535) {
        conditionResponse('error', 'Catatan diagnosis terlalu panjang.');
    }
    $personel = conditionRow($Conn, "SELECT medicalPersonelName FROM medical_personel WHERE medicalPersonelId = ? AND medicalPersonelStatus = 'Active' LIMIT 1", 'i', [$personelId]);
    $icd = conditionRow($Conn, "SELECT kode, long_des FROM icd WHERE kode = ? AND icd = 'ICD10' LIMIT 1", 's', [$icdCode]);
    if (!$personel || !$icd) {
        conditionResponse('error', 'Tenaga medis aktif atau kode ICD10 tidak ditemukan. Silakan pilih ulang.');
    }
    // Generate on the server; never accept UUID or audit values from the browser.
    do {
        $uuid = conditionUuid();
        $existing = conditionRow($Conn, 'SELECT id_diagnosis FROM diagnosis WHERE diagnosis_code = ? LIMIT 1', 's', [$uuid]);
    } while ($existing);
    $now = date('Y-m-d H:i:s');
    $stmt = conditionQuery($Conn, 'INSERT INTO diagnosis (diagnosis_code, id_kunjungan, id_pasien, medicalPersonelId, medicalPersonelName, category, icd_version, icd_code, icd_description, diagnosis_text, case_status, certainty_status, creat_at, creat_by_id, creat_by_name, update_at, update_by_id, update_by_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        'sisisssssssssissis', [$uuid, $id_kunjungan, $patient['id_pasien'], $personelId, $personel['medicalPersonelName'], $category, 'ICD10', $icd['kode'], $icd['long_des'], $diagnosisText, $caseStatus, $certaintyStatus, $now, $SessionIdAkses, $SessionNama, $now, $SessionIdAkses, $SessionNama]);
    $id = $Conn->insert_id;
    $stmt->close();
} catch (Throwable $error) {
    error_log('ProsesTambahCondition: '.$error->getMessage());
    conditionResponse('error', 'Diagnosis gagal disimpan. Silakan coba kembali.');
}

$satusehat = syncConditionSatuSehat($Conn, $id, (int) $SessionIdAkses, $SessionNama);
$message = 'Diagnosis berhasil disimpan. ';
if ($satusehat['status'] === 'success') {
    $message .= $satusehat['message'];
} elseif ($satusehat['status'] === 'skipped') {
    $message .= 'Belum dikirim ke SATUSEHAT: '.$satusehat['message'];
} elseif ($satusehat['status'] === 'error') {
    $message .= 'Pengiriman SATUSEHAT belum berhasil: '.$satusehat['message'];
} else {
    $message .= $satusehat['message'];
}
conditionResponse('success', $message, [
    'id_diagnosis' => $id,
    'diagnosis_code' => $uuid,
    'id_kunjungan' => $id_kunjungan,
    'id_condition' => $satusehat['id_condition'] ?? null,
    'satusehat' => $satusehat
]);
