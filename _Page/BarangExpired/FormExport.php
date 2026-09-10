<?php
// --------------------------------------------------
// Koneksi dan Session
// --------------------------------------------------
include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

// --------------------------------------------------
// Validasi Akses
// --------------------------------------------------
if (empty($SessionIdAkses)) {
    http_response_code(401);
    exit('
        <div class="alert alert-danger mb-0">
            Sesi akses sudah berakhir. Silakan login ulang.
        </div>
    ');
}

// --------------------------------------------------
// Hitung Jumlah Data
// --------------------------------------------------
try {
    $query = $Conn->query("SELECT COUNT(*) AS jumlah FROM barang_bacth");
    if (!$query) {
        throw new RuntimeException('Gagal menghitung data export.');
    }

    $data = $query->fetch_assoc();
    $jumlah = (int) $data['jumlah'];
    $query->free();
} catch (Throwable $e) {
    error_log('FormExport BarangExpired: ' . $e->getMessage());
    http_response_code(500);
    exit('
        <div class="alert alert-danger mb-0">
            Jumlah data gagal dimuat. Silakan coba kembali.
        </div>
    ');
}

// --------------------------------------------------
// Validasi Ketersediaan Data
// --------------------------------------------------
if ($jumlah === 0) {
    exit('
        <div class="alert alert-warning mb-0">
            Belum ada data batch dan expired yang dapat diexport.
        </div>
    ');
}
?>
<div class="row mb-2">
    <div class="col-12 text-center">
        <h1 class="bi bi-exclamation-triangle"></h1>
    </div>
</div>
<div class="row mb-2">
    <div class="col-12 text-center">
        <b>Semakin Banyak Data Yang Akan Di Export Maka Sistem Akan Membutuhkan Waktu Lebih Lama</b>
    </div>
</div>
<div class="row mb-2">
    <div class="col-12">
        <div class="alert alert-info">
            <div class="row">
                <div class="col-6"><small>Jumlah</small></div>
                <div class="col-6 text-end">
                    <small><b><?= number_format($jumlah, 0, ',', '.'); ?> Record</b></small>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row mb-2">
    <div class="col-12">
        <small>Apakah Anda yakin ingin melakukan export seluruh data batch dan expired?</small>
    </div>
</div>
<div class="form-check">
    <input type="checkbox" class="form-check-input" name="konfirmasi_export" id="konfirmasi_export" value="1" required>
    <label class="form-check-label" for="konfirmasi_export">
        <small>Ya, saya yakin ingin melakukan export.</small>
    </label>
</div>