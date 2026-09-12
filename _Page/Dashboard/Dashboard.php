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
            <?php
                // Tambahkan ringkasan baru di sini; kartu otomatis mengikuti daftar horizontal.
                $summaryCards = [
                    ['page' => 'Medication', 'label' => 'Index Obat/Alkes', 'icon' => 'bi-capsule-pill', 'tone' => 'blue', 'value' => 'put_count_medication'],
                    ['page' => 'Pasien', 'label' => 'Pasien', 'icon' => 'bi-people', 'tone' => 'teal', 'value' => 'put_count_pasien'],
                    ['page' => 'Kunjungan', 'label' => 'Kunjungan', 'icon' => 'bi-activity', 'tone' => 'orange', 'value' => 'put_count_kunjungan'],
                    ['page' => 'Resep', 'label' => 'Resep', 'icon' => 'bi-receipt', 'tone' => 'purple', 'value' => 'put_count_resep'],
                    ['page' => 'Barang', 'label' => 'Inventaris / Barang', 'icon' => 'bi-box', 'tone' => 'blue', 'value' => 'put_count_rp_barang', 'detail' => 'put_count_item_barang'],
                    ['page' => 'Penjualan', 'label' => 'Penjualan', 'icon' => 'bi-cart-dash', 'tone' => 'purple', 'value' => 'put_nominal_penjualan', 'detail' => 'put_record_penjualan'],
                    ['page' => 'Pembelian', 'label' => 'Pembelian', 'icon' => 'bi-cart-plus', 'tone' => 'orange', 'value' => 'put_nominal_pembelian', 'detail' => 'put_record_pembelian'],
                    ['page' => 'Transaksi', 'label' => 'Operasional', 'icon' => 'bi-arrow-left-right', 'tone' => 'teal', 'value' => 'put_nominal_transaksi', 'detail' => 'put_record_transaksi']
                ];
                $summaryEscape = static function ($value) {
                    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                };
            ?>
            <section class="dashboard-summary" aria-labelledby="dashboard-summary-title">
                <div class="dashboard-summary-heading">
                    <div>
                        <h2 id="dashboard-summary-title">Ringkasan Aktivitas</h2>
                        <p id="dashboard-summary-hint">Geser untuk melihat ringkasan lainnya.</p>
                    </div>
                    <div class="dashboard-summary-controls" hidden>
                        <button type="button" class="dashboard-summary-nav" id="dashboard-summary-prev" aria-label="Geser ringkasan ke kiri" aria-controls="dashboard-summary-list">
                            <i class="bi bi-chevron-left" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="dashboard-summary-nav" id="dashboard-summary-next" aria-label="Geser ringkasan ke kanan" aria-controls="dashboard-summary-list">
                            <i class="bi bi-chevron-right" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
                <ul class="dashboard-summary-list" id="dashboard-summary-list" tabindex="0" aria-label="Daftar ringkasan aktivitas" aria-describedby="dashboard-summary-hint">
                    <?php foreach ($summaryCards as $summaryCard): ?>
                        <li class="dashboard-summary-item">
                            <a class="dashboard-summary-card dashboard-summary-<?php echo $summaryEscape($summaryCard['tone']); ?>" href="index.php?Page=<?php echo $summaryEscape($summaryCard['page']); ?>">
                                <span class="dashboard-summary-top">
                                    <span class="dashboard-summary-icon"><i class="bi <?php echo $summaryEscape($summaryCard['icon']); ?>" aria-hidden="true"></i></span>
                                    <i class="bi bi-arrow-up-right dashboard-summary-arrow" aria-hidden="true"></i>
                                </span>
                                <span class="dashboard-summary-label"><?php echo $summaryEscape($summaryCard['label']); ?></span>
                                <span class="dashboard-summary-value<?php echo isset($summaryCard['detail']) ? ' dashboard-summary-money' : ''; ?>" id="<?php echo $summaryEscape($summaryCard['value']); ?>">—</span>
                                <?php if (isset($summaryCard['detail'])): ?>
                                    <span class="dashboard-summary-detail" id="<?php echo $summaryEscape($summaryCard['detail']); ?>">Memuat...</span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
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
