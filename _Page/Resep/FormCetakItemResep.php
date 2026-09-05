<?php
    //------------------------------------------
    // Koneksi, Session dan Setting
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    include "../../_Config/SettingGeneral.php";

    //------------------------------------------
    // Format Response
    header('Content-Type: application/json; charset=utf-8');

    //------------------------------------------
    // Default Response
    $response = [
        'status'  => 'error',
        'message' => 'Terjadi kesalahan.',
        'html'    => ''
    ];

    //------------------------------------------
    // Helper Error
    function responseError($message)
    {
        echo json_encode([
            'status'  => 'error',
            'message' => $message,
            'html'    => ''
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    //------------------------------------------
    // Escape HTML
    function e($value)
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    //------------------------------------------
    // Format Decimal
    function formatDecimal($value)
    {
        if ($value === null || $value === '') {
            return '0';
        }

        $value = number_format((float) $value, 2, '.', '');
        $value = rtrim($value, '0');
        $value = rtrim($value, '.');

        return str_replace('.', ',', $value);
    }

    //------------------------------------------
    // Validasi Session & Method
    if (empty($SessionIdAkses)) {
        responseError('Sesi akses sudah berakhir.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseError('Metode request tidak valid.');
    }

    //------------------------------------------
    // Tangkap MedicationRequestId
    $MedicationRequestId = trim($_POST['MedicationRequestId'] ?? '');

    if ($MedicationRequestId === '') {
        responseError('ID item resep tidak valid.');
    }

    //------------------------------------------
    // Ambil Item + Group + Pasien
    $sql = "
        SELECT
            mr.MedicationRequestId, mr.dosage_inst_text, mr.dosage_inst_frequency,
            mr.dosage_inst_period, mr.dosage_inst_period_unit, mr.dose_value,
            mr.dose_unit, mr.dispense_value, mr.dispense_unit, mr.racikan_code,
            mr.ingredient, mrg.nama_pasien, mrg.datetime_creat, a.id_pasien
        FROM medication_request AS mr
        INNER JOIN medication_request_group AS mrg
            ON mrg.id_medication_request_group = mr.id_medication_request_group
        LEFT JOIN anggota AS a
            ON a.id_anggota = mrg.id_anggota
        WHERE mr.MedicationRequestId = ?
        LIMIT 1
    ";

    $stmt = $Conn->prepare($sql);

    if (!$stmt) {
        responseError('Gagal mempersiapkan data etiket.');
    }

    $stmt->bind_param("s", $MedicationRequestId);

    if (!$stmt->execute()) {
        $stmt->close();
        responseError('Gagal mengambil data etiket.');
    }

    $result = $stmt->get_result();
    $data   = $result->fetch_assoc();
    $stmt->close();

    if (!$data) {
        responseError('Data item resep tidak ditemukan.');
    }

    //------------------------------------------
    // Data & Informasi
    $id_pasien         = trim($data['id_pasien'] ?? '') !== '' ? trim($data['id_pasien']) : '-';
    $nama_pasien       = trim($data['nama_pasien'] ?? '') !== '' ? trim($data['nama_pasien']) : '-';
    $frequency         = (int) ($data['dosage_inst_frequency'] ?? 0);
    $dose_value        = formatDecimal($data['dose_value'] ?? 0);
    $dose_unit         = trim($data['dose_unit'] ?? '');
    $dispense_value    = formatDecimal($data['dispense_value'] ?? 0);
    $dispense_unit     = trim($data['dispense_unit'] ?? '');
    $dosage_inst_text  = trim($data['dosage_inst_text'] ?? '') !== '' ? trim($data['dosage_inst_text']) : '-';

    $datetime_creat = !empty($data['datetime_creat'])
        ? date('d-m-Y H:i', strtotime($data['datetime_creat']))
        : '-';

    //------------------------------------------
    // Preview Etiket
    $html = '
        <input type="hidden" name="MedicationRequestId" value="'.e($MedicationRequestId).'">

        <div style="max-width:520px; margin:0 auto; border:1px solid #777; border-radius:6px; background:#ffffff; padding:12px 14px; color:#222222; font-family:Arial, Helvetica, sans-serif;">

            <!-- HEADER -->
            <div style="text-align:center; border-bottom:1px solid #999; padding-bottom:5px; margin-bottom:6px;">
                <div style="font-size:15px; font-weight:bold; line-height:1.2;">
                    '.e($title_page).'
                </div>
                <div style="margin-top:2px; font-size:9px; line-height:1.25; color:#444;">
                    '.e($alamat_bisnis).'
                </div>
                <div style="font-size:9px; line-height:1.25; color:#444;">
                    Telp. '.e($telepon_bisnis).' &nbsp; | &nbsp; '.e($email_bisnis).'
                </div>
            </div>

            <!-- ID ITEM -->
            <div style="font-size:8px; color:#666; text-align:right; margin-bottom:4px;">
                ID.Item : '.e($MedicationRequestId).'
            </div>

            <!-- PASIEN -->
            <table style="width:100%; border-collapse:collapse; font-size:11px; line-height:1.3;">
                <tr>
                    <td style="width:70px;">No. RM</td>
                    <td style="width:10px;">:</td>
                    <td>'.e($id_pasien).'</td>
                </tr>
                <tr>
                    <td>Nama</td>
                    <td>:</td>
                    <td>'.e($nama_pasien).'</td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>:</td>
                    <td>'.e($datetime_creat).'</td>
                </tr>
            </table>

            <div style="border-top:1px dashed #aaa; margin:6px 0;"></div>

            <!-- ATURAN PAKAI -->
            <table style="width:100%; border-collapse:collapse; font-size:11px; line-height:1.3;">
                <tr>
                    <td style="width:85px;">Dosis</td>
                    <td style="width:10px;">:</td>
                    <td>'.$frequency.' × '.$dose_value.' '.e($dose_unit).'</td>
                </tr>
                <tr>
                    <td>Jumlah</td>
                    <td>:</td>
                    <td>'.$dispense_value.' '.e($dispense_unit).'</td>
                </tr>
                <tr>
                    <td>Aturan Pakai</td>
                    <td>:</td>
                    <td>'.e($dosage_inst_text).'</td>
                </tr>
            </table>

            <!-- FOOTER -->
            <div style="margin-top:7px; padding-top:5px; border-top:1px solid #aaa; text-align:center; font-size:10px; font-style:italic;">
                "Semoga Lekas Sembuh"
            </div>

        </div>
    ';

    //------------------------------------------
    // Response
    echo json_encode([
        'status'  => 'success',
        'message' => 'Preview etiket berhasil dibuat.',
        'html'    => $html
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>