<?php
    //Cek Aksesibilitas ke halaman ini
    $IjinAksesSaya=IjinAksesSaya($Conn,$SessionIdAkses,'w8xdO79t7kdEeyBSxLJ');
    if($IjinAksesSaya!=="Ada"){
        include "_Page/Error/NoAccess.php";
    }else{
?>
    <div class="pagetitle">
        <h1>
            <a href="">
                <i class="bi bi-table"></i> Rekapitulasi Transaksi</a>
            </a>
        </h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active"> Rekapitulasi Transaksi</li>
            </ol>
        </nav>
    </div>
    <section class="section dashboard">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <small>
                        Berikut ini halaman rekapitulasi data transaksi berdasarkan periode waktu dan jenis transaksi yang sudah berlangsung.
                        Gunakan tombol "Filter" untuk menentukan dasar pengelompokan data.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </small>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-12 text-end">
                                <a class="btn btn-md btn-secondary btn-floating" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalFilterGrafik" title="Mode Grafik">
                                    <i class="bi bi-filter"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3 mt-3">
                            <div class="col-md-12 text-center" id="GrafikTransaksi">
                                <!-- Menampilkan Gambar Grafik -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-10">
                                <b class="card-title">
                                    <i class="bi bi-table"></i> Transaksi Operasional (Per Periode)
                                </b>
                            </div>
                            <div class="col-md-2 text-end">
                                <a class="btn btn-md btn-secondary btn-floating" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalExportTabelTransaksi" title="Export/Download">
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3 mt-3">
                            <div class="col-md-12">
                                <div class="table table-responsive">
                                    <table class="table table-striped table-hover table-md">
                                        <thead>
                                            <tr>
                                                <th><b>No</b></th>
                                                <th><b>Tahun</b></th>
                                                <th><b>Bulan</b></th>
                                                <th><b>Subtotal</b></th>
                                                <th><b>Pembayaran</b></th>
                                                <th><b>Lunas</b></th>
                                                <th><b>Utang</b></th>
                                                <th><b>Piutang</b></th>
                                            </tr>
                                        </thead>
                                        <tbody id="tabel_transaksi">
                                            <tr>
                                                <td colspan="8" class="text-center">
                                                    <small class="text-muted">No Data</small>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php } ?>