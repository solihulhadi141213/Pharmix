<?php
require_once __DIR__.'/../../_Config/Connection.php';
require_once __DIR__.'/../../_Config/GlobalFunction.php';
require_once __DIR__.'/../../_Config/Session.php';
require_once __DIR__.'/SatuSehatCondition.php';
header('Content-Type: application/json; charset=utf-8');
if (empty($SessionIdAkses)) conditionResponse('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') conditionResponse('error', 'Metode request tidak valid.');
try {
    $code = conditionInput('diagnosis_code');
    $diagnosis = conditionSatuSehatDiagnosis($Conn, $code);
    if (!$diagnosis) conditionResponse('error', 'Kode diagnosis wajib diisi dan harus merujuk ke satu data diagnosis yang valid.');
    $preview = conditionSatuSehatPreview($Conn, (int) $diagnosis['id_diagnosis']);
    $data = $preview['data'];
    $payloadJson = json_encode($preview['payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    error_log('FormKirimCondition: gagal memuat preview.');
    conditionResponse('error', 'Gagal memuat preview Condition. Silakan coba kembali.');
}
$escape = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
$parameters = [
    'Kode diagnosis lokal' => $data['diagnosis_code'],
    'Kategori lokal' => $data['category'],
    'Pasien' => $data['nama_pasien'],
    'ID IHS pasien' => $data['id_ihs'],
    'ID Encounter' => $data['id_encounter'],
    'Kode ICD10' => $data['icd_code'],
    'Deskripsi ICD10' => $data['icd_description'],
    'Status klinis' => 'Active',
    'Kategori Condition' => 'Encounter Diagnosis',
    'Status verifikasi' => $preview['payload']['verificationStatus']['coding'][0]['display'],
    'Catatan diagnosis' => $data['diagnosis_text'],
    'ID Condition tersimpan' => $data['id_condition']
];
ob_start();
?>
<input type="hidden" name="diagnosis_code" value="<?= $escape($data['diagnosis_code']) ?>">
<div class="alert <?= $preview['eligible'] ? 'alert-success' : 'alert-warning' ?>">
    <?= $preview['eligible'] ? 'Data memenuhi syarat untuk dikirim ke SATUSEHAT.' : 'Data belum dapat dikirim. Periksa persyaratan berikut.' ?>
</div>
<ul class="list-group mb-3">
    <?php foreach ($preview['checks'] as $check): ?>
    <li class="list-group-item d-flex justify-content-between gap-2">
        <span><?= $escape($check['label']) ?></span>
        <span class="<?= $check['valid'] ? 'text-success' : 'text-danger' ?> flex-shrink-0"><?= $check['valid'] ? 'Terpenuhi' : 'Tidak terpenuhi' ?></span>
    </li>
    <?php endforeach; ?>
</ul>
<div class="table-responsive">
    <table class="table table-sm">
        <tbody>
        <?php foreach ($parameters as $label => $value): ?>
            <tr><th scope="row"><?= $escape($label) ?></th><td class="text-break"><?= $escape(trim((string) $value) !== '' ? $value : '-') ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<details class="mb-3">
    <summary>Lihat payload JSON Condition</summary>
    <pre class="border rounded p-3 mt-2" style="white-space: pre-wrap; overflow-wrap: anywhere;"><?= $escape($payloadJson) ?></pre>
</details>
<small class="text-muted">Token akses diperiksa saat pengiriman. Penerimaan data tetap mengikuti validasi SATUSEHAT.</small>
<?php
conditionResponse('success', 'Preview Condition berhasil dimuat.', [
    'html' => ob_get_clean(), 'eligible' => $preview['eligible'], 'id_kunjungan' => (int) $diagnosis['id_kunjungan']
]);
