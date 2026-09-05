<?php
    // Koneksi dan session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Validasi Sesi Akses
    if (empty($SessionIdAkses)) {
        echo '
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-danger text-center">
                        <small>
                            <b>Opss!</b><br>
                            Sesi Akses Sudah Berakhir! Silahkan Login Ulang!
                        </small>
                    </div>
                </div>
            </div>
        ';
        exit;
    }

    // Tangkap id_medication_request_group dari POST
    $id_medication_request_group = $_POST['id_medication_request_group'] ?? '';

    if (empty($id_medication_request_group)) {
        echo '
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-danger text-center">
                        <small>
                            <b>Opss!</b><br>
                            ID Resep Tidak Boleh Kosong!
                        </small>
                    </div>
                </div>
            </div>
        ';
        exit;
    }

    // Query ambil data kunjungan dengan LEFT JOIN ke tabel anggota
    $query = "SELECT * FROM medication_request_group WHERE id_medication_request_group = ?";
    $stmt  = mysqli_prepare($Conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_medication_request_group);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$data) {
        echo '
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-danger text-center">
                        <small>
                            <b>Opss!</b><br>
                            ID Resep Tidak Valid!
                        </small>
                    </div>
                </div>
            </div>
        ';
        exit;
    }

    // Informasi pasien
    if(!empty($data['id_anggota'])){
        $id_anggota    = $data['id_anggota'];
        $id_pasien     = GetDetailData($Conn, 'anggota', 'id_anggota', $id_anggota, 'id_pasien');
        $nama_pasien   = GetDetailData($Conn, 'anggota', 'id_anggota', $id_anggota, 'nama');
        $nik           = GetDetailData($Conn, 'anggota', 'id_anggota', $id_anggota, 'nik');
        $gender        = GetDetailData($Conn, 'anggota', 'id_anggota', $id_anggota, 'gender');
        $gender        = GetDetailData($Conn, 'anggota', 'id_anggota', $id_anggota, 'gender');
        $tempat_lahir  = GetDetailData($Conn, 'anggota', 'id_anggota', $id_anggota, 'tempat_lahir');
        $tanggal_lahir = GetDetailData($Conn, 'anggota', 'id_anggota', $id_anggota, 'tanggal_lahir');
        $id_ihs        = GetDetailData($Conn, 'anggota', 'id_anggota', $id_anggota, 'id_ihs');
        if(empty($id_ihs)){
            $id_ihs = "-";
        }
    }else{
        $id_anggota    = "";
        $id_pasien     = "-";
        $nama_pasien   = $nama_pasien;
        $nik           = "-";
        $gender        = "-";
        $tempat_lahir  = "-";
        $tanggal_lahir = "-";
        $id_ihs        = "-";
    }

    // Informasi Kunjungan
    if(!empty($data['id_kunjungan'])){
        $id_kunjungan      = $data['id_kunjungan'];
        $id_encounter      = GetDetailData($Conn, 'kunjungan', 'id_anggota', $id_anggota, 'id_encounter');
        $tanggal_kunjungan = GetDetailData($Conn, 'kunjungan', 'id_anggota', $id_anggota, 'tanggal_kunjungan');
        $jenis_kunjungan   = GetDetailData($Conn, 'kunjungan', 'id_anggota', $id_anggota, 'jenis_kunjungan');
        if(empty($id_encounter)){
            $id_encounter = "-";
        }
    }else{
        $id_kunjungan      = "";
        $id_encounter      = "-";
        $tanggal_kunjungan = "-";
        $jenis_kunjungan   = "-";
    }

    // Dokter Pemberi Resep
    if(!empty($data['dokter_id'])){
        $dokter_id   = $data['dokter_id'];
        $dokter_code = GetDetailData($Conn, 'medical_personel', 'medicalPersonelId', $dokter_id, 'medicalPersonelCode');
        $dokter_ihs  = GetDetailData($Conn, 'medical_personel', 'medicalPersonelId', $dokter_id, 'id_practitioner');
        $dokter_nama = GetDetailData($Conn, 'medical_personel', 'medicalPersonelId', $dokter_id, 'medicalPersonelName');
    }else{
        $dokter_id   = "";
        $dokter_code = "-";
        $dokter_ihs  = "-";
        $dokter_nama = "-";
    }

     // Apoteker
    if(!empty($data['apoteker_id'])){
        $apoteker_id   = $data['apoteker_id'];
        $apoteker_code = GetDetailData($Conn, 'medical_personel', 'medicalPersonelId', $apoteker_id, 'medicalPersonelCode');
        $apoteker_ihs = GetDetailData($Conn, 'medical_personel', 'medicalPersonelId', $apoteker_id, 'id_practitioner');
        $apoteker_nama  = GetDetailData($Conn, 'medical_personel', 'medicalPersonelId', $apoteker_id, 'medicalPersonelName');
    }else{
        $apoteker_id   = "";
        $apoteker_code = "-";
        $apoteker_nama = "-";
        $apoteker_ihs  = "-";
    }

    // Informasi Resep
    $datetime_creat    = $data['datetime_creat'] ?: "-";
    $priority          = $data['priority'] ?: "-";
    $reason_code       = $data['reason_code'];
    $reason_display    = $data['reason_display'];
    $sumber_resep      = $data['sumber_resep']?: "-";
    $status_resep      = $data['status_resep']?: "-";
    $no_resep_nasional = $data['no_resep_nasional']?: "-";

    // Metadata
    $creat_at       = $data['creat_at'];
    $update_at      = $data['update_at'];
    if(!empty($data['creat_by_id'])){
        $creat_by_id = $data['creat_by_id'];
        $creator     = GetDetailData($Conn, 'akses', 'id_akses', $creat_by_id, 'nama_akses');
    }else{
        $creator = $data['creat_by_name'];
    }
    if(!empty($data['update_by_id'])){
        $update_by_id = $data['update_by_id'];
        $updater      = GetDetailData($Conn, 'akses', 'id_akses', $update_by_id, 'nama_akses');
    }else{
        $updater = $data['update_by_name'];
    }

    // Susun HTML untuk ditampilkan di modal body (FormDetail)
    $html = '
        <input type="hidden" name="id_medication_request_group" value="'.$id_medication_request_group.'">
         <div class="row mb-2">
            <div class="col-12"><small><b>A. Informasi Resep</b></small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Tanggal Resep</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$datetime_creat.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Priority</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$priority.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small><i>Reson Code</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$reason_code.' - '.$reason_display.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Sumber Resep</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$sumber_resep.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Resep Nasional (NRN)</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$no_resep_nasional.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Status Resep</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$status_resep.'</small></div>
        </div>
        <div class="row mb-2 mt-3">
            <div class="col-12"><small><b>B. Informasi Pasien</b></small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>No. RM</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$id_pasien.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Nama Pasien</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$nama_pasien.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>NIK</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$nik.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Gender</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$gender.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Tanggal Lahir</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$tanggal_lahir.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small><i>ID Patient</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$id_ihs.'</small></div>
        </div>
        <div class="row mb-2 mt-3">
            <div class="col-12"><small><b>C. Informasi Kunjungan</b></small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Tanggal Kunjungan</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$tanggal_kunjungan.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Kategori</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$jenis_kunjungan.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small><i>ID Encounter</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$id_encounter.'</small></div>
        </div>

        <div class="row mb-2 mt-3">
            <div class="col-12"><small><b>D. Dokter Pemberi Resep</b></small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Kode Dokter</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$dokter_code.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Nama Dokter</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$dokter_nama.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small><i>IHS Dokter</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$dokter_ihs.'</small></div>
        </div>

        <div class="row mb-2 mt-3">
            <div class="col-12"><small><b>E. Informasi Apoteker</b></small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Kode Apoteker</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$apoteker_code.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Nama Apoteker</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$apoteker_nama.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small><i>IHS Apoteker</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$apoteker_ihs.'</small></div>
        </div>

        <div class="row mb-2 mt-3">
            <div class="col-12"><small><b>F. Metadata</b></small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small><i>Creat At</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.date('d/m/Y H:i', strtotime($creat_at)).'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small><i>Update At</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.date('d/m/Y H:i', strtotime($update_at)).'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small><i>Creator</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$creator.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small><i>Updater</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$updater.'</small></div>
        </div>
       
    ';
