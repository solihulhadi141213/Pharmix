<!-- FILTER DATA  -->
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
                        <div class="col-4">
                            <label for="batas">
                                <small>Limit</small>
                            </label>
                        </div>
                        <div class="col-8">
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
                        <div class="col-4">
                            <label for="OrderBy">
                                <small>Dasar Urutan</small>
                            </label>
                        </div>
                        <div class="col-8">
                            <select name="OrderBy" id="OrderBy" class="form-control">
                                <option value="">Pilih</option>
                                <option value="medication_code">Kode</option>
                                <option value="medication_name">Nama Obat/Alkes</option>
                                <option value="medication_category">Kategori</option>
                                <option value="sediaan_display">Sediaan</option>
                                <option value="kfa_code">KFA</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="ShortBy">
                                <small>Tipe Urutan</small>
                            </label>
                        </div>
                        <div class="col-8">
                            <select name="ShortBy" id="ShortBy" class="form-control">
                                <option value="ASC">A To Z</option>
                                <option selected value="DESC">Z To A</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="KeywordBy">
                                <small>Dasar Pencarian</small>
                            </label>
                        </div>
                        <div class="col-8">
                            <select name="keyword_by" id="KeywordBy" class="form-control">
                                <option value="">Pilih</option>
                                <option value="medication_code">Kode</option>
                                <option value="medication_name">Nama Obat/Alkes</option>
                                <option value="medication_category">Kategori</option>
                                <option value="sediaan_display">Sediaan</option>
                                <option value="kfa_code">KFA</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="keyword">
                                <small>Kata Kunci</small>
                            </label>
                        </div>
                        <div class="col-8" id="FormFilter">
                            <input type="text" name="keyword" id="keyword" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded">
                        <i class="bi bi-check"></i> Tampilkan
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EXPORT MEDICATION -->
<div class="modal fade" id="ModalExport" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border border-2 border-primary-subtle rounded-4 shadow-lg">
            <<form action="javascript:void(0);" id="ProsesExport" autocomplete="off">
                <div class="modal-header bg-light">
                    <h5 class="modal-title text-dark"><i class="bi bi-download"></i> Export</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light">
                    <div class="row">
                        <div class="col-md-12" id="FormExport">
                            <!-- Form -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiExport">
                            <!-- Notifikasi Proses -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolExport">
                        <i class="bi bi-download"></i> Export
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- IMPORT MEDICATION -->
<div class="modal fade" id="ModalImport" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border border-2 border-primary-subtle rounded-4 shadow-lg">
            <form action="javascript:void(0);" id="ProsesImport" autocomplete="off" enctype="multipart/form-data">
                <div class="modal-header bg-light">
                    <h5 class="modal-title text-dark"><i class="bi bi-upload"></i> Import</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                           <div class="alert alert-info">
                                <small>Silahkan gunakan file hasil export sebagai template data <i>Medication</i> yang akan anda import.</small>
                           </div>
                        </div>
                        <div class="col-md-12">
                            <label for="file_medication_excel">
                                <small>File Excel</small>
                            </label>
                            <input type="file" name="file_medication_excel" id="file_medication_excel" class="form-control" accept=".xlsx,.xls,.csv" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiImport">
                            <!-- Notifikasi Proses -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolImport">
                        <i class="bi bi-upload"></i> Import
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- PENCARIAN KFA  -->
<div class="modal fade" id="ModalCariKfa" tabindex="-1">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark"><i class="bi bi-search"></i> Cari KFA</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                    <form action="javascript:void(0);" id="ProsesCariKfa" autocomplete="off">
                    <input type="hidden" name="page" id="page_kfa" value="1">
                    <div class="row mb-3 border-1 border-bottom">
                        <div class="col-md-2 mb-2">
                            <select name="versi_pencarian" id="versi_pencarian" class="form-control">
                                <option value="">Pilih Versi</option>
                                <option value="V1">V1</option>
                                <option selected value="V2">V2</option>
                                <option value="V3">V3</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <select name="kategori_pencarian" id="kategori_pencarian" class="form-control">
                                <option value="">Pilih Kategori</option>
                                <option selected value="farmasi">Obat</option>
                                <option value="alkes">Alkes</option>
                            </select>
                        </div>
                        <div class="col-md-7 mb-2">
                            <div class="input-group">
                                <input type="text" name="keyword_pencarian" id="keyword_pencarian" class="form-control" placeholder="Kata Kunci Pencarian">
                            </div>
                        </div>
                        <div class="col-md-1 mb-2">
                            <div class="input-group">
                                <button type="submit" class="btn btn-lg btn-primary btn-block">
                                    <i class="bi bi-search"></i> Cari
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="table-load-container mt-3">
                            <table class="table table-hover table-responsive-card" id="TableKfa">
                                <thead>
                                    <tr>
                                        <th class="text-center"><b>No</b></th>
                                        <th><b>Produk Obat/Alkes</b></th>
                                        <th><b>KFA</b></th>
                                        <th class="text-center"><b>Opsi</b></th>
                                    </tr>
                                </thead>
                                <tbody id="tabel_kfa">
                                    <tr>
                                        <td colspan="4" class="text-center">Loading...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-info btn-floating" id="prev_button_kfa">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-info btn-rounded" id="page_info_kfa">0 / 0</button>
                <button type="button" class="btn btn-sm btn-outline-info btn-floating" id="next_button_kfa">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- TAMBAH MEDICATION DARI KFA -->
