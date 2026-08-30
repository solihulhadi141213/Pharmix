<?php
    // =========================================================
    // KONFIGURASI
    // =========================================================
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/SettingGeneral.php";
    include "../../_Config/Session.php";
    header('Content-Type: application/json; charset=utf-8');

    // =========================================================
    // RESPONSE ERROR
    // =========================================================
    function responseError($message) {
        echo json_encode([
            'status'  => 'error',
            'message' => $message,
            'html'    => '
                <div class="row">
                    <div class="col-md-12 text-center">
                        <small class="text-danger">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</small>
                    </div>
                </div>
            '
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

    // =========================================================
    // VALIDASI ID
    // =========================================================
    if ($id_transaksi_rincian === '' || !ctype_digit($id_transaksi_rincian)) {
        responseError('ID rincian transaksi tidak valid.');
    }
    $id_transaksi_rincian = (int) $id_transaksi_rincian;
    if ($id_transaksi_rincian <= 0) {
        responseError('ID rincian transaksi tidak valid.');
    }

    // =========================================================
    // QUERY DATA
    // =========================================================
    $sql = "
        SELECT
            tr.id_transaksi_rincian,
            tr.id_transaksi,
            tr.rincian_transaksi,
            tr.harga,
            tr.qty,
            tr.satuan,
            tr.jumlah
        FROM transaksi_rincian AS tr
        WHERE tr.id_transaksi_rincian = ?
        LIMIT 1
    ";
    $stmt = mysqli_prepare($Conn, $sql);
    if (!$stmt) {
        responseError('Gagal mempersiapkan query rincian.');
    }
    mysqli_stmt_bind_param($stmt, 'i', $id_transaksi_rincian);
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        responseError('Gagal mengambil data rincian.');
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
    $id_transaksi       = $data['id_transaksi'];
    $rincian_transaksi  = $data['rincian_transaksi'] ?? '';
    $harga              = (int) ($data['harga'] ?? 0);
    $qty                = (int) ($data['qty'] ?? 0);
    $satuan             = $data['satuan'] ?? '';
    $jumlah             = (int) ($data['jumlah'] ?? 0);

    // =========================================================
    // FORMAT UANG
    // =========================================================
    $HargaFormat  = number_format($harga, 0, ',', '.');
    $QtyFormat    = number_format($qty, 0, ',', '.');
    $JumlahFormat = number_format($jumlah, 0, ',', '.');

    // =========================================================
    // ESCAPE HTML
    // =========================================================
    $rincianHtml = htmlspecialchars($rincian_transaksi, ENT_QUOTES, 'UTF-8');
    $satuanHtml  = htmlspecialchars($satuan, ENT_QUOTES, 'UTF-8');

    // =========================================================
    // HTML FORM
    // =========================================================
    $html = '
        <input type="hidden" name="id_transaksi_rincian" value="' . $id_transaksi_rincian . '">
        <input type="hidden" name="id_transaksi" value="' . $id_transaksi . '">
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="uraian_rincian_edit">* Uraian</label>
                <input type="text" name="uraian_rincian" id="uraian_rincian_edit" class="form-control" value="' . $rincianHtml . '" required autocomplete="off">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="uraian_harga_edit">Harga (Rp)</label>
                <input type="text" name="uraian_harga" id="uraian_harga_edit" class="form-control" inputmode="numeric" value="' . $HargaFormat . '" autocomplete="off">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="uraian_qty_edit">* QTY</label>
                <input type="text" name="uraian_qty" id="uraian_qty_edit" class="form-control" inputmode="numeric" value="' . $QtyFormat . '" required autocomplete="off">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="uraian_satuan_edit">Satuan</label>
                <input type="text" name="uraian_satuan" id="uraian_satuan_edit" class="form-control" value="' . $satuanHtml . '" autocomplete="off">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="uraian_jumlah_edit">Jumlah</label>
                <input type="text" name="uraian_jumlah" id="uraian_jumlah_edit" class="form-control" value="' . $JumlahFormat . '" readonly>
            </div>
        </div>
    ';

    // =========================================================
    // RESPONSE
    // =========================================================
    echo json_encode([
        'status'  => 'success',
        'message' => 'Data rincian berhasil ditemukan.',
        'html'    => $html
    ], JSON_UNESCAPED_UNICODE);
    exit;
?>