<?php
    //Koneksi
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Default Response JSON
    header('Content-Type: application/json; charset=utf-8');

    // Default $html
    $html ="";
    
    // Validasi 'id_pembayaran'
    if(empty($_POST['id_pembayaran'])){
        $response = [
            "status"  => "error",
            "message" => "ID Pembayaran Tidak Boleh Kosong",
            "html"    => ""
        ];
        echo json_encode($response);
        exit;
    }

    // Variabel dan sanitasi
    $id_transaksi_pembayaran = validateAndSanitizeInput($_POST['id_pembayaran']);
    
    // Buka Data 'transaksi_pembayaran'
    $Qry = $Conn->prepare("SELECT * FROM transaksi_pembayaran WHERE id_transaksi_pembayaran = ?");
    $Qry->bind_param("s", $id_transaksi_pembayaran);
    if (!$Qry->execute()) {
        $response = [
            "status"  => "error",
            "message" => 'Terjadi Kesalahan : '.$Conn->error,
            "html"    => ""
        ];
        echo json_encode($response);
        exit;
    }
    $Result = $Qry->get_result();
    $Data = $Result->fetch_assoc();
    $Qry->close();

    if (!$Data) {
        $response = [
            "status"  => "error",
            "message" => 'ID Pembayaran <i>'.$id_transaksi_pembayaran.'</i> Tidak Ditemukan Pada Database',
            "html"    => ""
        ];
        echo json_encode($response);
        exit;
    }

    // Ambil Data Transaksi Pembayaran
    $id_transaksi           = $Data['id_transaksi'];
    $id_transaksi_jual_beli = $Data['id_transaksi_jual_beli'];
    $kategori_pembayaran    = $Data['kategori_pembayaran'];
    $kategori_transaksi     = $Data['kategori_transaksi'];
    $tanggal                = $Data['tanggal'];
    $jumlah                 = pembulatan_nilai($Data['jumlah']);
    $creat_by_id            = $Data['creat_by_id'];
    $creat_by_name          = $Data['creat_by_name'];
    $creat_at               = $Data['creat_at'];
    $update_by_id           = $Data['update_by_id'];
    $update_by_name         = $Data['update_by_name'];
    $update_at              = $Data['update_at'];

    // Format Rupiah Total Pembayaran
    $jumlah_rp  = "Rp " . number_format($jumlah, 0, ',', '.');

    // Ambil nama creator dan updater dari tabel akses
    $creator = (!empty($creat_by_id)) ? GetDetailData($Conn, 'akses', 'id_akses', $creat_by_id, 'nama_akses') : "$creat_by_name";
    $updater = (!empty($update_by_id)) ? GetDetailData($Conn, 'akses', 'id_akses', $update_by_id, 'nama_akses') : "$update_by_name";

    // Routing Berdasarkan Transaksi Induk
    if(!empty($id_transaksi)){
        $KodeTransaksi = $id_transaksi;
    }else{
        $KodeTransaksi = $id_transaksi_jual_beli;
    }

    // Format tanggal
    $tanggal   = $tanggal ? date('d/m/Y H:i:s', strtotime($tanggal)) : '-';
    $creat_at  = $creat_at ? date('d/m/Y H:i:s', strtotime($creat_at)) : '-';
    $update_at = $update_at ? date('d/m/Y H:i:s', strtotime($update_at)) : '-';

    $html='
        <input type="hidden" name="id_transaksi_pembayaran" value="'.$id_transaksi_pembayaran.'">
         <div class="row mb-2">
            <div class="col-4">
                <small>ID Pembayaran</small>
            </div>
            <div class="col-8 text-end">
                <small class="text-muted">'.$id_transaksi_pembayaran.'</small>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-4">
                <small>ID Transaksi</small>
            </div>
            <div class="col-8 text-end">
                <small class="text-muted">'.$KodeTransaksi.'</small>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-4">
                <small>Transaksi</small>
            </div>
            <div class="col-8 text-end">
                <small class="text-muted">'.$kategori_transaksi.'</small>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-4">
                <small>Kategori</small>
            </div>
            <div class="col-8 text-end">
                <small class="text-muted">'.$kategori_pembayaran.'</small>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-4">
                <small>Tanggal</small>
            </div>
            <div class="col-8 text-end">
                <small class="text-muted">'.$tanggal.'</small>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-4">
                <small>Nominal</small>
            </div>
            <div class="col-8 text-end">
                <small class="text-muted">'.$jumlah_rp.'</small>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <div class="alert alert-warning text-center">
                    <small>
                        <b>Penting!</b><br>
                        Menghapus data pembayaran, mungkin akan menyebabkan status transaksi yang berkaitan berubah.<br><br>
                        <p><i>Apakah anda yakin akan menghapus data tersebut?</i></p>
                    </small>
                </div>
            </div>
        </div>
    ';

    $response = [
        "status"  => "success",
        "message" => 'Data Berhasil Ditampilkan',
        "html"    => $html
    ];
    echo json_encode($response);
?>