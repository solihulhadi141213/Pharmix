<?php
    //Buka Akses Pengguna Berdasarkan SessionIdAkses
    $QryAccessSession = mysqli_query($Conn,"SELECT * FROM akses WHERE id_akses='$SessionIdAkses'")or die(mysqli_error($Conn));
    $DataAccessSession = mysqli_fetch_array($QryAccessSession);
    if(empty($DataAccessSession['id_akses'])){
        $SessionNama           = "";
        $SessionKontakAkses    = "";
        $SessionEmailAkses     = "";
        $SessionGambar         = "";
        $SessionLevelAkses     = "None";
        $SessionDatetimeDaftar = "";
        $SessionDatetimeUpdate = "";
       
    }else{
        $SessionNama=$DataAccessSession['nama_akses'];
        $SessionKontakAkses=$DataAccessSession['kontak_akses'];
        $SessionEmailAkses=$DataAccessSession['email_akses'];
        $SessionGambar=$DataAccessSession['image_akses'];
        $SessionLevelAkses=$DataAccessSession['akses'];
        $SessionDatetimeDaftar=$DataAccessSession['datetime_daftar'];
        $SessionDatetimeUpdate=$DataAccessSession['datetime_update'];
    }

    // Jika Tidak Ada Foto
    if(empty($SessionGambar)){
        $SessionGambar="No-Image.png";
    }
?>
