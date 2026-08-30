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

        // Validasi File
        if(empty($_FILES['logo']['name'])){
            throw new Exception("Pilih file logo terlebih dahulu.");
        }

        // Validasi Ukuran File (2 MB)
        if($_FILES['logo']['size'] > 2097152){
            throw new Exception("Ukuran file logo maksimal 2 MB.");
        }

        // Validasi Ekstensi
        $Extensi = strtolower(
            pathinfo(
                $_FILES['logo']['name'],
                PATHINFO_EXTENSION
            )
        );

        $Allow = ['png', 'jpg', 'jpeg', 'webp'];

        if(!in_array($Extensi, $Allow)){
            throw new Exception(
                "Format file tidak didukung. Gunakan PNG, JPG, JPEG atau WEBP."
            );
        }

        // Cek Data Setting
        $QrySetting = mysqli_query(
            $Conn,
            "SELECT * FROM setting_general LIMIT 1"
        );

        if(mysqli_num_rows($QrySetting) > 0){

            $DataSetting = mysqli_fetch_assoc($QrySetting);

        }else{

            // Buat Data Setting Awal
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

            if(!$Insert){
                throw new Exception(mysqli_error($Conn));
            }

            $QrySetting = mysqli_query(
                $Conn,
                "SELECT * FROM setting_general
                ORDER BY id_setting_general DESC
                LIMIT 1"
            );

            $DataSetting = mysqli_fetch_assoc($QrySetting);
        }

        $id_setting_general = $DataSetting['id_setting_general'];

        // Folder Upload
        $Direktori = "../../assets/img/";

        // Nama File Baru
        $NamaBaru = "logo_" . date('YmdHis') . "_" . rand(1000,9999) . "." . $Extensi;

        // Upload File
        if(!move_uploaded_file(
            $_FILES['logo']['tmp_name'],
            $Direktori . $NamaBaru
        )){
            throw new Exception("Gagal mengupload file logo.");
        }

        // Hapus File Lama
        if(!empty($DataSetting['logo'])){

            $FileLama = $Direktori . $DataSetting['logo'];

            if(file_exists($FileLama)){
                @unlink($FileLama);
            }
        }

        // Update Database
        $Update = mysqli_query(
            $Conn,
            "UPDATE setting_general
            SET logo='$NamaBaru'
            WHERE id_setting_general='$id_setting_general'"
        );

        if(!$Update){
            throw new Exception(mysqli_error($Conn));
        }

        // Success
        echo json_encode([
            "success" => true,
            "message" => "Logo berhasil diperbarui."
        ]);

    }catch(Exception $e){

        echo json_encode([
            "success" => false,
            "message" => $e->getMessage()
        ]);

    }
?>