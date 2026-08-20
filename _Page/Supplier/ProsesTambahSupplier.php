<?php
    //Koneksi
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Time Zone
    date_default_timezone_set('Asia/Jakarta');

    // Time Now Tmp
    $now   = date('Y-m-d H:i:s');
    $today = date('Y-m-d');

    // Response Helper
    function Response($status, $message){
        echo json_encode([
            "status"  => $status,
            "message" => $message
        ]);
        exit;
    }

    // Validasi Sesi Akses
    if(empty($SessionIdAkses)){
        Response("error", "Sesi akses sudah berakhir. Silakan login ulang.");
    }

    // Validasi Mandatory
    if(empty($_POST['nama_supplier'])){
        Response("error", "Nama Supplier Tidak Boleh Kosong!");
    }

    // Buat Variabel
    $nama_supplier=validateAndSanitizeInput($_POST['nama_supplier']);
    if(empty($_POST['email_supplier'])){
        $email_supplier="";
    }else{
        $email_supplier=validateAndSanitizeInput($_POST['email_supplier']);
    }
    if(empty($_POST['kontak_supplier'])){
        $kontak_supplier="";
    }else{
        $kontak_supplier=validateAndSanitizeInput($_POST['kontak_supplier']);
    }
    if(empty($_POST['alamat_supplier'])){
        $alamat_supplier="";
    }else{
        $alamat_supplier=validateAndSanitizeInput($_POST['alamat_supplier']);
    }
    if(empty($_POST['pic'])){
        $pic="";
    }else{
        $pic=validateAndSanitizeInput($_POST['pic']);
    }
    if(empty($_POST['npwp'])){
        $npwp="";
    }else{
        $npwp=validateAndSanitizeInput($_POST['npwp']);
    }

    // Validasi Kontak
    if(!empty($_POST['kontak_supplier'])){
        $JumlahKarakterKontak=strlen($_POST['kontak_supplier']);
        if($JumlahKarakterKontak>20){
            Response("error", "Kontak hanya boleh terdiri dari 6-20 karakter!");
        }
    }
    
    //Validasi data duplikat 'nama_supplier'
    $ValidasiDuplikat=mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM supplier WHERE nama_supplier='$nama_supplier'"));
    if(!empty($ValidasiDuplikat)){
        Response("error", "Supplier <b>$nama_supplier</b> Sudah Terdaftar!");
    }

    //Validasi data duplikat 'npwp'
    if(!empty($npwp)){
        $ValidasiDuplikatNpwp=mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM supplier WHERE npwp='$npwp'"));
        if(!empty($ValidasiDuplikatNpwp)){
            Response("error", "NPWP <b>$npwp</b> Sudah Terdaftar!");
        }
    }
    
    //Simpan data
    $entry="INSERT INTO supplier (
        nama_supplier,
        alamat_supplier,
        email_supplier,
        kontak_supplier,
        pic,
        npwp
    ) VALUES (
        '$nama_supplier',
        '$alamat_supplier',
        '$email_supplier',
        '$kontak_supplier',
        '$pic',
        '$npwp'
    )";
    $Input=mysqli_query($Conn, $entry);
    if(!$Input){
        Response("error", "Terjadi kesalahan pada saat menyimpan data!");
    }

    // Simpan Log
    $kategori_log  = "Supplier";
    $deskripsi_log = "Input Supplier Baru";
    $InputLog=addLog($Conn,$SessionIdAkses,$now,$kategori_log,$deskripsi_log);
    if($InputLog!=="Success"){
       Response("error", "Terjadi Kesalahan Pada Saat Menyimpan Log ($InputLog)");
    }

    // Berhasil
    Response("success", "Data Berhasil Disimpan");
      
?>