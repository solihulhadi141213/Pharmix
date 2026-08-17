<div class="pagetitle">
    <h1>
        <a href="">
            <i class="bi bi-box"></i> Stock Opname</a>
        </a>
    </h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item active"> Stock Opname</li>
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
                            Berikut ini adalah halaman Stock Opname. 
                            Anda bisa mengelola perubahan stock barang dengan melakukan pemeriksaan stok secara rutin. 
                            Ketika anda menambahkan record stock Opname maka sistem akan melakukan update pada data stock barang.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </small>
                    </div>
                ';
            ?>
        </div>
    </div>

    <!-- Data View - Menampilkan Tabel Sesi Stock Opname -->
    <div id="data_view">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-12 mb-2 text-end">
                                <button class="btn btn-md btn-secondary btn-floating" type="button" data-bs-toggle="modal" data-bs-target="#ModalFilterSesi">
                                    <i class="bi bi-search"></i>
                                </button>
                                <button type="button" class="btn btn-md btn-primary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalTambahSesi" title="Tambah Sesi Stock Opname">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table table-responsive mt-3">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th><b>No</b></th>
                                        <th><b>Tanggal Sesi</b></th>
                                        <th><b>Jumlah Item</b></th>
                                        <th><b>Selisih (+)</b></th>
                                        <th><b>Selisih (-)</b></th>
                                        <th><b>Status</b></th>
                                        <th><b>Opsi</b></th>
                                    </tr>
                                </thead>
                                <tbody id="TabelSesi">
                                    <tr>
                                        <td colspan="7" class="text-center text-danger">Tidak Ada Data Yang Ditampilkan</td>
                                    </tr>
                                </tbody>
                            </table>
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
                                <button type="button" class="btn btn-sm btn-outline-info btn-floating" id="prev_button">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info btn-floating" id="next_button">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail View - Menampilkan Detail Sesi Stock Opname -->
    <div id="detail_view">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-6">
                                <b class="card-title"><i class="bi bi-info-circle"></i> Informasi Sesi Stock Opname</b>
                            </div>
                            <div class="col-6 text-end">
                                <button type="button" class="btn btn-secondary btn-floating" id="KembaliKeSesiSo">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body" id="info_sesi_stock_opename">
                        <!-- Menampilkan Info Sesi Stock Opname -->
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-6">
                                <b class="card-title"><i class="bi bi-list"></i> Rincian Stock Opname</b>
                            </div>
                            <div class="col-6 text-end">
                                <button type="button" class="btn btn-secondary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalExportStockOpnameBarang">
                                    <i class="bi bi-download"></i>
                                </button>
                                <button type="button" class="btn btn-secondary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalFilterBarang">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table table-responsive mt-3">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th><b>No</b></th>
                                        <th><b>Kode</b></th>
                                        <th><b>Barang</b></th>
                                        <th><b>Harga (Rp)</b></th>
                                        <th><b>Stok Awal</b></th>
                                        <th><b>Stok Akhir</b></th>
                                        <th><b>Selisih</b></th>
                                        <th><b>Jumlah (Rp)</b></th>
                                        <th><b>Opsi</b></th>
                                    </tr>
                                </thead>
                                <tbody id="TabelBarang">
                                    <tr>
                                        <td colspan="9" class="text-center text-danger">Tidak Ada Data Yang Ditampilkan</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-6">
                                <small id="page_info_barang">
                                    Page 1 Of 100
                                </small>
                            </div>
                            <div class="col-6 text-end">
                                <button type="button" class="btn btn-sm btn-outline-info btn-floating" id="prev_button_barang">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info btn-floating" id="next_button_barang">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>