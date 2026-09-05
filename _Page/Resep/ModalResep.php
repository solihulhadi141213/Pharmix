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
                        <div class="col-md-12">
                            <label for="batas_operasional">
                                <small>Limit/Batas</small>
                            </label>
                            <select name="batas" id="batas_operasional" class="form-control">
                                <option value="8">8</option>
                                <option selected value="12">12</option>
                                <option value="16">16</option>
                                <option value="20">20</option>
                                <option value="24">24</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="OrderByOperasional">
                                <small>Order By</small>
                            </label>
                            <select name="OrderBy" id="OrderByOperasional" class="form-control">
                                <option value="">Pilih</option>
                                <option value="id_pasien">No.RM</option>
                                <option value="nama_pasien">Nama Pasien</option>
                                <option value="no_resep_nasional">No Resep Nasional</option>
                                <option value="priority">Priority</option>
                                <option value="datetime_creat">Tanggal Resep</option>
                                <option value="dokter_nama">Dokter</option>
                                <option value="sumber_data">Sumber Data</option>
                                <option value="status_resep">Status</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
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
                        <div class="col-md-12">
                            <label for="keyword_by">
                                <small>Keyword By</small>
                            </label>
                            <select name="keyword_by" id="keyword_by" class="form-control">
                                <option value="">Pilih</option>
                                <option value="id_pasien">No.RM</option>
                                <option value="nama_pasien">Nama Pasien</option>
                                <option value="no_resep_nasional">No Resep Nasional</option>
                                <option value="priority">Priority</option>
                                <option value="datetime_creat">Tanggal Resep</option>
                                <option value="dokter_nama">Dokter</option>
                                <option value="sumber_data">Sumber Data</option>
                                <option value="status_resep">Status</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12" id="FormFilter">
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


<!-- Modal Tambah -->
<div class="modal fade" id="ModalTambah" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesTambah">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-plus-lg"></i> Tambah Resep</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="kategori_resep">
                                <small>Kategori Resep</small>
                            </label>
                        </div>
                        <div class="col-md-8">
                            <select name="kategori_resep" id="kategori_resep" class="form-control">
                                <option value="">Pilih</option>
                                <option value="Keluar">Resep Keluar</option>
                                <option value="Masuk">Resep Masuk</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="sumber_resep">
                                <small>* Sumber Resep</small>
                            </label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="sumber_resep" id="sumber_resep" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="id_anggota">
                                <small>No.RM</small>
                            </label>
                        </div>
                        <div class="col-md-8">
                            <select name="id_anggota" id="id_anggota" class="form-control">
                                <option value="">Pilih</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="id_kunjungan">
                                <small>No.Kunjungan</small>
                            </label>
                        </div>
                        <div class="col-md-8">
                            <select name="id_kunjungan" id="id_kunjungan" class="form-control">
                                <option value="">Pilih</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="priority">
                                <small><i>* Priority</i></small>
                            </label>
                        </div>
                        <div class="col-md-8">
                            <select name="priority" id="priority" class="form-control" required>
                                <option value="">Pilih</option>
                                <option selected value="routine">Biasa</option>
                                <option value="urgent">Segera</option>
                                <option value="asap">Darurat</option>
                                <option value="stat">Gawat</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="datetime_creat">
                                <small>* Tanggal & Jam</small>
                            </label>
                        </div>
                        <div class="col-md-8">
                            <div class="input-group">
                                <input type="date" class="form-control" name="date_creat" id="date_creat" value="<?php echo date('Y-m-d'); ?>" required>
                                <input type="time" class="form-control" name="time_creat" id="time_creat" value="<?php echo date('H:i'); ?>" required>
                            </div>
                            
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="dokter_id">
                                <small>Dokter Pemberi Resep</small>
                            </label>
                        </div>
                        <div class="col-md-8">
                            <input type="hidden" name="dokter_code">
                            <input type="hidden" name="dokter_ihs">
                            <input type="hidden" name="dokter_nama">
                            <select name="dokter_id" id="dokter_id" class="form-control">
                                <option value="">Pilih</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="reason_code">
                                <small><i><i>Reson Code</i></i></small>
                            </label>
                        </div>
                        <div class="col-md-8">
                            <input type="hidden" name="reason_display">
                            <input type="hidden" name="reason_system" value="http://hl7.org/fhir/sid/icd-10">
                            <select name="reason_code" id="reason_code" class="form-control">
                                <option value="">Pilih</option>
                            </select>
                            <small class="text-muted">Diagnosis Pasien / Alasan Pemberian Resep</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="apoteker_id">
                                <small>Apoteker</small>
                            </label>
                        </div>
                        <div class="col-md-8">
                            <input type="hidden" name="apoteker_code">
                            <input type="hidden" name="apoteker_nama">
                            <input type="hidden" name="apoteker_ihs">
                            <select name="apoteker_id" id="apoteker_id" class="form-control">
                                <option value="">Pilih</option>
                            </select>
                            <small class="text-muted">Petugas Apotek Yang Menerima Resep / Input Resep</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="no_resep_nasional">
                                <small>Nomor Resep Nasional (NRN)</small>
                            </label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="no_resep_nasional" id="no_resep_nasional">
                            <small class="text-muted">Hanya jika sudah dibuatkan NRN sebelumnnya</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="status_resep">
                                <small>* Status Resep</small>
                            </label>
                        </div>
                        <div class="col-md-8">
                            <select class="form-control" name="status_resep" id="status_resep" required>
                                <option value="">Pilih</option>
                                <option value="Draft">Draft</option>
                                <option value="Verified">Verified</option>
                                <option value="Partially">Partially</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiTambah">
                           <!-- Notifikasi Kesalahan Pada Saat Proses -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="ButtonTambah">
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

