<div class="pagetitle">
    <h1>
        <a href="">
            <i class="bi bi-cash-coin"></i> Pembayaran</a>
        </a>
    </h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Pembayaran</li>
        </ol>
    </nav>
</div>
<section class="section dashboard">
    
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-12 text-end">
                            <button type="button" class="btn btn-md btn-secondary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalFilter" title="Filter">
                                <i class="bi bi-filter"></i>
                            </button>
                            <button type="button" class="btn btn-md btn-secondary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalExport" title="Export Pembayaran">
                                <i class="bi bi-download"></i>
                            </button>
                            <button type="button" class="btn btn-md btn-primary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalTambahPembayaran" title="Tambah Pembayaran">
                                <i class="bi bi-plus"></i>
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
                                            <th><b>ID Pembayaran</b></th>
                                            <th><b>Tanggal</b></th>
                                            <th><b>Referensi</b></th>
                                            <th><b>Transaksi</b></th>
                                            <th><b>Nominal</b></th>
                                            <th><b>Petugas</b></th>
                                            <th><b>Opsi</b></th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabel_pembayaran">
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
