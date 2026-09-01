<?php
    include "../../_Config/Connection.php";
    include "../../_Config/Session.php";

    if (empty($SessionIdAkses)) {
        echo '<div class="alert alert-danger mb-0">Sesi akses sudah berakhir. Silakan login ulang.</div>';
        exit;
    }

    $idGroup = (int) ($_POST['id_medication_request_group'] ?? 0);
    if ($idGroup <= 0) {
        echo '<div class="alert alert-danger mb-0">ID resep tidak valid.</div>';
        exit;
    }

    $stmt = $Conn->prepare("SELECT * FROM medication_request_group WHERE id_medication_request_group = ? LIMIT 1");
    $stmt->bind_param('i', $idGroup);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$data) {
        echo '<div class="alert alert-warning mb-0">Data resep tidak ditemukan.</div>';
        exit;
    }

    function formValue($value)
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    $pasien = mysqli_query($Conn, "SELECT id_anggota, id_pasien, nama FROM anggota ORDER BY nama ASC");
    $priorities = ['routine', 'urgent', 'asap', 'stat'];
    $statuses = ['Draft', 'Verified', 'Partially', 'Completed', 'Cancelled'];
?>
<input type="hidden" name="id_medication_request_group" value="<?php echo $idGroup; ?>">
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="id_anggota_edit_resep" class="form-label">Pasien</label>
        <select name="id_anggota" id="id_anggota_edit_resep" class="form-select">
            <option value="">Pilih pasien</option>
            <?php while ($pasienData = mysqli_fetch_assoc($pasien)) { ?>
                <option value="<?php echo (int) $pasienData['id_anggota']; ?>" <?php echo ((int) $data['id_anggota'] === (int) $pasienData['id_anggota']) ? 'selected' : ''; ?>>
                    <?php echo formValue($pasienData['id_pasien'] . ' - ' . $pasienData['nama']); ?>
                </option>
            <?php } ?>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label for="id_kunjungan_edit_resep" class="form-label">ID Kunjungan</label>
        <input type="number" name="id_kunjungan" id="id_kunjungan_edit_resep" class="form-control" min="1" value="<?php echo formValue($data['id_kunjungan']); ?>">
    </div>
    <div class="col-md-6 mb-3">
        <label for="priority_edit_resep" class="form-label">Priority <span class="text-danger">*</span></label>
        <select name="priority" id="priority_edit_resep" class="form-select" required>
            <?php foreach ($priorities as $priority) { ?><option value="<?php echo $priority; ?>" <?php echo $data['priority'] === $priority ? 'selected' : ''; ?>><?php echo strtoupper($priority); ?></option><?php } ?>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label for="status_edit_resep" class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status_resep" id="status_edit_resep" class="form-select" required>
            <?php foreach ($statuses as $status) { ?><option value="<?php echo $status; ?>" <?php echo $data['status_resep'] === $status ? 'selected' : ''; ?>><?php echo $status; ?></option><?php } ?>
        </select>
    </div>
    <div class="col-md-6 mb-3"><label class="form-label">Kode Dokter <span class="text-danger">*</span></label><input type="text" name="dokter_kode" class="form-control" value="<?php echo formValue($data['dokter_kode']); ?>" required></div>
    <div class="col-md-6 mb-3"><label class="form-label">IHS Dokter <span class="text-danger">*</span></label><input type="text" name="dokter_ihs" class="form-control" value="<?php echo formValue($data['dokter_ihs']); ?>" required></div>
    <div class="col-md-6 mb-3"><label class="form-label">Nama Dokter <span class="text-danger">*</span></label><input type="text" name="dokter_nama" class="form-control" value="<?php echo formValue($data['dokter_nama']); ?>" required></div>
    <div class="col-md-6 mb-3"><label class="form-label">Sumber Data <span class="text-danger">*</span></label><input type="text" name="sumber_data" class="form-control" value="<?php echo formValue($data['sumber_data']); ?>" required></div>
    <div class="col-md-6 mb-3"><label class="form-label">Kode Diagnosa</label><input type="text" name="reason_code" class="form-control" value="<?php echo formValue($data['reason_code']); ?>"></div>
    <div class="col-md-6 mb-3"><label class="form-label">Diagnosa</label><input type="text" name="reason_display" class="form-control" value="<?php echo formValue($data['reason_display']); ?>"></div>
    <div class="col-md-12 mb-3"><label class="form-label">Sistem Kode Diagnosa</label><input type="text" name="reason_system" class="form-control" value="<?php echo formValue($data['reason_system']); ?>"></div>
    <div class="col-md-6 mb-3"><label class="form-label">Nama Apoteker</label><input type="text" name="apoteker_nama" class="form-control" value="<?php echo formValue($data['apoteker_nama']); ?>"></div>
    <div class="col-md-6 mb-3"><label class="form-label">IHS Apoteker</label><input type="text" name="apoteker_id_ihs" class="form-control" value="<?php echo formValue($data['apoteker_id_ihs']); ?>"></div>
</div>
