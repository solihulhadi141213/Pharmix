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
                                <option value="medicalPersonelCode">ID Nakes</option>
                                <option value="id_practitioner">ID Practitioner (SATUSEHAT)</option>
                                <option value="medicalPersonelCategory">Kategori</option>
                                <option value="medicalPersonelName">Nama</option>
                                <option value="medicalPersonelNik">NIK</option>
                                <option value="medicalPersonelStatus">Status</option>
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
                                <option value="medicalPersonelCode">ID Nakes</option>
                                <option value="id_practitioner">ID Practitioner (SATUSEHAT)</option>
                                <option value="medicalPersonelCategory">Kategori</option>
                                <option value="medicalPersonelName">Nama</option>
                                <option value="medicalPersonelNik">NIK</option>
                                <option value="medicalPersonelStatus">Status</option>
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
                    <h5 class="modal-title text-dark"><i class="bi bi-plus-lg"></i> Tambah Nakes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="medicalPersonelCode">
                                <small>* Kode Nakes</small>
                            </label>
                        </div>
                        <div class="col-md-8">
                            <div class="input-group">
                                <button type="button" class="btn btn-md btn-secondary" id="GenerateKodeNakes">
                                    Generate
                                </button>
                                <input type="text" class="form-control" name="medicalPersonelCode" id="medicalPersonelCode" required placeholder="MP-000091">
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="medicalPersonelName">
                                <small>* Nama Nakes</small>
                            </label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="medicalPersonelName" id="medicalPersonelName" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="medicalPersonelNik">
                                <small>* Nomor NIK/KTP</small>
                            </label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="medicalPersonelNik" id="medicalPersonelNik" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="id_practitioner">
                                <small><i>ID Practitioner</i></small>
                            </label>
                        </div>
                        <div class="col-md-8">
                            <div class="input-group">
                                <input type="text" class="form-control" name="id_practitioner" id="id_practitioner">
                                <button type="button" class="btn btn-md btn-secondary" id="TombolCariPractitioner">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="medicalPersonelCategory">
                                <small>* Kategori</small>
                            </label>
                        </div>
                        <div class="col-md-8">
                            <select class="form-control" name="medicalPersonelCategory" id="medicalPersonelCategory" required>
                                <option value="">Pilih</option>
                                <option value="Dokter Umum">Dokter Umum</option>
                                <option value="Dokter Spesialis">Dokter Spesialis</option>
                                <option value="Perawat">Perawat</option>
                                <option value="Bidan">Bidan</option>
                                <option value="Rekam Medis">Rekam Medis</option>
                                <option value="Administrasi">Administrasi</option>
                                <option value="Apoteker">Apoteker</option>
                                <option value="Analis Laboratorium">Analis Laboratorium</option>
                                <option value="Radiografer">Radiografer</option>
                                <option value="Terapis">Terapis</option>
                                <option value="Gizi">Gizi</option>
                                <option value="Penata Anestesi">Penata Anestesi</option>
                                <option value="Elektromedis">Elektromedis</option>
                                <option value="Sanitarian">Sanitarian</option>
                                <option value="Epidemiolog">Epidemiolog</option>
                                <option value="Kesehatan Lingkungan">Kesehatan Lingkungan</option>
                                <option value="Kesehatan Masyarakat">Kesehatan Masyarakat</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="medicalPersonelGender">
                                <small>* Gender Nakes</small>
                            </label>
                        </div>
                        <div class="col-md-8">
                            <select class="form-control" name="medicalPersonelGender" id="medicalPersonelGender" required>
                                <option value="">Pilih</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="medicalPersonelPhone">
                                <small>Nomor Kontak (HP)</small>
                            </label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="medicalPersonelPhone" id="medicalPersonelPhone" placeholder="62">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="medicalPersonelEmail">
                                <small>Alamat Email</small>
                            </label>
                        </div>
                        <div class="col-md-8">
                            <input type="email" class="form-control" name="medicalPersonelEmail" id="medicalPersonelEmail" placeholder="@">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="medicalPersonelAddress">
                                <small>Alamat/Domisili</small>
                            </label>
                        </div>
                        <div class="col-md-8">
                            <textarea name="medicalPersonelAddress" id="medicalPersonelAddress" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="medicalPersonelStatus">
                                <small>* Status Nakes</small>
                            </label>
                        </div>
                        <div class="col-md-8">
                            <select class="form-control" name="medicalPersonelStatus" id="medicalPersonelStatus" required>
                                <option value="">Pilih</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
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

<!-- Modal Pencarian Practitioner -->
<div class="modal fade" id="ModalCariPractitioner" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark"><i class="bi bi-plus-lg"></i> Cari Practitioner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <form action="javascript:void(0);" id="ProsesPencarianPractitioner">
                            <div class="input-group">
                                <input type="text" class="form-control" name="NikNakes" id="NikNakes" required placeholder="NIK Nakes">
                                <button type="submit" class="btn btn-md btn-secondary">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12" id="ListPractitioner">
                        <!-- Hasil Pencarian Practitioner -->
                        <div class="alert alert-warning text-center">
                            <h1 class="bi bi-exclamation-circle"></h1>
                            <small>Tidak Ada Data Yang Ditampilkan</small>
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

<!-- Modal Detail Nakes -->
<div class="modal fade" id="ModalDetail" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark"><i class="bi bi-info-circle"></i> Detail Nakes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-12" id="FormDetail">
                       <!-- Form Detail Disini -->
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

<!-- Modal Detail Practitioner -->
<div class="modal fade" id="ModalDetailPractitioner" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark"><i class="bi bi-info-circle"></i> Detail Practitioner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12" id="FormDetailPractitioner">
                       <!-- Form Detail Practitioner Disini -->
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

<!-- Modal Edit -->
<div class="modal fade" id="ModalEdit" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesEdit">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-plus-lg"></i> Edit Nakes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="FormEdit">
                        <!-- Form Edit -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiEdit">
                           <!-- Notifikasi Edit -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="ButtonEdit">
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

<!-- Modal Hapus -->
<div class="modal fade" id="ModalHapus" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesHapus">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-trash"></i> Hapus Nakes</h5>
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
                           <!-- Notifikasi Hapus -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="ButtonHapus">
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

<!-- Modal Akses Nakes -->
<div class="modal fade" id="ModalAksesNakes" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesAksesNakes">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-key"></i> Akses Nakes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="FormAksesNakes">
                        <!-- Form Akses Nakes -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiAksesNakes">
                           <!-- Notifikasi Akses Nakes -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-rounded" id="ButtonAksesNakes">
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

