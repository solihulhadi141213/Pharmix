<?php

// ============================================================
// KONFIGURASI
// ============================================================
date_default_timezone_set('Asia/Jakarta');

include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/SettingGeneral.php";
include "../../_Config/Session.php";


// ============================================================
// HELPER ERROR
// ============================================================
function showError($message)
{
    echo '
        <div class="row">
            <div class="col-md-12 mb-3 text-center">
                <small class="text-danger">
                    ' . $message . '
                </small>
            </div>
        </div>
    ';

    exit;
}


// ============================================================
// VALIDASI SESSION
// ============================================================
if (empty($SessionIdAkses)) {
    showError('Sesi Akses Sudah Berakhir, Silahkan Login Ulang');
}


// ============================================================
// VALIDASI ID
// ============================================================
$id_transaksi_jenis = (int) ($_POST['id_transaksi_jenis'] ?? 0);

if ($id_transaksi_jenis <= 0) {
    showError('ID Jenis Transaksi Tidak Valid!');
}


// ============================================================
// QUERY DATA
// ============================================================
// Mengambil data transaksi jenis beserta:
// - Akun Debet
// - Akun Kredit
// - Akun Utang/Piutang
// ============================================================

$sql = "
    SELECT
        tj.id_transaksi_jenis,
        tj.nama,
        tj.kategori,
        tj.deskripsi,

        tj.id_akun_debet,
        tj.id_akun_kredit,
        tj.id_utang_piutang,

        -- AKUN DEBET
        ad.kode AS kode_akun_debet,
        ad.nama AS nama_akun_debet,
        ad.saldo_normal AS saldo_normal_debet,

        -- AKUN KREDIT
        ak.kode AS kode_akun_kredit,
        ak.nama AS nama_akun_kredit,
        ak.saldo_normal AS saldo_normal_kredit,

        -- AKUN UTANG / PIUTANG
        aup.kode AS kode_akun_utang_piutang,
        aup.nama AS nama_akun_utang_piutang,
        aup.saldo_normal AS saldo_normal_utang_piutang

    FROM transaksi_jenis AS tj

    LEFT JOIN akun_perkiraan AS ad
        ON ad.id_perkiraan = tj.id_akun_debet

    LEFT JOIN akun_perkiraan AS ak
        ON ak.id_perkiraan = tj.id_akun_kredit

    LEFT JOIN akun_perkiraan AS aup
        ON aup.id_perkiraan = tj.id_utang_piutang

    WHERE tj.id_transaksi_jenis = ?

    LIMIT 1
";

$stmt = $Conn->prepare($sql);

if (!$stmt) {

    showError(
        '<b>Opsss!</b> Terjadi kesalahan pada saat mempersiapkan query.<br>' .
        htmlspecialchars(
            $Conn->error,
            ENT_QUOTES,
            'UTF-8'
        )
    );
}


// ============================================================
// BIND PARAMETER
// ============================================================
$stmt->bind_param(
    "i",
    $id_transaksi_jenis
);


// ============================================================
// EXECUTE
// ============================================================
if (!$stmt->execute()) {

    $error = htmlspecialchars(
        $stmt->error,
        ENT_QUOTES,
        'UTF-8'
    );

    $stmt->close();

    showError(
        '<b>Opsss!</b> Terjadi kesalahan pada saat mengambil data.<br>' .
        $error
    );
}


// ============================================================
// AMBIL DATA
// ============================================================
$result = $stmt->get_result();

$data = $result->fetch_assoc();

$stmt->close();


// ============================================================
// VALIDASI DATA
// ============================================================
if (!$data) {
    showError('Data jenis transaksi tidak ditemukan.');
}


// ============================================================
// DATA TRANSAKSI
// ============================================================
$id_transaksi_jenis = (int) (
    $data['id_transaksi_jenis'] ?? 0
);

$nama = htmlspecialchars(
    $data['nama'] ?? '',
    ENT_QUOTES,
    'UTF-8'
);

$kategori = htmlspecialchars(
    $data['kategori'] ?? '',
    ENT_QUOTES,
    'UTF-8'
);

$deskripsi = htmlspecialchars(
    $data['deskripsi'] ?? '',
    ENT_QUOTES,
    'UTF-8'
);


