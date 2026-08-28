<?php 
    $date_version=date('YmdHis');
    if(empty($_GET['Page'])){
        //Dafault Javascript Diarahkan Ke Dashboard
        echo '<script type="text/javascript" src="_Page/Dashboard/Dashboard.js?V='.$date_version.'"></script>';
    }else{
        $Page=$_GET['Page'];
        // Routing Javascript Berdasarkan Halaman
        $scripts = [
            "MyProfile"             => "_Page/MyProfile/MyProfile.js",
            "AksesFitur"            => "_Page/AksesFitur/AksesFitur.js",
            "AksesEntitas"          => "_Page/AksesEntitas/AksesEntitas.js",
            "Akses"                 => "_Page/Akses/Akses.js",
            "Pasien"                => "_Page/Pasien/Pasien.js",
            "Barang"                => "_Page/Barang/Barang.js",
            "BarangExpired"         => "_Page/BarangExpired/BarangExpired.js",
            "StockOpename"          => "_Page/StockOpename/StockOpename.js",
            "Supplier"              => "_Page/Supplier/Supplier.js",
            "JenisTransaksi"        => "_Page/JenisTransaksi/JenisTransaksi.js",
            "Transaksi"             => "_Page/Transaksi/Transaksi.js",
            "RekapTransaksi"        => "_Page/RekapTransaksi/RekapTransaksi.js",
            "Penjualan"             => "_Page/Penjualan/Penjualan.js",
            "Pembelian"             => "_Page/Pembelian/Pembelian.js",
            "RekapJualBeli"         => "_Page/RekapJualBeli/RekapJualBeli.js",
            "Pembayaran"          => "_Page/Pembayaran/Pembayaran.js",
            "UtangPiutang"          => "_Page/UtangPiutang/UtangPiutang.js",
            "TransaksiJualBeli"     => "_Page/TransaksiJualBeli/Transaksi.js",
            "AkunPerkiraan"         => "_Page/AkunPerkiraan/AkunPerkiraan.js",
            "Jurnal"                => "_Page/Jurnal/Jurnal.js",
            "SettingGeneral"        => "_Page/SettingGeneral/SettingGeneral.js",
            "EntitasAkses"          => "_Page/EntitasAkses/EntitasAkses.js",
            "ApiDoc"                => "_Page/ApiDoc/ApiDoc.js",
            "AutoJurnal"            => "_Page/AutoJurnal/AutoJurnal.js",
            "Dokumentasi"           => "_Page/Dokumentasi/Dokumentasi.js",
            "SettingEmail"          => "_Page/SettingService/SettingService.js",
            "Pembayaran"            => "_Page/Pembayaran/Pembayaran.js",
            "Aktivitas"             => "_Page/Aktivitas/Aktivitas.js",
            "BukuBesar"             => "_Page/BukuBesar/BukuBesar.js",
            "NeracaSaldo"           => "_Page/NeracaSaldo/NeracaSaldo.js",
            "LabaRugi"              => "_Page/LabaRugi/LabaRugi.js",
            "RekapitulasiTransaksi" => "_Page/RekapitulasiTransaksi/RekapitulasiTransaksi.js"
        ];

        // Cek apakah halaman ada dalam daftar dan sertakan file JS yang sesuai
        if (!empty($_GET['Page']) && isset($scripts[$_GET['Page']])) {
            echo '<script type="text/javascript" src="' . $scripts[$_GET['Page']] . '?V='.$date_version.'"></script>';
        }
    }
    echo '<script type="text/javascript" src="_Partial/Universal.js?V='.$date_version.'"></script>';
?>