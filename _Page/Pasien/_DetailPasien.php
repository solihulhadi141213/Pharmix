<?php
    // Koneksi
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    date_default_timezone_set('Asia/Jakarta');

    // Fungsi Tampil Data
    function tampilDetail($value){
        if ($value === null || trim((string)$value) === '') {
            return '-';
        }
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    // Fungsi Tampil Tanggal
    function tampilTanggalDetail($value, $format = 'd/m/Y H:i'){
        if ($value === null || trim((string)$value) === '') {
            return '-';
        }
        $timestamp = strtotime((string)$value);
        return $timestamp === false
            ? '-'
            : date($format, $timestamp);
    }

    // Validasi Akses
    if (empty($SessionIdAkses)) {
        echo '
            <div class="alert alert-danger">
                <small>Sesi Akses Sudah Berakhir. Silahkan Login Ulang!</small>
            </div>
        ';
        exit;
    }

    // Validasi ID
    if (empty($_POST['id_anggota'])) {
        echo '
            <div class="alert alert-danger">
                <small>ID Pasien Tidak Boleh Kosong!</small>
            </div>
        ';
        exit;
    }

    // Ambil ID
    $id_anggota = validateAndSanitizeInput($_POST['id_anggota']);

    // Query Database
    $Qry = $Conn->prepare("SELECT * FROM anggota WHERE id_anggota = ? LIMIT 1");
    $Qry->bind_param("i", $id_anggota);

    if (!$Qry->execute()) {
        echo '
            <div class="alert alert-danger">
                <small>
                    Terjadi kesalahan saat membuka data!<br>
                    Keterangan : '.htmlspecialchars($Qry->error, ENT_QUOTES, 'UTF-8').'
                </small>
            </div>
        ';
        exit;
    }

    $Result = $Qry->get_result();
    $Data   = $Result->fetch_assoc();
    $Qry->close();
    if (!$Data) {
        echo '
            <div class="alert alert-warning">
                <small>Data pasien tidak ditemukan.</small>
            </div>
        ';
        exit;
    }

    // Mapping Data
    $id_pasien     = tampilDetail($Data['id_pasien'] ?? null);
    $id_ihs        = tampilDetail($Data['id_ihs'] ?? null);
    $nik           = tampilDetail($Data['nik'] ?? null);
    $nama          = tampilDetail($Data['nama'] ?? null);
    $email         = tampilDetail($Data['email'] ?? null);
    $kontak        = tampilDetail($Data['kontak'] ?? null);
    $alamat        = tampilDetail($Data['alamat'] ?? null);
    $gender        = tampilDetail($Data['gender'] ?? null);
    $tempat_lahir  = tampilDetail($Data['tempat_lahir'] ?? null);
    $tanggal_lahir = tampilTanggalDetail($Data['tanggal_lahir'] ?? null, 'd/m/Y');

    // Metadata
    $creat_at       = $Data['creat_at'] ?? null;
    $creat_by_id    = $Data['creat_by_id'] ?? null;
    $creat_by_name  = $Data['creat_by_name'] ?? null;
    $update_at      = $Data['update_at'] ?? null;
    $update_by_id   = $Data['update_by_id'] ?? null;
    $update_by_name = $Data['update_by_name'] ?? null;

    $creator = !empty($creat_by_id)
        ? GetDetailData($Conn, 'akses', 'id_akses', $creat_by_id, 'nama_akses')
        : $creat_by_name;

    $updater = !empty($update_by_id)
        ? GetDetailData($Conn, 'akses', 'id_akses', $update_by_id, 'nama_akses')
        : $update_by_name;

    $creator = tampilDetail($creator);
    $updater = tampilDetail($updater);

    
?>
<style>
    .detail-pasien-info > .detail-pasien-row {
        display: grid;
        grid-template-columns: 5rem 0.5rem minmax(0, 1fr);
        column-gap: 0.5rem;
        align-items: baseline;
        margin-left: 0;
        margin-right: 0;
    }

    .detail-pasien-info > .row > .col-4,
    .detail-pasien-info > .row > .col-1,
    .detail-pasien-info > .row > .col-7 {
        width: auto;
        min-width: 0;
        padding-left: 0;
        padding-right: 0;
        white-space: nowrap;
    }

    .detail-pasien-info > .row > .col-7 > small {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>
<div class="row mb-3 g-3 MobileCard-grid">
    <!-- Card Aksi -->
    <div class="col-md-12">
        <div class="card card-tambah h-100">
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                <div class="aksi-resep mb-3">
                    <button type="button" class="icon-tambah back_to_data" title="Kembali Ke Halaman Pasien">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button type="button" class="icon-tambah" id="tombol_cari" data-bs-toggle="modal" data-bs-target="#ModalEdit" data-id="<?php echo $id_anggota; ?>">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="icon-tambah" id="tombol_cari" data-bs-toggle="modal" data-bs-target="#ModalDelete" data-id="<?php echo $id_anggota; ?>">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <h6 class="mb-1">Kelola Pasien</h6>
                <small class="text-muted">
                    Ubah Identitas Pasien Atau Hapus Data Pasien
                </small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12">

        <!-- Menampilkan Detail Informasi Pasien -->
         <?php
            // Output HTML
            echo '
                <input type="hidden" name="id_anggota" id="get_id" value="'.$id_anggota.'">
                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-header">
                                <div class="row">
                                    <div class="col-12">
                                        <b class="card-title">
                                            <i class="bi bi-info-circle"></i> Detail Pasien
                                        </b>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="container-fluid mt-3 mb-3 detail-pasien-info">
                                    <div class="row mb-2">
                                        <div class="col-12"><small><b># Informasi Pasien</b></small></div>
                                    </div>

                                    <div class="row mb-2 detail-pasien-row">
                                        <div class="col-4"><small>No.RM</small></div>
                                        <div class="col-1"><small>:</small></div>
                                        <div class="col-7"><small class="text-muted">'.$id_pasien.'</small></div>
                                    </div>

                                    <div class="row mb-2 detail-pasien-row">
                                        <div class="col-4"><small>Nama</small></div>
                                        <div class="col-1"><small>:</small></div>
                                        <div class="col-7"><small class="text-muted">'.$nama.'</small></div>
                                    </div>

                                    <div class="row mb-2 detail-pasien-row">
                                        <div class="col-4"><small>Gender</small></div>
                                        <div class="col-1"><small>:</small></div>
                                        <div class="col-7"><small class="text-muted">'.$gender.'</small></div>
                                    </div>

                                    <div class="row mb-2 detail-pasien-row">
                                        <div class="col-4"><small>NIK/KTP</small></div>
                                        <div class="col-1"><small>:</small></div>
                                        <div class="col-7"><small class="text-muted">'.$nik.'</small></div>
                                    </div>

                                    <div class="row mb-2 detail-pasien-row">
                                        <div class="col-4"><small>ID IHS</small></div>
                                        <div class="col-1"><small>:</small></div>
                                        <div class="col-7"><small class="text-muted">'.$id_ihs.'</small></div>
                                    </div>

                                    <div class="row mb-2 detail-pasien-row">
                                        <div class="col-4"><small>Email</small></div>
                                        <div class="col-1"><small>:</small></div>
                                        <div class="col-7"><small class="text-muted">'.$email.'</small></div>
                                    </div>

                                    <div class="row mb-2 detail-pasien-row">
                                        <div class="col-4"><small>Kontak</small></div>
                                        <div class="col-1"><small>:</small></div>
                                        <div class="col-7"><small class="text-muted">'.$kontak.'</small></div>
                                    </div>

                                    <div class="row mb-2 detail-pasien-row">
                                        <div class="col-4"><small>Alamat</small></div>
                                        <div class="col-1"><small>:</small></div>
                                        <div class="col-7"><small class="text-muted">'.$alamat.'</small></div>
                                    </div>

                                    <div class="row mb-2 detail-pasien-row">
                                        <div class="col-4"><small>Tpt.Lahir</small></div>
                                        <div class="col-1"><small>:</small></div>
                                        <div class="col-7"><small class="text-muted">'.$tempat_lahir.'</small></div>
                                    </div>

                                    <div class="row mb-2 detail-pasien-row">
                                        <div class="col-4"><small>Tgl.Lahir</small></div>
                                        <div class="col-1"><small>:</small></div>
                                        <div class="col-7"><small class="text-muted">'.$tanggal_lahir.'</small></div>
                                    </div>
                                    <hr>
                                    <div class="row mb-2 mt-3">
                                        <div class="col-12"><small><b># Metadata</b></small></div>
                                    </div>

                                    <div class="row mb-2 detail-pasien-row">
                                        <div class="col-4"><small>Create At</small></div>
                                        <div class="col-1"><small>:</small></div>
                                        <div class="col-7"><small class="text-muted">'.tampilTanggalDetail($creat_at).'</small></div>
                                    </div>

                                    <div class="row mb-2 detail-pasien-row">
                                        <div class="col-4"><small>Update At</small></div>
                                        <div class="col-1"><small>:</small></div>
                                        <div class="col-7"><small class="text-muted">'.tampilTanggalDetail($update_at).'</small></div>
                                    </div>

                                    <div class="row mb-2 detail-pasien-row">
                                        <div class="col-4"><small>Creator</small></div>
                                        <div class="col-1"><small>:</small></div>
                                        <div class="col-7"><small class="text-muted">'.$creator.'</small></div>
                                    </div>

                                    <div class="row mb-2 detail-pasien-row">
                                        <div class="col-4"><small>Updater</small></div>
                                        <div class="col-1"><small>:</small></div>
                                        <div class="col-7"><small class="text-muted">'.$updater.'</small></div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <i><small class="text-muted">'.date('d/m/Y').'</small></i>
                            </div>
                        </div>
                    </div>
                </div>
            ';
         ?>
    </div>

    <!-- Menampilkan Riwayat Kunjungan -->
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12">
         <form id="FilterKunjungan">
            <input type="hidden" name="id_anggota" id="pagid_anggota_kunjungan" value="<?php echo $id_anggota; ?>">
            <input type="hidden" name="page_kunjungan" id="page_kunjungan" value="1">
        </form>
        <div class="card">
            <div class="card-header">
                <b class="card-title">
                    <i class="bi bi-activity"></i> Riwayat Kunjungan
                </b>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mt-3 mb-3" id="riwayat_kunjungan">
                        <div class="alert alert-warning text-center">
                            <h1 class="bi bi-inboxes"></h1>
                            <small>
                                Belum Ada Riwayat Kunjungan
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-6">
                        <small id="page_info_kunjungan">
                            Page 1 Of 1
                        </small>
                    </div>
                    <div class="col-6 text-end">
                        <button type="button" class="btn btn-md btn-outline-info btn-floating" id="prev_button_kunjungan">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button type="button" class="btn btn-md btn-outline-info btn-floating" id="next_button_kunjungan">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Menampilkan Riwayat Resep -->
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12">
        <form id="FilterResep">
            <input type="hidden" name="id_anggota" value="<?php echo tampilDetail($id_anggota); ?>">
            <input type="hidden" name="page_resep" id="page_resep" value="1">
        </form>
        <div class="card">
            <div class="card-header">
                <b class="card-title">
                    <i class="bi bi-receipt"></i> Riwayat Resep
                </b>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mt-3 mb-3" id="riwayat_resep">
                        <div class="alert alert-warning text-center">
                            <h1 class="bi bi-inboxes"></h1>
                            <small>
                                Belum Ada Riwayat Resep
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-6">
                        <small id="page_info_resep">
                            Page 1 Of 1
                        </small>
                    </div>
                    <div class="col-6 text-end">
                        <button type="button" class="btn btn-md btn-outline-info btn-floating" id="prev_button_resep">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button type="button" class="btn btn-md btn-outline-info btn-floating" id="next_button_resep">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Menampilkan Riwayat Transaksi -->
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12">
        <form id="FilterTransaksi">
            <input type="hidden" name="id_anggota" value="<?php echo tampilDetail($id_anggota); ?>">
            <input type="hidden" name="page_transaksi" id="page_transaksi" value="1">
        </form>
        <div class="card">
            <div class="card-header">
                <b class="card-title">
                    <i class="bi bi-activity"></i> Riwayat Transaksi
                </b>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mt-3 mb-3" id="riwayat_transaksi">
                        <div class="alert alert-warning text-center">
                            <h1 class="bi bi-inboxes"></h1>
                            <small>
                                Belum Ada Riwayat Transaksi
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-6">
                        <small id="page_info_transaksi">
                            Page 1 Of 1
                        </small>
                    </div>
                    <div class="col-6 text-end">
                        <button type="button" class="btn btn-md btn-outline-info btn-floating" id="prev_button_transaksi">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button type="button" class="btn btn-md btn-outline-info btn-floating" id="next_button_transaksi">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
