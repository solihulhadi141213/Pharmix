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
    $tanggal                = $Data['tanggal'];
    $jumlah                 = pembulatan_nilai($Data['jumlah']);

    // Format Rupiah Total Pembayaran
    $jumlah_rp  = "" . number_format($jumlah, 0, ',', '.');

    // Format tanggal
    $tanggal_format   = $tanggal ? date('Y-m-d', strtotime($tanggal)) : '-';
    $jam_format  = date('H:i:s', strtotime($tanggal));

    $html='
        <input type="hidden" name="id_transaksi_pembayaran" value="'.$id_transaksi_pembayaran.'">
        <div class="row mb-3">
            <div class="col col-md-12">
                <label for="tanggal_pembayaran_edit">
                    <small>Tanggal Pembayaran</small>
                </label>
                <input type="date" name="tanggal_pembayaran" id="tanggal_pembayaran_edit" class="form-control" value="'.$tanggal_format .'">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col col-md-12">
                <label for="jam_pembayaran_edit">
                    <small>Jam Pembayaran</small>
                </label>
                <input type="time" name="jam_pembayaran" id="jam_pembayaran_edit" class="form-control" value="'.$jam_format .'">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col col-md-12">
                <label for="jumlah_edit">
                    <small>Jumlah Nominal</small>
                </label>
                <input type="text" name="jumlah" id="jumlah_edit" class="form-control form-money" placeholder="Rp" value="'.$jumlah_rp .'">
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