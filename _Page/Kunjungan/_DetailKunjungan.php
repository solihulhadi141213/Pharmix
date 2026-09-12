<?php

    // Koneksi dan session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Validasi Sesi Akses
    if (empty($SessionIdAkses)) {
        echo '
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger text-center">
                        <small>
                            <b>Opss!</b> <br>
                            Sesi Akses Sudah Berakhir! Silahkan Login Ulang
                        </small>
                    </div>
                </div>
            </div>
        ';
        exit;
    }

    // Tangkap ID Kunjungan
    if(empty($_POST['id_kunjungan'])){
        echo '
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger text-center">
                        <small>
                            <b>Opss!</b> <br>
                            ID Kunjungan Tidak Boleh Kosong!
                        </small>
                    </div>
                </div>
            </div>
        ';
        exit;
    }

    // Buat Variable ID kunjungan
    $id_kunjungan = validateAndSanitizeInput($_POST['id_kunjungan']);

    // Query ambil data kunjungan dengan LEFT JOIN ke tabel anggota
    $query = "SELECT kunjungan.*,  
                    anggota.id_pasien as rm_pasien, 
                    anggota.nama as nama_pasien, 
                    anggota.nik, 
                    anggota.gender, 
                    anggota.tanggal_lahir, 
                    anggota.kontak, 
                    anggota.alamat 
            FROM kunjungan 
            LEFT JOIN anggota ON kunjungan.id_anggota = anggota.id_anggota 
            WHERE kunjungan.id_kunjungan = ?";

    $stmt = mysqli_prepare($Conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_kunjungan);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$data) {
        echo '
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger text-center">
                        <small>
                            <b>Opss!</b> <br>
                            Data kunjungan tidak ditemukan di database.
                        </small>
                    </div>
                </div>
            </div>
        ';
        exit;
    }

    // Format data pendukung
    $id_anggota         = $data['id_anggota'] ?: "-";
    $rm_pasien         = $data['rm_pasien'] ?: "-";
    $nama_pasien       = $data['nama_pasien'] ?: "-";
    $nik               = $data['nik'] ?: "-";
    $gender            = $data['gender'] ?: "-";
    $tanggal_lahir     = $data['tanggal_lahir'] ? date('d-m-Y', strtotime($data['tanggal_lahir'])) : "-";
    $kontak            = $data['kontak'] ?: "-";
    $alamat            = $data['alamat'] ?: "-";

    $tanggal_kunjungan = $data['tanggal_kunjungan'] ? date('d-m-Y H:i', strtotime($data['tanggal_kunjungan'])) : "-";
    $jenis_kunjungan   = $data['jenis_kunjungan'] ?: "-";
    $priority          = $data['priority'] ?: "-";
    $keluhan           = $data['keluhan'] ?: "-";
    $nama_dokter       = $data['nama_dokter_penerima'] ?: "-";
    $nama_dpjp         = $data['nama_dpjp'] ?: "-";
    $nama_poli         = $data['nama_poli'] ?: "-";
    $id_encounter      = $data['id_encounter'] ?: "-";
    $status            = $data['status'] ?: "-";

    // Badge Priority
    switch ($priority) {
    case 'Emergency':
        $priorityBadge = '<span class="badge bg-danger">Emergency</span>';
        break;
    case 'Urgent':
        $priorityBadge = '<span class="badge bg-warning text-dark">Urgent</span>';
        break;
    default:
        $priorityBadge = '<span class="badge bg-secondary">Normal</span>';
        break;
}

    switch ($status) {
        case 'finished':
            $statusBadge = '<span class="badge bg-success">Finished</span>';
            break;
        case 'in-progress':
            $statusBadge = '<span class="badge bg-warning text-dark">In-Progress</span>';
            break;
        case 'cancelled':
            $statusBadge = '<span class="badge bg-danger">Cancelled</span>';
            break;
        case 'arrived':
            $statusBadge = '<span class="badge bg-primary">Arrived</span>';
            break;
        default:
            $statusBadge = '<span class="badge bg-secondary">'.ucfirst($status).'</span>';
            break;
    }

    // Menghitung Jumlah resep
    $jumlah_resep = mysqli_num_rows(mysqli_query($Conn, "SELECT id_medication_request_group FROM medication_request_group WHERE id_kunjungan='$id_kunjungan'"));
    $jumlah_diagnosis = mysqli_num_rows(mysqli_query($Conn, "SELECT id_diagnosis FROM diagnosis WHERE id_kunjungan='$id_kunjungan'"));
    $jumlah_transaksi = mysqli_num_rows(mysqli_query($Conn, "SELECT id_transaksi_jual_beli FROM transaksi_jual_beli WHERE id_anggota='$id_anggota'"));
