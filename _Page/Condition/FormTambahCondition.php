<?php
require_once __DIR__.'/../../_Config/Connection.php';
require_once __DIR__.'/../../_Config/GlobalFunction.php';
require_once __DIR__.'/../../_Config/Session.php';
require_once __DIR__.'/ConditionHelper.php';
header('Content-Type: application/json; charset=utf-8');
if (empty($SessionIdAkses)) {
    conditionResponse('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    conditionResponse('error', 'Metode request tidak valid.');
}
try {
    list($id_kunjungan, $category, $patient) = conditionVisit($Conn);
} catch (Throwable $error) {
    error_log('FormTambahCondition: '.$error->getMessage());
    conditionResponse('error', 'Gagal memuat data kunjungan.');
}
$escape = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
ob_start();
?>
<input type="hidden" name="id_kunjungan" value="<?= $id_kunjungan ?>">
<input type="hidden" name="category" value="<?= $escape($category) ?>">
<input type="hidden" name="icd_version" value="ICD10">
<div class="row g-3">
    <div class="col-md-6">
        <label for="condition_id_pasien" class="form-label">No. RM</label>
        <input type="text" class="form-control" id="condition_id_pasien" name="id_pasien" value="<?= $escape($patient['id_pasien']) ?>" readonly>
        <small class="text-muted"><?= $escape($patient['nama']) ?></small>
    </div>
    <div class="col-md-6">
        <label for="condition_category" class="form-label">Kategori Diagnosis</label>
        <input type="text" class="form-control" id="condition_category" value="<?= $escape($category) ?>" readonly>
    </div>
    <div class="col-12">
        <label for="condition_medical_personel" class="form-label">Tenaga Medis <span class="text-danger">*</span></label>
        <select class="form-select" id="condition_medical_personel" name="medicalPersonelId" required><option value=""></option></select>
        <input type="hidden" id="condition_medical_name" name="medicalPersonelName">
    </div>
    <div class="col-12">
        <label for="condition_icd_code" class="form-label">Diagnosis ICD10 <span class="text-danger">*</span></label>
        <select class="form-select" id="condition_icd_code" name="icd_code" required><option value=""></option></select>
    </div>
    <div class="col-12">
        <label for="condition_icd_description" class="form-label">Deskripsi ICD</label>
        <textarea class="form-control" id="condition_icd_description" name="icd_description" rows="2" readonly></textarea>
    </div>
    <div class="col-12">
        <label for="condition_diagnosis_text" class="form-label">Catatan Diagnosis</label>
        <textarea class="form-control" id="condition_diagnosis_text" name="diagnosis_text" rows="3"></textarea>
    </div>
    <div class="col-md-6">
        <label for="condition_case_status" class="form-label">Status Kasus <span class="text-danger">*</span></label>
        <select class="form-select" id="condition_case_status" name="case_status" required>
            <option value="">Pilih status kasus</option>
            <?php foreach (['Baru', 'Lama', 'Kambuh', 'Kronis'] as $status): ?>
                <option value="<?= $status ?>"><?= $status ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6">
        <label for="condition_certainty_status" class="form-label">Kepastian Diagnosis <span class="text-danger">*</span></label>
        <select class="form-select" id="condition_certainty_status" name="certainty_status" required>
            <option value="">Pilih kepastian diagnosis</option>
            <option value="Provisional">Sementara (Provisional)</option>
            <option value="Final">Final</option>
        </select>
    </div>
</div>
<?php
conditionResponse('success', 'Form berhasil dimuat.', ['html' => ob_get_clean()]);
