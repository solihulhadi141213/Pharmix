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
        <li class="nav-item">
            <a class="nav-link <?php if($PageMenu=="AksesFitur"||$PageMenu=="AksesEntitas"||$PageMenu=="Akses"){echo "active";}else{echo "collapsed";} ?>" data-bs-target="#akses-nav" data-bs-toggle="collapse" href="javascript:void(0);">
                <i class="bi bi-person"></i>
                <span>Akses</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="akses-nav" class="nav-content collapse <?php if($PageMenu=="AksesFitur"||$PageMenu=="AksesEntitas"||$PageMenu=="Akses"){echo "show";} ?>" data-bs-parent="#sidebar-nav">
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
            <a class="nav-link <?php if($PageMenu=="Barang"||$PageMenu=="BarangExpired"||$PageMenu=="StockOpename"){echo "active";}else{echo "collapsed";} ?>" data-bs-target="#icons2-nav" data-bs-toggle="collapse" href="javascript:void(0);">
                <i class="bi bi-box-seam"></i>
                <span>Barang</span><i class="bi bi-chevron-down ms-auto"></i>
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
        <li class="nav-item">
            <a class="nav-link <?php if($PageMenu=="JenisTransaksi"||$PageMenu=="RekapTransaksi"||$PageMenu=="Transaksi"||$PageMenu=="TransaksiJualBeli"){echo "active";}else{echo "collapsed";} ?>" data-bs-target="#transaksi-nav" data-bs-toggle="collapse" href="javascript:void(0);">
                <i class="bi bi-cash-coin"></i>
                <span>Transaksi Operasional</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="transaksi-nav" class="nav-content collapse <?php if($PageMenu=="JenisTransaksi"||$PageMenu=="RekapTransaksi"||$PageMenu=="Transaksi"||$PageMenu=="TransaksiJualBeli"){echo "show";} ?>" data-bs-parent="#sidebar-nav">
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
                    <a href="index.php?Page=RekapTransaksi" class="<?php if($PageMenu=="RekapTransaksi"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Rekap Transaksi</span>
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php if($PageMenu=="Penjualan"||$PageMenu=="Pembelian"||$PageMenu=="RekapJualBeli"||$PageMenu=="UtangPiutang"){echo "active";}else{echo "collapsed";} ?>" data-bs-target="#transaksi_jual_beli-nav" data-bs-toggle="collapse" href="javascript:void(0);">
                <i class="bi bi-cart-check"></i>
                <span>Transaksi Jual Beli</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="transaksi_jual_beli-nav" class="nav-content collapse <?php if($PageMenu=="Penjualan"||$PageMenu=="Pembelian"||$PageMenu=="RekapJualBeli"||$PageMenu=="UtangPiutang"){echo "show";} ?>" data-bs-parent="#sidebar-nav">
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
                <li>
                    <a href="index.php?Page=RekapJualBeli" class="<?php if($PageMenu=="RekapJualBeli"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Rekap Jual/Beli</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?Page=UtangPiutang" class="<?php if($PageMenu=="UtangPiutang"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Utang/Piutang</span>
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php if($PageMenu=="Pembayaran"||$PageMenu=="AkunPerkiraan"||$PageMenu=="Jurnal"||$PageMenu=="BagiHasil"){echo "active";}else{echo "collapsed";} ?>" data-bs-target="#keuangan-nav" data-bs-toggle="collapse" href="javascript:void(0);">
                <i class="bi bi-gem"></i>
                <span>Keuangan</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="keuangan-nav" class="nav-content collapse <?php if($PageMenu=="Pembayaran"||$PageMenu=="AkunPerkiraan"||$PageMenu=="Jurnal"||$PageMenu=="BagiHasil"){echo "show";} ?>" data-bs-parent="#sidebar-nav">
                <li>
                    <a href="index.php?Page=AkunPerkiraan" class="<?php if($PageMenu=="AkunPerkiraan"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Akun Perkiraan</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?Page=Jurnal" class="<?php if($PageMenu=="Jurnal"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Jurnal Keuangan</span>
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php if($PageMenu=="SimpanPinjam"||$PageMenu=="BukuBesar"||$PageMenu=="NeracaSaldo"||$PageMenu=="LabaRugi"||$PageMenu=="RiwayatSimpanPinjam"){echo "active";}else{echo "collapsed";} ?>" data-bs-target="#charts-nav" data-bs-toggle="collapse" href="javascript:void(0);">
                <i class="bi bi-bar-chart"></i><span>Laporan</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="charts-nav" class="nav-content collapse <?php if($PageMenu=="SimpanPinjam"||$PageMenu=="BukuBesar"||$PageMenu=="NeracaSaldo"||$PageMenu=="LabaRugi"||$PageMenu=="RiwayatSimpanPinjam"){echo "show";} ?>" data-bs-parent="#sidebar-nav">
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
            </ul>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php if($PageMenu=="SettingGeneral"||$PageMenu=="SettingEmail"||$PageMenu=="AutoJurnal"){echo "active";}else{echo "collapsed";} ?>" data-bs-target="#components-nav" data-bs-toggle="collapse" href="javascript:void(0);">
                <i class="bi bi-gear"></i>
                    <span>Pengaturan</span><i class="bi bi-chevron-down ms-auto">
                </i>
            </a>
            <ul id="components-nav" class="nav-content collapse <?php if($PageMenu=="SettingGeneral"||$PageMenu=="SettingEmail"||$PageMenu=="AutoJurnal"){echo "show";} ?>" data-bs-parent="#sidebar-nav">
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
                    <a href="index.php?Page=SettingEmail" class="<?php if($PageMenu=="SettingEmail"){echo "active";} ?>">
                        <i class="bi bi-circle"></i><span>Email Gateway</span>
                    </a>
                </li> 
            </ul>
        </li>
        <li class="nav-heading">Fitur Lainnya</li>
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
        <li class="nav-item">
            <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalLogout">
                <i class="bi bi-box-arrow-in-left"></i>
                <span>Keluar</span>
            </a>
        </li>
    </ul>
</aside>
<div class="sidebar-backdrop" aria-hidden="true"></div>