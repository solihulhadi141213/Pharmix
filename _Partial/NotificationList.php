<?php
    //Karena Ini Di running Dengan JS maka Panggil Ulang Koneksi
    include "../_Config/Connection.php";
    include "../_Config/GlobalFunction.php";
    include "../_Config/Session.php";
    
    //Menghitung Jumlah Pinjaman Yang Menunggak
    $JumlahNotifikasi=0;
    
    //Apabila Tidak ada notifgikasi
    if(empty($JumlahNotifikasi)){
        echo '<li class="dropdown-header">';
        echo '  Tidak Ada Pemberitahuan Baru';
        echo '</li>';
    }else{
        //Apabila Ada
        echo '<li class="dropdown-header">';
        echo '  Ada '.$JumlahNotifikasi.' Pemberitahuan';
        echo '</li>';
        if(!empty($JumlahNotifikasi)){
            echo '<li><hr class="dropdown-divider"></li>';
            echo '<li class="notification-item">';
            echo '  <i class="bi bi-exclamation-circle text-danger"></i>';
            echo '  <div>';
            echo '      <h4><a href="index.php?Page=Tagihan">Tagihan Pinjaman Belum Dibayar</a></h4>';
            echo '      <p>Ada '.$JumlahNotifikasi.' tagihan pinjaman belum dibayar</p>';
            echo '  </div>';
            echo '</li>';
        }
    }
?>