<?php
    //---------------------------------------------------------------------------------------------
    //Data Jual/Beli
    $query_penjualan = "
        SELECT
            SUM(CASE WHEN kategori='Pembelian' AND status='Kredit' THEN total ELSE 0 END) AS utang_pembelian,
            SUM(CASE WHEN kategori='Retur Pembelian' AND status='Kredit' THEN total ELSE 0 END) AS piutang_retur_pembelian,
            SUM(CASE WHEN kategori='Penjualan' AND status='Kredit' THEN total ELSE 0 END) AS piutang_penjualan,
            SUM(CASE WHEN kategori='Retur Penjualan' AND status='Kredit' THEN total ELSE 0 END) AS utang_retur_penjualan
        FROM transaksi_jual_beli
    ";
    $stmt_penjualan = mysqli_prepare($Conn, $query_penjualan);
    mysqli_stmt_execute($stmt_penjualan);
    $result_penjualan = mysqli_stmt_get_result($stmt_penjualan);
    $data_penjualan_ringkas = mysqli_fetch_assoc($result_penjualan);
    mysqli_stmt_close($stmt_penjualan);

    // Variabel Utang-Piutang Jual/Beli
    $utang_pembelian         = (float) ($data_penjualan_ringkas['utang_pembelian'] ?? 0);
    $piutang_retur_pembelian = (float) ($data_penjualan_ringkas['piutang_retur_pembelian'] ?? 0);
    $piutang_penjualan       = (float) ($data_penjualan_ringkas['piutang_penjualan'] ?? 0);
    $utang_retur_penjualan   = (float) ($data_penjualan_ringkas['utang_retur_penjualan'] ?? 0);
    $utang_jual_beli         = $utang_pembelian + $utang_retur_penjualan;
    $piutang_jual_beli       = $piutang_retur_pembelian + $piutang_penjualan;
    
    
    //-------------------------------------------------------------------------------
    //Data operasional
    $query_operasional = "
        SELECT
            SUM(CASE WHEN status='Utang' THEN jumlah ELSE 0 END) AS utang_operasional,
            SUM(CASE WHEN status='Piutang' THEN jumlah ELSE 0 END) AS piutang_operasional
        FROM transaksi
    ";
    $stmt_operasional = mysqli_prepare($Conn, $query_operasional);
    mysqli_stmt_execute($stmt_operasional);
    $result_operasional = mysqli_stmt_get_result($stmt_operasional);
    $data_operasional = mysqli_fetch_assoc($result_operasional);
    mysqli_stmt_close($stmt_operasional);

    // Variabel Utang-Piutang Operasional
    $utang_operasional   = (float) ($data_operasional['utang_operasional'] ?? 0);
    $piutang_operasional = (float) ($data_penjualan_ringkas['piutang_operasional'] ?? 0);

    //-------------------------------------------------------------------------------
    // Menghitung Utang-piutang total
    $total_utang   = $utang_operasional + $utang_jual_beli;
    $total_piutang = $piutang_operasional + $piutang_jual_beli;

    //-------------------------------------------------------------------------------
    //Format Jual-Beli
    $utang_pembelian         = "Rp " . number_format($utang_pembelian,0,',','.');
    $piutang_retur_pembelian = "Rp " . number_format($piutang_retur_pembelian,0,',','.');
    $piutang_penjualan       = "Rp " . number_format($piutang_penjualan,0,',','.');
    $utang_retur_penjualan   = "Rp " . number_format($utang_retur_penjualan,0,',','.');
    $utang_jual_beli         = "Rp " . number_format($utang_jual_beli,0,',','.');
    $piutang_jual_beli       = "Rp " . number_format($piutang_jual_beli,0,',','.');

    // Operasional Format
    $utang_operasional   = "Rp " . number_format($utang_operasional,0,',','.');
    $piutang_operasional = "Rp " . number_format($piutang_operasional,0,',','.');

    // Total Format
    $total_utang   = "Rp " . number_format($total_utang,0,',','.');
    $total_piutang = "Rp " . number_format($total_piutang,0,',','.');
?>
<div class="pagetitle">
    <h1>
        <a href="">
            <i class="bi bi-arrow-left-right"></i> Utang/Piutang</a>
        </a>
    </h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Utang/Piutang</li>
        </ol>
    </nav>
