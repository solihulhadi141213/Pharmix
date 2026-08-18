<?php
    //Inisiasi setting menggunakan Prepared Statement
    $id_setting = '1';
    $stmt       = mysqli_prepare($Conn, "SELECT * FROM setting_email_gateway WHERE id_setting_email_gateway = ?");
    mysqli_stmt_bind_param($stmt, "s", $id_setting);
    mysqli_stmt_execute($stmt);
    $QryPaymentSetting  = mysqli_stmt_get_result($stmt);
    $DataPaymentsetting = mysqli_fetch_array($QryPaymentSetting);
    mysqli_stmt_close($stmt);
    
    // Inisialisasi default jika data tidak ditemukan
    $email_gateway    = $DataPaymentsetting['email_gateway'] ?? '';
    $password_gateway = $DataPaymentsetting['password_gateway'] ?? '';
    $url_provider     = $DataPaymentsetting['url_provider'] ?? '';
    $port_gateway     = $DataPaymentsetting['port_gateway'] ?? '';
    $nama_pengirim    = $DataPaymentsetting['nama_pengirim'] ?? '';
    $url_service      = $DataPaymentsetting['url_service'] ?? '';
?>