// ============================================================
// AKUN DEBET
// ============================================================
$id_akun_debet = (int) (
    $data['id_akun_debet'] ?? 0
);

$kode_akun_debet = htmlspecialchars(
    $data['kode_akun_debet'] ?? '',
    ENT_QUOTES,
    'UTF-8'
);

$nama_akun_debet = htmlspecialchars(
    $data['nama_akun_debet'] ?? '',
    ENT_QUOTES,
    'UTF-8'
);

$saldo_normal_debet = htmlspecialchars(
    $data['saldo_normal_debet'] ?? '',
    ENT_QUOTES,
    'UTF-8'
);


// Format Text Akun Debet
$text_akun_debet = '';

if ($kode_akun_debet !== '') {
    $text_akun_debet .= $kode_akun_debet;
}

if ($nama_akun_debet !== '') {

    if ($text_akun_debet !== '') {
        $text_akun_debet .= ' - ';
    }

    $text_akun_debet .= $nama_akun_debet;
}

if ($saldo_normal_debet !== '') {
    $text_akun_debet .=
        ' (' . $saldo_normal_debet . ')';
}


// ============================================================
// AKUN KREDIT
// ============================================================
$id_akun_kredit = (int) (
    $data['id_akun_kredit'] ?? 0
);

$kode_akun_kredit = htmlspecialchars(
    $data['kode_akun_kredit'] ?? '',
    ENT_QUOTES,
    'UTF-8'
);

$nama_akun_kredit = htmlspecialchars(
    $data['nama_akun_kredit'] ?? '',
    ENT_QUOTES,
    'UTF-8'
);

$saldo_normal_kredit = htmlspecialchars(
    $data['saldo_normal_kredit'] ?? '',
    ENT_QUOTES,
    'UTF-8'
);


// Format Text Akun Kredit
$text_akun_kredit = '';

if ($kode_akun_kredit !== '') {
    $text_akun_kredit .= $kode_akun_kredit;
}

if ($nama_akun_kredit !== '') {

    if ($text_akun_kredit !== '') {
        $text_akun_kredit .= ' - ';
    }

    $text_akun_kredit .= $nama_akun_kredit;
}

if ($saldo_normal_kredit !== '') {
    $text_akun_kredit .=
        ' (' . $saldo_normal_kredit . ')';
}


// ============================================================
// AKUN UTANG / PIUTANG
// ============================================================
$id_utang_piutang = (int) (
    $data['id_utang_piutang'] ?? 0
);

$kode_akun_utang_piutang = htmlspecialchars(
    $data['kode_akun_utang_piutang'] ?? '',
    ENT_QUOTES,
    'UTF-8'
);

$nama_akun_utang_piutang = htmlspecialchars(
    $data['nama_akun_utang_piutang'] ?? '',
    ENT_QUOTES,
    'UTF-8'
);

$saldo_normal_utang_piutang = htmlspecialchars(
    $data['saldo_normal_utang_piutang'] ?? '',
    ENT_QUOTES,
    'UTF-8'
);


// Format Text Akun Utang / Piutang
$text_akun_utang_piutang = '';

if ($kode_akun_utang_piutang !== '') {
    $text_akun_utang_piutang .=
        $kode_akun_utang_piutang;
}

if ($nama_akun_utang_piutang !== '') {

    if ($text_akun_utang_piutang !== '') {
        $text_akun_utang_piutang .= ' - ';
    }

    $text_akun_utang_piutang .=
        $nama_akun_utang_piutang;
}

if ($saldo_normal_utang_piutang !== '') {
    $text_akun_utang_piutang .=
        ' (' . $saldo_normal_utang_piutang . ')';
}

?>

<!-- ========================================================
     ID
========================================================= -->
<input
    type="hidden"
    name="id_transaksi_jenis"
    value="<?php echo $id_transaksi_jenis; ?>"
>


<!-- ========================================================
     NAMA
========================================================= -->
<div class="row mb-3">

    <div class="col-md-4">
        <label for="nama_edit">
            <small>Jenis Transaksi</small>
        </label>
    </div>

    <div class="col-md-8">

        <input
            type="text"
            name="nama"
            id="nama_edit"
            class="form-control"
            value="<?php echo $nama; ?>"
        >

        <small class="text-muted">
            Nama jenis transaksi
            (Contoh: Iuran Air dan Listrik, Gaji Staf, ATK, dll)
        </small>

    </div>

