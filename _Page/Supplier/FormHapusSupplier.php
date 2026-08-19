<?php
    // Json Format
    header('Content-Type: application/json; charset=utf-8');

    //Koneksi
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Sesi Akses
    if (empty($SessionIdAkses)) {
        echo json_encode([
            "status" => "error",
            "message" => "Sesi Akses Sudah Berakhir. Silahkan Login Ulang!",
            "html"   => ''
        ]);
        exit;
    }

    //Tangkap 'id_supplier'
    if(empty($_POST['id_supplier'])){
        echo json_encode([
            "status" => "error",
            "message" => "ID Supplier Tidak Boleh Kosong!",
            "html"   => ''
        ]);
        exit;
    }

    $id_supplier = (int) $_POST['id_supplier'];

    //Ambil detail supplier dan total volume transaksi dalam 1 query
    $sql = "
        SELECT
            s.id_supplier,
            s.nama_supplier,
            s.alamat_supplier,
            s.email_supplier,
            s.kontak_supplier,
            s.pic,
            s.npwp,
            COALESCE(SUM(t.total), 0) AS total_transaksi
        FROM supplier s
        LEFT JOIN transaksi_jual_beli t ON t.id_supplier = s.id_supplier
        WHERE s.id_supplier = ?
        GROUP BY
            s.id_supplier,
            s.nama_supplier,
            s.alamat_supplier,
            s.email_supplier,
            s.kontak_supplier,
            s.pic,
            s.npwp
        LIMIT 1
    ";
    $stmt = $Conn->prepare($sql);
    if(!$stmt){
        echo json_encode([
            "status" => "error",
            "message" => "Gagal menyiapkan data supplier!",
            "html"   => ''
        ]);
        exit;
    }
    $stmt->bind_param("i", $id_supplier);
    $stmt->execute();
    $result = $stmt->get_result();
    $DataSupplier = $result->fetch_assoc();
    $stmt->close();

    if(empty($DataSupplier)){
        echo json_encode([
            "status" => "error",
            "message" => "Data supplier tidak ditemukan!",
            "html"   => ''
        ]);
        exit;
    }

    $id_supplier = $DataSupplier['id_supplier'];
    $nama_supplier = $DataSupplier['nama_supplier'];
    if(empty($DataSupplier['alamat_supplier'])){
        $alamat_supplier='-';
    }else{
        $alamat_supplier= $DataSupplier['alamat_supplier'];
    }
    if(empty($DataSupplier['email_supplier'])){
        $email_supplier='-';
    }else{
        $email_supplier= $DataSupplier['email_supplier'];
    }
    if(empty($DataSupplier['kontak_supplier'])){
        $kontak_supplier='-';
    }else{
        $kontak_supplier= $DataSupplier['kontak_supplier'];
    }
    if(empty($DataSupplier['pic'])){
        $pic='-';
    }else{
        $pic= $DataSupplier['pic'];
    }
    if(empty($DataSupplier['npwp'])){
        $npwp='-';
    }else{
        $npwp= $DataSupplier['npwp'];
    }

    //Hitung volume transaksi
    $jumlah_transaksi = (float) ($DataSupplier['total_transaksi'] ?? 0);
    $VolumeTransaksi  = "Rp " . number_format($jumlah_transaksi,0,',','.');

    echo json_encode([
        "status" => "success",
        "message" => "Form Berhasil Ditampilkan",
        "html"   => '
            <input type="hidden" name="id_supplier" value="'.$id_supplier.'">

            <div class="row mb-2">
                <div class="col-5"><small>Nama Supplier</small></div>
                <div class="col-7"><small class="text-grayish">'.$nama_supplier.'</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><small>Alamat Email</small></div>
                <div class="col-7"><small class="text-grayish">'.$email_supplier.'</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><small>No. Kontak</small></div>
                <div class="col-7"><small class="text-grayish">'.$kontak_supplier.'</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><small>Alamat Operasional</small></div>
                <div class="col-7"><small class="text-grayish">'.$alamat_supplier.'</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><small>PIC <i>(Person In Charge)</i></small></div>
                <div class="col-7"><small class="text-grayish">'.$pic.'</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><small>NPWP </small></div>
                <div class="col-7"><small class="text-grayish">'.$npwp.'</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><small>Volume Transaksi</small></div>
                <div class="col-7"><small class="text-grayish">'.$VolumeTransaksi.'</small></div>
            </div>
            <div class="row mb-2 mt-3">
                <div class="col-12">
                    <div class="alert alert-danger">
                        <small>
                            <b>Penting!</b> Menghapus supplier mungkin akan menyebabkan transaksi yang melibatkan supplier tersebut tidak akan terdefinisikan.<br>
                            <i>Apakah anda yakin akan menghapus data tersebut?</i>
                        </small>
                    </div>
                </div>
            </div>
        '
    ]);
    exit;
?>
