<div class="modal fade" id="ModalFilter" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesFilter">
                <input type="hidden" name="page" id="page" value="1">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-funnel"></i> Filter Pasien</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="batas">
                                <small>Batas/Limit</small>
                            </label>
                            <select name="batas" id="batas" class="form-control">
                                <option value="10">10</option>
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
                                <small><i>Order By</i></small>
                            </label>
                            <select name="OrderBy" id="OrderBy" class="form-control">
                                <option value="">Pilih</option>
                                <option value="nik">NIK</option>
                                <option value="nama">Nama</option>
                                <option value="gender">Gender</option>
                                <option value="email">Email</option>
                                <option value="kontak">Kontak</option>
                                <option value="alamat">Alamat</option>
                                <option value="tanggal_masuk">Tanggal Daftar</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="ShortBy">
                                <small><i>Short By</i></small>
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
                                <small><i>Keyword By</i></small>
                            </label>
                            <select name="keyword_by" id="keyword_by" class="form-control">
                                <option value="">Pilih</option>
                                <option value="id_pasien">RM</option>
                                <option value="nik">NIK</option>
                                <option value="nama">Nama</option>
                                <option value="gender">Gender</option>
                                <option value="kontak">Kontak</option>
                                <option value="tanggal_masuk">Tanggal Daftar</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12" id="FormFilter">
                            <label for="keyword">
                                <small><i>Keyword</i></small>
                            </label>
                            <input type="text" name="keyword" id="keyword" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-responsive">
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

<div class="modal fade" id="ModalTambahPasien" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesTambahPasien" autocomplete="off">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-plus"></i> Tambah Pasien</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="id_pasien">* No.RM</label>
                            <div class="input-group">
                                <button class="btn btn-secondary" type="button" id="generate_rm">
                                    <i class="bi bi-arrow-repeat"></i>
                                    Generate
                                </button>
                                <input type="text" name="id_pasien" id="id_pasien" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="nama">* Nama Lengkap</label>
                            <input type="text" name="nama" id="nama" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="nik">NIK/KTP</label>
                            <input type="text" name="nik" id="nik" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="id_ihs">ID IHS</label>
                            <div class="input-group">
                                <input type="text" name="id_ihs" id="id_ihs" class="form-control">
                                <button class="btn btn-secondary" type="button" id="cari_ihs">
                                    <i class="bi bi-cloud"></i> Cari
                                </button>
                            </div>
                            <small>ID Patient Dari Satusehat</small>
                        </div>
                    </div>
                    <div id="notifikasi_pencarian_ihs">
                        <!-- Notifikasi Pencarian IHS -->
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="gender">*Jenis Kelamin (Gender)</label>
                            <select name="gender" id="gender" class="form-control" required>
                                <option value="">Pilih</option>
                                <option value="Male">Laki-laki</option>
                                <option value="Female">Perempuan</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="tempat_lahir">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" id="tempat_lahir" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="tanggal_lahir">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="kontak">No.Kontak</label>
                            <input type="text" name="kontak" id="kontak" class="form-control" placeholder="62">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="alamat">Alamat Tinggal</label>
                            <textarea name="alamat" id="alamat" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="email@domain.com">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12" id="NotifikasiTambahPasien">
                            <!-- Notifikasi Tambah Pasien Muncul Disini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-responsive">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolTambahPasien">
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

<!-- Modal Detail -->
<div class="modal fade" id="ModalDetail" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesDetail">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-info-circle"></i> Detail Pasien
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="FormDetail">
                    <!-- Form Detail -->
                </div>
                <div class="modal-footer modal-footer-responsive">
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

<!-- Modal Edit -->
<div class="modal fade" id="ModalEdit" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesEdit">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-pencil"></i> Edit Pasien</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="FormEdit">
                            <!-- Form Edit Pasien -->
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12" id="NotifikasiEdit">
                            <!-- Notifikasi Edit Pasien Muncul Disini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-responsive">
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

