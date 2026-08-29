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
                        '.$message.'
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
    // VALIDASI ID JENIS TRANSAKSI
    // ============================================================
    if (empty($_POST['id_transaksi_jenis'])) {
        showError('ID Jenis Transaksi Tidak Boleh Kosong!');
    }

    $id_transaksi_jenis = (int) validateAndSanitizeInput(
        $_POST['id_transaksi_jenis']
    );

    if ($id_transaksi_jenis <= 0) {
        showError('ID Jenis Transaksi Tidak Valid!');
    }


    // ============================================================
    // QUERY DATA JENIS TRANSAKSI
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

            -- AKUN KREDIT
            ak.kode AS kode_akun_kredit,
            ak.nama AS nama_akun_kredit,

            -- AKUN UTANG / PIUTANG
            aup.kode AS kode_akun_utang_piutang,
            aup.nama AS nama_akun_utang_piutang,

            -- STATISTIK TRANSAKSI
            COUNT(t.id_transaksi) AS jumlah_transaksi,
            COALESCE(SUM(t.jumlah), 0) AS total_transaksi

        FROM transaksi_jenis AS tj

        LEFT JOIN akun_perkiraan AS ad
            ON ad.id_perkiraan = tj.id_akun_debet

        LEFT JOIN akun_perkiraan AS ak
            ON ak.id_perkiraan = tj.id_akun_kredit

        LEFT JOIN akun_perkiraan AS aup
            ON aup.id_perkiraan = tj.id_utang_piutang

        LEFT JOIN transaksi AS t
            ON t.id_transaksi_jenis = tj.id_transaksi_jenis

        WHERE tj.id_transaksi_jenis = ?

        GROUP BY
            tj.id_transaksi_jenis,
            tj.nama,
            tj.kategori,
            tj.deskripsi,

            tj.id_akun_debet,
            tj.id_akun_kredit,
            tj.id_utang_piutang,

            ad.kode,
            ad.nama,

            ak.kode,
            ak.nama,

            aup.kode,
            aup.nama
    ";


    // ============================================================
    // PREPARE QUERY
    // ============================================================
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
    // EXECUTE QUERY
    // ============================================================
    if (!$stmt->execute()) {

        $error = $stmt->error;

        $stmt->close();

        showError(
            '<b>Opsss!</b> Terjadi kesalahan pada saat mengambil data.<br>' .
            htmlspecialchars(
                $error,
                ENT_QUOTES,
                'UTF-8'
            )
        );
    }


    // ============================================================
    // AMBIL DATA
    // ============================================================
    $result = $stmt->get_result();

    $Data = $result->fetch_assoc();

    $stmt->close();


    // ============================================================
    // VALIDASI DATA
    // ============================================================
    if (!$Data) {
        showError('Data jenis transaksi tidak ditemukan.');
    }


    // ============================================================
    // DATA JENIS TRANSAKSI
    // ============================================================
    $nama = $Data['nama'] ?? '';

    $kategori = $Data['kategori'] ?? '';

    $deskripsi = $Data['deskripsi'] ?? '';


    // ============================================================
    // FORMAT AKUN DEBET
    // ============================================================
    $debet_kode = $Data['kode_akun_debet'] ?? '';

    $debet_nama = $Data['nama_akun_debet'] ?? '';

    $text_debet = '-';

    if (!empty($debet_kode) || !empty($debet_nama)) {

        $text_debet = '';

        if (!empty($debet_kode)) {
            $text_debet .= $debet_kode;
        }

        if (!empty($debet_nama)) {

            if ($text_debet !== '') {
                $text_debet .= ' - ';
            }

            $text_debet .= $debet_nama;
        }
    }


    // ============================================================
    // FORMAT AKUN KREDIT
    // ============================================================
    $kredit_kode = $Data['kode_akun_kredit'] ?? '';

    $kredit_nama = $Data['nama_akun_kredit'] ?? '';

    $text_kredit = '-';

    if (!empty($kredit_kode) || !empty($kredit_nama)) {

        $text_kredit = '';

        if (!empty($kredit_kode)) {
            $text_kredit .= $kredit_kode;
        }

        if (!empty($kredit_nama)) {

            if ($text_kredit !== '') {
                $text_kredit .= ' - ';
            }

            $text_kredit .= $kredit_nama;
        }
    }


    // ============================================================
    // FORMAT AKUN UTANG / PIUTANG
    // ============================================================
    $utang_piutang_kode = $Data['kode_akun_utang_piutang'] ?? '';

    $utang_piutang_nama = $Data['nama_akun_utang_piutang'] ?? '';

    $text_utang_piutang = '-';

    if (!empty($utang_piutang_kode) || !empty($utang_piutang_nama)) {

        $text_utang_piutang = '';

        if (!empty($utang_piutang_kode)) {
            $text_utang_piutang .= $utang_piutang_kode;
        }

        if (!empty($utang_piutang_nama)) {

            if ($text_utang_piutang !== '') {
                $text_utang_piutang .= ' - ';
            }

            $text_utang_piutang .= $utang_piutang_nama;
        }
    }


    // ============================================================
    // STATISTIK TRANSAKSI
    // ============================================================
    $jumlah_transaksi = number_format(
        (int) ($Data['jumlah_transaksi'] ?? 0),
        0,
        ',',
        '.'
    );

    $total_transaksi = 'Rp ' . number_format(
        (float) ($Data['total_transaksi'] ?? 0),
        0,
        ',',
        '.'
    );


    // ============================================================
    // LABEL KATEGORI
    // ============================================================
    if ($kategori === 'Pengeluaran') {

        $label_kategori = '
            <span class="badge bg-danger">
                Pengeluaran
            </span>
        ';

        $label_utang_piutang = 'Akun Utang';

    } elseif ($kategori === 'Pemasukan') {

        $label_kategori = '
            <span class="badge bg-success">
                Pemasukan
            </span>
        ';

        $label_utang_piutang = 'Akun Piutang';

    } else {

        $label_kategori = '
            <span class="badge bg-secondary">
                -
            </span>
        ';

        $label_utang_piutang = 'Akun Utang/Piutang';
    }

