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
        kontak,
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

// Data
$nama   = htmlspecialchars($data['nama'] ?? '', ENT_QUOTES, 'UTF-8');
$nik    = htmlspecialchars($data['nik'] ?? '', ENT_QUOTES, 'UTF-8');
$kontak = htmlspecialchars($data['kontak'] ?? '', ENT_QUOTES, 'UTF-8');

if ($data['gender'] == "Male") {
    $gender = "Laki-laki";
} else {
    $gender = "Perempuan";
}

?>

<input 
    type="hidden" 
    name="id_anggota" 
    value="<?php echo $id_anggota; ?>"
>

<div class="text-center">

    <div class="mb-3">
        <i 
            class="bi bi-exclamation-triangle text-danger"
            style="font-size: 50px;"
        ></i>
    </div>

    <h5 class="text-danger">
        Apakah Anda yakin ingin menghapus data ini?
    </h5>

    <p class="text-muted">
        Data pasien yang sudah dihapus tidak dapat dikembalikan.
    </p>

</div>

<div class="table-responsive">
    <table class="table table-sm table-bordered">

        <tbody>

            <tr>
                <th width="35%">
                    Nama
                </th>
                <td>
                    <?php echo $nama; ?>
                </td>
            </tr>

            <tr>
                <th>
                    NIK/KTP
                </th>
                <td>
                    <?php echo !empty($nik) ? $nik : '-'; ?>
                </td>
            </tr>

            <tr>
                <th>
                    Jenis Kelamin
                </th>
                <td>
                    <?php echo $gender; ?>
                </td>
            </tr>

            <tr>
                <th>
                    No. Kontak
                </th>
                <td>
                    <?php echo !empty($kontak) ? $kontak : '-'; ?>
                </td>
            </tr>

        </tbody>

    </table>
</div>

<div class="alert alert-warning mb-0">
    <i class="bi bi-info-circle"></i>
    <small>
        Pastikan pasien yang akan dihapus sudah benar.
    </small>
</div>