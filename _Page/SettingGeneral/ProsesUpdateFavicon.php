<?php
header('Content-Type: application/json');

include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";

try{

    if(empty($SessionIdAkses)){
        throw new Exception("Sesi akses tidak valid.");
    }

    if(empty($_FILES['favicon']['name'])){
        throw new Exception("Pilih file favicon terlebih dahulu.");
    }

    $Extensi = strtolower(
        pathinfo(
            $_FILES['favicon']['name'],
            PATHINFO_EXTENSION
        )
    );

    $Allow = ['png','jpg','jpeg','webp','ico','svg'];

    if(!in_array($Extensi,$Allow)){
        throw new Exception("Format file tidak didukung.");
    }

    if($_FILES['favicon']['size'] > 2097152){
        throw new Exception("Ukuran file maksimal 2 MB.");
    }

    // Cari setting
    $QrySetting = mysqli_query(
        $Conn,
        "SELECT * FROM setting_general LIMIT 1"
    );

    if(mysqli_num_rows($QrySetting)>0){

        $DataSetting = mysqli_fetch_assoc($QrySetting);

    }else{

        mysqli_query(
            $Conn,
            "INSERT INTO setting_general(
                title_page,
                kata_kunci,
                deskripsi,
                alamat_bisnis,
                email_bisnis,
                telepon_bisnis,
                favicon,
                logo,
                base_url,
                author
            ) VALUES(
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            )"
        );

        $QrySetting = mysqli_query(
            $Conn,
            "SELECT * FROM setting_general ORDER BY id_setting_general DESC LIMIT 1"
        );

        $DataSetting = mysqli_fetch_assoc($QrySetting);
    }

    $id_setting_general = $DataSetting['id_setting_general'];

    $NamaBaru = "favicon_".time().".".$Extensi;

    $Direktori = "../../assets/img/";

    if(!move_uploaded_file(
        $_FILES['favicon']['tmp_name'],
        $Direktori.$NamaBaru
    )){
        throw new Exception("Gagal upload file.");
    }

    // Hapus file lama
    if(!empty($DataSetting['favicon'])){

        $FileLama = $Direktori.$DataSetting['favicon'];

        if(file_exists($FileLama)){
            unlink($FileLama);
        }
    }

    $Update = mysqli_query(
        $Conn,
        "UPDATE setting_general
        SET favicon='$NamaBaru'
        WHERE id_setting_general='$id_setting_general'"
    );

    if(!$Update){
        throw new Exception(mysqli_error($Conn));
    }

    echo json_encode([
        "success"=>true,
        "message"=>"Favicon berhasil diperbarui."
    ]);

}catch(Exception $e){

    echo json_encode([
        "success"=>false,
        "message"=>$e->getMessage()
    ]);

}