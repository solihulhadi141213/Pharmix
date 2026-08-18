<?php
    //Menangkap session kemudian menampilkannya
    session_start();
    date_default_timezone_set('Asia/Jakarta');
    
    // Inisialisasi default
    $SessionIdAkses = "";
    $SessionLoginToken = "";
    
    // Validasi session tersedia
    if(empty($_SESSION["id_akses"]) || empty($_SESSION["login_token"])) {
        // Session tidak valid - gunakan nilai default
    } else {
        $SessionIdAkses    = validateAndSanitizeInput($_SESSION["id_akses"]);
        $SessionLoginToken = validateAndSanitizeInput($_SESSION["login_token"]);
        
        // Validasi Token Akses menggunakan Prepared Statement
        $stmt = mysqli_prepare($Conn, "SELECT * FROM akses_login WHERE id_akses = ? AND token = ?");
        mysqli_stmt_bind_param($stmt, "ss", $SessionIdAkses, $SessionLoginToken);
        mysqli_stmt_execute($stmt);
        $QryAksesLogin  = mysqli_stmt_get_result($stmt);
        $DataAksesLogin = mysqli_fetch_array($QryAksesLogin);
        mysqli_stmt_close($stmt);
        
        // Apabila token akses tidak ditemukan
        if(empty($DataAksesLogin['id_akses'])) {
            $SessionIdAkses = "";
            $SessionLoginToken = "";
        } else {
            // Validasi token masih berlaku
            $SessionDateExpired = $DataAksesLogin['date_expired'];
            $DateSekarang = date('Y-m-d H:i:s');
            
            if($SessionDateExpired < $DateSekarang) {
                // Token sudah expired
                $SessionIdAkses = "";
                $SessionLoginToken = "";
            } else {
                // Token masih valid - Update token expiration
                $expired_milisecond = 1000*60*60;
                $date_expired_new = calculateExpirationTimeFromDateTime($DateSekarang, $expired_milisecond);
                
                // Update Token menggunakan Prepared Statement
                $stmtUpdate = mysqli_prepare($Conn, "UPDATE akses_login SET date_expired = ? WHERE id_akses = ?");
                mysqli_stmt_bind_param($stmtUpdate, "ss", $date_expired_new, $SessionIdAkses);
                $UpdateToken = mysqli_stmt_execute($stmtUpdate);
                mysqli_stmt_close($stmtUpdate);
                
                if(!$UpdateToken) {
                    $SessionIdAkses    = "";
                    $SessionLoginToken = "";
                }
                // Jika update berhasil, session tetap valid dengan data dari database
            }
        }
    }
?>
