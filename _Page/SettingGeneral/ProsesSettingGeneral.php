<?php
header('Content-Type: application/json');

include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";

try{

    // Validasi Session
    if(empty($SessionIdAkses)){
        throw new Exception("Sesi akses tidak valid, silahkan login kembali.");
    }

    // Ambil Data
    $title_page      = trim($_POST['title_page'] ?? '');
    $kata_kunci      = trim($_POST['kata_kunci'] ?? '');
    $deskripsi       = trim($_POST['deskripsi'] ?? '');
    $alamat_bisnis   = trim($_POST['alamat_bisnis'] ?? '');
    $email_bisnis    = trim($_POST['email_bisnis'] ?? '');
    $telepon_bisnis  = trim($_POST['telepon_bisnis'] ?? '');
    $base_url        = trim($_POST['base_url'] ?? '');
    $author          = trim($_POST['author'] ?? '');

    // Validasi
    if(empty($title_page)){
        throw new Exception("Nama perusahaan tidak boleh kosong.");
    }

    // Cek Data Setting
    $QrySetting = mysqli_query(
        $Conn,
        "SELECT * FROM setting_general LIMIT 1"
    );

    if(mysqli_num_rows($QrySetting)>0){

        $DataSetting = mysqli_fetch_assoc($QrySetting);
        $id_setting_general = $DataSetting['id_setting_general'];

        $Update = mysqli_query(
            $Conn,
            "UPDATE setting_general SET
                title_page='$title_page',
                kata_kunci='$kata_kunci',
                deskripsi='$deskripsi',
                alamat_bisnis='$alamat_bisnis',
                email_bisnis='$email_bisnis',
                telepon_bisnis='$telepon_bisnis',
                base_url='$base_url',
                author='$author'
            WHERE id_setting_general='$id_setting_general'"
        );

        if(!$Update){
            throw new Exception(mysqli_error($Conn));
        }

    }else{

        $Insert = mysqli_query(
            $Conn,
            "INSERT INTO setting_general (
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
            ) VALUES (
                '$title_page',
                '$kata_kunci',
                '$deskripsi',
                '$alamat_bisnis',
                '$email_bisnis',
                '$telepon_bisnis',
                '',
                '',
                '$base_url',
                '$author'
            )"
        );

        if(!$Insert){
            throw new Exception(mysqli_error($Conn));
        }
    }

    echo json_encode([
        "success" => true,
        "message" => "Pengaturan berhasil diperbarui."
    ]);

}catch(Exception $e){

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);

}