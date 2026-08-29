<?php
    // Koneksi dan konfigurasi
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/SettingGeneral.php";
    include "../../_Config/Session.php";

    if (empty($SessionIdAkses)) {
        echo '
            <div class="alert alert-danger text-center">
                <small>Sesi Akses Sudah Berakhir, Silahkan Login Ulang</small>
            </div>
        ';
        exit;
    }

    // Tangkap ID transaksi pembayaran
    if (empty($_POST['id_transaksi_pembayaran'])) {
        echo '
            <div class="alert alert-danger text-center">
                <small>ID Pembayaran Tidak Boleh Kosong!</small>
            </div>
        ';
        exit;
    }
    // Tangkap ID
    if (empty($_POST['id'])) {
        echo '
            <div class="alert alert-danger text-center">
                <small>ID Transaksi Tidak Boleh Kosong!</small>
            </div>
        ';
        exit;
    }
    // Tangkap kategori
    if (empty($_POST['kategori'])) {
        echo '
            <div class="alert alert-danger text-center">
                <small>Kategori Transaksi Tidak Boleh Kosong!</small>
            </div>
        ';
        exit;
    }

    $id_transaksi_pembayaran = validateAndSanitizeInput($_POST['id_transaksi_pembayaran']);
    $id                      = validateAndSanitizeInput($_POST['id']);
    $kategori                = $_POST['kategori'];

    // Ambil data pembayaran
    $Qry = $Conn->prepare("SELECT tanggal, jumlah FROM transaksi_pembayaran WHERE id_transaksi_pembayaran = ?");
    $Qry->bind_param("s", $id_transaksi_pembayaran);

    if (!$Qry->execute()) {
        $error = $Conn->error;
        echo '
            <div class="alert alert-danger text-center">
                <small>Terjadi kesalahan saat membuka data pembayaran.<br>
                Keterangan: ' . htmlspecialchars($error) . '</small>
            </div>
        ';
        exit;
    }

    $Result = $Qry->get_result();
    $Data = $Result->fetch_assoc();
    $Qry->close();

    if (!$Data) {
        echo '
            <div class="alert alert-warning text-center">
                <small>Data pembayaran tidak ditemukan.</small>
            </div>
        ';
        exit;
    }

    // Ambil data dari hasil query
    $tanggal                = $Data['tanggal'] ?? '';
    $jumlah                 = $Data['jumlah'] ?? 0;

    // Format tanggal dan jam
    $tanggal_format = $tanggal ? date('Y-m-d', strtotime($tanggal)) : '';
    $jam_format     = $tanggal ? date('H:i', strtotime($tanggal)) : '';

    // Pastikan jumlah angka, lalu format
    $jumlah = is_numeric($jumlah) ? $jumlah : 0;

    //format uang
    $jumlah_rp = "" . number_format($jumlah,0,',','.');

    echo '
        <input type="hidden" name="id_transaksi_pembayaran" value="'.$id_transaksi_pembayaran.'">
        <input type="hidden" name="id" value="'.$id.'">
        <input type="hidden" name="kategori" value="'.$kategori.'">

        <div class="row mb-3">
            <div class="col-4">
                <label for="tanggal_pembayaran_piutang_penjualan_edit">
                    <small>Tanggal Pembayaran</small>
                </label>
            </div>
            <div class="col-8 text-end">
                <small class="text-muted">'.htmlspecialchars($tanggal_format).'</small>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-4">
                <label for="jam_pembayaran_piutang_penjualan">
                    <small>Jam Pembayaran</small>
                </label>
            </div>
            <div class="col-8 text-end">
                <small class="text-muted">'.htmlspecialchars($jam_format).'</small>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-4">
                <label for="nominal_pembayaran_piutang_penjualan">
                    <small>Jumlah (Nominal)</small>
                </label>
            </div>
            <div class="col-8 text-end">
                <small class="text-muted">'.htmlspecialchars($jumlah_rp).'</small>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="alert alert-warning text-center">
                    <small>
                        <b>Penting!</b> <br>
                        Menghapus data pembayaran, akan menyebabkan status transaksi berubah.
                    </small>
                </div>
            </div>
        </div>
    ';
?>