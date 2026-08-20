<div class="modal fade" id="ModalFilter" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesFilter">
                <input type="hidden" name="page" id="page" value="1">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-filter"></i> Filter Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="batas">Limit/Batas</label>
                            <select name="batas" id="batas" class="form-control">
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
                        <div class="col-md-12">
                            <label for="OrderBy">Order By</label>
                            <select name="OrderBy" id="OrderBy" class="form-control">
                                <option value="">Pilih</option>
                                <option value="nama_supplier">Nama Supplier</option>
                                <option value="email_supplier">Email Perusahaan</option>
                                <option value="kontak_supplier">Kontak Perusahaan</option>
                                <option value="pic">PIC</option>
                                <option value="npwp">NPWP</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="ShortBy">Short By</label>
                            <select name="ShortBy" id="ShortBy" class="form-control">
                                <option value="DESC">Z To A</option>
                                <option value="ASC">A To Z</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="keyword_by">Keyword By</label>
                            <select name="keyword_by" id="keyword_by" class="form-control">
                                <option value="">Pilih</option>
                                <option value="nama_supplier">Nama Supplier</option>
                                <option value="email_supplier">Email Perusahaan</option>
                                <option value="kontak_supplier">Kontak Perusahaan</option>
                                <option value="pic">PIC</option>
                                <option value="npwp">NPWP</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12" id="FormFilterKeyword">
                            <label for="keyword">Keyword</label>
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

<!-- Modal Tambah Supplier -->
<div class="modal fade" id="ModalTambahSupplier" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesTambahSupplier">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-plus"></i> Tambah Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="nama_supplier"><span class="text-danger">*</span> Nama Supplier</label>
                            <input type="text" name="nama_supplier" id="nama_supplier" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="email_supplier">Email Perusahaan</label>
                            <input type="email" name="email_supplier" id="email_supplier" class="form-control" placeholder="email@domain.com">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="kontak_supplier">Kontak Perusahaan</label>
                            <input type="text" name="kontak_supplier" id="kontak_supplier" class="form-control" placeholder="62">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="alamat_supplier">Alamat Kantor</label>
                            <input type="text" name="alamat_supplier" id="alamat_supplier" class="form-control" placeholder="Contoh : Jalan Anggrek 4 Nomor 5 Kabupaten Kuningan-Jawa Barat">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="pic">PIC <i>(Person In Charge)</i></label>
                            <input type="text" name="pic" id="pic" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="npwp">NPWP (Nomor Pokok Wajib Pajak)</label>
                            <input type="text" name="npwp" id="npwp" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiTambahSupplier">
                            <!-- Notifikasi Tambah Supplier -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolTambahSupplier">
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

<!-- Modal Detail Supplier -->
<div class="modal fade" id="ModalDetailSupplier" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="index.php" method="GET">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-info-circle"></i> Detail Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="FormDetailSupplier">
                    <!-- Detail Supplier -->
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded">
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

<!-- Modal Edit Supplier -->
<div class="modal fade" id="ModalEditSupplier" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesEditSupplier">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-pencil"></i> Edit Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="FormEditSupplier">
                            <!-- Form Edit Supplier -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiEditSupplier">
                            <!-- Notifikasi Edit Supplier -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolEditSupplier">
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

<!-- Modal Hapus Supplier -->
<div class="modal fade" id="ModalHapusSupplier" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesHapusSupplier">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-trash"></i> Hapus Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="FormHapusSupplier">
                            <!-- Form Hapus Disini -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiHapusSupplier">
                            <!-- Notifikasi Hapus Disini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolHapusSupplier">
                        <i class="bi bi-check"></i> Ya, Hapus
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tidak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Export Supplier -->
<div class="modal fade" id="ModalExportSupplier" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="_Page/Supplier/ProsesExportSupplier.php" method="POST" target="_blank">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-download"></i> Export/Download Supplier
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="FormExportSupplier">
                            <!-- Form Export Supplier Akan Tampil Disini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded">
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

