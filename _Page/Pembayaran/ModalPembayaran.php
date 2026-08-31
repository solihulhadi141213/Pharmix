<!-- Modal Filter -->
<div class="modal fade" id="ModalFilter" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesFilter">
                <input type="hidden" name="page" id="page" value="1">
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
                                <option value="tanggal">Tanggal</option>
                                <option value="id_transaksi">Ref Operasional</option>
                                <option value="id_transaksi_jual_beli">Ref Jual/Beli</option>
                                <option value="kategori_transaksi">Transaksi</option>
                                <option value="kategori_pembayaran">Kategori</option>
                                <option value="jumlah">Nominal</option>
                                <option value="creat_by_name">Petugas</option>
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
                                <option value="id_transaksi_pembayaran">ID Pembayaran</option>
                                <option value="tanggal">Tanggal</option>
                                <option value="id_transaksi">Ref Operasional</option>
                                <option value="id_transaksi_jual_beli">Ref Jual/Beli</option>
                                <option value="kategori_transaksi">Transaksi</option>
                                <option value="kategori_pembayaran">Kategori</option>
                                <option value="creat_by_name">Petugas</option>
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

<!-- Modal 'ModalTambahPembayaran' -->
<div class="modal fade" id="ModalTambahPembayaran" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesTambahPembayaran">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-plus-lg"></i> Tambah Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col col-md-12">
                            <label for="kategori_transaksi">
                                <small>Kategori Transaksi</small>
                            </label>
                            <select name="kategori_transaksi" id="kategori_transaksi" class="form-control">
                                <option value="">Pilih</option>
                                <option value="Operasional">Transaksi Operasional</option>
                                <option value="jual_beli">Jual/Beli</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-12">
                            <label for="id">
                                <small>ID Transaksi</small>
                            </label>
                            <select name="id" id="id" class="form-control">
                                <option value="">Pilih</option>
                            </select>
                            <small class="text-muted">Hanya menampilkan transaksi yang belum LUNAS</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-12">
                            <label for="tanggal_pembayaran">
                                <small>Tanggal Pembayaran</small>
                            </label>
                            <input type="date" name="tanggal_pembayaran" id="tanggal_pembayaran" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-12">
                            <label for="jam_pembayaran">
                                <small>Jam Pembayaran</small>
                            </label>
                            <input type="time" name="jam_pembayaran" id="jam_pembayaran" class="form-control" value="<?php echo date('H:i'); ?>">
                        </div>
                    </div>
                    <div id="row">
                        <div class="col-12" id="InfoJumlahSisaTagihan">
                            <!-- Menampilkan Jumlah sisa tagihan -->
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-12">
                            <label for="jumlah">
                                <small>Jumlah Nominal</small>
                            </label>
                            <input type="text" name="jumlah" id="jumlah" class="form-control form-money" placeholder="Rp">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12" id="NotifikasiTambahPembayaran">
                            <!-- Notifikasi Tambah Pembayaran -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded">
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

<!-- Modal 'ModalDetailPembayaran' -->
<div class="modal fade" id="ModalDetailPembayaran" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesDetailPembayaran">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-info-circle"></i> Detail Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12" id="FormDetailPembayaran">
                            <!-- Form Detail Pembayaran -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 'ModalDetailTransaksi' -->
<div class="modal fade" id="ModalDetailTransaksi" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark"><i class="bi bi-info-circle"></i> Detail Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12" id="FormDetailTransaksi">
                        <!-- Form Detail Pembayaran -->
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

<!-- Modal 'ModalEditPembayaran' -->
<div class="modal fade" id="ModalEditPembayaran" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesEditPembayaran">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-pencil"></i> Edit Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12" id="FormEditPembayaran">
                            <!-- Form Hapus Pembayaran -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12" id="NotifikasiEditPembayaran">
                            <!-- Notifikasi Hapus Pembayaran -->
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

<!-- Modal 'ModalHapusPembayaran' -->
<div class="modal fade" id="ModalHapusPembayaran" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesHapusPembayaran">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-trash"></i> Hapus Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12" id="FormHapusPembayaran">
                            <!-- Form Hapus Pembayaran -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12" id="NotifikasiHapusPembayaran">
                            <!-- Notifikasi Hapus Pembayaran -->
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

<!-- Modal 'ModalTambahJurnal' -->
<div class="modal fade" id="ModalTambahJurnal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesTambahJurnal">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-plus"></i> Tambah Jurnal
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12" id="FormTambahJurnal">
                            <!-- Form Tambah -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12" id="NotifikasiTambahJurnal">
                            <!-- Notifikasi Tambah Jurnal -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="ButtonTambahJurnal">
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

<!-- Modal 'ModalEditJurnal' -->
<div class="modal fade" id="ModalEditJurnal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesEditJurnal">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-pencil"></i> Edit Jurnal
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12" id="FormEditJurnal">
                            <!-- Form Edit Jurnal -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12" id="NotifikasiEditJurnal">
                            <!-- Notifikasi Edit Jurnal -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="ButtonEditJurnal">
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

<!-- Modal 'ModalHapusJurnnal' -->
<div class="modal fade" id="ModalHapusJurnnal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesHapusJurnnal">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-trash"></i> Hapus Jurnal
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12" id="FormHapusJurnnal">
                            <!-- Form Hapus Jurnal -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12" id="NotifikasiHapusJurnnal">
                            <!-- Notifikasi Hapus Jurnal -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="ButtonHapusJurnnal">
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

<!-- Modal 'ModalExport' -->
<div class="modal fade" id="ModalExport" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="_Page/Pembayaran/ProsesExport.php" method="POST" target="_blank">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-download"></i> Export Pembayaran
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="periode_awal">Periode Awal</label>
                            <input type="date" name="periode_awal" id="periode_awal" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="periode_akhir">Periode Akhir</label>
                            <input type="date" name="periode_akhir" id="perperiode_akhiriode_awal" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="format_data">Format Data</label>
                            <select name="format_data" id="format_data" class="form-control">
                                <option value="HTML">HTML</option>
                                <option value="Excel">Excel</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded">
                        <i class="bi bi-download"></i> Simpan
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
