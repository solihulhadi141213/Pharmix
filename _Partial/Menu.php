<?php
    if(empty($_GET['Page'])){
        $PageMenu="";
    }else{
        $PageMenu=$_GET['Page'];
    }
    if(empty($_GET['Sub'])){
        $SubMenu="";
    }else{
        $SubMenu=$_GET['Sub'];
    }
?>

<aside id="sidebar" class="sidebar menu_background">
    <ul class="sidebar-nav" id="sidebar-nav">
        <li class="nav-item">
            <a class="nav-link <?php if($PageMenu==""){echo "active";}else{echo "collapsed";} ?>" href="index.php">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <hr class="menu-divider">
        <li class="nav-heading">Master</li>
        <li class="nav-item">
            <a class="nav-link <?php if($PageMenu=="Pasien"){echo "active";}else{echo "collapsed";} ?>" href="index.php?Page=Pasien">
                <i class="bi bi-people"></i>
                <span>Pasien</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php if($PageMenu=="Supplier"){echo "active";}else{echo "collapsed";} ?>" href="index.php?Page=Supplier">
                <i class="bi bi-truck"></i>
                <span>Supplier</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php if($PageMenu=="Resep"){echo "active";}else{echo "collapsed";} ?>" href="index.php?Page=Resep">
                <i class="bi bi-receipt"></i> <span>Resep</span>
            </a>
        </li>
        
        
        <li class="nav-item">
            <a class="nav-link <?php if($PageMenu=="Barang"||$PageMenu=="BarangExpired"||$PageMenu=="StockOpename"){echo "active";}else{echo "collapsed";} ?>" data-bs-target="#icons2-nav" data-bs-toggle="collapse" href="javascript:void(0);">
                <i class="bi bi-box-seam"></i>
                <span>Inventaris</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="icons2-nav" class="nav-content collapse <?php if($PageMenu=="Barang"||$PageMenu=="BarangExpired"||$PageMenu=="StockOpename"){echo "show";} ?>" data-bs-parent="#sidebar-nav">
                <li>
                    <a href="index.php?Page=Barang" class="<?php if($PageMenu=="Barang"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Master Barang</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?Page=BarangExpired" class="<?php if($PageMenu=="BarangExpired"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Batch & Expired</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?Page=StockOpename" class="<?php if($PageMenu=="StockOpename"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Stock Opname</span>
                    </a>
                </li>
            </ul>
        </li>
        <hr class="menu-divider">
        <li class="nav-heading">Transaksi</li>
        <li class="nav-item">
            <a class="nav-link <?php if($PageMenu=="JenisTransaksi"||$PageMenu=="Transaksi"||$PageMenu=="Penjualan"||$PageMenu=="Pembelian"){echo "active";}else{echo "collapsed";} ?>" data-bs-target="#transaksi-nav" data-bs-toggle="collapse" href="javascript:void(0);">
                <i class="bi bi-cart-check"></i>
                <span>Transaksi</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="transaksi-nav" class="nav-content collapse <?php if($PageMenu=="JenisTransaksi"||$PageMenu=="Transaksi"||$PageMenu=="Penjualan"||$PageMenu=="Pembelian"){echo "show";} ?>" data-bs-parent="#sidebar-nav">
                <li>
                    <a href="index.php?Page=JenisTransaksi" class="<?php if($PageMenu=="JenisTransaksi"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Kategori Operasional</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?Page=Transaksi" class="<?php if($PageMenu=="Transaksi"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Transaksi Operasional</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?Page=Penjualan" class="<?php if($PageMenu=="Penjualan"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Transaksi Penjualan</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?Page=Pembelian" class="<?php if($PageMenu=="Pembelian"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Transaksi Pembelian</span>
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a class="nav-link 
            <?php 
                if(
                    $PageMenu=="Pembayaran"||
                    $PageMenu=="AkunPerkiraan"||
                    $PageMenu=="UtangPiutang"
                ){
                    echo "active";
                }else{
                    echo "collapsed";
                } 
            ?>
            " data-bs-target="#keuangan-nav" data-bs-toggle="collapse" href="javascript:void(0);">
                <i class="bi bi-gem"></i>
                <span>Keuangan</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="keuangan-nav" class="nav-content collapse 
            <?php 
                if(
                    $PageMenu=="Pembayaran"||
                    $PageMenu=="AkunPerkiraan"||
                    $PageMenu=="UtangPiutang"
                ){echo "show";} 
            ?>
            " data-bs-parent="#sidebar-nav">
                <li>
                    <a href="index.php?Page=AkunPerkiraan" class="<?php if($PageMenu=="AkunPerkiraan"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Akun Perkiraan</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?Page=UtangPiutang" class="<?php if($PageMenu=="UtangPiutang"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Utang/Piutang</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?Page=Pembayaran" class="<?php if($PageMenu=="Pembayaran"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Pembayaran</span>
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a class="nav-link 
            <?php 
                if(
                    $PageMenu=="Jurnal"||
                    $PageMenu=="BukuBesar"||
                    $PageMenu=="NeracaSaldo"||
                    $PageMenu=="LabaRugi"
                ){
                    echo "active";
                }else{
                    echo "collapsed";
                } 
            ?>
            " data-bs-target="#charts-nav" data-bs-toggle="collapse" href="javascript:void(0);">
                <i class="bi bi-bar-chart"></i><span>Laporan</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="charts-nav" class="nav-content collapse 
            <?php 
                if(
                    $PageMenu=="Jurnal"||
                    $PageMenu=="BukuBesar"||
                    $PageMenu=="NeracaSaldo"||
                    $PageMenu=="LabaRugi"||
                    $PageMenu=="RekapTransaksi"||
                    $PageMenu=="RekapJualBeli"
                ){
                    echo "show";
                } 
            ?>
            " data-bs-parent="#sidebar-nav">
                <li>
                    <a href="index.php?Page=Jurnal" class="<?php if($PageMenu=="Jurnal"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Jurnal</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?Page=BukuBesar" class="<?php if($PageMenu=="BukuBesar"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Buku Besar</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?Page=NeracaSaldo" class="<?php if($PageMenu=="NeracaSaldo"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Neraca saldo</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?Page=LabaRugi" class="<?php if($PageMenu=="LabaRugi"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Laba Rugi</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?Page=RekapTransaksi" class="<?php if($PageMenu=="RekapTransaksi"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Operasional</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?Page=RekapJualBeli" class="<?php if($PageMenu=="RekapJualBeli"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Jual/Beli</span>
                    </a>
                </li>
            </ul>
        </li>
        <hr class="menu-divider">
        <li class="nav-heading">Sistem</li>
        <li class="nav-item">
            <a class="nav-link <?php if($PageMenu=="SettingGeneral"||$PageMenu=="SettingEmailGateway"||$PageMenu=="AutoJurnal"||$PageMenu=="SettingSatuSehat"||$PageMenu=="AksesFitur"||$PageMenu=="AksesEntitas"||$PageMenu=="Akses"){echo "active";}else{echo "collapsed";} ?>" data-bs-target="#components-nav" data-bs-toggle="collapse" href="javascript:void(0);">
                <i class="bi bi-gear"></i>
                    <span>Pengaturan</span><i class="bi bi-chevron-down ms-auto">
                </i>
            </a>
            <ul id="components-nav" class="nav-content collapse <?php if($PageMenu=="SettingGeneral"||$PageMenu=="SettingEmailGateway"||$PageMenu=="AutoJurnal"||$PageMenu=="SettingSatuSehat"||$PageMenu=="AksesFitur"||$PageMenu=="AksesEntitas"||$PageMenu=="Akses"){echo "show";} ?>" data-bs-parent="#sidebar-nav">
                <li>
                    <a href="index.php?Page=SettingGeneral" class="<?php if($PageMenu=="SettingGeneral"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Pengaturan Umum</span>
                    </a>
                </li> 
                <li>
                    <a href="index.php?Page=AutoJurnal" class="<?php if($PageMenu=="AutoJurnal"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Auto Jurnal</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?Page=SettingEmailGateway" class="<?php if($PageMenu=="SettingEmailGateway"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Email Gateway</span>
                    </a>
                </li> 
                <li>
                    <a href="index.php?Page=SettingSatuSehat" class="<?php if($PageMenu=="SettingSatuSehat"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>SATUSEHAT</span>
                    </a>
                </li> 
                <li class="nav-submenu">
                    <a class="nav-content-toggle <?php if($PageMenu=="AksesFitur"||$PageMenu=="AksesEntitas"||$PageMenu=="Akses"){echo "active";}else{echo "collapsed";} ?>" data-bs-target="#akses-nav" data-bs-toggle="collapse" href="javascript:void(0);">
                        <i class="bi bi-circle"></i><span>Aksesibilitas</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="akses-nav" class="nav-content nav-content-nested collapse <?php if($PageMenu=="AksesFitur"||$PageMenu=="AksesEntitas"||$PageMenu=="Akses"){echo "show";} ?>" data-bs-parent="#components-nav">
                        <li>
                            <a href="index.php?Page=AksesFitur" class="<?php if($PageMenu=="AksesFitur"){echo "active";} ?>">
                                <i class="bi bi-circle"></i><span>Fitur Aplikasi</span>
                            </a>
                        </li>
                        <li>
                            <a href="index.php?Page=AksesEntitas" class="<?php if($PageMenu=="AksesEntitas"){echo "active";} ?>">
                                <i class="bi bi-circle"></i><span>Entitas Akses</span>
                            </a>
                        </li>
                        <li>
                            <a href="index.php?Page=Akses" class="<?php if($PageMenu=="Akses"){echo "active";} ?>">
                                <i class="bi bi-circle"></i><span>Akses Pengguna</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a class="nav-link 
                <?php 
                    if(
                        $PageMenu=="Route"||
                        $PageMenu=="Sediaan"||
                        $PageMenu=="SatuanDosis"||
                        $PageMenu=="Denominator"||
                        $PageMenu=="Numerator"||
                        $PageMenu=="Poliklinik"||
                        $PageMenu=="Nakes"
                    ){echo "active";}else{echo "collapsed";} 
                ?>
            " data-bs-target="#iconsaa-nav" data-bs-toggle="collapse" href="javascript:void(0);">
                <i class="bi bi-list-columns"></i>
                <span>Referensi</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="iconsaa-nav" class="nav-content collapse 
                <?php 
                    if(
                        $PageMenu=="Route"||
                        $PageMenu=="Sediaan"||
                        $PageMenu=="SatuanDosis"||
                        $PageMenu=="Denominator"||
                        $PageMenu=="Numerator"||
                        $PageMenu=="Poliklinik"||
                        $PageMenu=="Nakes"
                    ){
                        echo "show";
                    } 
                ?>
            " data-bs-parent="#sidebar-nav">
                <li>
                    <a href="index.php?Page=Route" class="<?php if($PageMenu=="Route"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Route</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?Page=Sediaan" class="<?php if($PageMenu=="Sediaan"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Sediaan</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?Page=SatuanDosis" class="<?php if($PageMenu=="SatuanDosis"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Satuan Dosis</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?Page=Denominator" class="<?php if($PageMenu=="Denominator"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Denominator</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?Page=Numerator" class="<?php if($PageMenu=="Numerator"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Numerator</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?Page=Poliklinik" class="<?php if($PageMenu=="Poliklinik"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Poliklinik</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?Page=Nakes" class="<?php if($PageMenu=="Nakes"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Nakes</span>
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php if($PageMenu=="Aktivitas"){echo "active";}else{echo "collapsed";} ?>" href="index.php?Page=Aktivitas&Sub=AktivitasUmum">
                <i class="bi bi-circle"></i>
                <span>Log Aktivitas</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php if($PageMenu=="Dokumentasi"){echo "active";}else{echo "collapsed";} ?>" href="index.php?Page=Dokumentasi">
                <i class="bi bi-bookmark-check"></i>
                <span>Dokumentasi</span>
            </a>
        </li>
        <hr class="menu-divider">
        <li class="nav-heading">Fitur Lainnya</li>
        <li class="nav-item">
            <a class="nav-link <?php if($PageMenu=="Bantuan"){echo "active";}else{echo "collapsed";} ?>" href="index.php?Page=Bantuan">
                <i class="bi bi-question-circle"></i>
                <span>Bantuan</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalLogout">
                <i class="bi bi-box-arrow-in-left"></i>
                <span>Keluar</span>
            </a>
        </li>
    </ul>
</aside>
<div class="sidebar-backdrop" aria-hidden="true"></div>