<!-- Modal Delete -->
<div class="modal fade" id="ModalDelete" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesDelete">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-trash"></i> Hapus Data Pasien</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="FormDelete">

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-center" id="NotifikasiDelete">
                            <!-- Notifikasi Edit Pasien Muncul Disini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-responsive">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolDelete">
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

<!-- Modal Detail Kunjungan -->
<div class="modal fade" id="ModalDetailKunjungan" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
           <div class="modal-header">
                <h5 class="modal-title text-dark">
                    <i class="bi bi-info-circle"></i> Detail Kunjungan Pasien
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="FormDetailKunjungan">
                <!-- Form Detail -->
            </div>
            <div class="modal-footer modal-footer-responsive">
                <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>


<!-- Modal Detail Resep -->
<div class="modal fade" id="ModalDetailResep" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
           <div class="modal-header">
                <h5 class="modal-title text-dark">
                    <i class="bi bi-info-circle"></i> Detail Resep
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="FormDetailResep">
                <!-- Form Detail -->
            </div>
            <div class="modal-footer modal-footer-responsive">
                <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Transaksi -->
<div class="modal fade" id="ModalDetailTransaksi" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
           <div class="modal-header">
                <h5 class="modal-title text-dark">
                    <i class="bi bi-info-circle"></i> Detail Transaksi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12" id="FormDetailTransaksi">
                        <!-- Form Detail -->
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

<!-- Modal Detail IHS -->
<div class="modal fade" id="ModalDetailIhs" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
           <div class="modal-header">
                <h5 class="modal-title text-dark">
                    <i class="bi bi-info-circle"></i> Detail IHS
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12" id="FormDetailIhs">
                        <!-- Form Detail -->
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


<!-- Modal Export -->
<div class="modal fade" id="ModalExport" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="_Page/Pasien/ProsesExport.php" method="POST" target="_blank">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-download"></i> Export/Download
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="FormExport">
                            <!-- Form Export Akan Tampil Disini -->
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
<div class="modal fade" id="ModalImport" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesImport">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-upload"></i> Import Data
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
                                        pada link <a href="_Page/Pasien/Template-Pasien.xlsx">berikut ini</a>.
                                    </li>
                                    <li>
                                        Isi kolom <b>No RM, IHS, NIK, Nama, Email, Kontak, Alamat, Gender, Tempat Lahir dan Tanggal Lahir</b> sesuai data yang anda miliki.
                                    </li>
                                    <li>
                                        Kolom <b>IHS</b> diisi hanya jika pasien sudah diketahui IHS nya.
                                    </li>
                                    <li>
                                        ID Pasien, Nama, dan Gender wajib diisi. Gender diisi Male atau Female.
                                    </li>
                                    <li>
                                        ID Pasien tidak boleh duplikat. IHS, NIK, Email, dan Kontak juga tidak boleh duplikat jika diisi.
                                        Jika ada data tidak valid, seluruh import dibatalkan.
                                    </li>
                                </ol>
                            </small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="file_pasien">Upload File (Excel)</label>
                            <div class="input-group">
                                <input type="file" name="file_pasien" id="file_pasien" class="form-control">
                                <button type="submit" class="btn btn-primary" id="TombolImport">
                                    <i class="bi bi-upload"></i> Import
                                </button>
                            </div>
                            <small class="text text-muted">Maksimal 5 mb</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="table-responsive border rounded" style="max-height: 350px; overflow-y: auto;">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th><b>No.RM</b></th>
                                            <th><b>IHS</b></th>
                                            <th><b>NIK</b></th>
                                            <th><b>Nama</b></th>
                                            <th><b>Kontak</b></th>
                                            <th><b>Gender</b></th>
                                        </tr>
                                    </thead>
                                    <tbody id="NotifikasiImportPasien">
                                        <tr>
                                            <td colspan="6" class="text-center">
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
