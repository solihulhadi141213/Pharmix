<?php
    //Koneksi
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    
    // Validasi Akses
    if (empty($SessionIdAkses)) {
        echo '
            <div class="row">
                <div class="col-12 text-center">
                   <div class="alert alert-danger">
                        <small>Sesi Akses Sudah Berakhir! Silahkan Login Ulang</small>
                   </div>
                </td>
            </tr>
        ';
        exit;
    }
    //Tangkap id_barang_bacth
    if(empty($_POST['id_barang_bacth'])){
        echo '
            <div class="row">
                <div class="col-12 text-center">
                   <div class="alert alert-danger">
                        <small>ID Supplier Tidak Boleh Kosong!</small>
                   </div>
                </td>
            </tr>
        ';
        exit;
    }

    //Buat Variabel 'id_barang_bacth'
    $id_barang_bacth=validateAndSanitizeInput($_POST['id_barang_bacth']);
    
    //Buka data supplier
    $QryBatch        = mysqli_query($Conn,"SELECT * FROM barang_bacth WHERE id_barang_bacth='$id_barang_bacth'")or die(mysqli_error($Conn));
    $DataBatch       = mysqli_fetch_array($QryBatch);
    $id_barang_bacth = $DataBatch['id_barang_bacth'];
    $id_barang       = $DataBatch['id_barang'];
    $no_batch        = $DataBatch['no_batch'];
    $expired_date    = $DataBatch['expired_date'];
    $qty_batch       = $DataBatch['qty_batch'];
    $qty_batch       = ($qty_batch == floor($qty_batch)) ? number_format($qty_batch, 0) : $qty_batch;
    $reminder_date   = $DataBatch['reminder_date'];
    $StatusExpired   = $DataBatch['status'];

    //Buka data barang
    $QryBarang = mysqli_query($Conn,"SELECT * FROM barang WHERE id_barang='$id_barang'")or die(mysqli_error($Conn));
    $DataBarang = mysqli_fetch_array($QryBarang);
    $nama_barang= $DataBarang['nama_barang'];
    $kode_barang= $DataBarang['kode_barang'];
    $satuan_barang= $DataBarang['satuan_barang'];
    echo '
        <div class="row mb-3">
            <div class="col-5"><small>Kode Barang</small></div>
            <div class="col-7">
                <small><span class="text text-grayish">'.$kode_barang.'</span></small>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-5"><small>Nama Barang</small></div>
            <div class="col-7">
                <small><span class="text text-grayish">'.$nama_barang.'</span></small>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-5"><small>No. Batch</small></div>
            <div class="col-7">
                <small><span class="text text-grayish">'.$no_batch.'</span></small>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-5"><small>Expire Date</small></div>
            <div class="col-7">
                <small><span class="text text-grayish">'.$expired_date.'</span></small>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-5"><small>Reminder Date</small></div>
            <div class="col-7">
                <small><span class="text text-grayish">'.$reminder_date.'</span></small>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-5"><small>Jumlah (QTY)</small></div>
            <div class="col-7">
                <small><span class="text text-grayish">'.$qty_batch.' '.$satuan_barang.'</span></small>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-5"><small>Status</small></div>
            <div class="col-7">
                <small><span class="text text-grayish">'.$StatusExpired.'</span></small>
            </div>
        </div>
    ';
?>