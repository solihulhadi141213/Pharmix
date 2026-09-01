<?php

    // Koneksi dan session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    date_default_timezone_set("Asia/Jakarta");
    function tampil($value){
        return ($value === null || trim($value) === '')
            ? '-'
            : htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    // Validasi session
    if (empty($SessionIdAkses)) {
        echo '
            <div class="alert alert-danger">
                <small>Sesi akses sudah berakhir. Silahkan login ulang.</small>
            </div>
        ';
        exit;
    }

    // Validasi ID
    if (empty($_POST['id_anggota'])) {
        echo '
            <div class="alert alert-danger">
                <small>ID pasien tidak ditemukan.</small>
            </div>
        ';
        exit;
    }

    $id_anggota = (int) $_POST['id_anggota'];

    /* ============================================================
    * QUERY DATABASE
    * ============================================================ */
    $Qry = $Conn->prepare("SELECT * FROM anggota WHERE id_anggota = ?");
    $Qry->bind_param("i", $id_anggota);

    if (!$Qry->execute()) {
        echo '
            <div class="alert alert-danger">
                <small>Terjadi kesalahan saat membuka data!<br>
                Keterangan : ' . htmlspecialchars($Conn->error) . '</small>
            </div>
        ';
        exit;
    }

    $Result = $Qry->get_result();
    $Data   = $Result->fetch_assoc();
    $Qry->close();

    if (!$Data) {
        echo '
            <div class="alert alert-warning">
                <small>Data pasien tidak ditemukan.</small>
            </div>
        ';
        exit;
    }

    /* ============================================================
    * MAPPING DATA
    * ============================================================ */
    $id_pasien     = $Data['id_pasien'] ?? null;
    $id_ihs        = $Data['id_ihs'] ?? null;
    $nik           = $Data['nik'] ?? null;
    $nama          = $Data['nama'] ?? null;
    $email         = $Data['email'] ?? null;
    $kontak        = $Data['kontak'] ?? null;
    $alamat        = $Data['alamat'] ?? null;
    $gender        = $Data['gender'] ?? null;
    $tempat_lahir  = $Data['tempat_lahir'] ?? null;
    $tanggal_lahir = $Data['tanggal_lahir'] ?? null;

    // Metadata
    $creat_at       = $Data['creat_at'] ?? null;
    $creat_by_id    = $Data['creat_by_id'] ?? null;
    $creat_by_name  = $Data['creat_by_name'] ?? null;
    $update_at      = $Data['update_at'] ?? null;
    $update_by_id   = $Data['update_by_id'] ?? null;
    $update_by_name = $Data['update_by_name'] ?? null;

    // Creat By ID
    if(empty($creat_by_id)){
        $creator = GetDetailData($Conn, 'akses', 'id_akses', $creat_by_id, 'nama_akses');
    }else{
        $creator = $creat_by_name;
    }

    // Update By ID
    if(empty($update_by_id)){
        $updater = GetDetailData($Conn, 'akses', 'id_akses', $update_by_id, 'nama_akses');
    }else{
        $updater = $creat_by_name;
    }

?>

<input type="hidden" name="id_anggota" value="<?php echo $id_anggota; ?>">
<div class="row mb-3">
    <div class="col-md-12">
        <label for="id_pasien_edit">* No.RM</label>
        <input type="text" name="id_pasien" id="id_pasien_edit" class="form-control" value="<?php echo $id_pasien; ?>" required>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-12">
        <label for="nama_edit">* Nama Lengkap</label>
        <input type="text" name="nama" id="nama_edit" class="form-control" value="<?php echo $nama; ?>" required>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-12">
        <label for="nik_edit">NIK/KTP</label>
        <input type="text" name="nik" id="nik_edit" class="form-control" value="<?php echo $nik; ?>">
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-12">
        <label for="id_ihs_edit">ID IHS</label>
        <div class="input-group">
            <input type="text" name="id_ihs" id="id_ihs_edit" class="form-control" value="<?php echo $id_ihs; ?>">
            <button class="btn btn-secondary" type="button" id="cari_ihs_edit">
                <i class="bi bi-cloud"></i> Cari
            </button>
        </div>
        <small>ID Patient Dari Satusehat</small>
    </div>
</div>
<div id="notifikasi_pencarian_ihs_edit">
    <!-- Notifikasi Pencarian IHS -->
</div>
<div class="row mb-3">
    <div class="col-md-12">
        <label for="gender_edit">*Jenis Kelamin (Gender)</label>
        <select name="gender" id="gender_edit" class="form-control" required>
            <option <?php if($gender==""){echo "selected";} ?> value="">Pilih</option>
            <option <?php if($gender=="Male"){echo "selected";} ?> value="Male">Laki-laki</option>
            <option <?php if($gender=="Female"){echo "selected";} ?> value="Female">Perempuan</option>
        </select>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-12">
        <label for="tempat_lahir_edit">Tempat Lahir</label>
        <input type="text" name="tempat_lahir" id="tempat_lahir_edit" class="form-control" value="<?php echo $tempat_lahir; ?>">
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-12">
        <label for="tanggal_lahir_edit">Tanggal Lahir</label>
        <input type="date" name="tanggal_lahir" id="tanggal_lahir_edit" class="form-control" value="<?php echo $tanggal_lahir; ?>">
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-12">
        <label for="kontak_edit">No.Kontak</label>
        <input type="text" name="kontak" id="kontak_edit" class="form-control" placeholder="62" value="<?php echo $kontak; ?>">
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-12">
        <label for="alamat_edit">Alamat Tinggal</label>
        <textarea name="alamat" id="alamat_edit" class="form-control"><?php echo $alamat; ?></textarea>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-12">
        <label for="email_edit">Email</label>
        <input type="email" name="email" id="email_edit" class="form-control" placeholder="email@domain.com" value="<?php echo $email; ?>">
    </div>
</div>