<?php
require_once __DIR__.'/../../_Config/Connection.php';
require_once __DIR__.'/../../_Config/GlobalFunction.php';
require_once __DIR__.'/../../_Config/Session.php';
require_once __DIR__.'/ConditionHelper.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (empty($SessionIdAkses)) conditionResponse('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') conditionResponse('error', 'Metode request tidak valid.');
$id = filter_var(conditionInput('id_diagnosis'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) conditionResponse('error', 'ID diagnosis wajib diisi dan harus valid.');
try {
    $data = conditionRow($Conn, 'SELECT id_diagnosis, diagnosis_code, id_pasien, id_condition, icd_code, icd_description, category, medicalPersonelName FROM diagnosis WHERE id_diagnosis = ? LIMIT 1', 'i', [$id]);
    if (!$data) conditionResponse('error', 'Data diagnosis tidak ditemukan.');
    $token = bin2hex(random_bytes(32));
    $_SESSION['hapus_condition'][$id] = $token;
} catch (Throwable $error) {
    error_log('FormHapusCondition: gagal memuat diagnosis.');
    conditionResponse('error', 'Gagal memuat konfirmasi hapus diagnosis.');
}
$escape = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
$fields = ['No. RM' => $data['id_pasien'], 'Kode ICD' => $data['icd_code'], 'Deskripsi' => $data['icd_description'], 'Kategori' => $data['category'], 'Tenaga Medis' => $data['medicalPersonelName']];
ob_start();
?>
<input type="hidden" name="id_diagnosis" value="<?= $id ?>">
<input type="hidden" name="delete_token" value="<?= $escape($token) ?>">
<?php foreach ($fields as $label => $value): ?>
<div class="row mb-2">
    <div class="col-5"><small><?= $escape($label) ?></small></div>
    <div class="col-1"><small>:</small></div>
    <div class="col-6 text-end text-break"><small><?= $escape($value) ?></small></div>
</div>
<?php endforeach; ?>
<?php if (trim((string) $data['id_condition']) !== ''): ?>
<div class="alert alert-info mt-3">
    <small>Diagnosis ini memiliki ID Condition SATUSEHAT <span class="text-break"><?= $escape($data['id_condition']) ?></span>. Sistem akan menandai resource tersebut sebagai <b>entered-in-error</b> sebelum menghapus diagnosis lokal. Jika pembaruan SATUSEHAT gagal, diagnosis lokal tidak dihapus.</small>
</div>
<?php endif; ?>
<div class="alert alert-warning mt-3 mb-0">
    Hapus diagnosis ini dari database aplikasi? Data yang dihapus tidak dapat dikembalikan melalui aplikasi.
</div>
<?php
conditionResponse('success', 'Konfirmasi hapus berhasil dimuat.', ['html' => ob_get_clean()]);