<!-- Modal Import -->
<div class="modal fade" id="ModalImportSupplier" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesImportSupplier">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-upload"></i> Import Data Supplier
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <small class="credit">
                                Sebelum melakukan import data, perhatikan hal berikut ini.
                                <ol>
                                    <li>
                                        Pastikan anda menggunakan template file untuk melakukan import 
                                        pada link <a href="_Page/Supplier/Template-Supplier.xlsx">berikut ini</a>.
                                    </li>
                                    <li>
                                        Isi kolom <b>No, Nama Supplier, Alamat, Email, Kontak, PIC dan NPWP</b> sesuai data yang anda miliki.
                                    </li>
                                    <li>
                                        Kolom <b>No dan Nama Supplier</b> wajib diisi sebagai data utama identifikasi supplier.
                                    </li>
                                    <li>
                                        Sistem akan menolak data jika Nama Supplier sudah terdaftar atau NPWP yang diisi sudah digunakan.
                                    </li>
                                </ol>
                            </small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="file_supplier">Upload File (Excel)</label>
                            <div class="input-group">
                                <input type="file" name="file_supplier" id="file_supplier" class="form-control">
                                <button type="submit" class="btn btn-primary" id="TombolImport">
                                    <i class="bi bi-upload"></i> Import
                                </button>
                            </div>
                            <small class="text text-muted">Maksimal 10 mb</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="table-responsive border rounded" style="max-height: 350px; overflow-y: auto;">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th><b>No</b></th>
                                            <th><b>Supplier</b></th>
                                            <th><b>Alamat</b></th>
                                            <th><b>Email</b></th>
                                            <th><b>Kontak</b></th>
                                            <th><b>PIC</b></th>
                                            <th><b>NPWP</b></th>
                                        </tr>
                                    </thead>
                                    <tbody id="NotifikasiImportSupplier">
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                <!-- Notifikasi Import Akan Muncul Disini -->
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="button" class="btn btn-primary btn-md w-100" id="TombolSelesai" disabled>
                                <i class="bi bi-check"></i> Selesai
                            </button>
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

