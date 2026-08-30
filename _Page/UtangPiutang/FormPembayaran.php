<?php
    //Koneksi
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

    // ==========================================
    // DEFAULT NILAI
    // ==========================================
    $nama_transaksi        = "";
    $total                 = 0;
    $cash                  = 0;
    $pembayaran_sebelumnya = 0;
    $sisa_tagihan          = 0;

    // ==========================================
    // ROUTING TRANSAKSI
    // ==========================================
    if ($kategori == "jual_beli") {

        // --------------------------------------
        // AMBIL DATA TRANSAKSI JUAL BELI
        // --------------------------------------
        $Qry = $Conn->prepare("
            SELECT
                total,
                cash,
                kategori,
                status
            FROM transaksi_jual_beli
            WHERE id_transaksi_jual_beli = ?
            LIMIT 1
        ");

        if (!$Qry) {
            echo '
                <div class="alert alert-danger text-center">
                    <small>
                        Gagal mempersiapkan data transaksi.
                    </small>
                </div>
            ';
            exit;
        }

        $Qry->bind_param("s", $id);

        if (!$Qry->execute()) {
            echo '
                <div class="alert alert-danger text-center">
                    <small>
                        Gagal mengambil data transaksi.
                    </small>
                </div>
            ';
            exit;
        }

        $Result = $Qry->get_result();
        $Data   = $Result->fetch_assoc();

        $Qry->close();

        // Validasi Data
        if (!$Data) {
            echo '
                <div class="alert alert-danger text-center">
                    <small>
                        <b>Opsss!</b><br>
                        Data Transaksi Jual/Beli Tidak Ditemukan!
                    </small>
                </div>
            ';
            exit;
        }

        // Ambil Data
        $nama_transaksi = $Data['kategori'];
        $total          = (float) $Data['total'];
        $cash           = (float) $Data['cash'];

        // --------------------------------------
        // HITUNG PEMBAYARAN SEBELUMNYA
        // --------------------------------------
        $QryPembayaran = $Conn->prepare("
            SELECT COALESCE(SUM(jumlah), 0) AS pembayaran
            FROM transaksi_pembayaran
            WHERE id_transaksi_jual_beli = ?
        ");

        $QryPembayaran->bind_param("s", $id);
        $QryPembayaran->execute();

        $ResultPembayaran = $QryPembayaran->get_result();
        $DataPembayaran   = $ResultPembayaran->fetch_assoc();

        $pembayaran_sebelumnya = (float) $DataPembayaran['pembayaran'];

        $QryPembayaran->close();

    } else {

        // --------------------------------------
        // AMBIL DATA TRANSAKSI OPERASIONAL
        // --------------------------------------
        $id_transaksi = $id;

        $Qry = $Conn->prepare(" 
            SELECT
                jumlah,
                pembayaran,
                status,
                tj.kategori
            FROM transaksi t
            INNER JOIN transaksi_jenis tj ON tj.id_transaksi_jenis = t.id_transaksi_jenis
            WHERE t.id_transaksi = ?
            LIMIT 1
        ");

        if (!$Qry) {
            echo '
                <div class="alert alert-danger text-center">
                    <small>
                        Gagal mempersiapkan data transaksi.
                    </small>
                </div>
            ';
            exit;
        }

        $Qry->bind_param("s", $id_transaksi);

        if (!$Qry->execute()) {
            echo '
                <div class="alert alert-danger text-center">
                    <small>
                        Gagal mengambil data transaksi.
                    </small>
                </div>
            ';
            exit;
        }

        $Result = $Qry->get_result();
        $Data   = $Result->fetch_assoc();

        $Qry->close();

        // Validasi Data
        if (!$Data) {
            echo '
                <div class="alert alert-danger text-center">
                    <small>
                        <b>Opsss!</b><br>
                        Data Transaksi Operasional Tidak Ditemukan!
                    </small>
                </div>
            ';
            exit;
        }

        // Ambil Data
        // Gunakan nilai enum kategori transaksi, bukan label umum.
        $nama_transaksi = $Data['kategori'];
        $total          = (float) $Data['jumlah'];
        $cash           = (float) $Data['pembayaran'];

        // --------------------------------------
        // HITUNG PEMBAYARAN SEBELUMNYA
        // --------------------------------------
        $QryPembayaran = $Conn->prepare("
            SELECT COALESCE(SUM(jumlah), 0) AS pembayaran
            FROM transaksi_pembayaran
            WHERE id_transaksi = ?
        ");

        $QryPembayaran->bind_param("s", $id_transaksi);
        $QryPembayaran->execute();

        $ResultPembayaran = $QryPembayaran->get_result();
        $DataPembayaran   = $ResultPembayaran->fetch_assoc();

        $pembayaran_sebelumnya = (float) $DataPembayaran['pembayaran'];

        $QryPembayaran->close();

    }

    // ==========================================
    // HITUNG SISA TAGIHAN
    // ==========================================
    $sisa_tagihan = $total - $cash - $pembayaran_sebelumnya;

    // Hindari Nilai Minus
    if ($sisa_tagihan < 0) {
        $sisa_tagihan = 0;
    }

    // ==========================================
    // FORMAT NILAI
    // ==========================================
    $total_format = number_format(
        $total,
        0,
        ',',
        '.'
    );

    $cash_format = number_format(
        $cash,
        0,
        ',',
        '.'
    );

    $pembayaran_sebelumnya_format = number_format(
        $pembayaran_sebelumnya,
        0,
        ',',
        '.'
    );

    $sisa_tagihan_format = number_format(
        $sisa_tagihan,
        0,
        ',',
        '.'
    );
?>

<!-- ID TRANSAKSI -->
<div class="row mb-3">
    <div class="col-4">
        <label for="id">
            <small>ID Transaksi</small>
        </label>
    </div>
    <div class="col-8">
        <input type="text" class="form-control" name="id" id="id" value="<?php echo htmlspecialchars($id); ?>" readonly>
    </div>
</div>

<!-- KATEGORI -->
<div class="row mb-3">
    <div class="col-4">
        <label for="kategori">
            <small>Kategori</small>
        </label>
    </div>
    <div class="col-8">
        <input type="hidden" name="kategori" value="<?php echo htmlspecialchars($kategori, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="text" readonly class="form-control" id="nama_transaksi" name="nama_transaksi" value="<?php echo htmlspecialchars($nama_transaksi, ENT_QUOTES, 'UTF-8'); ?>">
    </div>
</div>

<!-- TANGGAL PEMBAYARAN -->
<div class="row mb-3">
    <div class="col-4">
        <label for="tanggal_pembayaran">
            <small>Tanggal</small>
        </label>
    </div>
    <div class="col-8">
        <input type="date" class="form-control" name="tanggal_pembayaran" id="tanggal_pembayaran" value="<?php echo date('Y-m-d'); ?>">
    </div>
</div>

<!-- JAM PEMBAYARAN -->
<div class="row mb-3">
    <div class="col-4">
        <label for="jam_pembayaran">
            <small>Jam</small>
        </label>
    </div>
    <div class="col-8">
        <input type="time" class="form-control" name="jam_pembayaran" id="jam_pembayaran" value="<?php echo date('H:i'); ?>">
    </div>
</div>

<!-- TOTAL -->
<div class="row mb-3">
    <div class="col-4">
        <label for="total">
            <small>Total</small>
        </label>
    </div>
    <div class="col-8">
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="text" readonly class="form-control" id="total" name="total" value="<?php echo $total_format; ?>">
        </div>
    </div>
</div>

<!-- CASH -->
<div class="row mb-3">
    <div class="col-4">
        <label for="cash">
            <small>Cash/Tunai</small>
        </label>
    </div>
    <div class="col-8">
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="text" readonly class="form-control" id="cash" name="cash" value="<?php echo $cash_format; ?>">
        </div>
    </div>
</div>

<!-- PEMBAYARAN SEBELUMNYA -->
<div class="row mb-3">
    <div class="col-4">
        <label for="pembayaran_sebelumnya">
            <small>Pembayaran Sebelumnya</small>
        </label>
    </div>
    <div class="col-8">
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="text" readonly class="form-control" id="pembayaran_sebelumnya" name="pembayaran_sebelumnya" value="<?php echo $pembayaran_sebelumnya_format; ?>">
        </div>
    </div>
</div>

<!-- SISA TAGIHAN -->
<div class="row mb-3">
    <div class="col-4">
        <label for="sisa_tagihan">
            <small>Sisa Tagihan</small>
        </label>
    </div>
    <div class="col-8">
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="text" readonly class="form-control" id="sisa_tagihan" name="sisa_tagihan" value="<?php echo $sisa_tagihan_format; ?>">
        </div>
    </div>
</div>

<!-- NOMINAL PEMBAYARAN -->
<div class="row mb-3">
    <div class="col-4">
        <label for="nominal_pembayaran">
            <small>Nominal Pembayaran</small>
        </label>
    </div>
    <div class="col-8">
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="text" name="nominal_pembayaran" id="nominal_pembayaran" class="form-control form-money" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
        </div>
    </div>
</div>
