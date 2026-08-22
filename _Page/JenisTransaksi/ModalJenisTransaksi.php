<div class="modal fade" id="ModalFilter" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesFilter">
                <input type="hidden" name="page" id="page">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-filter"></i> Filter Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="batas">Batas/Limit</label>
                            <select name="batas" id="batas" class="form-control">
                                <option value="10">10</option>
                                <option selected value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="250">250</option>
                                <option value="500">500</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="OrderBy">Order By</label>
                            <select name="OrderBy" id="OrderBy" class="form-control">
                                <option value="">Pilih</option>
                                <option value="nama">Nama Transaksi</option>
                                <option value="kategori">Kategori</option>
                                <option value="id_akun_debet">Akun Debet</option>
                                <option value="id_akun_kredit">Akun Kredit</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="ShortBy">Short By</label>
                            <select name="ShortBy" id="ShortBy" class="form-control">
                                <option value="DESC">Z To A</option>
                                <option value="ASC">A To Z</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="keyword_by">Keyword By</label>
                            <select name="keyword_by" id="keyword_by" class="form-control">
                                <option value="">Pilih</option>
                                <option value="nama">Nama Transaksi</option>
                                <option value="kategori">Kategori</option>
                                <option value="id_akun_debet">Akun Debet</option>
                                <option value="id_akun_kredit">Akun Kredit</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3" id="FormFilter">
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
<div class="modal fade" id="ModalTambahJenisTransaksi" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesTambahJenisTransaksi" autocomplete="off">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-plus"></i> Tambah Jenis Transaksi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col col-md-4">
                            <label for="nama">
                                <small>Jenis Transaksi</small>
                            </label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="nama" id="nama" class="form-control">
                            <small class="text-muted">
                                Nama jenis transaksi (Ex: Iuran Air dan listrik, Gaji Staf, ATK, Dll)
                            </small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-4">
                            <label for="kategori">
                                <small>Kategori Transaksi</small>
                            </label>
                        </div>

                        <div class="col-md-8">
                            <select name="kategori" id="kategori" class="form-select" style="width: 100%;">
                                <option value=""></option>
                            </select>
                            <small class="text-muted">
                               Pilih kategori yang tersedia atau ketik kategori baru.
                            </small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-4">
                            <label for="deskripsi">
                                <small>Deskripsi</small>
                            </label>
                        </div>
                        <div class="col-md-8">
                            <textarea name="deskripsi" id="deskripsi" class="form-control"></textarea>
                            <small class="text-muted">
                                Jelaskan secara singkat mengenai transaksi tersebut.
                            </small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-4">
                            <label for="id_akun_debet">
                                <small>Akun Perkiraan (Debet)</small>
                            </label>
                        </div>
                        <div class="col-md-8">
                            <select name="id_akun_debet" id="id_akun_debet" class="form-select" style="width: 100%;">
                                <option value=""></option>
                            </select>
                            <small class="text-muted">
                                Pengaturan akun perkiraan yang akan digunakan pada lajur debet
                            </small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col col-md-4">
                            <label for="id_akun_kredit">
                                <small>Akun Perkiraan (Kredit)</small>
                            </label>
                        </div>
                        <div class="col-md-8">
                            <select name="id_akun_kredit" id="id_akun_kredit" class="form-select" style="width: 100%;">
                                <option value=""></option>
                            </select>
                            <small class="text-muted">
                                Pengaturan akun perkiraan yang akan digunakan pada lajur kredit
                            </small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiTambahJenisTransaksi">
                            <!-- Notifikasi Tambah Anggota Muncul Disini -->
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
<div class="modal fade" id="ModalDetail" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark">
                    <i class="bi bi-info-circle"></i> Detail Jenis Transaksi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="FormDetail">
                <!-- Uraian Detail Jenis Transaksi Akan Muncul Disini -->
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesEdit">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-pencil"></i> Edit Jenis Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="FormEdit">
                            <!-- Form Edit Disini -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifiasiEdit">
                            <!-- Notifikasi Edit Muncul Disini -->
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
<div class="modal fade" id="ModalHapus" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesHapus">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-trash"></i> Hapus Jenis Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="FormHapus">
                            <!-- Form Hapus -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiHapus">
                            <!-- Notifikasi Hapus Muncul Disini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded">
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