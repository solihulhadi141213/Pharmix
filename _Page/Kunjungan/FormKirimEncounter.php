<?php
    // Koneksi dan session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Header output JSON
    header('Content-Type: application/json; charset=utf-8');

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

    if (empty($id_kunjungan) || !ctype_digit($id_kunjungan) || (int) $id_kunjungan <= 0) {
        echo json_encode([
            "status" => "error",
            "message" => "ID Kunjungan tidak valid."
        ]);
        exit;
    }

    $id_kunjungan = (int) $id_kunjungan;

    // Query ambil data kunjungan dengan LEFT JOIN ke tabel anggota, medical_personel, dan polyclinic
    $query = "SELECT 
                k.*, 
                a.id_pasien AS rm_pasien, 
                a.id_ihs AS id_ihs_pasien, 
                a.nama AS nama_pasien, 
                a.nik, 
                a.gender, 
                a.tanggal_lahir,
                dokter_penerima.id_practitioner AS id_practitioner_penerima,
                dokter_penerima.medicalPersonelName AS nama_practitioner_penerima,
                dokter_penerima.medicalPersonelCode AS kode_practitioner_penerima,
                dpjp.id_practitioner AS id_practitioner_dpjp,
                dpjp.medicalPersonelName AS nama_practitioner_dpjp,
                dpjp.medicalPersonelCode AS kode_practitioner_dpjp,
                poli.satuSehatCode AS id_location,
                poli.polyclinicCode AS kode_poliklinik,
                poli.polyclinicName AS nama_poliklinik
              FROM kunjungan AS k 
              LEFT JOIN anggota AS a ON k.id_anggota = a.id_anggota 
              LEFT JOIN medical_personel AS dokter_penerima ON k.id_dokter_penerima = dokter_penerima.medicalPersonelId
              LEFT JOIN medical_personel AS dpjp ON k.id_dpjp = dpjp.medicalPersonelId
              LEFT JOIN polyclinic AS poli ON k.id_poli = poli.polyclinicId
              WHERE k.id_kunjungan = ? 
              LIMIT 1";

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

    // Format data pendukung pasien & kunjungan
    $rm_pasien      = $data['rm_pasien'] ?: "-";
    $nama_pasien    = $data['nama_pasien'] ?: "-";
    $nik            = $data['nik'] ?: "-";
    $gender         = $data['gender'] ?: "-";
    $tanggal_lahir  = $data['tanggal_lahir'] ? date('d-m-Y', strtotime($data['tanggal_lahir'])) : "-";
    $id_ihs_pasien  = trim($data['id_ihs_pasien'] ?? '');

    $tanggal_kunjungan = $data['tanggal_kunjungan'] ? date('d-m-Y H:i', strtotime($data['tanggal_kunjungan'])) : "-";
    $jenis_kunjungan   = $data['jenis_kunjungan'] ?: "-";
    $priority          = $data['priority'] ?: "-";
    $keluhan           = $data['keluhan'] ?: "-";
    $status            = $data['status'] ?: "-";
    $id_encounter      = trim($data['id_encounter'] ?? '');

    // Dokter & Poliklinik
    $kode_dokter_penerima = $data['kode_practitioner_penerima'] ?: ($data['kode_dokter_penerima'] ?: '-');
    $nama_dokter_penerima = $data['nama_practitioner_penerima'] ?: ($data['nama_dokter_penerima'] ?: '-');
    $id_practitioner_penerima = trim($data['id_practitioner_penerima'] ?? '');

    $kode_dpjp = $data['kode_practitioner_dpjp'] ?: ($data['kode_dpjp'] ?: '-');
    $nama_dpjp = $data['nama_practitioner_dpjp'] ?: ($data['nama_dpjp'] ?: '-');
    $id_practitioner_dpjp = trim($data['id_practitioner_dpjp'] ?? '');

    $kode_poli = $data['kode_poliklinik'] ?: ($data['kode_poli'] ?: '-');
    $nama_poli = $data['nama_poliklinik'] ?: ($data['nama_poli'] ?: '-');
    $id_location = trim($data['id_location'] ?? '');

    // Display jenis kunjungan
    switch ($jenis_kunjungan) {
        case 'AMB': $jenisDisplay = 'Rawat Jalan (AMB)'; break;
        case 'IMP': $jenisDisplay = 'Rawat Inap (IMP)'; break;
        case 'EMER': $jenisDisplay = 'Gawat Darurat (EMER)'; break;
        default: $jenisDisplay = $jenis_kunjungan ?: '-'; break;
    }

    // Badge Priority
    switch ($priority) {
        case 'Emergency': $priorityBadge = '<span class="badge bg-danger">Emergency</span>'; break;
        case 'Urgent': $priorityBadge = '<span class="badge bg-warning text-dark">Urgent</span>'; break;
        default: $priorityBadge = '<span class="badge bg-secondary">Normal</span>'; break;
    }

    // Badge Status
    switch ($status) {
        case 'finished': $statusBadge = '<span class="badge bg-success">Finished</span>'; break;
        case 'in-progress': $statusBadge = '<span class="badge bg-warning text-dark">In-Progress</span>'; break;
        case 'cancelled': $statusBadge = '<span class="badge bg-danger">Cancelled</span>'; break;
        case 'arrived': $statusBadge = '<span class="badge bg-primary">Arrived</span>'; break;
        case 'planned': $statusBadge = '<span class="badge bg-secondary">Planned</span>'; break;
        case 'triaged': $statusBadge = '<span class="badge bg-info text-dark">Triaged</span>'; break;
        case 'onleave': $statusBadge = '<span class="badge bg-secondary">On Leave</span>'; break;
        case 'entered-in-error': $statusBadge = '<span class="badge bg-danger">Entered In Error</span>'; break;
        default: $statusBadge = '<span class="badge bg-secondary">'.ucfirst($status).'</span>'; break;
    }

    // Validasi Kelengkapan Encounter
    $errors = [];
    if ($id_ihs_pasien === '') {
        $errors[] = 'ID IHS pasien belum tersedia.';
    }
    if (empty($data['tanggal_kunjungan'])) {
        $errors[] = 'Tanggal kunjungan belum tersedia.';
    }
    if ($jenis_kunjungan === '') {
        $errors[] = 'Jenis kunjungan belum tersedia.';
    }
    if ($status === '') {
        $errors[] = 'Status kunjungan belum tersedia.';
    }
    if (empty($data['id_poli'])) {
        $errors[] = 'Poliklinik belum dipilih.';
    } elseif ($id_location === '') {
        $errors[] = 'Poliklinik belum memiliki ID Location SATUSEHAT.';
    }
    if (empty($data['id_dokter_penerima'])) {
        $errors[] = 'Dokter penerima belum dipilih.';
    } elseif ($id_practitioner_penerima === '') {
        $errors[] = 'Dokter penerima belum memiliki ID Practitioner SATUSEHAT.';
    }
    if (empty($data['id_dpjp'])) {
        $errors[] = 'Dokter DPJP belum dipilih.';
    } elseif ($id_practitioner_dpjp === '') {
        $errors[] = 'Dokter DPJP belum memiliki ID Practitioner SATUSEHAT.';
    }

    $alreadySent = ($id_encounter !== '' && $id_encounter !== '-');
    if ($alreadySent) {
        $errors[] = 'Encounter sudah pernah dikirim ke SATUSEHAT.';
    }

    $valid = empty($errors);

    // Display ID SATUSEHAT
    $displayIhsPasien = $id_ihs_pasien !== '' ? '<code>Patient/' . htmlspecialchars($id_ihs_pasien) . '</code>' : '<span class="text-danger">Belum tersedia</span>';
    $displayPractitionerPenerima = $id_practitioner_penerima !== '' ? '<code>Practitioner/' . htmlspecialchars($id_practitioner_penerima) . '</code>' : '<span class="text-danger">Belum tersedia</span>';
    $displayPractitionerDpjp = $id_practitioner_dpjp !== '' ? '<code>Practitioner/' . htmlspecialchars($id_practitioner_dpjp) . '</code>' : '<span class="text-danger">Belum tersedia</span>';
    $displayLocation = $id_location !== '' ? '<code>Location/' . htmlspecialchars($id_location) . '</code>' : '<span class="text-danger">Belum tersedia</span>';
    $displayEncounter = ($id_encounter !== '' && $id_encounter !== '-') ? '<code>Encounter/' . htmlspecialchars($id_encounter) . '</code>' : '<span class="text-muted">Belum dikirim</span>';

    // Susun HTML untuk ditampilkan di modal body (FormKirimEncounter)
    $html = '
        <input type="hidden" name="id_kunjungan" id="id_kunjungan_encounter" value="'.$id_kunjungan.'">

        <div class="row mb-2">
            <div class="col-12"><small><b>A. Informasi Pasien</b></small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>No. RM</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.htmlspecialchars($rm_pasien).'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Nama Pasien</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.htmlspecialchars($nama_pasien).'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>NIK</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.htmlspecialchars($nik).'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Gender</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.htmlspecialchars($gender).'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Tanggal Lahir</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.htmlspecialchars($tanggal_lahir).'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small><i>Patient ID SATUSEHAT</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.$displayIhsPasien.'</small></div>
        </div>

        <div class="row mb-2 mt-3">
            <div class="col-12"><small><b>B. Informasi Kunjungan</b></small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>ID Kunjungan</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.$id_kunjungan.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small><i>ID Encounter</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.$displayEncounter.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Tanggal Kunjungan</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.htmlspecialchars($tanggal_kunjungan).'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Jenis Kunjungan</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.htmlspecialchars($jenisDisplay).'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small><i>Priority</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6">'.$priorityBadge.'</div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Status</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6">'.$statusBadge.'</div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Keluhan</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.nl2br(htmlspecialchars($keluhan)).'</small></div>
        </div>

        <div class="row mb-2 mt-3">
            <div class="col-12"><small><b>C. Informasi Tenaga Medis</b></small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Dokter Penerima</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.htmlspecialchars($kode_dokter_penerima).' - '.htmlspecialchars($nama_dokter_penerima).'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small><i>Practitioner ID Penerima</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.$displayPractitionerPenerima.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Dokter DPJP</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.htmlspecialchars($kode_dpjp).' - '.htmlspecialchars($nama_dpjp).'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small><i>Practitioner ID DPJP</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.$displayPractitionerDpjp.'</small></div>
        </div>

        <div class="row mb-2 mt-3">
            <div class="col-12"><small><b>D. Informasi Lokasi Pelayanan</b></small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Poliklinik</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.htmlspecialchars($kode_poli).' - '.htmlspecialchars($nama_poli).'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small><i>Location ID SATUSEHAT</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small>'.$displayLocation.'</small></div>
        </div>
    ';

    // Informasi Rawat Inap jika IMP
    if ($jenis_kunjungan === 'IMP') {
        $kelas_inap = $data['kelas_inap'] ?: '-';
        $ruang_inap = $data['ruang_inap'] ?: '-';
        $html .= '
            <div class="row mb-2">
                <div class="col-5"><small>Kelas Inap</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6"><small>'.htmlspecialchars($kelas_inap).'</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><small>Ruang Inap</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6"><small>'.htmlspecialchars($ruang_inap).'</small></div>
            </div>
        ';
    }

    // Validasi Pengiriman Section
    $html .= '
        <div class="row mb-2 mt-3">
            <div class="col-12"><small><b>E. Validasi Pengiriman</b></small></div>
        </div>
    ';

    if ($valid) {
        $html .= '
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-success mb-0">
                        <small><i class="bi bi-check-circle"></i> <b>Data siap dikirim.</b> Data utama untuk resource Encounter sudah tersedia.</small>
                    </div>
                </div>
            </div>
        ';
    } else {
        $html .= '
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger mb-0">
                        <small>
                            <i class="bi bi-exclamation-triangle"></i> <b>Encounter belum dapat dikirim.</b><br>Periksa data berikut:
                            <ul class="mb-0 mt-2">
        ';
        foreach ($errors as $error) {
            $html .= '<li>' . htmlspecialchars($error) . '</li>';
        }
        $html .= '
                            </ul>
                        </small>
                    </div>
                </div>
            </div>
        ';
    }

    echo json_encode([
        "status"       => "success",
        "message"      => "Data Encounter berhasil dimuat.",
        "html"         => $html,
        "valid"        => $valid,
        "already_sent" => $alreadySent,
        "id_kunjungan" => $id_kunjungan
    ]);
?>