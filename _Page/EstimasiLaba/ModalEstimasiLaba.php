
<div class="modal fade" id="ModalFilterLaba" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesFilterLaba">
                <input type="hidden" name="page" id="page_laba" value="1">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-funnel"></i> Filter Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="batas_laba">
                                <small>Data/Limit</small>
                            </label>
                        </div>
                        <div class="col-8">
                            <select name="batas" id="batas_laba" class="form-control">
                                <option value="5">5</option>
                                <option selected value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="250">250</option>
                                <option value="500">500</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="OrderByLaba">
                                <small>Mode Urutan</small>
                            </label>
                        </div>
                        <div class="col-8">
                            <select name="OrderBy" id="OrderByLaba" class="form-control">
                                <option value="">Pilih</option>
                                <option value="kategori">Kategori Transaksi</option>
                                <option value="tanggal">Tanggal Transaksi</option>
                                <option value="nama_barang">Nama Barang</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="ShortByLaba">
                                <small>Tipe Urutan</small>
                            </label>
                        </div>
                        <div class="col-8">
                            <select name="ShortBy" id="ShortByLaba" class="form-control">
                                <option value="ASC">A To Z</option>
                                <option selected value="DESC">Z To A</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="keyword_by_laba">
                                <small>Pencarian</small>
                            </label>
                        </div>
                        <div class="col-8">
                            <select name="keyword_by" id="keyword_by_laba" class="form-control">
                                <option value="">Pilih</option>
                                <option value="kategori">Kategori Transaksi</option>
                                <option value="tanggal">Tanggal Transaksi</option>
                                <option value="nama_barang">Nama Barang</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="keyword_laba">
                                <small>Kata Kunci</small>
                            </label>
                        </div>
                        <div class="col-8" id="FormFilterKeywordLaba">
                            <input type="text" name="keyword" id="keyword_laba" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded">
                        <i class="bi bi-save"></i> Filter
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="ModalExportLaba" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesExportLaba">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-download"></i> Export Laba Penjualan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="periode_1_laba">
                                <small>Periode Awal</small>
                            </label>
                        </div>
                        <div class="col-8">
                            <input type="date" name="periode_1" id="periode_1_laba" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="periode_2_laba">
                                <small>Periode Akhir</small>
                            </label>
                        </div>
                        <div class="col-8">
                            <input type="date" name="periode_2" id="periode_2_laba" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="type_data_laba">
                                <small>Type Data</small>
                            </label>
                        </div>
                        <div class="col-8">
                            <select name="type_data_laba" id="type_data_laba" class="form-control">
                                <option value="HTML">HTML</option>
                                <option value="Excel">Excel</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiExportTransaksi">
                            <div class="alert alert-warning">
                                <small>Semakin banyak data transaksi yang akan diexport, maka proses sistem membutuhkan waktu lebih lama.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="ButtonExportLaba">
                        <i class="bi bi-download"></i> Export
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalDetail" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="index.php" method="GET">
                <input type="hidden" name="Page" value="Penjualan">
                <input type="hidden" name="Sub" value="DetailPenjualan">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-info-circle"></i> Detail Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-12" id="FormDetail">
                            <!-- Form Detail Transaksi Disini -->
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="table table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th class="bg-dark text-white"><b>No</b></th>
                                            <th class="bg-dark text-white"><b>Uraian</b></th>
                                            <th class="bg-dark text-white"><b>QTY</b></th>
                                            <th class="bg-dark text-white"><b>Harga</b></th>
                                            <th class="bg-dark text-white"><b>PPN</b></th>
                                            <th class="bg-dark text-white"><b>Diskon</b></th>
                                            <th class="bg-dark text-white"><b>Subtotal</b></th>
                                        </tr>
                                    </thead>
                                    <tbody id="ListDetail">
                                        <!-- List Detail Transaksi Disini -->
                                    </tbody>
                                </table>
                            </div>
                            
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-md btn-primary btn-rounded" id="ButtonSelengkapnya">
                        Selengkapnya <i class="bi bi-chevron-right"></i>
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>