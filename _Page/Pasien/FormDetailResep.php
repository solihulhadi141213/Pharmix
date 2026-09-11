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
        $id_encounter      = GetDetailData($Conn, 'kunjungan', 'id_kunjungan', $id_kunjungan, 'id_encounter');
        $tanggal_kunjungan = GetDetailData($Conn, 'kunjungan', 'id_kunjungan', $id_kunjungan, 'tanggal_kunjungan');
        $jenis_kunjungan   = GetDetailData($Conn, 'kunjungan', 'id_kunjungan', $id_kunjungan, 'jenis_kunjungan');
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
            <div class="col-4"><small>ID Resep</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$id_medication_request_group.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Nomor Resep Nasional (NRN)</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$no_resep_nasional.'</small></div>
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
            <div class="col-4"><small>Dokter Pembuat Resep</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$dokter_nama.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-4"><small>Sumber Resep</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$sumber_resep.'</small></div>
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
            <div class="col-4"><small>Nama Pasien</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$nama_pasien.'</small></div>
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
    ';

    // Ambil item hanya untuk resep yang sedang ditampilkan.
    try {
        $queryItem = '
            SELECT MedicationRequestId, name_medication, racikan_code, racikan_display,
                   dose_value, dose_unit, dosage_inst_frequency, dosage_inst_period,
                   dosage_inst_period_unit, dispense_value, dispense_unit,
                   supply_duration_value, supply_duration_unit, dosage_inst_text
            FROM medication_request
            WHERE id_medication_request_group = ?
            ORDER BY name_medication ASC, MedicationRequestId ASC
        ';
        $stmtItem = mysqli_prepare($Conn, $queryItem);
        if (!$stmtItem) {
            throw new RuntimeException('Gagal menyiapkan daftar item resep.');
        }

        try {
            mysqli_stmt_bind_param($stmtItem, 'i', $id_medication_request_group);
            if (!mysqli_stmt_execute($stmtItem)) {
                throw new RuntimeException('Gagal membaca item resep.');
            }
            $daftarItem = mysqli_fetch_all(mysqli_stmt_get_result($stmtItem), MYSQLI_ASSOC);
        } finally {
            mysqli_stmt_close($stmtItem);
        }
    } catch (Exception $e) {
        error_log('Detail item resep pasien: ' . $e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal memuat item resep. Silakan coba lagi.'
        ]);
        exit;
    }

    // Helper tampilan: pertahankan nilai nol dan escape seluruh teks item.
    $teksItem = static function ($value) {
        $value = trim((string) ($value ?? ''));
        return htmlspecialchars($value === '' ? '-' : $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    };
    $nilaiSatuan = static function ($nilai, $satuan) {
        if ($nilai === null || $nilai === '') {
            return '-';
        }
        return trim($nilai . ' ' . ($satuan ?? ''));
    };

    // Template list responsif tanpa tabel atau lebar kolom tetap.
    ob_start();
    ?>
    <section class="mt-4" aria-labelledby="judul-item-resep-pasien">
        <h6 id="judul-item-resep-pasien" class="fw-bold mb-3">
            D. Item Resep <span class="badge bg-secondary"><?= count($daftarItem) ?></span>
        </h6>

        <?php if (!$daftarItem): ?>
            <div class="alert alert-warning mb-0" role="status">
                <small>Belum ada item resep yang dibuat.</small>
            </div>
        <?php else: ?>
            <ul class="list-group">
                <?php foreach ($daftarItem as $index => $item): ?>
                    <?php
                    $frekuensi = '-';
                    if ($item['dosage_inst_frequency'] !== null && $item['dosage_inst_frequency'] !== '') {
                        $frekuensi = $item['dosage_inst_frequency'] . ' kali';
                        if ($item['dosage_inst_period'] !== null && $item['dosage_inst_period'] !== '') {
                            $frekuensi .= ' / ' . $nilaiSatuan($item['dosage_inst_period'], $item['dosage_inst_period_unit']);
                        }
                    }
                    $rincianItem = [
                        'Tipe Resep' => trim(($item['racikan_code'] ?? '') . ' ' . ($item['racikan_display'] ?? '')),
                        'Dosis per Pemberian' => $nilaiSatuan($item['dose_value'], $item['dose_unit']),
                        'Frekuensi' => $frekuensi,
                        'Jumlah Total' => $nilaiSatuan($item['dispense_value'], $item['dispense_unit']),
                        'Durasi Persediaan' => $nilaiSatuan($item['supply_duration_value'], $item['supply_duration_unit']),
                    ];
                    ?>
                    <li class="list-group-item p-3">
                        <div class="fw-bold text-break mb-3">
                            <?= $index + 1 ?>. <?= $teksItem($item['name_medication']) ?>
                        </div>
                        <dl class="row g-3 small mb-0">
                            <?php foreach ($rincianItem as $label => $nilai): ?>
                                <div class="col-12 col-md-6">
                                    <dt class="text-muted fw-normal"><?= $teksItem($label) ?></dt>
                                    <dd class="text-break mb-0"><?= $teksItem($nilai) ?></dd>
                                </div>
                            <?php endforeach; ?>
                            <div class="col-12">
                                <dt class="text-muted fw-normal">Instruksi Penggunaan</dt>
                                <dd class="text-break mb-0"><?= nl2br($teksItem($item['dosage_inst_text'])) ?></dd>
                            </div>
                        </dl>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
    <?php
    $html .= ob_get_clean();

    // Kirim detail beserta list item untuk ditampilkan di modal.
    echo json_encode([
        "status"       => "success",
        "html"         => $html,
        "id_kunjungan" => $id_kunjungan
    ]);
?>
