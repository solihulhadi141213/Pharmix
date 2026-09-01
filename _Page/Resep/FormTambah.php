<?php
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    if (empty($SessionIdAkses)) {
        echo '<div class="alert alert-danger mb-0">Sesi akses sudah berakhir. Silakan login ulang.</div>';
        exit;
    }

    $pasien = mysqli_query($Conn, "SELECT id_anggota, id_pasien, nama FROM anggota ORDER BY nama ASC");
?>
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="id_anggota_resep" class="form-label">Pasien</label>
        <select name="id_anggota" id="id_anggota_resep" class="form-select">
            <option value="">Pilih pasien</option>
            <?php while ($data = mysqli_fetch_assoc($pasien)) { ?>
                <option value="<?php echo (int) $data['id_anggota']; ?>">
                    <?php echo htmlspecialchars($data['id_pasien'] . ' - ' . $data['nama'], ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php } ?>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label for="id_kunjungan_resep" class="form-label">ID Kunjungan</label>
        <input type="number" name="id_kunjungan" id="id_kunjungan_resep" class="form-control" min="1">
    </div>
    <div class="col-md-6 mb-3">
        <label for="priority_resep" class="form-label">Priority <span class="text-danger">*</span></label>
        <select name="priority" id="priority_resep" class="form-select" required>
            <option value="routine">Routine</option>
            <option value="urgent">Urgent</option>
            <option value="asap">ASAP</option>
            <option value="stat">Stat</option>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label for="status_resep" class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status_resep" id="status_resep" class="form-select" required>
            <option value="Draft">Draft</option>
            <option value="Verified">Verified</option>
            <option value="Partially">Partially</option>
            <option value="Completed">Completed</option>
            <option value="Cancelled">Cancelled</option>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label for="dokter_kode_resep" class="form-label">Kode Dokter <span class="text-danger">*</span></label>
        <input type="text" name="dokter_kode" id="dokter_kode_resep" class="form-control" required>
    </div>
    <div class="col-md-6 mb-3">
        <label for="dokter_ihs_resep" class="form-label">IHS Dokter <span class="text-danger">*</span></label>
        <input type="text" name="dokter_ihs" id="dokter_ihs_resep" class="form-control" required>
    </div>
    <div class="col-md-6 mb-3">
        <label for="dokter_nama_resep" class="form-label">Nama Dokter <span class="text-danger">*</span></label>
        <input type="text" name="dokter_nama" id="dokter_nama_resep" class="form-control" required>
    </div>
    <div class="col-md-6 mb-3">
        <label for="sumber_data_resep" class="form-label">Sumber Data <span class="text-danger">*</span></label>
        <input type="text" name="sumber_data" id="sumber_data_resep" class="form-control" value="Pharmix" required>
    </div>
    <div class="col-md-6 mb-3">
        <label for="reason_code_resep" class="form-label">Kode Diagnosa</label>
        <input type="text" name="reason_code" id="reason_code_resep" class="form-control">
    </div>
    <div class="col-md-6 mb-3">
        <label for="reason_display_resep" class="form-label">Diagnosa</label>
        <input type="text" name="reason_display" id="reason_display_resep" class="form-control">
    </div>
    <div class="col-md-12 mb-3">
        <label for="reason_system_resep" class="form-label">Sistem Kode Diagnosa</label>
        <input type="text" name="reason_system" id="reason_system_resep" class="form-control">
    </div>
    <div class="col-md-6 mb-3">
        <label for="apoteker_nama_resep" class="form-label">Nama Apoteker</label>
        <input type="text" name="apoteker_nama" id="apoteker_nama_resep" class="form-control">
    </div>
    <div class="col-md-6 mb-3">
        <label for="apoteker_id_ihs_resep" class="form-label">IHS Apoteker</label>
        <input type="text" name="apoteker_id_ihs" id="apoteker_id_ihs_resep" class="form-control">
    </div>
</div>
