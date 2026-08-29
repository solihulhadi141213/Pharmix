<?php
    // ==========================================
    // KONEKSI DAN SESSION
    // ==========================================
    date_default_timezone_set('Asia/Jakarta');

    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // ==========================================
    // VALIDASI SESI AKSES
    // ==========================================
    if (empty($SessionIdAkses)) {
        echo '
            <div class="alert alert-danger text-center">
                <small>
                    <b>Opsss!</b><br>
                    Sesi akses sudah berakhir! Silahkan Login Ulang!
                </small>
            </div>
        ';
        exit;
    }

    // ==========================================
    // VALIDASI ID TRANSAKSI
    // ==========================================
    if (empty($_POST['id'])) {
        echo '
            <div class="alert alert-danger text-center">
                <small>
                    <b>Opsss!</b><br>
                    ID Transaksi Tidak Boleh Kosong!
                </small>
            </div>
        ';
        exit;
    }

    // ==========================================
    // VALIDASI KATEGORI
    // ==========================================
    if (empty($_POST['kategori'])) {
        echo '
            <div class="alert alert-danger text-center">
                <small>
                    <b>Opsss!</b><br>
                    Kategori Transaksi Tidak Boleh Kosong!
                </small>
            </div>
        ';
        exit;
    }

    // ==========================================
    // VARIABEL DAN SANITASI
    // ==========================================
    $id       = validateAndSanitizeInput($_POST['id']);
    $kategori = validateAndSanitizeInput($_POST['kategori']);

    $id_transaksi           = "";
    $id_transaksi_jual_beli = "";
    $kategori_tempo         = "";
    $tanggal_tempo          = "";

    // ==========================================
    // ROUTING TRANSAKSI
    // ==========================================
    if ($kategori == "jual_beli") {
        $id_transaksi_jual_beli = $id;
        $kategori_tempo = GetDetailData($Conn, 'transaksi_jual_beli', 'id_transaksi_jual_beli', $id_transaksi_jual_beli, 'kategori');
        
        if (empty($kategori_tempo)) {
            echo '
                <div class="alert alert-danger text-center">
                    <small>
                        <b>Opsss!</b><br>
                        ID Transaksi Jual/Beli Tidak Valid!
                    </small>
                </div>
            ';
            exit;
        }

        // Tanggal Tempo Diambil berdasarkan id_transaksi_jual_beli
        $tanggal_tempo = GetDetailData($Conn, 'transaksi_tempo', 'id_transaksi_jual_beli', $id, 'tanggal_tempo');

    } else {
        $id_transaksi = $id;
        $id_transaksi_jenis = GetDetailData($Conn, 'transaksi', 'id_transaksi', $id_transaksi, 'id_transaksi_jenis');

        // Validasi 'id_transaksi_jenis'
        if (empty($id_transaksi_jenis)) {
            echo '
                <div class="alert alert-danger text-center">
                    <small>
                        <b>Opsss!</b><br>
                        ID Transaksi Operasional Tidak Valid!
                    </small>
                </div>
            ';
            exit;
        }

        $kategori_tempo = GetDetailData($Conn, 'transaksi_jenis', 'id_transaksi_jenis', $id_transaksi_jenis, 'kategori');
        
        // Validasi 'kategori_tempo'
        if (empty($kategori_tempo)) {
            echo '
                <div class="alert alert-danger text-center">
                    <small>
                        <b>Opsss!</b><br>
                        Kategori Operasional Tidak Valid!
                    </small>
                </div>
            ';
            exit;
        }

        $tanggal_tempo = GetDetailData($Conn, 'transaksi_tempo', 'id_transaksi', $id, 'tanggal_tempo');
    }
?>

<!-- Hidden Input untuk menjaga nilai parameter ID & Kategori agar terbaca di ProsesTempo.php -->
<input type="hidden" name="id" value="<?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?>">
<input type="hidden" name="kategori" value="<?php echo htmlspecialchars($kategori, ENT_QUOTES, 'UTF-8'); ?>">

<div class="row mb-3">
    <div class="col-12">
        <label for="id_transaksi_jual_beli_input">ID Transaksi Jual/Beli</label>
        <input type="text" id="id_transaksi_jual_beli_input" class="form-control" value="<?php echo htmlspecialchars($id_transaksi_jual_beli, ENT_QUOTES, 'UTF-8'); ?>" readonly>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <label for="id_transaksi_input">ID Transaksi Operasional</label>
        <input type="text" id="id_transaksi_input" class="form-control" value="<?php echo htmlspecialchars($id_transaksi, ENT_QUOTES, 'UTF-8'); ?>" readonly>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <label for="kategori_tempo">Kategori Transaksi</label>
        <input type="text" id="kategori_tempo" class="form-control" value="<?php echo htmlspecialchars($kategori_tempo, ENT_QUOTES, 'UTF-8'); ?>" required readonly>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <label for="tanggal_tempo">Tanggal Jatuh Tempo</label>
        <input type="date" name="tanggal_tempo" id="tanggal_tempo" class="form-control" value="<?php echo htmlspecialchars($tanggal_tempo, ENT_QUOTES, 'UTF-8'); ?>" required>
    </div>
</div>