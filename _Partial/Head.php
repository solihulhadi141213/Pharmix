<?php
    // Menentukan environment
    $environment = "Production"; // Bernilai Production OR Development
    if($environment=="Development"){
        $lib_version = "";
    }else{
        $lib_version = date('YmdHis');
    }

    // Menentukan Judul Halaman Berdasarkan Fitur
    if(empty($_GET['Page'])){
        $subtitle = $title_page;
    }else{
        $Page=$_GET['Page'];
        //Index Halaman
        $page_arry=[
            "AksesFitur"            => "Fitur Aplikasi",
            "AksesEntitas"          => "Entitas Akses",
            "Akses"                 => "Akses Pengguna",
            "Pasien"                => "Master Pasien",
            "JenisTransaksi"        => "Kategori Operasional",
            "Transaksi"             => "Transaksi Operasional",
            "RekapTransaksi"        => "Rekapitulasi Transaksi Operasional",
            "Penjualan"             => "Transaksi Penjualan",
            "Pembelian"             => "Transaksi Pembelian",
            "RekapJualBeli"         => "Rekapitulasi Transaksi",
            "SettingGeneral"        => "Setting General",
            "UtangPiutang"          => "Utang-Piutang",
            "Barang"                => "Master Barang",
            "BarangExpired"         => "Barang Expired",
            "StockOpename"          => "Stock Opname",
            "Supplier"              => "Master Supplier",
            "AutoJurnal"            => "Auto Jurnal",
            "MyProfile"             => "Profil Saya",
            "Dokumentasi"           => "Dokumentasi Aplikasi",
            "SettingEmail"          => "Setting Email",
            "Aktivitas"             => "Log Aktivitas",
            "AkunPerkiraan"         => "Akun Perkiraan",
            "Jurnal"                => "Jurnal Transaksi",
            "BukuBesar"             => "Buku Besar",
            "NeracaSaldo"           => "Neraca Saldo",
            "LabaRugi"              => "Laba Rugi",
            "RekapitulasiTransaksi" => "Rekapitulasi Transaksi",
            "CetakInvoice"          => "Cetak Invoice",
            "Error"                 => "Error"
        ];

        //Tangkap 'Page'
        $Page = !empty($_GET['Page']) ? $_GET['Page'] : "";

        //Kondisi Pada masing-masing Page
        if (array_key_exists($Page, $page_arry)) { 
            $subtitle = $page_arry[$Page]; 
        } else { 
            $subtitle = $title_page;
        }
    }
?>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title><?php echo "$subtitle"; ?></title>
<meta content="<?php echo "$deskripsi"; ?>" name="description">
<meta content="<?php echo "$kata_kunci"; ?>" name="keywords">

<!-- Favicons -->
<link href="assets/img/<?php echo "$favicon"; ?>" rel="icon">
<link href="assets/img/<?php echo "$favicon"; ?>" rel="apple-touch-icon">

<!-- Google Fonts -->
<link href="assets/fonts/fonts.css" rel="stylesheet">

<!-- Vendor CSS Files -->
<link href="node_modules/bootstrap/dist/css/bootstrap.min.css?v=<?php echo $lib_version; ?>" rel="stylesheet">
<link href="node_modules/bootstrap-icons/font/bootstrap-icons.css?v=<?php echo $lib_version; ?>" rel="stylesheet">
<link href="node_modules/boxicons/css/boxicons.min.css?v=<?php echo $lib_version; ?>" rel="stylesheet">

<!-- Quil -->
<?php
    if(!empty($_GET['Page'])){
        if($_GET['Page']=="Dokumentasi"){
            echo '
                <link href="node_modules/quill/dist/quill.snow.css" rel="stylesheet">
                <link href="node_modules/quill/dist/quill.bubble.css" rel="stylesheet">
            ';
        }
    }
?>

<link href="node_modules/remixicon/fonts/remixicon.css" rel="stylesheet">
<link href="node_modules/mdb-ui-kit/css/mdb.min.css" rel="stylesheet">

<!-- Custome CSS -->
<link href="assets/css/style.css?v=<?php echo $lib_version; ?>" rel="stylesheet">
<script>
    if (localStorage.getItem('theme_mode') === 'dark') {
        document.documentElement.classList.add('dark-mode');
    }
</script>

<!-- Header JS -->
<script type="text/javascript" src="node_modules/jquery/dist/jquery.min.js"></script>
<script type="text/javascript" src="node_modules/marked/marked.min.js"></script>

<!-- Select2 -->
<link href="node_modules\select2\dist\css\select2.min.css" rel="stylesheet" />
<link href="node_modules\select2-bootstrap-5-theme\dist\select2-bootstrap-5-theme.min.css" rel="stylesheet" />

