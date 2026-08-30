<?php

    // Koneksi, Helper Dan Sesi Akses
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/SettingGeneral.php";
    include "../../_Config/Session.php";

    // Default JSON
    header('Content-Type: application/json; charset=utf-8');

    // Default Response
    $response = ['status' => 'error', 'message' => 'Terjadi kesalahan.', 'html' => ''];

    // Fungsi untuk menangani respons error terpusat
    function responseError($message) {
        $response = [
            'status' => 'error',
            'message' => $message,
            'html' => '<div class="row"><div class="col-md-12 mb-2 text-center"><small class="text-danger">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</small></div></div>'
        ];
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (empty($SessionIdAkses)) {
        responseError('Sesi akses sudah berakhir. Silakan login ulang.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseError('Metode request tidak valid.');
    }

    if(empty($_POST['id_transaksi'])){
        responseError('ID Transaksi tidak boleh kosong.');
    }
    

    $id_transaksi = $_POST['id_transaksi'];
    

    // Form Tambah Rincian
    $html = '
        <input type="hidden" name="id_transaksi" value="' . $id_transaksi . '">
        
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="uraian_rincian">* Uraian</label>
                <input type="text" name="uraian_rincian" id="uraian_rincian" class="form-control" autocomplete="off" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="uraian_harga">Harga (Rp)</label>
                <input type="text" name="uraian_harga" id="uraian_harga" class="form-control">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="uraian_qty">* QTY</label>
                <input type="text" name="uraian_qty" id="uraian_qty" class="form-control" autocomplete="off" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="uraian_satuan">Satuan</label>
                <input type="text" name="uraian_satuan" id="uraian_satuan" class="form-control" autocomplete="off">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="uraian_jumlah">Jumlah</label>
                <input type="text" name="uraian_jumlah" id="uraian_jumlah" class="form-control" readonly>
            </div>
        </div>
    ';

    $response = [
        'status' => 'success',
        'message' => 'Data transaksi berhasil ditemukan.',
        'html' => $html
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
?>