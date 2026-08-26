<?php
    //Cek Aksesibilitas ke halaman ini
    $IjinAksesSaya=IjinAksesSaya($Conn,$SessionIdAkses,'LTBuBzSi7njYRiLe6la');
    if($IjinAksesSaya!=="Ada"){
        include "_Page/Error/NoAccess.php";
    }else{
?>
    <div class="pagetitle">
        <h1>
            <a href="">
                <i class="bi bi-cart-dash"></i> Transaksi Pembelian</a>
            </a>
        </h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Pembelian</li>
            </ol>
        </nav>
    </div>
    <section class="section dashboard">
        <div class="row">
            <div class="col-md-12">
                <?php
                    echo '
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <small>
                                Berikut ini adalah halaman untuk mengelola transaksi Pembelian. Setiap aktivitas Pembelian dicatat pada halaman ini. 
                                Untuk Pembelian terhadap anggota harus dicatat informasi anggotanya sehingga data Pembelian terhubung dengan riwayat belanja anggota.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </small>
                        </div>
                    ';
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-12 text-end">
                                <button type="button" class="btn btn-md btn-secondary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalFilter" title="Filter">
                                    <i class="bi bi-search"></i>
                                </button>
                                <button type="button" class="btn btn-md btn-secondary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalExportTransaksi" title="Export">
                                    <i class="bi bi-download"></i>
                                </button>
                                <button type="button" class="btn btn-md btn-primary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalTambahTransaksiPembelian" title="Tambah Transaksi Pembelian">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mt-3 mb-3">
                            <div class="col-12">
                                <div class="tabel table-responsive">
                                    <table class="table table-hover table-striped">
                                        <thead>
                                            <tr>
                                                <th><b>No</b></th>
                                                <th><b>Tanggal</b></th>
                                                <th>
                                                    <b>Kategori</b> 
                                                    <a href="javascript:void(0);" 
                                                        data-bs-toggle="tooltip" 
                                                        data-bs-placement="top" 
                                                        data-bs-custom-class="custom-tooltip" 
                                                        data-bs-title="Transaksi Pembelian / Retur Pembelian"
                                                    >
                                                        <i class="bi bi-question-circle"></i>
                                                    </a>
                                                </th>
                                                <th><b>Supplier</b></th>
                                                <th><b>Jumlah</b></th>
                                                <th>
                                                    <b>Status</b> 
                                                    <a href="javascript:void(0);" 
                                                        data-bs-toggle="tooltip" 
                                                        data-bs-placement="top" 
                                                        data-bs-custom-class="custom-tooltip" 
                                                        data-bs-title="Lunas, Utang, Piutang"
                                                    >
                                                        <i class="bi bi-question-circle"></i>
                                                    </a>
                                                </th>
                                                <th><b>Jurnal</b></th>
                                                <th><b>Opsi</b></th>
                                            </tr>
                                        </thead>
                                        <tbody id="TabelPembelian">
                                            <!-- Data Barang Akan Ditampilkan Disini -->
                                            <tr>
                                                <td colspan="7" class="text-center text-danger">
                                                    Tidak Ada Data yang Ditampilkan
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
    </section>
<?php } ?>