<?php
     // Koneksi dan session
    require_once __DIR__."/../../_Config/Connection.php";
    require_once __DIR__."/../../_Config/GlobalFunction.php";
    require_once __DIR__."/../../_Config/Session.php";

    // Validasi Sesi Akses
    if (empty($SessionIdAkses)) {
        echo '
            <div class="alert alert-danger text-center">
                <small>
                    <b>Opss!</b><br>
                    Sesi Akses Sudah Berakhir! Silahkan Login Ulang!
                </small>
            </div>
        ';
        exit;
    }

    if(empty($_POST['id'])){
        echo '
            <div class="alert alert-danger text-center">
                <small>
                    <b>Opss!</b><br>
                    ID Kunjungan Tidak Boleh Kosong!
                </small>
            </div>
        ';
        exit;
    }
    // Parameter id dari AttachmentView adalah ID kunjungan, bukan ID grup resep.
    $id_kunjungan = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);
    if ($id_kunjungan === false || $id_kunjungan === null) {
        echo '<div class="alert alert-danger text-center"><small>ID Kunjungan tidak valid.</small></div>';
        return;
    }

    $categories = [
        'Admission' => 'A. Diagnosis Masuk',
        'Provisional' => 'B. Diagnosis Sementara',
        'Primary' => 'C. Diagnosis Utama',
        'Secondary' => 'D. Diagnosis Sekunder',
        'Working' => 'E. Diagnosis Kerja',
        'Differential' => 'F. Diagnosis Banding',
        'Final' => 'G. Diagnosis Akhir'
    ];
    $diagnoses = array_fill_keys(array_keys($categories), []);
    try {
        require_once __DIR__.'/../Condition/ConditionHelper.php';
        $stmtDiagnosis = conditionQuery($Conn,
            'SELECT id_diagnosis, diagnosis_code, id_condition, category, icd_code, icd_description, medicalPersonelName, case_status, certainty_status, creat_at FROM diagnosis WHERE id_kunjungan = ? ORDER BY creat_at DESC, id_diagnosis DESC',
            'i', [$id_kunjungan]);
        $resultDiagnosis = $stmtDiagnosis->get_result();
        while ($diagnosis = $resultDiagnosis->fetch_assoc()) {
            if (isset($diagnoses[$diagnosis['category']])) {
                $diagnoses[$diagnosis['category']][] = $diagnosis;
            }
        }
        $stmtDiagnosis->close();
    } catch (Throwable $error) {
        error_log('Condition: '.$error->getMessage());
        echo '<div class="alert alert-danger"><small>Gagal memuat data diagnosis. Silakan coba kembali.</small></div>';
        return;
    }
    $escapeCondition = function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };
?>
<div class="row mb-3">
    <div class="col-12">
        <b># Diagnosis (<i>Condition</i>)</b>
    </div>
</div>
<?php foreach ($categories as $category => $label): ?>
<hr>
<section data-condition-category="<?= $escapeCondition($category) ?>">
    <div class="row mb-3">
        <div class="col-10">
            <?= $escapeCondition($label) ?> <i>(<?= $escapeCondition($category) ?>)</i>
        </div>
        <div class="col-2 text-end">
            <button type="button" class="btn btn-sm btn-secondary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalTambahCondition" data-id="<?= $id_kunjungan ?>" data-category="<?= $escapeCondition($category) ?>" title="Tambah <?= $escapeCondition($label) ?>">
                <i class="bi bi-plus-lg"></i>
            </button>
        </div>
    </div>
    <div class="row">
        <?php if (empty($diagnoses[$category])): ?>
            <div class="col-12 mb-3"><small class="text-muted">Belum ada diagnosis pada kategori ini.</small></div>
        <?php else: ?>
            <?php foreach ($diagnoses[$category] as $diagnosis):
                $diagnosisId = (int) $diagnosis['id_diagnosis'];
                $timestamp = empty($diagnosis['creat_at']) ? false : strtotime($diagnosis['creat_at']);
                $createdAt = $timestamp === false ? '-' : date('d/m/Y H:i', $timestamp);
                $isFinal = $diagnosis['certainty_status'] === 'Final';
                $conditionId = trim((string) ($diagnosis['id_condition'] ?? ''));
                $fields = [
                    'Code' => $diagnosis['icd_code'],
                    'Description' => $diagnosis['icd_description'],
                    'Dokter' => $diagnosis['medicalPersonelName'],
                    'ID Condition' => $conditionId,
                    'Kasus' => $diagnosis['case_status']
                ];
            ?>
            <div class="col-md-6 mb-3">
                <div class="card h-100 shadow-0 border border-1 border-secondary-subtle">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-10">
                                <small>
                                    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalDetailCondition" data-id="<?= $diagnosisId ?>"><?= $escapeCondition($createdAt) ?></a>
                                </small>
                            </div>
                            <div class="col-2 text-end">
                                <a href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Opsi diagnosis">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                    <li class="dropdown-header text-start"><h6>Option</h6></li>
                                    <li><a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalDetailCondition" data-id="<?= $diagnosisId ?>"><i class="bi bi-info-circle"></i> Detail</a></li>
                                    <li><a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalEditCondition" data-id="<?= $diagnosisId ?>"><i class="bi bi-pencil"></i> Edit</a></li>
                                    <li><a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalHapusCondition" data-id="<?= $diagnosisId ?>"><i class="bi bi-trash"></i> Hapus</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <?php foreach ($fields as $fieldLabel => $fieldValue): ?>
                        <div class="d-flex align-items-baseline mb-2">
                            <small class="flex-shrink-0" style="width: 6rem;"><?= $escapeCondition($fieldLabel) ?></small>
                            <small class="flex-shrink-0 me-2">:</small>
                            <small class="d-block text-truncate <?= $fieldLabel === 'Kasus' ? 'text-success' : 'text-muted' ?>" style="min-width: 0;" title="<?= $escapeCondition($fieldValue) ?>">
                                <?php if ($fieldLabel === 'ID Condition'): ?>
                                    <?php if ($conditionId !== ''): ?>
                                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalDetailIdCondition" data-id="<?= $escapeCondition($conditionId) ?>"><?= $escapeCondition($conditionId) ?></a>
                                    <?php else: ?>
                                        <a href="javascript:void(0);" class="text-danger" data-bs-toggle="modal" data-bs-target="#ModalKirimCondition" data-id="<?= $escapeCondition($diagnosis['diagnosis_code']) ?>">
                                            <i class="bi bi-send"></i> Kirim ke SATUSEHAT
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?= $escapeCondition($fieldValue) ?>
                                <?php endif; ?>
                            </small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-6"><small>Status :</small></div>
                            <div class="col-6 text-end">
                                <span class="badge <?= $isFinal ? 'bg-success' : 'bg-info text-dark' ?>"><small><?= $isFinal ? 'Final' : 'Sementara' ?></small></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
<?php endforeach; ?>
