<?php
    if(empty($_GET['Page'])){
        include "_Page/Dashboard/Dashboard.php";
    }else{
        $Page=$_GET['Page'];
        //Index Halaman
        $page_arry=[
            "AksesFitur"            => "_Page/AksesFitur/AksesFitur.php",
            "AksesEntitas"          => "_Page/AksesEntitas/AksesEntitas.php",
            "Akses"                 => "_Page/Akses/Akses.php",
            "Pasien"                => "_Page/Pasien/Pasien.php",
            "JenisTransaksi"        => "_Page/JenisTransaksi/JenisTransaksi.php",
            "Transaksi"             => "_Page/Transaksi/Transaksi.php",
            "RekapTransaksi"        => "_Page/RekapTransaksi/RekapTransaksi.php",
            "Penjualan"             => "_Page/Penjualan/Penjualan.php",
            "Pembelian"             => "_Page/Pembelian/Pembelian.php",
            "RekapJualBeli"         => "_Page/RekapJualBeli/RekapJualBeli.php",
            "Version"               => "_Page/Version/Version.php",
            "SettingGeneral"        => "_Page/SettingGeneral/SettingGeneral.php",
            "EntitasAkses"          => "_Page/EntitasAkses/EntitasAkses.php",
            "Pembayaran"          => "_Page/Pembayaran/Pembayaran.php",
            "UtangPiutang"          => "_Page/UtangPiutang/UtangPiutang.php",
            "Barang"                => "_Page/Barang/Barang.php",
            "BarangExpired"         => "_Page/BarangExpired/BarangExpired.php",
            "StockOpename"          => "_Page/StockOpename/StockOpename.php",
            "Supplier"              => "_Page/Supplier/Supplier.php",
            "AutoJurnal"            => "_Page/AutoJurnal/AutoJurnal.php",
            "MyProfile"             => "_Page/MyProfile/MyProfile.php",
            "Dokumentasi"           => "_Page/Dokumentasi/Dokumentasi.php",
            "SettingEmailGateway"          => "_Page/SettingEmailGateway/SettingEmailGateway.php",
            "Aktivitas"             => "_Page/Aktivitas/Aktivitas.php",
            "AkunPerkiraan"         => "_Page/AkunPerkiraan/AkunPerkiraan.php",
            "Jurnal"                => "_Page/Jurnal/Jurnal.php",
            "BukuBesar"             => "_Page/BukuBesar/BukuBesar.php",
            "NeracaSaldo"           => "_Page/NeracaSaldo/NeracaSaldo.php",
            "LabaRugi"              => "_Page/LabaRugi/LabaRugi.php",
            "RekapitulasiTransaksi" => "_Page/RekapitulasiTransaksi/RekapitulasiTransaksi.php",
            "SettingSatuSehat"          => "_Page/SettingSatuSehat/SettingSatuSehat.php",
            "Error"                 => "_Page/Error/Error.php"
        ];

        //Tangkap 'Page'
        $Page = !empty($_GET['Page']) ? $_GET['Page'] : "";

        //Kondisi Pada masing-masing Page
        if (array_key_exists($Page, $page_arry)) { 
            include $page_arry[$Page]; 
        } else { 
            include "_Page/Dashboard/Dashboard.php";
        }
    }
?>