?>


<!-- ============================================================
     DETAIL JENIS TRANSAKSI
============================================================ -->
<div class="col-md-12 mb-4">

    <!-- NAMA TRANSAKSI -->
    <div class="row mb-3">

        <div class="col-md-5">
            <small>Nama Transaksi</small>
        </div>

        <div class="col-md-7">
            <small class="text-grayish">
                <?= htmlspecialchars(
                    $nama,
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>
            </small>
        </div>

    </div>


    <!-- KATEGORI -->
    <div class="row mb-3">

        <div class="col-md-5">
            <small>Kategori Transaksi</small>
        </div>

        <div class="col-md-7">
            <?= $label_kategori; ?>
        </div>

    </div>


    <!-- DESKRIPSI -->
    <div class="row mb-3">

        <div class="col-md-5">
            <small>Deskripsi/Keterangan</small>
        </div>

        <div class="col-md-7">
            <small class="text-grayish">
                <?= htmlspecialchars(
                    $deskripsi,
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>
            </small>
        </div>

    </div>


    <!-- AKUN DEBET -->
    <div class="row mb-3">

        <div class="col-md-5">
            <small>Akun Debet</small>
        </div>

        <div class="col-md-7">
            <small class="text-grayish">
                <?= htmlspecialchars(
                    $text_debet,
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>
            </small>
        </div>

    </div>


    <!-- AKUN KREDIT -->
    <div class="row mb-3">

        <div class="col-md-5">
            <small>Akun Kredit</small>
        </div>

        <div class="col-md-7">
            <small class="text-grayish">
                <?= htmlspecialchars(
                    $text_kredit,
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>
            </small>
        </div>

    </div>


    <!-- AKUN UTANG / PIUTANG -->
    <div class="row mb-3">

        <div class="col-md-5">
            <small><?= htmlspecialchars(
                $label_utang_piutang,
                ENT_QUOTES,
                'UTF-8'
            ); ?></small>
        </div>

        <div class="col-md-7">
            <small class="text-grayish">
                <?= htmlspecialchars(
                    $text_utang_piutang,
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>
            </small>
        </div>

    </div>


    <!-- JUMLAH RECORD -->
    <div class="row mb-3">

        <div class="col-md-5">
            <small>Jumlah Record</small>
        </div>

        <div class="col-md-7">
            <small class="text-grayish">
                <?= $jumlah_transaksi; ?> Record
            </small>
        </div>

    </div>


    <!-- TOTAL TRANSAKSI -->
    <div class="row mb-3">

        <div class="col-md-5">
            <small>Total / Volume (Rp)</small>
        </div>

        <div class="col-md-7">
            <small class="text-grayish">
                <?= $total_transaksi; ?>
            </small>
        </div>

    </div>

</div>