<!-- Modal Detail Resep -->
<div class="modal fade" id="ModalDetail" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesDetail">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-info-circle"></i> Detail Resep</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-12" id="FormDetail">
                           <!-- Menampilkan Form Detail -->
                        </div>
                    </div>
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

<!-- Modal Edit Resep -->
<div class="modal fade" id="ModalEdit" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesEdit">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-pencil"></i> Edit Resep</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="FormEdit">
                           <!-- Menampilkan Form Detail -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiEdit">
                           <!-- Menampilkan Form Detail -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolEdit">
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

<!-- Modal Hapus Resep -->
<div class="modal fade" id="ModalHapus" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesHapus">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-trash"></i> Hapus Resep</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="FormHapus">
                           <!-- Menampilkan Form -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiHapus">
                           <!-- Menampilkan Notifikasi -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolHapus">
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

<!-- Modal Cetak Resep -->
<div class="modal fade" id="ModalCetak" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesCetak">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-printer"></i> Cetak Resep</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="FormCetak">
                           <!-- Menampilkan Form -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolCetak">
                        <i class="bi bi-printer"></i> Mulai Cetak
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Item Resep -->
<div class="modal fade" id="ModalTambahItemResep" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesTambahItemResep">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-plus"></i> Tambah Item Resep
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="FormTambahItemResep">
                           <!-- Menampilkan Form -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiTambahItemResep">
                           <!-- Menampilkan Notifikasi -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolTambahItemResep">
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


<div class="modal fade" id="ModalTambahIngridient" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
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

<!-- Modal Detail Item Resep -->
<div class="modal fade" id="ModalDetailItemResep" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark">
                    <i class="bi bi-info-circle"></i> Detail Item Resep
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="FormDetailItemResep">
                        <!-- Form Detail Item Resep -->
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

<!-- Modal Edit Item Resep -->
<div class="modal fade" id="ModalEditItemResep" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesEditItemResep">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-pencil"></i> Edit Item Resep
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="FormEditItemResep">
                           <!-- Menampilkan Form -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiEditItemResep">
                           <!-- Menampilkan Notifikasi -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolEditItemResep">
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


<div class="modal fade" id="ModalTambahIngridientEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
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

<!-- Modal Cetak Label ETiket -->
<div class="modal fade" id="ModalCetakItemResep" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesCetakItemResep">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-printer"></i> Cetak Etiket
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="FormCetakItemResep">
                           <!-- Menampilkan Form -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiCetakItemResep">
                           <!-- Menampilkan Notifikasi -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolCetakItemResep">
                        <i class="bi bi-printer"></i> Print
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hapus Resep -->
<div class="modal fade" id="ModalHapusItemResep" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesHapusItemResep">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-trash"></i> Hapus Resep Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="FormHapusItemResep">
                           <!-- Menampilkan Form -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiHapusItemResep">
                           <!-- Menampilkan Notifikasi -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolHapusItemResep">
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

<!-- Modal Edit Item Resep -->
<div class="modal fade" id="ModalEditItemResep" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesEditItemResep">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-pencil"></i> Edit Item Resep
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="FormEditItemResep">
                           <!-- Menampilkan Form -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiEditItemResep">
                           <!-- Menampilkan Notifikasi -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolEditItemResep">
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