</div>


<!-- ========================================================
     KATEGORI
========================================================= -->
<div class="row mb-3">

    <div class="col-md-4">
        <label for="kategori_edit">
            <small>Kategori Transaksi</small>
        </label>
    </div>

    <div class="col-md-8">

        <select
            name="kategori"
            id="kategori_edit"
            class="form-select"
        >
            <option value="">Pilih</option>

            <option
                value="Pengeluaran"
                <?php echo ($kategori === 'Pengeluaran') ? 'selected' : ''; ?>
            >
                Pengeluaran
            </option>

            <option
                value="Pemasukan"
                <?php echo ($kategori === 'Pemasukan') ? 'selected' : ''; ?>
            >
                Pemasukan
            </option>

        </select>

    </div>

</div>


<!-- ========================================================
     DESKRIPSI
========================================================= -->
<div class="row mb-3">

    <div class="col-md-4">
        <label for="deskripsi_edit">
            <small>Deskripsi</small>
        </label>
    </div>

    <div class="col-md-8">

        <textarea
            name="deskripsi"
            id="deskripsi_edit"
            class="form-control"
        ><?php echo $deskripsi; ?></textarea>

        <small class="text-muted">
            Gambaran singkat mengenai transaksi tersebut.
        </small>

    </div>

</div>


<!-- ========================================================
     AKUN DEBET
========================================================= -->
<div class="row mb-3">

    <div class="col-md-4">
        <label for="id_akun_debet_edit">
            <small>Akun Perkiraan (Debet)</small>
        </label>
    </div>

    <div class="col-md-8">

        <select
            name="id_akun_debet"
            id="id_akun_debet_edit"
            class="form-select"
            style="width: 100%;"
        >

            <?php if ($id_akun_debet > 0): ?>

                <option
                    value="<?php echo $id_akun_debet; ?>"
                    selected
                >
                    <?php echo $text_akun_debet; ?>
                </option>

            <?php else: ?>

                <option value=""></option>

            <?php endif; ?>

        </select>

        <small class="text-muted">
            Pengaturan akun perkiraan yang akan digunakan
            pada lajur debet.
        </small>

    </div>

</div>


<!-- ========================================================
     AKUN KREDIT
========================================================= -->
<div class="row mb-3">

    <div class="col-md-4">
        <label for="id_akun_kredit_edit">
            <small>Akun Perkiraan (Kredit)</small>
        </label>
    </div>

    <div class="col-md-8">

        <select
            name="id_akun_kredit"
            id="id_akun_kredit_edit"
            class="form-select"
            style="width: 100%;"
        >

            <?php if ($id_akun_kredit > 0): ?>

                <option
                    value="<?php echo $id_akun_kredit; ?>"
                    selected
                >
                    <?php echo $text_akun_kredit; ?>
                </option>

            <?php else: ?>

                <option value=""></option>

            <?php endif; ?>

        </select>

        <small class="text-muted">
            Pengaturan akun perkiraan yang akan digunakan
            pada lajur kredit.
        </small>

    </div>

</div>


<!-- ========================================================
     AKUN UTANG / PIUTANG
========================================================= -->
<div class="row mb-3">

    <div class="col-md-4">
        <label
            for="id_utang_piutang_edit"
            id="label_utang_piutang_edit"
        >
            <small>
                <?php
                    if ($kategori === 'Pengeluaran') {
                        echo 'Akun Utang';
                    } elseif ($kategori === 'Pemasukan') {
                        echo 'Akun Piutang';
                    } else {
                        echo 'Akun Utang/Piutang';
                    }
                ?>
            </small>
        </label>
    </div>

    <div class="col-md-8">

        <select
            name="id_utang_piutang"
            id="id_utang_piutang_edit"
            class="form-select"
            style="width: 100%;"
        >

            <?php if ($id_utang_piutang > 0): ?>

                <option
                    value="<?php echo $id_utang_piutang; ?>"
                    selected
                >
                    <?php echo $text_akun_utang_piutang; ?>
                </option>

            <?php else: ?>

                <option value=""></option>

            <?php endif; ?>

        </select>

        <small class="text-muted">
            Pengaturan akun perkiraan yang akan digunakan
            ketika transaksi menimbulkan utang atau piutang.
        </small>

    </div>

</div>