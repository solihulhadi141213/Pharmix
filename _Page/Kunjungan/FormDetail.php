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

    // Tangkap id_kunjungan dari POST
    $id_kunjungan = $_POST['id_kunjungan'] ?? '';

    if (empty($id_kunjungan)) {
        echo json_encode([
            "status" => "error",
            "message" => "ID Kunjungan tidak valid."
        ]);
        exit;
    }

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
        echo json_encode([
            "status" => "error",
            "message" => "Data kunjungan tidak ditemukan di database."
        ]);
        exit;
    }

    // Format data pendukung
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

    // Susun HTML untuk ditampilkan di modal body (FormDetail)
    $html = '
        <input type="hidden" name="id_kunjungan" name="id_kunjungan" value="'.$id_kunjungan.'">
        <div class="row mb-2">
            <div class="col-12"><small><b>A. Informasi Pasien</b></small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>No. RM</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.$rm_pasien.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Nama Pasien</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.$nama_pasien.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>NIK</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.$nik.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Gender</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.$gender.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Tanggal Lahir</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.$tanggal_lahir.'</small></div>
        </div>
        <div class="row mb-2 mt-3">
            <div class="col-12"><small><b>B. Informasi Kunjungan</b></small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small><i>ID Encounter</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.$id_encounter.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Tanggal Kunjungan</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.$tanggal_kunjungan.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Kategori</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.$jenis_kunjungan.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small><i>Priority</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.$priorityBadge.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Status</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.$statusBadge.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Poliklinik</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.$nama_poli.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Dokter Penerima</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.$nama_dokter.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Dokter DPJP</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.$nama_dpjp.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Keluhan</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.$keluhan.'</small></div>
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