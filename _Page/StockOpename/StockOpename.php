<?php
    //Cek Aksesibilitas ke halaman ini
    $IjinAksesSaya=IjinAksesSaya($Conn,$SessionIdAkses,'PLj70Mfj5dhUUvjZqnd');
    if($IjinAksesSaya!=="Ada"){
        include "_Page/Error/NoAccess.php";
    }else{
        include "_Page/StockOpename/StockOpenameHome.php";
    }
?>