?>
<div class="row mb-3 g-3 MobileCard-grid">
    <!-- Card Aksi -->
    <div class="col-md-12">
        <div class="card card-tambah h-100">
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                <div class="aksi-resep mb-3">
                    <button type="button" class="icon-tambah back_to_data" title="Kembali Ke Halaman Kunjungan">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button type="button" class="icon-tambah" id="tombol_cari" data-bs-toggle="modal" data-bs-target="#ModalEdit" data-id="<?php echo $id_kunjungan; ?>">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="icon-tambah" id="tombol_cari" data-bs-toggle="modal" data-bs-target="#ModalDelete" data-id="<?php echo $id_kunjungan; ?>">
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
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <b class="card-title"># Informasi Kunjungan</b>
            </div>
            <div class="card-body">
                <div class="row mb-2 mt-3">
                    <div class="col-12"><small><b>A. Identitas Pasien</b></small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-5"><small>No. RM</small></div>
                    <div class="col-1"><small>:</small></div>
                    <div class="col-6 text-end"><small><?php echo $rm_pasien;?></small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-5"><small>Nama Pasien</small></div>
                    <div class="col-1"><small>:</small></div>
                    <div class="col-6 text-end"><small><?php echo''.$nama_pasien.'';?></small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-5"><small>NIK</small></div>
                    <div class="col-1"><small>:</small></div>
                    <div class="col-6 text-end"><small><?php echo''.$nik.'';?></small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-5"><small>Gender</small></div>
                    <div class="col-1"><small>:</small></div>
                    <div class="col-6 text-end"><small><?php echo $gender; ?></small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-5"><small>Tgl. Lahir</small></div>
                    <div class="col-1"><small>:</small></div>
                    <div class="col-6 text-end"><small><?php echo ''.$tanggal_lahir.'';?></small></div>
                </div>
                <div class="row mb-2 mt-3">
                    <div class="col-12"><small><b>B. Informasi Kunjungan</b></small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-5"><small><i>ID Encounter</i></small></div>
                    <div class="col-1"><small>:</small></div>
                    <div class="col-6 text-end"><small><?php echo''.$id_encounter.''?></small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-5"><small>Tgl. Kunjungan</small></div>
                    <div class="col-1"><small>:</small></div>
                    <div class="col-6 text-end"><small><?php echo''.$tanggal_kunjungan.''?></small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-5"><small>Kategori</small></div>
                    <div class="col-1"><small>:</small></div>
                    <div class="col-6 text-end"><small><?php echo''.$jenis_kunjungan.'';?></small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-5"><small><i>Priority</i></small></div>
                    <div class="col-1"><small>:</small></div>
                    <div class="col-6 text-end"><small><?php echo''.$priorityBadge.'';?></small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-5"><small>Status</small></div>
                    <div class="col-1"><small>:</small></div>
                    <div class="col-6 text-end"><small><?php echo ''.$statusBadge.'';?></small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-5"><small>Poliklinik</small></div>
                    <div class="col-1"><small>:</small></div>
                    <div class="col-6 text-end"><small><?php echo ''.$nama_poli.'';?></small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-5"><small>Penerima</small></div>
                    <div class="col-1"><small>:</small></div>
                    <div class="col-6 text-end"><small><?php echo''.$nama_dokter.'';?></small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-5"><small>DPJP</small></div>
                    <div class="col-1"><small>:</small></div>
                    <div class="col-6 text-end"><small><?php echo ''.$nama_dpjp.'';?> </small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-5"><small>Keluhan</small></div>
                    <div class="col-1"><small>:</small></div>
                    <div class="col-6 text-end"><small><?php echo ''.$keluhan.'';?> </small></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-10">
                        <b class="card-title"><i class="bi bi-paperclip"></i> Attachment</b>
                    </div>
                    <div class="col-2 text-end">
                        <a href="javascript:void(0);" class="btn btn-md btn-primary btn-floating" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-list"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow" style="max-height: min(320px, 60vh); overflow-y: auto;">
                            <li>
                                <a class="dropdown-item sub_feature" href="javascript:void(0)" data-doc="Condition" data-id="<?php echo $id_kunjungan; ?>">
                                    1. Condition (<?php echo $jumlah_diagnosis; ?>)
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item sub_feature" href="javascript:void(0)">
                                    2. Observation
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item sub_feature" href="javascript:void(0)">
                                    3. Procedure
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item sub_feature" href="javascript:void(0)" data-doc="MedicationRequest" data-id="<?php echo $id_kunjungan; ?>">
                                    4. Medication Request (<?php echo $jumlah_resep; ?>)
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item sub_feature" href="javascript:void(0)">
                                    5. Medication Dispense
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item sub_feature" href="javascript:void(0)">
                                    6. Medication Statement
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item sub_feature" href="javascript:void(0)">
                                    7. Allergy Intolerance
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item sub_feature" href="javascript:void(0)">
                                    8. Clinical Impression
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item sub_feature" href="javascript:void(0)">
                                    9. Episode Of Care
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item sub_feature" href="javascript:void(0)">
                                    10. Care Plan
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item sub_feature" href="javascript:void(0)">
                                    11. Immunization
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item sub_feature" href="javascript:void(0)">
                                    12. Questionnaire Response
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item sub_feature" href="javascript:void(0)">
                                    13. Service Request
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item sub_feature" href="javascript:void(0)">
                                    14. Imaging Study
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item sub_feature" href="javascript:void(0)">
                                    15. Specimen
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item sub_feature" href="javascript:void(0)">
                                    16. Diagnostic Report
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item sub_feature" href="javascript:void(0)">
                                    17. Composition
                                </a>
                            </li>
                            <hr>
                            <li>
                                <a class="dropdown-item sub_feature" href="javascript:void(0)" data-doc="Transaksi" data-id="<?php echo $id_anggota; ?>">
                                    18. Transaksi
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3 mt-3">
                    <div class="col-12" id="attchment_view">
                        <div class="alert alert-warning text-center">
                            <h1 class="bi bi-inbox"></h1>
                            <small>
                                <b>Opss!</b> <br>
                                Belum Ada Lampiran Yang Ditampilkan. Silahkan Pilih Lampiran Terlebih Dulu.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
