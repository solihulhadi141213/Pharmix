<?php
    //Buka Akses Pengguna Berdasarkan SessionIdAkses menggunakan Prepared Statement
    $stmt = mysqli_prepare($Conn, "SELECT * FROM akses WHERE id_akses = ?");
    mysqli_stmt_bind_param($stmt, "s", $SessionIdAkses);
    mysqli_stmt_execute($stmt);
    $QryAccessSession  = mysqli_stmt_get_result($stmt);
    $DataAccessSession = mysqli_fetch_array($QryAccessSession);
    mysqli_stmt_close($stmt);
    
    if(empty($DataAccessSession['id_akses'])){
        $SessionNama           = "";
        $SessionKontakAkses    = "";
        $SessionEmailAkses     = "";
        $SessionGambar         = "";
        $SessionLevelAkses     = "None";
        $SessionDatetimeDaftar = "";
        $SessionDatetimeUpdate = "";
       
    }else{
        $SessionNama           = $DataAccessSession['nama_akses'];
        $SessionKontakAkses    = $DataAccessSession['kontak_akses'];
        $SessionEmailAkses     = $DataAccessSession['email_akses'];
        $SessionGambar         = $DataAccessSession['image_akses'];
        $SessionLevelAkses     = $DataAccessSession['akses'];
        $SessionDatetimeDaftar = $DataAccessSession['datetime_daftar'];
        $SessionDatetimeUpdate = $DataAccessSession['datetime_update'];
    }

    // Jika Tidak Ada Foto
    if(empty($SessionGambar)){
        $SessionGambar="No-Image.png";
    }
?>
