<?php
    // =========================================================
    // KONEKSI, HELPER DAN SESSION
    // =========================================================
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/SettingGeneral.php";
    include "../../_Config/Session.php";
    header('Content-Type: application/json; charset=utf-8');

    // =========================================================
    // DEFAULT RESPONSE
    // =========================================================
    $response = [
        'status'  => 'error',
        'message' => 'Terjadi kesalahan.',
        'html'    => ''
    ];

    // =========================================================
    // FUNGSI RESPONSE ERROR
    // =========================================================
    function responseError($message) {
        $html = '
            <div class="alert alert-danger">
                <small>
                    <i class="bi bi-exclamation-circle"></i>
                    ' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '
                </small>
            </div>
        ';
        echo json_encode([
            'status'  => 'error',
            'message' => $message,
            'html'    => $html
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // =========================================================
    // VALIDASI SESSION
    // =========================================================
    if (empty($SessionIdAkses)) {
        responseError('Sesi akses sudah berakhir. Silakan login ulang.');
    }

    // =========================================================
    // VALIDASI METHOD
    // =========================================================
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseError('Metode request tidak valid.');
    }

    // =========================================================
    // AMBIL ID RINCIAN
    // =========================================================
    $id_transaksi_rincian = trim($_POST['id_transaksi_rincian'] ?? '');
    if ($id_transaksi_rincian === '' || !ctype_digit($id_transaksi_rincian)) {
        responseError('ID rincian transaksi tidak valid.');
    }
    $id_transaksi_rincian = (int)$id_transaksi_rincian;
    if ($id_transaksi_rincian <= 0) {
        responseError('ID rincian transaksi tidak valid.');
    }

    // =========================================================
    // QUERY DATA RINCIAN
    // =========================================================
    $sql = "
        SELECT
            tr.id_transaksi_rincian,
            tr.id_transaksi,
            tr.rincian_transaksi,
            tr.harga,
            tr.qty,
            tr.satuan,
            tr.jumlah,
            t.tanggal,
            t.status,
            tj.nama AS nama_transaksi,
            tj.kategori
        FROM transaksi_rincian AS tr
        LEFT JOIN transaksi AS t
            ON t.id_transaksi = tr.id_transaksi
        LEFT JOIN transaksi_jenis AS tj
            ON tj.id_transaksi_jenis = t.id_transaksi_jenis
        WHERE tr.id_transaksi_rincian = ?
        LIMIT 1
    ";
    $stmt = mysqli_prepare($Conn, $sql);
    if (!$stmt) {
        responseError('Gagal menyiapkan query data rincian.');
    }
    mysqli_stmt_bind_param($stmt, 'i', $id_transaksi_rincian);
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        responseError('Gagal mengambil data rincian transaksi.');
    }
    $result = mysqli_stmt_get_result($stmt);
    if (!$result || mysqli_num_rows($result) === 0) {
        mysqli_stmt_close($stmt);
        responseError('Data rincian transaksi tidak ditemukan.');
    }
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // =========================================================
    // AMBIL DATA
    // =========================================================
    $id_transaksi       = (int)($data['id_transaksi'] ?? 0);
    $rincian_transaksi  = $data['rincian_transaksi'] ?? '';
    $harga              = (int)($data['harga'] ?? 0);
    $qty                = (int)($data['qty'] ?? 0);
    $satuan             = $data['satuan'] ?? '';
    $jumlah             = (int)($data['jumlah'] ?? 0);
    $nama_transaksi     = $data['nama_transaksi'] ?? '-';
    $kategori           = $data['kategori'] ?? '-';
    $tanggal            = $data['tanggal'] ?? '';
    $status             = $data['status'] ?? '-';

    // =========================================================
    // FORMAT
    // =========================================================
    $HargaFormat = 'Rp ' . number_format($harga, 0, ',', '.');
    $JumlahFormat = 'Rp ' . number_format($jumlah, 0, ',', '.');
    $TanggalFormat = '-';
    if (!empty($tanggal)) {
        $TanggalFormat = date('d/m/Y H:i', strtotime($tanggal));
    }

    // =========================================================
    // ESCAPE HTML
    // =========================================================
    $rincianHtml       = htmlspecialchars($rincian_transaksi, ENT_QUOTES, 'UTF-8');
    $namaTransaksiHtml = htmlspecialchars($nama_transaksi, ENT_QUOTES, 'UTF-8');
    $kategoriHtml      = htmlspecialchars($kategori, ENT_QUOTES, 'UTF-8');
    $satuanHtml        = htmlspecialchars($satuan, ENT_QUOTES, 'UTF-8');
    $statusHtml        = htmlspecialchars($status, ENT_QUOTES, 'UTF-8');

    // =========================================================
    // FORM HAPUS
    // =========================================================
    $html = '
        <input type="hidden" name="id_transaksi_rincian" value="' . $id_transaksi_rincian . '">
        <input type="hidden" name="id_transaksi" value="' . $id_transaksi . '">
        
        <!-- INFORMASI TRANSAKSI -->
        <div class="card border mb-3">
            <div class="card-header">
                <strong>
                    <i class="bi bi-receipt"></i>
                    Informasi Transaksi
                </strong>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-5 text-muted">Transaksi</div>
                    <div class="col-7"><strong>' . $namaTransaksiHtml . '</strong></div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted">Kategori</div>
                    <div class="col-7">' . $kategoriHtml . '</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted">Tanggal</div>
                    <div class="col-7">' . $TanggalFormat . '</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted">Status</div>
                    <div class="col-7">' . $statusHtml . '</div>
                </div>
            </div>
        </div>
        <!-- INFORMASI RINCIAN -->
        <div class="card border">
            <div class="card-header">
                <strong>
                    <i class="bi bi-list-ul"></i>
                    Detail Rincian
                </strong>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-5 text-muted">Uraian</div>
                    <div class="col-7"><strong>' . $rincianHtml . '</strong></div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted">Harga</div>
                    <div class="col-7">' . $HargaFormat . '</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted">QTY</div>
                    <div class="col-7">' . $qty . ' ' . $satuanHtml . '</div>
                </div>
                <div class="row">
                    <div class="col-5 text-muted">Jumlah</div>
                    <div class="col-7"><strong>' . $JumlahFormat . '</strong></div>
                </div>
            </div>
        </div>
        <div class="text-center mt-3">
            <small class="text-muted">
                Apakah Anda yakin ingin menghapus rincian tersebut?
            </small>
        </div>
    ';

    // =========================================================
    // RESPONSE SUCCESS
    // =========================================================
    echo json_encode([
        'status'  => 'success',
        'message' => 'Data rincian transaksi berhasil ditemukan.',
        'html'    => $html
    ], JSON_UNESCAPED_UNICODE);
    exit;
?>