<?php
    //Jumlah Transaksi
    $SumTransaksi = mysqli_fetch_array(mysqli_query($Conn, "SELECT SUM(jumlah) AS jumlah FROM transaksi"));
    $JumlahTransaksi = $SumTransaksi['jumlah'];
    $JumlahTransaksi = "Rp " . number_format($JumlahTransaksi,0,',','.');
?>
<div class="pagetitle">
    <h1>
        <a href="">
            <i class="bi bi-grid"></i> Dashboard
        </a>
    </h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </nav>
</div>
<section class="section dashboard">
    <div class="row">
        <div class="col-12">
            <div class="card" id="card_jam_menarik">
                <div class="card-body">
                    <div class="dashboard-clock">
                        <div id="tanggal_menarik">Hari, 01 Januari 1900</div>
                        <div id="jam_menarik">00:00:00</div>
                    </div>
                    <div class="dashboard-quick-actions">
                        <a class="dashboard-quick-action" href="index.php?Page=Penjualan&Sub=TambahPenjualan&retur=Tidak">
                            <i class="bi bi-cart-dash" aria-hidden="true"></i>
                            <span>Penjualan</span>
                        </a>
                        <a class="dashboard-quick-action" href="index.php?Page=Pembelian&Sub=TambahPembelian&retur=Tidak">
                            <i class="bi bi-cart-plus" aria-hidden="true"></i>
                            <span>Pembelian</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">

                        <div class="col-xxl-3 col-lg-6 col-md-6 col-sm-12 col-12">
                            <div class="card info-card sales-card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="index.php?Page=Medication">
                                            Index Obat/Alkes <i class="bi bi-arrow-up-right-square"></i>
                                        </a>
                                    </h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-capsule-pill"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h2 class="text-muted fw-bold" id="put_count_medication">0.000</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-lg-6 col-md-6 col-sm-12 col-12">
                            <div class="card info-card sales-card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="index.php?Page=Pasien">
                                            Pasien <i class="bi bi-arrow-up-right-square"></i>
                                        </a>
                                    </h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-people"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h2 class="text-muted fw-bold" id="put_count_pasien">0.000</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-lg-6 col-md-6 col-sm-12 col-12">
                            <div class="card info-card sales-card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="index.php?Page=Kunjungan">
                                            Kunjungan <i class="bi bi-arrow-up-right-square"></i>
                                        </a>
                                    </h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-activity"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h2 class="text-muted fw-bold" id="put_count_kunjungan">0.000</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-lg-6 col-md-6 col-sm-12 col-12">
                            <div class="card info-card sales-card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="index.php?Page=Resep">
                                            Resep <i class="bi bi-arrow-up-right-square"></i>
                                        </a>
                                    </h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-receipt"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h2 class="text-muted fw-bold" id="put_count_resep">0.000</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xxl-3 col-lg-6 col-md-6 col-sm-12 col-12">
                            <div class="card info-card sales-card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="index.php?Page=Barang">
                                            Inventaris / Barang <i class="bi bi-arrow-up-right-square"></i>
                                        </a>
                                    </h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-box"></i>
                                        </div>
                                        <div class="ps-3">
                                            <span class="text-muted small pt-1 fw-bold" id="put_count_rp_barang"></span><br>
                                            <span class="text-muted small pt-2 ps-1" id="put_count_item_barang"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xxl-3 col-lg-6 col-md-6 col-sm-12 col-12">
                            <div class="card info-card purple-card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="index.php?Page=Penjualan">
                                            Penjualan <i class="bi bi-arrow-up-right-square"></i>
                                        </a>
                                    </h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-cart-dash"></i>
                                        </div>
                                        <div class="ps-3">
                                            <b class="text-muted small pt-1 ps-1" id="put_nominal_penjualan">
                                                0.000.000
                                            </b>
                                            <br>
                                            <span class="text-muted small pt-2 ps-1" id="put_record_penjualan">
                                                0.000 Record
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-lg-6 col-md-6 col-sm-12 col-12">
                            <div class="card info-card customers-card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="index.php?Page=Pembelian">
                                            Pembelian <i class="bi bi-arrow-up-right-square"></i>
                                        </a>
                                    </h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-cart-plus"></i>
                                        </div>
                                        <div class="ps-3">
                                            <b class="text-muted small pt-2 ps-1" id="put_nominal_pembelian">
                                                0.00.000
                                            </b>
                                            <br>
                                            <span class="text-muted small pt-2 ps-1" id="put_record_pembelian">
                                                0.00.000
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xxl-3 col-lg-6 col-md-6 col-sm-12 col-12">
                            <div class="card info-card transsaction-card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="index.php?Page=Transaksi">
                                            Operasional <i class="bi bi-arrow-up-right-square"></i>
                                        </a>
                                    </h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-arrow-left-right"></i>
                                        </div>
                                        <div class="ps-3">
                                            <b class="text-muted small pt-2 ps-1" id="put_nominal_transaksi">
                                                0.00.000
                                            </b>
                                            <br>
                                            <span class="text-muted small pt-2 ps-1" id="put_record_transaksi">
                                                0.00.000
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                
            </div>
            
            <div class="row dashboard-report-row">
                <!-- Reports -->
                <div class="col-xl-9 col-lg-8 col-md-7 col-sm-12 col-12 dashboard-report-column">
                    <div class="card dashboard-report-card">
                        <div class="card-header">
                            <b class="card-title">
                                Penjualan & Pembelian
                            </b>
                        </div>
                        <div class="card-body">
                            <div class="row mt-4 mb-4">
                                <div class="col-12">
                                    <h5 class="card-title" id="NamaTitleData"></h5>
                                    <div id="chart"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-5 col-sm-12 col-12 dashboard-report-column">
                    <div class="card dashboard-report-card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-8">
                                    <b class="card-title">Transaksi</b>
                                </div>
                                <div class="col-4 text-end">
                                    <small class="text text-grayish">Terbaru</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4 mt-4">
                                <div class="col-12" id="transaksi_terbaru">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row dashboard-report-row">

                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 dashboard-report-column">
                    <div class="card dashboard-report-card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-12">
                                    <b class="card-title">
                                        <a href="index.php?Page=BarangExpired">
                                            Segera Expire <i class="bi bi-arrow-up-right-square"></i>
                                        </a>
                                    </b>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4 mt-4">
                                <div class="col-12" id="barang_expire">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 dashboard-report-column">
                    <div class="card dashboard-report-card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-12">
                                    <b class="card-title">
                                        <a href="index.php?Page=Barang">
                                            Segera Habis <i class="bi bi-arrow-up-right-square"></i>
                                        </a>
                                    </b>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4 mt-4">
                                <div class="col-12" id="barang_limit">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 dashboard-report-column">
                    <div class="card dashboard-report-card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-12">
                                    <b class="card-title">
                                        <a href="index.php?Page=UtangPiutang">
                                            Jatuh Tempo <i class="bi bi-arrow-up-right-square"></i>
                                        </a>
                                    </b>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4 mt-4">
                                <div class="col-12" id="jatuh_tempo">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
