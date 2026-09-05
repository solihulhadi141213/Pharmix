<?php
    // Koneksi dan session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Header output JSON
    header('Content-Type: application/json');

    // Validasi Sesi Akses
    if (empty($SessionIdAkses)) {
        echo json_encode([
            "status" => "error",
            "message" => "Sesi akses sudah berakhir. Silakan login ulang."
        ]);
        exit;
    }

    // Tangkap id_medication_request_group dari POST
    $id_medication_request_group = $_POST['id_medication_request_group'] ?? '';

    if (empty($id_medication_request_group)) {
        echo json_encode([
            "status" => "error",
            "message" => "ID Resep Tidak Boleh Kosong."
        ]);
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
        echo json_encode([
            "status" => "error",
            "message" => "ID Resep Tidak Valid"
        ]);
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
            <div class="col-4"><small>Nomor Resep Nasional (NRN)</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$no_resep_nasional.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Status Resep</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$status_resep.'</small></div>
        </div>
        <div class="row mb-2 mt-3">
            <div class="col-12">
                <div class="alert alert-danger text-center">
                    <small>
                        <b>PENTING!</b><br>
                        Data Yang Sudah Dihapus Tidak Akan Bisa Dikembalikan Lagi<br><br>
                        <i>Apakah Anda Yakin AKan Menghapus Data Ini?</i>
                    </small>
                </div>
            </div>
        </div>
       
    ';

    // Mengaktifkan kembali tombol "Selengkapnya" pada modal dan menyematkan data-id
    // Catatan: Kita mengirimkan id_kunjungan ke response agar jQuery bisa menyematkannya ke form submit
    echo json_encode([
        "status"       => "success",
        "html"         => $html,
        "id_kunjungan" => $id_kunjungan
    ]);
?>