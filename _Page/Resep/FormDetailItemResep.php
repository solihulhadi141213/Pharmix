<?php
    //------------------------------------------
    // Koneksi, Session dan Helper
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
    // Helper Response Error
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
    // Helper Escape HTML
    function e($value)
    {
        return htmlspecialchars(
            (string) ($value ?? ''),
            ENT_QUOTES,
            'UTF-8'
        );
    }

    //------------------------------------------
    // Helper Format Decimal
    function formatDecimal($value)
    {
        if ($value === null || $value === '') {
            return '0';
        }

        $value = number_format(
            (float) $value,
            2,
            '.',
            ''
        );

        $value = rtrim($value, '0');
        $value = rtrim($value, '.');

        return str_replace('.', ',', $value);
    }

    //------------------------------------------
    // Helper Value Kosong
    function showValue($value)
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? e($value) : '-';
    }

    //------------------------------------------
    // Validasi Session
    if (empty($SessionIdAkses)) {
        responseError('Sesi akses sudah berakhir.');
    }

    //------------------------------------------
    // Validasi Method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseError('Metode request tidak valid.');
    }

    //------------------------------------------
    // Tangkap Parameter
    $MedicationRequestId = trim(
        $_POST['MedicationRequestId'] ?? ''
    );

    if ($MedicationRequestId === '') {
        responseError('ID item resep tidak boleh kosong.');
    }

    //------------------------------------------
    // Ambil Data Medication Request
    $sql = "
        SELECT
            mr.*,
            m.medication_code,
            m.medication_name,
            m.kfa_code,
            m.kfa_display,
            m.sediaan_code,
            m.sediaan_display
        FROM medication_request AS mr
        LEFT JOIN medication AS m
            ON m.id_index_medication = mr.id_index_medication
        WHERE mr.MedicationRequestId = ?
        LIMIT 1
    ";

    $stmt = $Conn->prepare($sql);

    if (!$stmt) {
        responseError('Gagal mempersiapkan data item resep.');
    }

    $stmt->bind_param(
        "s",
        $MedicationRequestId
    );

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
    // Data Utama
    $id_medication_request_group = $data['id_medication_request_group'];
    $id_medication_request       = $data['id_medication_request'];
    $intent                      = $data['intent'];
    $id_index_medication         = $data['id_index_medication'];
    $name_medication             = $data['name_medication'];
    $status                      = $data['status'];

    //------------------------------------------
    // Data Medication
    $medication_code    = $data['medication_code'];
    $medication_name    = $data['medication_name'];
    $kfa_code           = $data['kfa_code'];
    $kfa_display        = $data['kfa_display'];
    $sediaan_code       = $data['sediaan_code'];
    $sediaan_display    = $data['sediaan_display'];

    //------------------------------------------
    // Dosage Instruction
    $dosage_inst_text        = $data['dosage_inst_text'];
    $dosage_inst_frequency   = $data['dosage_inst_frequency'];
    $dosage_inst_period      = $data['dosage_inst_period'];
    $dosage_inst_period_unit = $data['dosage_inst_period_unit'];

    //------------------------------------------
    // Dose
    $dose_value  = formatDecimal($data['dose_value']);
    $dose_unit   = $data['dose_unit'];
    $dose_code   = $data['dose_code'];
    $dose_system = $data['dose_system'];

    //------------------------------------------
    // Route
    $route_display = $data['route_display'];
    $route_code    = $data['route_code'];
    $route_system  = $data['route_system'];

    //------------------------------------------
    // Dispense
    $dispense_value = formatDecimal($data['dispense_value']);
    $dispense_unit  = $data['dispense_unit'];
    $dispense_code  = $data['dispense_code'];
    $dispense_sys   = $data['dispense_sys'];

    //------------------------------------------
    // Supply Duration
    $supply_duration_value = $data['supply_duration_value'];
    $supply_duration_unit  = $data['supply_duration_unit'];
    $supply_duration_code  = $data['supply_duration_code'];
    $supply_duration_sys   = $data['supply_duration_sys'];

    //------------------------------------------
    // Racikan
    $racikan_code    = $data['racikan_code'];
    $racikan_display = $data['racikan_display'];
    $ingredient      = $data['ingredient'];

    //------------------------------------------
    // Ingredient HTML
    $ingredientHtml = '
        <div class="alert alert-secondary text-center mb-0">
            <small>Tidak Ada Ingredient</small>
        </div>
    ';

    if (!empty($ingredient)) {

        $ingredientArray = json_decode(
            $ingredient,
            true
        );

        if (
            is_array($ingredientArray) &&
            count($ingredientArray) > 0
        ) {

            $ingredientHtml = '
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead>
                            <tr>
                                <th class="text-center">
                                    <small>No</small>
                                </th>
                                <th>
                                    <small>Kode KFA</small>
                                </th>
                                <th>
                                    <small>Nama KFA</small>
                                </th>
                                <th class="text-center">
                                    <small>Numerator</small>
                                </th>
                                <th class="text-center">
                                    <small>Denominator</small>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
            ';

            $no = 1;

            foreach ($ingredientArray as $item) {

                $kode_kfa = showValue(
                    $item['kode_kfa'] ?? ''
                );

                $nama_kfa = showValue(
                    $item['nama_kfa'] ?? ''
                );

                //------------------------------------------
                // Numerator
                $numerator = '-';

                if (
                    isset($item['jumlah_numerator']) &&
                    $item['jumlah_numerator'] !== ''
                ) {
                    $numerator =
                        e($item['jumlah_numerator']) .
                        ' ' .
                        e($item['nama_numerator'] ?? '');
                }

                //------------------------------------------
                // Denominator
                $denominator = '-';

                if (
                    isset($item['jumlah_denominator']) &&
                    $item['jumlah_denominator'] !== ''
                ) {
                    $denominator =
                        e($item['jumlah_denominator']) .
                        ' ' .
                        e($item['nama_denominator'] ?? '');
                }

                $ingredientHtml .= '
                    <tr>
                        <td class="text-center">
                            <small>'.$no.'</small>
                        </td>

                        <td>
                            <small>'.$kode_kfa.'</small>
                        </td>

                        <td>
                            <small>'.$nama_kfa.'</small>
                        </td>

                        <td class="text-center">
                            <small>'.$numerator.'</small>
                        </td>

                        <td class="text-center">
                            <small>'.$denominator.'</small>
                        </td>
                    </tr>
                ';

                $no++;
            }

            $ingredientHtml .= '
                        </tbody>
                    </table>
                </div>
            ';
        }
    }

    //------------------------------------------
    // Susun HTML
    $html = '

        <!-- A. INFORMASI ITEM -->
        <div class="row mb-3">
            <div class="col-md-12">
                <h6 class="text-primary">
                    A. Informasi Item Resep
                </h6>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <small>MedicationRequestId Lokal</small>
            </div>
            <div class="col-md-1">
                <small>:</small>
            </div>
            <div class="col-md-7">
                <small>'.showValue($MedicationRequestId).'</small>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <small>ID Group Resep</small>
            </div>
            <div class="col-md-1">
                <small>:</small>
            </div>
            <div class="col-md-7">
                <small>'.showValue($id_medication_request_group).'</small>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <small>ID MedicationRequest SATUSEHAT</small>
            </div>
            <div class="col-md-1">
                <small>:</small>
            </div>
            <div class="col-md-7 text-break">
                <small>'.showValue($id_medication_request).'</small>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <small>Intent</small>
            </div>
            <div class="col-md-1">
                <small>:</small>
            </div>
            <div class="col-md-7">
                <small>'.showValue($intent).'</small>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <small>Status</small>
            </div>
            <div class="col-md-1">
                <small>:</small>
            </div>
            <div class="col-md-7">
                <small>'.showValue($status).'</small>
            </div>
        </div>


        <!-- B. MEDICATION -->
        <div class="row mt-4 mb-3">
            <div class="col-md-12">
                <h6 class="text-primary">
                    B. Medication
                </h6>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <small>Index Medication</small>
            </div>
            <div class="col-md-1">
                <small>:</small>
            </div>
            <div class="col-md-7">
                <small>'.showValue($id_index_medication).'</small>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <small>Kode Lokal</small>
            </div>
            <div class="col-md-1">
                <small>:</small>
            </div>
            <div class="col-md-7">
                <small>'.showValue($medication_code).'</small>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <small>Nama Obat</small>
            </div>
            <div class="col-md-1">
                <small>:</small>
            </div>
            <div class="col-md-7">
                <small>
                    <b>'.showValue($name_medication).'</b>
                </small>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <small>KFA</small>
            </div>
            <div class="col-md-1">
                <small>:</small>
            </div>
            <div class="col-md-7">
                <small>
                    '.showValue($kfa_code).'
                    -
                    '.showValue($kfa_display).'
                </small>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <small>Sediaan</small>
            </div>
            <div class="col-md-1">
                <small>:</small>
            </div>
            <div class="col-md-7">
                <small>
                    '.showValue($sediaan_code).'
                    -
                    '.showValue($sediaan_display).'
                </small>
            </div>
        </div>


        <!-- C. DOSAGE -->
        <div class="row mt-4 mb-3">
            <div class="col-md-12">
                <h6 class="text-primary">
                    C. Dosis & Aturan Pakai
                </h6>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <small>Instruksi</small>
            </div>
            <div class="col-md-1">
                <small>:</small>
            </div>
            <div class="col-md-7">
                <small>'.showValue($dosage_inst_text).'</small>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <small>Frequency</small>
            </div>
            <div class="col-md-1">
                <small>:</small>
            </div>
            <div class="col-md-7">
                <small>
                    '.showValue($dosage_inst_frequency).' kali
                </small>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <small>Period</small>
            </div>
            <div class="col-md-1">
                <small>:</small>
            </div>
            <div class="col-md-7">
                <small>
                    '.showValue($dosage_inst_period).'
                    '.showValue($dosage_inst_period_unit).'
                </small>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <small>Dose</small>
            </div>
            <div class="col-md-1">
                <small>:</small>
            </div>
            <div class="col-md-7">
                <small>
                    '.$dose_value.'
                    '.showValue($dose_unit).'
                </small>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <small>Dose Code</small>
            </div>
            <div class="col-md-1">
                <small>:</small>
            </div>
            <div class="col-md-7">
                <small>'.showValue($dose_code).'</small>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <small>Dose System</small>
            </div>
            <div class="col-md-1">
                <small>:</small>
            </div>
            <div class="col-md-7 text-break">
                <small>'.showValue($dose_system).'</small>
            </div>
        </div>


        <!-- D. ROUTE -->
        <div class="row mt-4 mb-3">
            <div class="col-md-12">
                <h6 class="text-primary">
                    D. Route
                </h6>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <small>Route</small>
            </div>
            <div class="col-md-1">
                <small>:</small>
            </div>
            <div class="col-md-7">
                <small>
                    '.showValue($route_code).'
                    -
                    '.showValue($route_display).'
                </small>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <small>Route System</small>
            </div>
            <div class="col-md-1">
                <small>:</small>
            </div>
            <div class="col-md-7 text-break">
                <small>'.showValue($route_system).'</small>
            </div>
        </div>


        <!-- E. DISPENSE -->
        <div class="row mt-4 mb-3">
            <div class="col-md-12">
                <h6 class="text-primary">
                    E. Dispense Request
                </h6>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <small>Jumlah Dispense</small>
            </div>
            <div class="col-md-1">
                <small>:</small>
            </div>
            <div class="col-md-7">
                <small>
                    '.$dispense_value.'
                    '.showValue($dispense_unit).'
                </small>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <small>Dispense Code</small>
            </div>
            <div class="col-md-1">
                <small>:</small>
            </div>
            <div class="col-md-7">
                <small>'.showValue($dispense_code).'</small>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <small>Dispense System</small>
            </div>
            <div class="col-md-1">
                <small>:</small>
            </div>
            <div class="col-md-7 text-break">
                <small>'.showValue($dispense_sys).'</small>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <small>Supply Duration</small>
            </div>
            <div class="col-md-1">
                <small>:</small>
            </div>
            <div class="col-md-7">
                <small>
                    '.showValue($supply_duration_value).'
                    '.showValue($supply_duration_unit).'
                </small>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <small>Supply Duration Code</small>
            </div>
            <div class="col-md-1">
                <small>:</small>
            </div>
            <div class="col-md-7">
                <small>'.showValue($supply_duration_code).'</small>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <small>Supply Duration System</small>
            </div>
            <div class="col-md-1">
                <small>:</small>
            </div>
            <div class="col-md-7 text-break">
                <small>'.showValue($supply_duration_sys).'</small>
            </div>
        </div>


        <!-- F. RACIKAN -->
        <div class="row mt-4 mb-3">
            <div class="col-md-12">
                <h6 class="text-primary">
                    F. Racikan / Ingredient
                </h6>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <small>Tipe Racikan</small>
            </div>
            <div class="col-md-1">
                <small>:</small>
            </div>
            <div class="col-md-7">
                <small>
                    '.showValue($racikan_code).'
                    -
                    '.showValue($racikan_display).'
                </small>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12">
                '.$ingredientHtml.'
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