<div class="modal fade" id="ModalTambahMedicationKfa" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesTambahMedicationKfa" autocomplete="off">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-plus"></i> Tambah Index Medication</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="FormTambahMedicationKfa">
                            <!-- Form Proses -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiTambahMedicationKfa">
                            <!-- Notifikasi Proses -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- TAMBAH MEDICATION MANUAL -->
<div class="modal fade" id="ModalTambahManual" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesTambahManual" autocomplete="off">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-plus"></i> Tambah Index Medication</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                   
                    <div class="row mb-2">
                        <div class="col-md-12" id="FormTambahManual">
                            <!-- Form -->
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-12" id="list_ingridient">
                            <!-- Menampilkan List Ingridient -->
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12" id="NotifikasiTambahManual">
                            <!-- Notifikasi Proses -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolTambahManual">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- TAMBAH INGRIDIENT -->
<div class="modal fade" id="ModalTambahIngridient" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border border-2 border-primary-subtle rounded-4 shadow-lg">
            <form action="javascript:void(0);" id="ProsesTambahIngridient" autocomplete="off">
                <div class="modal-header bg-light">
                    <h5 class="modal-title text-dark"><i class="bi bi-plus"></i> Tambah Ingridient</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light">
                    <div class="row">
                        <div class="col-md-12" id="FormTambahIngridient">
                            <!-- Form Tambah -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiTambahIngridient">
                            <!-- Notifikasi Proses -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-primary btn-rounded">
                        <i class="bi bi-plus"></i> Tambahkan
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DETAIL MEDICATION -->
<div class="modal fade" id="ModalDetail" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark"><i class="bi bi-plus"></i> Detail Index Medication</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="FormDetail">
                        <!-- Form Proses -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <!-- <button type="button" class="btn btn-primary btn-rounded">
                    Selengkapnya <i class="bi bi-chevron-right"></i>
                </button> -->
                <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                    <i class="bi bi-x"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Medication -->
<div class="modal fade" id="ModalDetailMedication" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark"><i class="bi bi-info-circle"></i> Detail Medication</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="FormDetailMedication">
                        <!-- Menampilkan Form -->
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

<!-- EDIT MEDICATION-->
<div class="modal fade" id="ModalEdit" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesEdit" autocomplete="off">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-pencil"></i> Edit Index Medication</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                   
                    <div class="row mb-2">
                        <div class="col-md-12" id="FormEdit">
                            <!-- Form -->
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiEdit">
                            <!-- Notifikasi Proses -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolEdit">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- TAMBAH INGRIDIENT EDIT -->
<div class="modal fade" id="ModalTambahIngridientEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border border-2 border-primary-subtle rounded-4 shadow-lg">
            <form action="javascript:void(0);" id="ProsesTambahIngridientEdit" autocomplete="off">
                <div class="modal-header bg-light">
                    <h5 class="modal-title text-dark"><i class="bi bi-plus"></i> Tambah Ingridient</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light">
                    <div class="row">
                        <div class="col-md-12" id="FormTambahIngridientEdit">
                            <!-- Form Tambah -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiTambahIngridientEdit">
                            <!-- Notifikasi Proses -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-primary btn-rounded">
                        <i class="bi bi-plus"></i> Tambahkan
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hapus Medication -->
<div class="modal fade" id="ModalHapusMedication" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesHapusMedication">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-trash"></i> Hapus <i>Medication</i></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-12" id="FormHapusMedication">
                           <!-- Menampilkan Form -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiHapusMedication">
                           <!-- Menampilkan Notifikasi -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolHapusMedication">
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