<div class="modal fade" id="ModalExcelRiwayatTransaksi" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-light"><i class="bi bi-download"></i> Download Riwayat Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="FormExcelRiwayatTransaksi">
                
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalFilterriwayatTransaksi" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesFilterriwayatTransaksi">
                <input type="hidden" name="page" id="page_riwayat_transaksi" value="1">
                <input type="hidden" name="id_supplier" id="put_id_supplier_on_riwayat_transaksi" value="1">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-funnel"></i> Filter Riwayat Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-4">
                            <label for="batas_riwayat_transaksi">Limit/Batas</label>
                        </div>
                        <div class="col-8">
                            <select name="batas" id="batas_riwayat_transaksi" class="form-control">
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
                    <div class="row mb-2">
                        <div class="col-4">
                            <label for="OrderByRiwayatTransaksi">Mode Urutan</label>
                        </div>
                        <div class="col-8">
                            <select name="OrderBy" id="OrderByRiwayatTransaksi" class="form-control">
                                <option value="">Pilih</option>
                                <option value="kategori">Kategori</option>
                                <option value="tanggal">Tanggal</option>
                                <option value="status">Status</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4">
                            <label for="ShortByRiwayatTransaksi">Tipe Urutan</label>
                        </div>
                        <div class="col-8">
                            <select name="ShortBy" id="ShortByRiwayatTransaksi" class="form-control">
                                <option value="DESC">Z To A</option>
                                <option value="ASC">A To Z</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4">
                            <label for="keyword_by_riwayat_transaksi">Pencarian</label>
                        </div>
                        <div class="col-8">
                            <select name="keyword_by" id="keyword_by_riwayat_transaksi" class="form-control">
                                <option value="">Pilih</option>
                                <option value="kategori">Kategori</option>
                                <option value="tanggal">Tanggal</option>
                                <option value="status">Status</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4">
                            <label for="keyword_riwayat_transaksi">Kata Kunci</label>
                        </div>
                        <div class="col-8" id="FormFilterKeywordRiwayatTransaksi">
                            <input type="text" name="keyword" id="keyword_riwayat_transaksi" class="form-control">
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
<div class="modal fade" id="ModalDetailTransaksi" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="index.php" method="GET">
                <input type="hidden" name="Page" value="Pembelian">
                <input type="hidden" name="Sub" value="DetailPembelian">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-info-circle"></i> Detail Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-12" id="FormDetailTransaksi">
                            <!-- Form Detail Transaksi Disini -->
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="table table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th><b>No</b></th>
                                            <th><b>Barang</b></th>
                                            <th><b>QTY</b></th>
                                            <th><b>Harga</b></th>
                                            <th><b>PPN</b></th>
                                            <th><b>Diskon</b></th>
                                            <th><b>Subtotal</b></th>
                                        </tr>
                                    </thead>
                                    <tbody id="ListRincianTransaksi">
                                        <!-- List Detail Transaksi Disini -->
                                    </tbody>
                                </table>
                            </div>
                            
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-md btn-outline-info btn-rounded" id="ButtonSelengkapnyaTransaksi">
                        <i class="bi bi-three-dots"></i> Selengkapnya
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="ModalExportTransaksi" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesExportTransaksi">
                <input type="hidden" name="id_supplier" id="put_id_supplier_for_export_transaksi">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-download"></i> Export Riwayat Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-4"><small>Nama Supplier</small></div>
                        <div class="col-8"><small class="text text-muted" id="put_nama_supplier"></small></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4"><small>Format</small></div>
                        <div class="col-8"><small class="text text-muted">Excel</small></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="periode_transaksi_1"><small>Periode Awal</small></label>
                        </div>
                        <div class="col-8">
                            <input type="date" class="form-control" name="periode_transaksi_1" id="periode_transaksi_1">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="periode_transaksi_2"><small>Periode Akhir</small></label>
                        </div>
                        <div class="col-8">
                            <input type="date" class="form-control" name="periode_transaksi_2" id="periode_transaksi_2">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12" id="NotifikasiExportTransaksi">
                            <!-- Notifikasi Export Transaksi Disini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-md btn-primary btn-rounded" id="ButtonExportTransaksi">
                        <i class="bi bi-download"></i> Download/Export
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="ModalFilterRincian" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesFilterRincian">
                <input type="hidden" name="page" id="page_riwayat_rincian_transaksi" value="1">
                <input type="hidden" name="id_supplier" id="put_id_supplier_on_riwayat_rincian_transaksi" value="1">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-funnel"></i> Filter Riwayat Rincian Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-4">
                            <label for="batas_riwayat_rincian_transaksi">Limit/Batas</label>
                        </div>
                        <div class="col-8">
                            <select name="batas" id="batas_riwayat_rincian_transaksi" class="form-control">
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
                    <div class="row mb-2">
                        <div class="col-4">
                            <label for="OrderByRiwayatRincianTransaksi">Mode Urutan</label>
                        </div>
                        <div class="col-8">
                            <select name="OrderBy" id="OrderByRiwayatRincianTransaksi" class="form-control">
                                <option value="">Pilih</option>
                                <option value="nama_barang">Nama Barang</option>
                                <option value="kategori">Kategori</option>
                                <option value="tanggal">Tanggal</option>
                                <option value="status">Status</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4">
                            <label for="ShortByRiwayatRincianTransaksi">Tipe Urutan</label>
                        </div>
                        <div class="col-8">
                            <select name="ShortBy" id="ShortByRiwayatRincianTransaksi" class="form-control">
                                <option value="DESC">Z To A</option>
                                <option value="ASC">A To Z</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4">
                            <label for="keyword_by_riwayat_rincian_transaksi">Pencarian</label>
                        </div>
                        <div class="col-8">
                            <select name="keyword_by" id="keyword_by_riwayat_rincian_transaksi" class="form-control">
                                <option value="">Pilih</option>
                                <option value="nama_barang">Nama Barang</option>
                                <option value="kategori">Kategori</option>
                                <option value="tanggal">Tanggal</option>
                                <option value="status">Status</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4">
                            <label for="keyword_riwayat_rincian_transaksi">Kata Kunci</label>
                        </div>
                        <div class="col-8" id="FormFilterKeywordRiwayatRincianTransaksi">
                            <input type="text" name="keyword" id="keyword_riwayat_rincian_transaksi" class="form-control">
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

<div class="modal fade" id="ModalExportRincian" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="_Page/Supplier/ProsesExportRiwayatRincianTransaksi.php" method="POST" id="ProsesExportRincian">
                <input type="hidden" name="id_supplier" id="put_id_supplier_for_export_rincian_transaksi">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-download"></i> Export Rincian Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-4"><small>Nama Supplier</small></div>
                        <div class="col-8"><small class="text text-muted" id="put_nama_supplier_for_export_rincian"></small></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4"><small>Format</small></div>
                        <div class="col-8"><small class="text text-muted">Excel</small></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="periode_rincian_transaksi_1"><small>Periode Awal</small></label>
                        </div>
                        <div class="col-8">
                            <input type="date" class="form-control" name="periode_transaksi_1" id="periode_rincian_transaksi_1">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="periode_rincian_transaksi_2"><small>Periode Akhir</small></label>
                        </div>
                        <div class="col-8">
                            <input type="date" class="form-control" name="periode_transaksi_2" id="periode_rincian_transaksi_2">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12" id="NotifikasiExportRincian">
                            <!-- Notifikasi Export Rincian Transaksi Disini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-md btn-primary btn-rounded" id="ButtonExportRincian">
                        <i class="bi bi-download"></i> Download/Export
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>