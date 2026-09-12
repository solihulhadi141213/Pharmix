<?php
require_once __DIR__.'/../../_Config/Connection.php';
require_once __DIR__.'/../../_Config/GlobalFunction.php';
require_once __DIR__.'/../../_Config/Session.php';
require_once __DIR__.'/ConditionHelper.php';
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('Asia/Jakarta');

if (empty($SessionIdAkses)) {
    echo '<div class="alert alert-danger">Sesi akses sudah berakhir. Silakan login ulang.</div>';
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<div class="alert alert-danger">Metode request tidak valid.</div>';
    exit;
}
$idDiagnosis = filter_var(conditionInput('id_diagnosis'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$idDiagnosis) {
    echo '<div class="alert alert-danger">ID diagnosis wajib diisi dan harus valid.</div>';
    exit;
}
try {
    $data = conditionRow($Conn, 'SELECT d.*, creator.nama_akses AS creator_name, updater.nama_akses AS updater_name
        FROM diagnosis d
        LEFT JOIN akses creator ON creator.id_akses = d.creat_by_id
        LEFT JOIN akses updater ON updater.id_akses = d.update_by_id
        WHERE d.id_diagnosis = ? LIMIT 1', 'i', [$idDiagnosis]);
} catch (Throwable $error) {
    error_log('FormDetailCondition: gagal memuat diagnosis '.(int) $idDiagnosis);
    echo '<div class="alert alert-danger">Gagal memuat detail diagnosis. Silakan coba kembali.</div>';
    exit;
}
if (!$data) {
    echo '<div class="alert alert-warning">Data diagnosis tidak ditemukan.</div>';
    exit;
}

$display = function ($value) {
    if ($value === null || trim((string) $value) === '') return '-';
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$displayDate = function ($value) {
    if (empty($value) || $value === '0000-00-00 00:00:00') return '-';
    $timestamp = strtotime($value);
    return $timestamp === false ? '-' : date('d/m/Y H:i:s', $timestamp).' WIB';
};
// Use the current access name when the ID is present. Retain the saved name
// when the ID is empty or the referenced access record no longer exists.
$creatorName = !empty($data['creat_by_id']) ? ($data['creator_name'] ?? $data['creat_by_name']) : $data['creat_by_name'];
$updaterName = !empty($data['update_by_id']) ? ($data['updater_name'] ?? $data['update_by_name']) : $data['update_by_name'];
$sections = [
    'A. Identitas Diagnosis' => [
        'Kode Diagnosis Lokal' => $data['diagnosis_code'],
        'ID Condition SATUSEHAT' => $data['id_condition'],
        'No. RM Pasien' => $data['id_pasien'],
        'Nama Tenaga Medis' => $data['medicalPersonelName']
    ],
    'B. Informasi Diagnosis' => [
        'Kategori' => $data['category'],
        'Versi ICD' => $data['icd_version'],
        'Kode ICD' => $data['icd_code'],
        'Deskripsi ICD' => $data['icd_description'],
        'Catatan Diagnosis' => $data['diagnosis_text'],
        'Status Kasus' => $data['case_status'],
        'Kepastian Diagnosis' => $data['certainty_status']
    ],
    'C. Informasi Pembuat dan Pengubah' => [
        'Creator' => $creatorName,
        'Waktu Dibuat' => $displayDate($data['creat_at']),
        'Updater' => $updaterName,
        'Waktu Diubah' => $displayDate($data['update_at'])
    ]
];
?>
<?php foreach ($sections as $heading => $fields): ?>
    <div class="row mb-2 mt-3">
        <div class="col-12"><small><b><?= $display($heading) ?></b></small></div>
    </div>
    <?php foreach ($fields as $label => $value): ?>
        <div class="row mb-2">
            <div class="col-5 text-break"><small><?= $display($label) ?></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6 text-start text-break"><small style="white-space: pre-wrap; overflow-wrap: anywhere;"><?= $display($value) ?></small></div>
        </div>
    <?php endforeach; ?>
<?php endforeach; ?>