?>
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center">
                
                <!-- Tombol Kembali -->
                <button type="button" class="mt-4 btn btn-lg btn-primary btn-floating tombol_kembali">
                    <i class="bi bi-chevron-left"></i>
                </button>

                <!-- Edit Resep -->
                <button type="button" class="mt-4 btn btn-lg btn-info btn-floating edit_resep" data-id="<?php echo $id_medication_request_group; ?>">
                    <i class="bi bi-pencil"></i>
                </button>

                <!-- Delete Resep -->
                <button type="button" class="mt-4 btn btn-lg btn-danger btn-floating hapus_resep" data-id="<?php echo $id_medication_request_group; ?>">
                    <i class="bi bi-trash"></i>
                </button>

                <!-- Cetak Resep -->
                <button type="button" class="mt-4 btn btn-lg btn-secondary btn-floating cetak_resep" data-id="<?php echo $id_medication_request_group; ?>">
                    <i class="bi bi-printer"></i>
                </button>

            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <b class="card-title"># Informasi Resep</b>
            </div>
            <div class="card-body">
                <div class="row mt-4 mb-4">
                    <div class="col-12">
                        <?php echo $html; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <button type="button" class="btn btn-lg btn-block text-primary border border-primary border-2 py-3 tambah_item_resep" style="border-style: dashed !important;" data-id="<?php echo $id_medication_request_group; ?>">
            <i class="bi bi-plus-lg"></i> Tambah Item Resep
        </button>

        <div class="row mt-3 mb-3" id="list_item_resep">
            <div class="col-12">
                 <!-- List Item Resep Akan Muncul Disini -->
                <div class="alert alert-secondary text-center">
                    No Data
                </div>
            </div>
        </div>

    </div>
</div>