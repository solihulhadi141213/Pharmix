<div class="modal fade" id="ModalFilter" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesFilter">
                <input type="hidden" name="page" id="PutPage" value="1">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-funnel"></i> Filter Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="batas">
                                <small>Batas/Limit</small>
                            </label>
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
                        <div class="col-12">
                            <label for="OrderBy">
                                <small>Order By</small>
                            </label>
                            <select name="OrderBy" id="OrderBy" class="form-control">
                                <option value="">Pilih</option>
                                <option value="judul">Judul</option>
                                <option value="creat_at">Tanggal</option>
                                <option value="author_name">Author</option>
                                <option value="status">Status</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
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
                        <div class="col-12">
                            <label for="keyword_by">
                                <small>Keyword By</small>
                            </label>
                            <select name="keyword_by" id="keyword_by" class="form-control">
                                <option value="judul">Judul</option>
                                <option value="creat_at">Tanggal</option>
                                <option value="author_name">Author</option>
                                <option value="tags">Tags</option>
                                <option value="status">Status</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12" id="FormFilter">
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
                    <button type="button" class="btn btn-dark btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalTambah" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesTambah">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-plus"></i> Tambah Dokumentasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="judul_dokumentasi">
                                <small>* Judul Dokumentasi</small>
                            </label>
                            <input type="text" name="judul_dokumentasi" id="judul_dokumentasi" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="tags_dokumentasi">
                                <small>* Tags / Category</small>
                            </label>
                           <select name="tags_dokumentasi[]" id="tags_dokumentasi" class="form-control" multiple required></select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="deskripsi_dokumentasi">
                                <small>* Deskripsi</small>
                            </label>
                            <textarea name="deskripsi_dokumentasi" id="deskripsi_dokumentasi" class="form-control"></textarea>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12" id="NotifikasiTambah">
                            <!-- Notifikasi Akan Muncul Disini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolTambah">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                    <button type="button" class="btn btn-dark btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalTambahKonten" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesTambahKonten">
                <input type="hidden" name="id_dokumentasi" id="put_id_dokumentasi_konten" value="0">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-plus"></i> Tambah Konten</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="tipe_konten">
                                <small>* Tipe Konten</small>
                            </label>
                            <select name="tipe_konten" id="tipe_konten" class="form-control" required>
                                <option value="">Pilih</option>
                                <option value="Text">Text</option>
                                <option value="List Numbering">List Numbering</option>
                                <option value="List Bullet">List Bullet</option>
                                <option value="Local Image">Local Image</option>
                                <option value="Url Image">Url Image</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12" id="FormTambahKonten">
                            <!-- Form Lanjutan Akan Muncul Disini -->
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12" id="NotifikasiTambahKonten">
                            <!-- Notifikasi Akan Muncul Disini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolTambahKonten">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                    <button type="button" class="btn btn-dark btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==== MODAL EDIT KONTEN -->
<div class="modal fade" id="ModalEditKonten" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesEditKonten">
                <input type="hidden" name="id_dokumentasi_konten" id="edit_id_dokumentasi_konten" value="0">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-pencil"></i> Edit Konten
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-------- TIPE KONTEN -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="edit_tipe_konten"><small>* Tipe Konten</small></label>
                            <select name="tipe_konten" id="edit_tipe_konten" class="form-control" disabled>
                                <option value="">Pilih</option>
                                <option value="Text">Text</option>
                                <option value="List Numbering">List Numbering</option>
                                <option value="List Bullet">List Bullet</option>
                                <option value="Local Image">Local Image</option>
                                <option value="Url Image">Url Image</option>
                            </select>
                            <!-------- Karena select disabled tidak ikut terkirim, simpan tipe dalam hidden input -->
                            <input type="hidden" name="tipe_konten" id="edit_tipe_konten_hidden">
                        </div>
                    </div>
                    <!-------- FORM DINAMIS -->
                    <div class="row">
                        <div class="col-12" id="FormEditKonten">
                            <!-- Form akan dibuat menggunakan Javascript -->
                        </div>
                    </div>
                    <!-------- OPSI LANJUTAN -->
                    <div class="row mb-3">
                        <div class="col-12 text-center">
                            <button type="button" class="btn btn-md btn-secondary btn-floating pindah_ke_atas" title="Pindahkan Ke Atas">
                                <i class="bi bi-chevron-up"></i>
                            </button>
                            <button type="button" class="btn btn-md btn-secondary btn-floating pindah_ke_bawah" title="Pindahkan Ke Bawah">
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            <button type="button" class="btn btn-md btn-danger btn-floating hapus_konten" title="Hapus Konten">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                    <!-------- NOTIFIKASI -->
                    <div class="row mb-3">
                        <div class="col-12" id="NotifikasiEditKonten"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolEditKonten">
                        <i class="bi bi-save"></i> Simpan Perubahan
                    </button>
                    <button type="button" class="btn btn-dark btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDIT DOKUMENTASI -->
 <div class="modal fade" id="ModalEditDokumentasi" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesEditDokumentasi">
                <input type="hidden" name="id_dokumentasi" id="edit_id_dokumentasi" value="">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-pencil"></i> Edit Dokumentasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="judul_dokumentasi_edit">
                                <small>* Judul Dokumentasi</small>
                            </label>
                            <input type="text" name="judul_dokumentasi" id="judul_dokumentasi_edit" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="tags_dokumentasi_edit">
                                <small>* Tags / Category</small>
                            </label>
                           <select name="tags_dokumentasi[]" id="tags_dokumentasi_edit" class="form-control" multiple required></select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="deskripsi_dokumentasi_edit">
                                <small>* Deskripsi</small>
                            </label>
                            <textarea name="deskripsi_dokumentasi" id="deskripsi_dokumentasi_edit" class="form-control"></textarea>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12" id="NotifikasiEditDokumentasi">
                            <!-- Notifikasi Akan Muncul Disini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolEditDokumentasi">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                    <button type="button" class="btn btn-dark btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL HAPUS DOKUMENTASI -->
<div class="modal fade" id="ModalHapusDokumentasi" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesHapusDokumentasi">
                <input type="hidden" name="id_dokumentasi" id="hapus_id_dokumentasi" value="">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-trash"></i> Hapus Dokumentasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12"  id="FormHapusDokumentasi">
                            <!-- Form Hapus Disini -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12"  id="NotifikasiHapusDokumentasi">
                            <!-- Notifikasi Hapus Disini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="ButtonHapusDokumentasi">
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