<?php
    include "_Page/Logout/ModalLogout.php";
    if(!empty($_GET['Page'])){
        $Page=$_GET['Page'];
        
        // Daftar halaman dan modal yang terkait
        $modals = [
            "MyProfile"             => "_Page/MyProfile/ModalMyProfile.php",
            "AksesFitur"            => "_Page/AksesFitur/ModalAksesFitur.php",
            "AksesEntitas"          => "_Page/AksesEntitas/ModalAksesEntitas.php",
            "Akses"                 => "_Page/Akses/ModalAkses.php",
            "Pasien"               => "_Page/Pasien/ModalPasien.php",
            "Barang"                => "_Page/Barang/ModalBarang.php",
            "BarangExpired"         => "_Page/BarangExpired/ModalBarangExpired.php",
            "StockOpename"          => "_Page/StockOpename/ModalStockOpename.php",
            "Supplier"              => "_Page/Supplier/ModalSupplier.php",
            "JenisTransaksi"        => "_Page/JenisTransaksi/ModalJenisTransaksi.php",
            "Transaksi"             => "_Page/Transaksi/ModalTransaksi.php",
            "RekapTransaksi"        => "_Page/RekapTransaksi/ModalRekapTransaksi.php",
            "Penjualan"             => "_Page/Penjualan/ModalPenjualan.php",
            "Pembelian"             => "_Page/Pembelian/ModalPembelian.php",
            "RekapJualBeli"         => "_Page/RekapJualBeli/ModalRekapJualBeli.php",
            "UtangPiutang"          => "_Page/UtangPiutang/ModalUtangPiutang.php",
            "SettingGeneral"        => "_Page/SettingGeneral/ModalSettingGeneral.php",
            "AutoJurnal"            => "_Page/AutoJurnal/ModalAutoJurnal.php",
            "Help"                  => "_Page/Help/ModalHelp.php",
            "SettingEmail"          => "_Page/SettingService/ModalSettingService.php",
            "Supplier"              => "_Page/Supplier/ModalSupplier.php",
            "Pembayaran"            => "_Page/Pembayaran/ModalPembayaran.php",
            "Aktivitas"             => "_Page/Aktivitas/ModalAktivitas.php",
            "AkunPerkiraan"         => "_Page/AkunPerkiraan/ModalAkunPerkiraan.php",
            "Jurnal"                => "_Page/Jurnal/ModalJurnal.php",
            "BukuBesar"             => "_Page/BukuBesar/ModalBukuBesar.php",
            "NeracaSaldo"           => "_Page/NeracaSaldo/ModalNeracaSaldo.php",
            "LabaRugi"              => "_Page/LabaRugi/ModalLabaRugi.php",
            "RekapitulasiTransaksi" => "_Page/RekapitulasiTransaksi/ModalRekapitulasiTransaksi.php"
        ];

        // Cek apakah halaman memiliki modal terkait dan sertakan file modalnya
        if (!empty($_GET['Page']) && isset($modals[$_GET['Page']])) {
            include $modals[$_GET['Page']];
        }
    }
?>