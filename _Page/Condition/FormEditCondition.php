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
    $idDiagnosis = filter_var(conditionInput('id_diagnosis'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$idDiagnosis) conditionResponse('error', 'ID diagnosis wajib diisi dan harus valid.');
    $data = conditionRow($Conn, 'SELECT d.*, a.nama AS nama_pasien FROM diagnosis d LEFT JOIN anggota a ON a.id_pasien = d.id_pasien WHERE d.id_diagnosis = ? LIMIT 1', 'i', [$idDiagnosis]);
    if (!$data) conditionResponse('error', 'Data diagnosis tidak ditemukan.');
    $id_kunjungan = (int) $data['id_kunjungan'];
    $category = $data['category'];
    $patient = ['id_pasien' => $data['id_pasien'], 'nama' => $data['nama_pasien'] ?? ''];
    $isIcd10 = $data['icd_version'] === 'ICD10';
} catch (Throwable $error) {
    error_log('FormEditCondition: '.$error->getMessage());
    conditionResponse('error', 'Gagal memuat data diagnosis.');
}
$escape = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
ob_start();
?>
<input type="hidden" name="id_diagnosis" value="<?= $idDiagnosis ?>">
<input type="hidden" name="diagnosis_code" value="<?= $escape($data['diagnosis_code']) ?>">
<input type="hidden" name="id_kunjungan" value="<?= $id_kunjungan ?>">
<input type="hidden" name="category" value="<?= $escape($category) ?>">
<input type="hidden" name="icd_version" value="ICD10">
<div class="row g-3">
    <div class="col-md-6">
        <label for="edit_condition_id_pasien" class="form-label">No. RM</label>
        <input type="text" class="form-control" id="edit_condition_id_pasien" name="id_pasien" value="<?= $escape($patient['id_pasien']) ?>" readonly>
        <small class="text-muted"><?= $escape($patient['nama']) ?></small>
    </div>
    <div class="col-md-6">
        <label for="edit_condition_category" class="form-label">Kategori Diagnosis</label>
        <input type="text" class="form-control" id="edit_condition_category" value="<?= $escape($category) ?>" readonly>
    </div>
    <div class="col-12">
        <label for="edit_condition_medical_personel" class="form-label">Tenaga Medis <span class="text-danger">*</span></label>
        <select class="form-select" id="edit_condition_medical_personel" name="medicalPersonelId" required>
            <option value=""></option>
            <?php if (!empty($data['medicalPersonelId'])): ?>
            <option value="<?= $escape($data['medicalPersonelId']) ?>" selected><?= $escape($data['medicalPersonelName']) ?></option>
            <?php endif; ?>
        </select>
        <input type="hidden" id="edit_condition_medical_name" name="medicalPersonelName" value="<?= $escape($data['medicalPersonelName']) ?>">
    </div>
    <div class="col-12">
        <label for="edit_condition_icd_code" class="form-label">Diagnosis ICD10 <span class="text-danger">*</span></label>
        <select class="form-select" id="edit_condition_icd_code" name="icd_code" required>
            <option value=""></option>
            <?php if ($isIcd10 && trim($data['icd_code']) !== ''): ?>
            <option value="<?= $escape($data['icd_code']) ?>" selected><?= $escape($data['icd_code'].' - '.$data['icd_description']) ?></option>
            <?php endif; ?>
        </select>
        <?php if (!$isIcd10): ?>
        <small class="text-warning">Diagnosis sebelumnya menggunakan <?= $escape($data['icd_version']) ?>. Pilih kode ICD10 untuk form ini.</small>
        <?php endif; ?>
    </div>
    <div class="col-12">
        <label for="edit_condition_icd_description" class="form-label">Deskripsi ICD</label>
        <textarea class="form-control" id="edit_condition_icd_description" name="icd_description" rows="2" readonly><?= $escape($isIcd10 ? $data['icd_description'] : '') ?></textarea>
    </div>
    <div class="col-12">
        <label for="edit_condition_diagnosis_text" class="form-label">Catatan Diagnosis</label>
        <textarea class="form-control" id="edit_condition_diagnosis_text" name="diagnosis_text" rows="3"><?= $escape($data['diagnosis_text']) ?></textarea>
    </div>
    <div class="col-md-6">
        <label for="edit_condition_case_status" class="form-label">Status Kasus <span class="text-danger">*</span></label>
        <select class="form-select" id="edit_condition_case_status" name="case_status" required>
            <option value="">Pilih status kasus</option>
            <?php foreach (['Baru', 'Lama', 'Kambuh', 'Kronis'] as $status): ?>
                <option value="<?= $status ?>" <?= $data['case_status'] === $status ? 'selected' : '' ?>><?= $status ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6">
        <label for="edit_condition_certainty_status" class="form-label">Kepastian Diagnosis <span class="text-danger">*</span></label>
        <select class="form-select" id="edit_condition_certainty_status" name="certainty_status" required>
            <option value="">Pilih kepastian diagnosis</option>
            <option value="Provisional" <?= $data['certainty_status'] === 'Provisional' ? 'selected' : '' ?>>Sementara (Provisional)</option>
            <option value="Final" <?= $data['certainty_status'] === 'Final' ? 'selected' : '' ?>>Final</option>
        </select>
    </div>
</div>
<?php
conditionResponse('success', 'Form berhasil dimuat.', ['html' => ob_get_clean()]);
