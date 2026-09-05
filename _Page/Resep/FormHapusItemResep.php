<?php
    //------------------------------------------
    // Koneksi, Function Dan Session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

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
        responseError('ID item resep tidak boleh kosong.');
    }

    //------------------------------------------
    // Ambil Data Item Resep
    $sql = "
        SELECT
            MedicationRequestId, id_medication_request, name_medication,
            status, intent, dosage_inst_text, dosage_inst_frequency,
            dose_value, dose_unit, dispense_value, dispense_unit,
            racikan_code, racikan_display
        FROM medication_request
        WHERE MedicationRequestId = ?
        LIMIT 1
    ";

    $stmt = $Conn->prepare($sql);

    if (!$stmt) {
        responseError('Gagal mempersiapkan data item resep.');
    }

    $stmt->bind_param("s", $MedicationRequestId);

    if (!$stmt->execute()) {
        $stmt->close();
        responseError('Gagal mengambil data item resep.');
    }

    $result = $stmt->get_result();
    $data   = $result->fetch_assoc();
    $stmt->close();

    //------------------------------------------
    // Validasi Data
    if (!$data) {
        responseError('Data item resep tidak ditemukan.');
    }

    //------------------------------------------
    // Data Assignment
    $id_medication_request = trim($data['id_medication_request'] ?? '');
    $name_medication       = trim($data['name_medication'] ?? '');
    $status                = trim($data['status'] ?? '');
    $intent                = trim($data['intent'] ?? '');
    $dosage_inst_text      = trim($data['dosage_inst_text'] ?? '') !== '' ? trim($data['dosage_inst_text']) : '-';
    $frequency             = (int) ($data['dosage_inst_frequency'] ?? 0);
    $dose_value            = formatDecimal($data['dose_value'] ?? 0);
    $dose_unit             = trim($data['dose_unit'] ?? '');
    $dispense_value        = formatDecimal($data['dispense_value'] ?? 0);
    $dispense_unit         = trim($data['dispense_unit'] ?? '');
    $racikan_code          = trim($data['racikan_code'] ?? '');
    $racikan_display       = trim($data['racikan_display'] ?? '');

    //------------------------------------------
    // Status SATUSEHAT
    $statusSatusehat = $id_medication_request === '' 
        ? '<span class="text-muted">Belum dikirim ke SATUSEHAT</span>' 
        : '<span class="text-danger">Sudah memiliki ID MedicationRequest SATUSEHAT</span>';

    //------------------------------------------
    // HTML Preview
    $html = '
        <input type="hidden" name="MedicationRequestId" value="'.e($MedicationRequestId).'">

        <div class="row mb-2">
            <div class="col-4"><small>ID Item</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7 text-break"><small>'.e($MedicationRequestId).'</small></div>
        </div>

        <div class="row mb-2">
            <div class="col-4"><small>Nama Obat</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.e($name_medication).'</small></div>
        </div>

        <div class="row mb-2">
            <div class="col-4"><small>Intent</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.e($intent).'</small></div>
        </div>

        <div class="row mb-2">
            <div class="col-4"><small>Status</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.e($status).'</small></div>
        </div>

        <div class="row mb-2">
            <div class="col-4"><small>Dosis</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$frequency.' × '.$dose_value.' '.e($dose_unit).'</small></div>
        </div>

        <div class="row mb-2">
            <div class="col-4"><small>Jumlah</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$dispense_value.' '.e($dispense_unit).'</small></div>
        </div>

        <div class="row mb-2">
            <div class="col-4"><small>Instruksi</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.e($dosage_inst_text).'</small></div>
        </div>

        <div class="row mb-2">
            <div class="col-4"><small>Racikan</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.e($racikan_code).' - '.e($racikan_display).'</small></div>
        </div>

        <div class="row mb-2">
            <div class="col-4"><small>SATUSEHAT</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small>'.$statusSatusehat.'</small></div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="alert alert-danger text-center mb-0">
                    <small>
                        <b>PENTING!</b><br>
                        Data item resep yang telah dihapus tidak dapat dikembalikan lagi.
                        <br><br>
                        <i>Apakah Anda yakin akan menghapus item resep ini?</i>
                    </small>
                </div>
            </div>
        </div>
    ';

    //------------------------------------------
    // Response Success
    echo json_encode([
        'status'  => 'success',
        'message' => 'Data item resep berhasil ditemukan.',
        'html'    => $html
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>