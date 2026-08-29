<!-- Modal Filter Operasional -->
<div class="modal fade" id="ModalFilterOperasional" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesFilterOperasional">
                <input type="hidden" name="page" id="page_filter_operasional" value="1">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-funnel"></i> Filter Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col col-md-12">
                            <label for="batas_operasional">
                                <small>Limit/Batas</small>
                            </label>
                            <select name="batas" id="batas_operasional" class="form-control">
                                <option value="5">5</option>
                                <option selected value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-12">
                            <label for="OrderByOperasional">
                                <small>Order By</small>
                            </label>
                            <select name="OrderBy" id="OrderByOperasional" class="form-control">
                                <option value="">Pilih</option>
                                <option value="tanggal">Tanggal Transaksi</option>
                                <option value="nama">Nama Transaksi</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-12">
                            <label for="ShortByOperasional">
                                <small>Short By</small>
                            </label>
                            <select name="ShortBy" id="ShortByOperasional" class="form-control">
                                <option value="DESC">Z To A</option>
                                <option value="ASC">A To Z</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-12">
                            <label for="keyword_by_operasional">
                                <small>Keyword By</small>
                            </label>
                            <select name="keyword_by" id="keyword_by_operasional" class="form-control">
                                <option value="">Pilih</option>
                                <option value="tanggal">Tanggal Transaksi</option>
                                <option value="nama">Nama Transaksi</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-12" id="FormFilterOperasional">
                            <label for="keyword_operasional">
                                <small>Keyword</small>
                            </label>
                            <input type="text" name="keyword" id="keyword_operasional" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Filter Jual Beli -->
<div class="modal fade" id="ModalFilterPenjualan" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesFilter">
                <input type="hidden" name="page" id="page_filter" value="1">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-funnel"></i> Filter Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col col-md-12">
                            <label for="batas">
                                <small>Limit/Batas</small>
                            </label>
                            <select name="batas" id="batas" class="form-control">
                                <option value="5">5</option>
                                <option selected value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-12">
                            <label for="OrderBy">
                                <small>Order By</small>
                            </label>
                            <select name="OrderBy" id="OrderBy" class="form-control">
                                <option value="">Pilih</option>
                                <option value="tanggal">Tanggal Transaksi</option>
                                <option value="kategori">Kategori Transaksi</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-12">
                            <label for="ShortBy">
                                <small>Short By</small>
                            </label>
                            <select name="ShortBy" id="ShortBy" class="form-control">
                                <option value="DESC">Z To A</option>
                                <option value="ASC">A To Z</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-12">
                            <label for="keyword_by">
                                <small>Keyword By</small>
                            </label>
                            <select name="keyword_by" id="keyword_by" class="form-control">
                                <option value="">Pilih</option>
                                <option value="tanggal">Tanggal Transaksi</option>
                                <option value="kategori">Kategori Transaksi</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-12" id="FormFilter">
                            <label for="keyword">
                                <small>Keyword</small>
                            </label>
                            <input type="text" name="keyword" id="keyword" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail Operasional -->
 <div class="modal fade" id="ModalDetailTransaksiOperasional" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
           <div class="modal-header">
                <h5 class="modal-title text-dark"><i class="bi bi-info-circle"></i> Detail Transaksi Operasional</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-12" id="FormDetailTransaksiOperasional">
                        <!-- Form Detail Transaksi Disini -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Jual/Beli -->
<div class="modal fade" id="ModalDetailTransaksiJualBeli" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="index.php" method="GET">
                <input type="hidden" name="Page" value="Penjualan">
                <input type="hidden" name="Sub" value="DetailPenjualan">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-info-circle"></i> Detail Transaksi Jual/Beli</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-12" id="FormDetailTransaksiJualBeli">
                            <!-- Form Detail Transaksi Disini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-md btn-primary btn-rounded" id="ButtonTransaksiJualBeliSelengkapnya">
                        Selengkapnya <i class="bi bi-chevron-right"></i>
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalRiwayatPembayaran" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark">
                    <i class="bi bi-clock-history"></i> Riwayat Pembayaran
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <div class="table table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th><b>No</b></th>
                                        <th><b>Tanggal</b></th>
                                        <th><b>Transaksi</b></th>
                                        <th><b>Pembayaran</b></th>
                                        <th><b>Nominal</b></th>
                                        <th><b>Opsi</b></th>
                                    </tr>
                                </thead>
                                <tbody id="tabel_riwayat_pembayaran">
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <small>No Data</small>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalPembayaran" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesPembayaran">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-plus"></i> Pembayaran Utang/Piutang
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id_transaksi_penjualan" name="id_transaksi_jual_beli">
                    <div class="row">
                        <div class="col-12" id="FormPembayaran">
                            <!-- Form Pembayaran Akan Muncul Disini -->
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12" id="NotifikasiPembayaran">
                            <!-- Notifikasi Pembayaran Disini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-md btn-primary btn-rounded" id="ButtonPembayaran">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="ModalEditPembayaran" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesEditPembayaran">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-pencil"></i> Edit Pembayaran
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12" id="FormEditPembayaran">
                            <!-- Form Edit Pembayaran Disini -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12" id="NotifikasiEditPembayaran">
                            <!-- Notifikasi Pembayaran Disini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="ButtonEditPembayaran">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalHapusPembayaran" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesHapusPembayaran">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-trash"></i> Hapus Pembayaran
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12" id="FormHapusPembayaran">
                            <!-- Form Hapus Pembayaran Disini -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12" id="NotifikasiHapusPembayaran">
                            <!-- Notifikasi Hapus Pembayaran Disini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="ButtonHapusPembayaran">
                        <i class="bi bi-check"></i> Ya, Hapus
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalTempo" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesTempo">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-plus"></i> Tempo Pembayaran
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12" id="FormTempo">
                            <!-- Form Tempo Akan Muncul Disini -->
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12" id="NotifikasiTempo">
                            <!-- Notifikasi Tempo Disini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-md btn-primary btn-rounded" id="ButtonTempo">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="ModalHapusTempo" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesHapusTempo">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-plus"></i> Hapus Tanggal Tempo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12" id="FormHapusTempo">
                            <!-- Form Tempo Akan Muncul Disini -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12" id="NotifikasiHapusTempo">
                            <!-- Notifikasi Tempo Disini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-md btn-primary btn-rounded" id="ButtonHapusTempo">
                        <i class="bi bi-check-circle"></i> Ya, Hapus
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>