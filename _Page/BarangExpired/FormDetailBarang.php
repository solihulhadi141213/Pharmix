<?php
    //Koneksi
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    //Tangkap id_mitra
    if(empty($_POST['id_barang'])){
        echo '
            <div class="alert alert-danger text-center">
                <small>
                    <b>Opss!</b><br<
                    ID Barang Tidak Boleh Kosong!
                </small>
            </div>
        ';
        exit;
    }

    $id_barang=$_POST['id_barang'];
    //Buka data barang
    $QryBarang = mysqli_query($Conn,"SELECT * FROM barang WHERE id_barang='$id_barang'")or die(mysqli_error($Conn));
    $DataBarang = mysqli_fetch_array($QryBarang);
    if(empty($DataBarang['id_barang'])){
        echo '
            <div class="alert alert-danger text-center">
                <small>
                    <b>Opss!</b><br<
                    ID Barang Tidak Valid atau Tidak Ditemukan Pada Database!
                </small>
            </div>
        ';
        exit;
    }
    $id_barang       = $DataBarang['id_barang'];
    $kode_barang     = $DataBarang['kode_barang'];
    $nama_barang     = $DataBarang['nama_barang'];
    $kategori_barang = $DataBarang['kategori_barang'];
    $satuan_barang   = $DataBarang['satuan_barang'];
    $konversi        = $DataBarang['konversi'];
    $harga_beli      = $DataBarang['harga_beli'];
    $harga_beli_rp   = "Rp " . number_format($harga_beli,0,',','.');
    $stok_barang     = $DataBarang['stok_barang'];

    // Tampilkan Data
    echo '
        <div class="row mb-2">
            <div class="col-4"><small>ID Barang</small></div>
            <div class="col-8 text-end">
                <small class="text-dark">'.$id_barang.'</small>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Kode</small></div>
            <div class="col-8 text-end">
                <small class="text-dark">'.$kode_barang.'</small>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Nama/Merek</small></div>
            <div class="col-8 text-end">
                <small class="text-dark">'.$nama_barang.'</small>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Kategori</small></div>
            <div class="col-8 text-end">
                <small class="text-dark">'.$kategori_barang.'</small>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Satuan</small></div>
            <div class="col-8 text-end">
                <small class="text-dark">'.$satuan_barang.'</small>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>HPP</small></div>
            <div class="col-8 text-end">
                <small class="text-dark">'.$harga_beli_rp.'</small>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Stock</small></div>
            <div class="col-8 text-end">
                <small class="text-dark">'.$stok_barang.'</small>
            </div>
        </div>
    ';
?>

