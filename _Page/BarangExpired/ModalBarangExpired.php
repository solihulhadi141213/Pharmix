<div class="modal fade" id="ModalFilter" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesFilter">
                <input type="hidden" name="page" id="page_exp" value="1">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-funnel"></i> Filter Batch & Expired</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="FilterBatas">Batas/Limit</label>
                            <select name="batas" id="FilterBatas" class="form-control">
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
                                <option value="kode_barang">Kode Barang</option>
                                <option value="nama_barang">Nama Barang</option>
                                <option value="no_batch">No Batch</option>
                                <option value="expired_date">Tanggal Expired</option>
                                <option value="reminder_date">Tanggal Pemberitahuan</option>
                                <option value="status">Status</option>
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
                                <option value="kode_barang">Kode Barang</option>
                                <option value="nama_barang">Nama Barang</option>
                                <option value="no_batch">No Batch</option>
                                <option value="expired_date">Tanggal Expired</option>
                                <option value="reminder_date">Tanggal Pemberitahuan</option>
                                <option value="status">Status</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="FormFilterKeyword">
                            <label for="keyword">Keyword</label>
                            <input type="text" name="keyword" id="keyword" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-responsive">
                    <button type="submit" class="btn btn-primary btn-rounded">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalTambahBarangExpired" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesTambahBarangExpired">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-plus"></i> Tambah Batch & Expired
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="id_barang">* Kode/Nama Barang</label>
                            <select name="id_barang" id="id_barang" required class="form-control">
                                <option value="">Pilih</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="no_batch">* No/Kode Batch</label>
                            <div class="input-group">
                                <input type="text" name="no_batch" id="no_batch" class="form-control" required>
                                <button type="button" class="btn btn-md btn-secondary generate_batch" title="Generate Kode Batch">
                                    <i class="bi bi-repeat"></i>
                                </button>
                            </div>
                            <small class="text text-grayish">
                                Kode Batch Produk/Barang Pada Kemasan
                            </small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="expired_date">* Expire Date</label>
                            <input type="date" name="expired_date" id="expired_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="reminder_date">* Reminder Date</label>
                            <input type="date" name="reminder_date" id="reminder_date" class="form-control" required>
                            <small class="text text-grayish">
                                Tanggal/Waktu kapan sistem menampilkan pemberitahuan.
                            </small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="id_barang_satuan">Satuan</label>
                            <select name="id_barang_satuan" id="id_barang_satuan_detail" class="form-control">
                                <option value="">Pilih</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="qty_batch">* Jumlah (QTY)</label>
                            <input type="number" min="0" step="0.01" name="qty_batch" id="qty_batch" class="form-control" value="1" required>
                            <small class="text text-grayish">
                                Jumlah barang dengan nomor batch yang sama
                            </small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <label for="status">* Status</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="">Pilih</option>
                                <option value="Terdaftar">Terdaftar</option>
                                <option value="Terjual">Sudah Terjual</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-12" id="NotifikasiTambahBarangExpired">
                            <!-- Notifikasi Tambah Barang Batch Disini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-responsive">
                    <button type="submit" class="btn btn-primary btn-rounded" id="ButtonTambahBarangExpired">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-toggle="modal" data-bs-target="#ModalPilihBarang">
                        <i class="bi bi-chevron-left"></i> Kembali
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalDetail" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark">
                    <i class="bi bi-info-circle"></i> Detail Batch & Expired
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-12" id="FormDetail">
                        <!-- Form Detail Batch & Expired Disini -->
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


<div class="modal fade" id="ModalDetailBarang" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark">
                    <i class="bi bi-info-circle"></i> Detail Barang
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-12" id="FormDetailBarang">
                        <!-- Form Detail Barang Disini -->
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
<div class="modal fade" id="ModalEdit" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesEdit">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-pencil-square"></i> Edit Batch & Expired</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-12" id="FormEdit">
                            <!-- Form Edit Barang Batch Disini -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiEdit">
                            Notifikasi Edit Barang Batch Disini
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-responsive">
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
<div class="modal fade" id="ModalHapus" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesHapus">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-trash"></i> Hapus Batch & Expired</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-md-12" id="FormHapus">
                            <!-- Form Hapus -->
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-12" id="NotifikasiHapus">
                            <!-- Notifikasi Hapus -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-responsive">
                    <button type="submit" class="btn btn-primary btn-rounded">
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
<div class="modal fade" id="ModalImport" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark"><i class="bi bi-upload"></i> Import Batch & Expired</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <small class="credit">
                            Sebelum melakukan import data, perhatikan hal berikut ini.
                            <ol>
                                <li>
                                    Pastikan anda menggunakan template file untuk melakukan import 
                                    pada link <a href="_Page/BarangExpired/Template-Batch-Expired.xlsx"><b>berikut ini</b></a>.
                                </li>
                                <li>
                                    Pada kolom <b>Kode Barang</b> diisi dengan kode barang yang terdaftar.
                                </li>
                                <li>
                                    Kolom <b>Nomor Batch</b> diisi dengan nomor batch masing-masing item barang.
                                </li>
                                <li>
                                    Sistem akan melakukan validasi duplikasi data berdasarkan <b>Nomor Batch</b>. 
                                    Apabila Nomor Batch yang anda masukan sudah terdaftar maka sistem tidak akan menambahkannya.
                                </li>
                            </ol>
                        </small>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col col-md-12">
                        <form action="javascript:void(0);" id="ProsesImport">
                            <label for="file_excel">File Excel</label>
                            <div class="input-group">
                                <input type="file" name="file_excel" id="file_excel" class="form-control">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-upload"></i> Upload
                                </button>
                            </div>
                            
                            <small class="credit">
                                File Type Excel (Max : 2 Mb) 
                            </small>
                        </form>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12" id="NotifikasiImport">
                        <!-- Notifikasi Import Disini -->
                    </div>
                </div>
            </div>
            <div class="modal-footer modal-footer-responsive">
                <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalExport" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="_Page/BarangExpired/ProsesExport.php" method="POST" target="_blank">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-download"></i> Export Data Batch & Expired</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="FormExport">
                            <!-- Notifikasi Import Disini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-responsive">
                    <button type="submit" class="btn btn-primary btn-rounded" disabled>
                        <i class="bi bi-download"></i> Download
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>