<?php

// Koneksi dan session
include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";

date_default_timezone_set("Asia/Jakarta");

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

// Ambil data anggota
$stmt = mysqli_prepare(
    $Conn,
    "SELECT 
        id_anggota,
        tanggal_masuk,
        nik,
        nama,
        email,
        kontak,
        alamat,
        gender
     FROM anggota
     WHERE id_anggota = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id_anggota);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

// Jika data tidak ditemukan
if (!$data) {
    echo '
        <div class="alert alert-danger">
            <small>Data pasien tidak ditemukan.</small>
        </div>
    ';
    exit;
}

// Ambil data
$nik           = htmlspecialchars($data['nik'] ?? '', ENT_QUOTES, 'UTF-8');
$nama          = htmlspecialchars($data['nama'] ?? '', ENT_QUOTES, 'UTF-8');
$email         = htmlspecialchars($data['email'] ?? '', ENT_QUOTES, 'UTF-8');
$kontak        = htmlspecialchars($data['kontak'] ?? '', ENT_QUOTES, 'UTF-8');
$alamat        = htmlspecialchars($data['alamat'] ?? '', ENT_QUOTES, 'UTF-8');
$gender        = $data['gender'] ?? '';
$tanggal_masuk = $data['tanggal_masuk'] ?? '';

?>

<input type="hidden" name="id_anggota" value="<?php echo $id_anggota; ?>">

<div class="row">

    <!-- Nama -->
    <div class="col-md-6 mb-3">
        <label for="edit_nama">
            Nama Lengkap
        </label>
        <input 
            type="text"
            name="nama"
            id="edit_nama"
            class="form-control"
            value="<?php echo $nama; ?>"
            required
        >
    </div>

    <!-- NIK -->
    <div class="col-md-6 mb-3">
        <label for="edit_nik">
            NIK/KTP
        </label>
        <input 
            type="text"
            name="nik"
            id="edit_nik"
            class="form-control"
            value="<?php echo $nik; ?>"
        >
    </div>

    <!-- Gender -->
    <div class="col-md-6 mb-3">
        <label for="edit_gender">
            Jenis Kelamin (Gender)
        </label>

        <select 
            name="gender"
            id="edit_gender"
            class="form-control"
            required
        >
            <option value="">Pilih</option>

            <option 
                value="Male"
                <?php echo ($gender == "Male") ? "selected" : ""; ?>
            >
                Laki-laki
            </option>

            <option 
                value="Female"
                <?php echo ($gender == "Female") ? "selected" : ""; ?>
            >
                Perempuan
            </option>
        </select>
    </div>

    <!-- Kontak -->
    <div class="col-md-6 mb-3">
        <label for="edit_kontak">
            No. Kontak
        </label>
        <input 
            type="text"
            name="kontak"
            id="edit_kontak"
            class="form-control"
            value="<?php echo $kontak; ?>"
            placeholder="62"
        >
    </div>

    <!-- Email -->
    <div class="col-md-6 mb-3">
        <label for="edit_email">
            Email
        </label>
        <input 
            type="email"
            name="email"
            id="edit_email"
            class="form-control"
            value="<?php echo $email; ?>"
            placeholder="email@domain.com"
        >
    </div>

    <!-- Tanggal Masuk -->
    <div class="col-md-6 mb-3">
        <label for="edit_tanggal_masuk">
            Tanggal Masuk
        </label>
        <input 
            type="date"
            name="tanggal_masuk"
            id="edit_tanggal_masuk"
            class="form-control"
            value="<?php echo $tanggal_masuk; ?>"
            required
        >
    </div>

    <!-- Alamat -->
    <div class="col-md-12 mb-3">
        <label for="edit_alamat">
            Alamat
        </label>
        <textarea
            name="alamat"
            id="edit_alamat"
            class="form-control"
            rows="3"
        ><?php echo $alamat; ?></textarea>
    </div>

</div>