</div>
<section class="section dashboard">
    <div class="row mb-3">
        <div class="col-md-12 text-end">
            <a href="Javascript:void(0);" class="text text-primary" id="ReloadCount">
                <small><i class="bi bi-arrow-counterclockwise"></i> Reload Data</small>
            </a>
        </div>
    </div>
    
    <!-- Notifikasi Sistem -->
     <div class="row">
        <div class="col-12" id="NotifikasiSistem"></div>
     </div>

    <!-- Tampilan Dashboarad Utang Piutang -->
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6">
                            <b class="card-title">Utang</b>
                        </div>
                        <div class="col-6 text-end">
                            <b class="card-title text-danger" id="utang_jual_beli">-</b>
                        </div>
                    </div>
                    
                </div>
                <div class="card-body">
                    <div class="row mt-3">
                        <div class="col-6">
                            <small class="text-dark">Utang Pembelian</small>
                        </div>
                        <div class="col-6 text-end">
                            <small class="text-muted" id="utang_pembelian">-</small>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-6">
                            <small class="text-dark">Utang Retur Penjualan</small>
                        </div>
                        <div class="col-6 text-end">
                            <small class="text-muted" id="utang_retur_penjualan">-</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-4">
                            <b class="card-title">Piutang</b>
                        </div>
                        <div class="col-8 text-end">
                            <b class="card-title text-warning" id="piutang_jual_beli">-</b>
                        </div>
                    </div>
                    
                </div>
                <div class="card-body">
                    <div class="row mt-3">
                        <div class="col-6">
                            <small class="text-dark">Piutang Penjualan</small>
                        </div>
                        <div class="col-6 text-end">
                            <small class="text-muted" id="piutang_penjualan">-</small>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-6">
                            <small class="text-dark">Retur Pembelian</small>
                        </div>
                        <div class="col-6 text-end">
                            <small class="text-muted" id="piutang_retur_pembelian">-</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-12">
                            <b class="card-title">Transaksi Operasional</b>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mt-3">
                        <div class="col-6">
                            <small class="text-dark">Utang Operasional</small>
                        </div>
                        <div class="col-6 text-end">
                            <small class="text-danger" id="utang_operasional">-</small>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-6">
                            <small class="text-dark">Piutang Operasional</small>
                        </div>
                        <div class="col-6 text-end">
                            <small class="text-warning" id="piutang_operasional"></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-12">
                            <b class="card-title">Total Utang/Piutang</b>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mt-3">
                        <div class="col-6">
                            <small class="text-dark">Total Utang</small>
                        </div>
                        <div class="col-6 text-end">
                            <small class="text-danger" id="total_utang">-</small>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-6">
                            <small class="text-dark">Total Piutang</small>
                        </div>
                        <div class="col-6 text-end">
                            <small class="text-warning" id="total_piutang">-</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kontrol Tab -->
    <div class="row">
        <div class="col-12">
            <div class="card text-center">
                <div class="card-body text-center">
                    <div class="nav nav-pills justify-content-center mt-3" id="transaksiTab" role="tablist">
                        <!-- TAB OPERASIONAL -->
                        <button class="nav-link active me-2" id="operasional-tab" data-bs-toggle="pill" data-bs-target="#operasional" type="button" role="tab" aria-controls="operasional" aria-selected="true">
                            <i class="bi bi-truck"></i> Operasional
                        </button>
                        <!-- TAB JUAL BELI -->
                        <button class="nav-link" id="jual-beli-tab" data-bs-toggle="pill" data-bs-target="#jual-beli" type="button" role="tab" aria-controls="jual-beli" aria-selected="false">
                            <i class="bi bi-cart-check"></i> Jual/Beli
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaksi Operasional -->
    <div class="tab-content" id="transaksiTabContent">

        <!-- TAB OPERASIONAL -->
        <div class="tab-pane fade show active" id="operasional" role="tabpanel" aria-labelledby="operasional-tab">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-8">
                                    <b class="card-title"># Transaksi Operasional</b>
                                </div>
                                <div class="col-4 text-end">
                                    <button type="button" class="btn btn-md btn-secondary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalFilterOperasional" title="Filter">
                                        <i class="bi bi-filter"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mt-3 mb-3">
                                <div class="col-12">
                                    <div class="table table-responsive mb-3 mt-3">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th><b>No</b></th>
                                                    <th><b>Tanggal</b></th>
                                                    <th><b>Transaksi</b></th>
                                                    <th><b>Total</b></th>
                                                    <th><b>Cash</b></th>
                                                    <th><b>Pembayaran</b></th>
                                                    <th><b>U/P</b></th>
                                                    <th><b>Status</b></th>
                                                    <th><b>Tempo</b></th>
                                                    <th><b>Opsi</b></th>
                                                </tr>
                                            </thead>
                                            <tbody id="tabel_operasional">
                                                <tr>
                                                    <td colspan="10" class="text-center">
                                                        <small class="text-muted">No Data</small>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-6">
                                    <small id="page_info_operasional">
                                        Page 1 Of 100
                                    </small>
                                </div>
                                <div class="col-6 text-end">
                                    <button type="button" class="btn btn-md btn-outline-info btn-floating" id="prev_button_operasional">
                                        <i class="bi bi-chevron-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-md btn-outline-info btn-floating" id="next_button_operasional">
                                        <i class="bi bi-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB JUAL BELI -->
        <div class="tab-pane fade" id="jual-beli" role="tabpanel" aria-labelledby="jual-beli-tab">
           <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-8">
                                    <b class="card-title"># Transaksi Jual Beli</b>
                                </div>
                                <div class="col-4 text-end">
                                    <button type="button" class="btn btn-md btn-secondary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalFilterPenjualan" title="Filter">
                                        <i class="bi bi-filter"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mt-3 mb-3">
                                <div class="col-12">
                                    <div class="table table-responsive mb-3 mt-3">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th><b>No</b></th>
                                                    <th><b>Tanggal</b></th>
                                                    <th><b>Transaksi</b></th>
                                                    <th><b>Total</b></th>
                                                    <th><b>Cash</b></th>
                                                    <th><b>Pembayaran</b></th>
                                                    <th><b>U/P</b></th>
                                                    <th><b>Status</b></th>
                                                    <th><b>Tempo</b></th>
                                                    <th><b>Opsi</b></th>
                                                </tr>
                                            </thead>
                                            <tbody id="tabel_utang_piutang">
                                                <tr>
                                                    <td colspan="10" class="text-center">
                                                        <small class="text-muted">No Data</small>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-6">
                                    <small id="page_info">
                                        Page 1 Of 100
                                    </small>
                                </div>
                                <div class="col-6 text-end">
                                    <button type="button" class="btn btn-md btn-outline-info btn-floating" id="prev_button">
                                        <i class="bi bi-chevron-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-md btn-outline-info btn-floating" id="next_button">
                                        <i class="bi bi-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    
</section>
