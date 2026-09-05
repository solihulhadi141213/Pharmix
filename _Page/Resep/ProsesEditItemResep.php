<?php
    //------------------------------------------
    // Koneksi, Session dan Helper
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    //------------------------------------------
    // Default JSON Response
    header('Content-Type: application/json; charset=utf-8');

    //------------------------------------------
    // Default Timezone
    date_default_timezone_set('Asia/Jakarta');

    //------------------------------------------
    // Default Response
    $response = [
        'status'  => 'error',
        'message' => 'Terjadi kesalahan.'
    ];

    //------------------------------------------
    // Helper Response Error
    function responseError($message)
    {
        echo json_encode([
            'status'  => 'error',
            'message' => $message
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    //------------------------------------------
    // Helper Pecah Value Select
    function parseSelectValue($value)
    {
        $value = trim((string) $value);

        if ($value === '') {
            return [
                'code'    => '',
                'display' => '',
                'system'  => ''
            ];
        }

        $parts = explode('|', $value);

        return [
            'code'    => trim($parts[0] ?? ''),
            'display' => trim($parts[1] ?? ''),
            'system'  => trim($parts[2] ?? '')
        ];
    }

    //------------------------------------------
    // Helper Referensi Satuan Dosis
    function getReferensiSatuanDosis($Conn, $code)
    {
        $code = trim((string) $code);

        if ($code === '') {
            return null;
        }

        $sql = "
            SELECT
                nama_satuan_dosis,
                unit_satuan_dosis,
                code_satuan_dosis,
                system_satuan_dosis
            FROM referensi_satuan_dosis
            WHERE code_satuan_dosis = ?
            LIMIT 1
        ";

        $stmt = $Conn->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("s", $code);

        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }

        $result = $stmt->get_result();
        $data   = $result->fetch_assoc();

        $stmt->close();

        return $data ?: null;
    }

    //------------------------------------------
    // Helper Referensi Route
    function getReferensiRoute($Conn, $code)
    {
        $code = trim((string) $code);

        if ($code === '') {
            return null;
        }

        $sql = "
            SELECT
                code_route,
                display_route,
                system_route
            FROM referensi_route
            WHERE code_route = ?
            LIMIT 1
        ";

        $stmt = $Conn->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("s", $code);

        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }

        $result = $stmt->get_result();
        $data   = $result->fetch_assoc();

        $stmt->close();

        return $data ?: null;
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
    $MedicationRequestId          = trim($_POST['MedicationRequestId'] ?? '');
    $intent                       = trim($_POST['intent'] ?? '');
    $id_index_medication          = (int) ($_POST['id_index_medication'] ?? 0);
    $name_medication              = trim($_POST['name_medication'] ?? '');
    $status                       = trim($_POST['status'] ?? '');
    $dosage_inst_text             = trim($_POST['dosage_inst_text'] ?? '');

    $dosage_inst_frequency        = (int) ($_POST['dosage_inst_frequency'] ?? 0);
    $dosage_inst_period           = (int) ($_POST['dosage_inst_period'] ?? 0);
    $dosage_inst_period_unit_raw  = trim($_POST['dosage_inst_period_unit'] ?? '');

    $dose_value                   = (float) ($_POST['dose_value'] ?? 0);
    $dose_code_raw                = trim($_POST['dose_code'] ?? '');

    $route_code_raw               = trim($_POST['route_code'] ?? '');

    $dispense_value               = (float) ($_POST['dispense_value'] ?? 0);
    $dispense_code_raw            = trim($_POST['dispense_code'] ?? '');

    $supply_duration_value        = (int) ($_POST['supply_duration_value'] ?? 0);
    $supply_duration_code_raw     = trim($_POST['supply_duration_code'] ?? '');

    $racikan_code                 = trim($_POST['racikan_code'] ?? '');

    //------------------------------------------
    // Validasi MedicationRequestId
    if ($MedicationRequestId === '') {
        responseError('ID item resep tidak boleh kosong.');
    }

    //------------------------------------------
    // Cek Data Item
    $stmt = $Conn->prepare("
        SELECT MedicationRequestId
        FROM medication_request
        WHERE MedicationRequestId = ?
        LIMIT 1
    ");

    if (!$stmt) {
        responseError('Gagal mempersiapkan data item resep.');
    }

    $stmt->bind_param("s", $MedicationRequestId);

    if (!$stmt->execute()) {
        $stmt->close();
        responseError('Gagal memeriksa data item resep.');
    }

    $result = $stmt->get_result();
    $dataLama = $result->fetch_assoc();

    $stmt->close();

    if (!$dataLama) {
        responseError('Data item resep tidak ditemukan.');
    }

    //------------------------------------------
    // Validasi Intent
    if (!in_array(
        $intent,
        ['order', 'plan', 'proposal'],
        true
    )) {
        responseError('Tujuan permintaan obat tidak valid.');
    }

    //------------------------------------------
    // Validasi Nama Medication
    if ($name_medication === '') {
        responseError('Nama obat tidak boleh kosong.');
    }

    //------------------------------------------
    // Validasi Status
    $statusValid = [
        'active',
        'on-hold',
        'completed',
        'stopped',
        'cancelled',
        'entered-in-error'
    ];

    if (!in_array($status, $statusValid, true)) {
        responseError('Status item resep tidak valid.');
    }

    //------------------------------------------
    // Validasi Frequency
    if ($dosage_inst_frequency < 1) {
        responseError('Frequency minimal 1.');
    }

    //------------------------------------------
    // Validasi Period
    if ($dosage_inst_period < 1) {
        responseError('Interval minimal 1.');
    }

    //------------------------------------------
    // Validasi Dose
    if ($dose_value <= 0) {
        responseError('Dosis obat harus lebih dari 0.');
    }

    //------------------------------------------
    // Validasi Dispense
    if ($dispense_value <= 0) {
        responseError('Jumlah dispense harus lebih dari 0.');
    }

    //------------------------------------------
    // Validasi Supply Duration
    if ($supply_duration_value < 1) {
        responseError('Durasi penggunaan obat minimal 1.');
    }

    //------------------------------------------
    // Validasi Racikan
    if (!in_array(
        $racikan_code,
        ['NC', 'SD', 'EP'],
        true
    )) {
        responseError('Kode racikan tidak valid.');
    }

    //------------------------------------------
    // Validasi Index Medication
    if ($id_index_medication > 0) {

        $stmt = $Conn->prepare("
            SELECT
                id_index_medication,
                medication_name
            FROM medication
            WHERE id_index_medication = ?
            LIMIT 1
        ");

        if (!$stmt) {
            responseError('Gagal mempersiapkan data medication.');
        }

        $stmt->bind_param(
            "i",
            $id_index_medication
        );

        $stmt->execute();

        $result = $stmt->get_result();
        $dataMedication = $result->fetch_assoc();

        $stmt->close();

        if (!$dataMedication) {
            responseError('Data medication tidak ditemukan.');
        }

    } else {
        $id_index_medication = null;
    }

    //------------------------------------------
    // Period Unit
    $periodUnit = parseSelectValue(
        $dosage_inst_period_unit_raw
    );

    $dosage_inst_period_unit = $periodUnit['code'];

    if ($dosage_inst_period_unit === '') {
        responseError('Satuan interval tidak boleh kosong.');
    }

    //------------------------------------------
    // Dose Code
    $dose = parseSelectValue(
        $dose_code_raw
    );

    $dose_code = $dose['code'];

    if ($dose_code === '') {
        responseError('Satuan dosis tidak boleh kosong.');
    }

    //------------------------------------------
    // Ambil Referensi Dose
    $referensiDose = getReferensiSatuanDosis(
        $Conn,
        $dose_code
    );

    if (!$referensiDose) {
        responseError(
            'Kode satuan dosis "'.$dose_code.'" tidak ditemukan.'
        );
    }

    $dose_code = trim(
        $referensiDose['code_satuan_dosis'] ?? ''
    );

    $dose_unit = trim(
        $referensiDose['unit_satuan_dosis'] ?? ''
    );

    $dose_system = trim(
        $referensiDose['system_satuan_dosis'] ?? ''
    );

    //------------------------------------------
    // Route Code
    $route = parseSelectValue(
        $route_code_raw
    );

    $route_code = $route['code'];

    if ($route_code === '') {
        responseError('Route pemberian obat tidak boleh kosong.');
    }

    //------------------------------------------
    // Ambil Referensi Route
    $referensiRoute = getReferensiRoute(
        $Conn,
        $route_code
    );

    if ($referensiRoute) {

        $route_code = trim(
            $referensiRoute['code_route'] ?? ''
        );

        $route_display = trim(
            $referensiRoute['display_route'] ?? ''
        );

        $route_system = trim(
            $referensiRoute['system_route'] ?? ''
        );

    } else {

        //------------------------------------------
        // Fallback Bila Select2 Mengirim Lengkap
        $route_display = $route['display'];
        $route_system  = $route['system'];
    }

    if ($route_display === '') {
        responseError(
            'Referensi route "'.$route_code.'" tidak ditemukan.'
        );
    }

    //------------------------------------------
    // Dispense Code
    $dispense = parseSelectValue(
        $dispense_code_raw
    );

    $dispense_code = $dispense['code'];

    if ($dispense_code === '') {
        responseError('Satuan dispense tidak boleh kosong.');
    }

    //------------------------------------------
    // Ambil Referensi Dispense
    $referensiDispense = getReferensiSatuanDosis(
        $Conn,
        $dispense_code
    );

    if (!$referensiDispense) {
        responseError(
            'Kode satuan dispense "'.$dispense_code.'" tidak ditemukan.'
        );
    }

    $dispense_code = trim(
        $referensiDispense['code_satuan_dosis'] ?? ''
    );

    $dispense_unit = trim(
        $referensiDispense['unit_satuan_dosis'] ?? ''
    );

    $dispense_sys = trim(
        $referensiDispense['system_satuan_dosis'] ?? ''
    );

    //------------------------------------------
    // Supply Duration
    $supplyDuration = parseSelectValue(
        $supply_duration_code_raw
    );

    $supply_duration_code = $supplyDuration['code'];
    $supply_duration_unit = $supplyDuration['display'];

    if ($supply_duration_code === '') {
        responseError(
            'Satuan durasi penggunaan obat tidak boleh kosong.'
        );
    }

    if ($supply_duration_unit === '') {

        switch ($supply_duration_code) {
            case 's':
                $supply_duration_unit = 'second';
                break;

            case 'm':
                $supply_duration_unit = 'minute';
                break;

            case 'h':
                $supply_duration_unit = 'hour';
                break;

            case 'd':
                $supply_duration_unit = 'day';
                break;

            case 'wk':
                $supply_duration_unit = 'week';
                break;

            case 'mo':
                $supply_duration_unit = 'month';
                break;

            default:
                responseError(
                    'Satuan durasi penggunaan obat tidak valid.'
                );
        }
    }

    $supply_duration_sys =
        'http://unitsofmeasure.org';

    //------------------------------------------
    // Racikan Display
    switch ($racikan_code) {

        case 'NC':
            $racikan_display = 'Non-compound';
            break;

        case 'SD':
            $racikan_display = 'Gives of such doses';
            break;

        case 'EP':
            $racikan_display = 'Divide into equal parts';
            break;

        default:
            $racikan_display = null;
            break;
    }

    //------------------------------------------
    // Proses Ingredient
    $ingredientArray   = [];
    $payloadIngredient = $_POST['payload_ingridient'] ?? [];

    if (!is_array($payloadIngredient)) {
        $payloadIngredient = [];
    }

    //------------------------------------------
    // Racikan Wajib Memiliki Ingredient
    if (
        in_array($racikan_code, ['SD', 'EP'], true) &&
        empty($payloadIngredient)
    ) {
        responseError(
            'Obat racikan wajib memiliki minimal satu ingredient.'
        );
    }

    //------------------------------------------
    // Decode Ingredient
    if ($racikan_code !== 'NC') {

        foreach ($payloadIngredient as $item) {

            $item = json_decode(
                $item,
                true
            );

            if (!is_array($item)) {
                responseError(
                    'Format data ingredient tidak valid.'
                );
            }

            $kode_kfa = trim(
                $item['kode_kfa'] ?? ''
            );

            $nama_kfa = trim(
                $item['nama_kfa'] ?? ''
            );

            $jumlah_numerator = trim(
                (string) (
                    $item['jumlah_numerator'] ?? ''
                )
            );

            $kode_numerator = trim(
                $item['kode_numerator'] ?? ''
            );

            $nama_numerator = trim(
                $item['nama_numerator'] ?? ''
            );

            $jumlah_denominator = trim(
                (string) (
                    $item['jumlah_denominator'] ?? ''
                )
            );

            $kode_denominator = trim(
                $item['kode_denominator'] ?? ''
            );

            $nama_denominator = trim(
                $item['nama_denominator'] ?? ''
            );

            //------------------------------------------
            // Validasi Ingredient
            if (
                $kode_kfa === '' ||
                $nama_kfa === ''
            ) {
                responseError(
                    'Kode KFA dan nama ingredient tidak boleh kosong.'
                );
            }

            //------------------------------------------
            // Simpan Ingredient
            $ingredientArray[] = [
                'kode_kfa'           => $kode_kfa,
                'nama_kfa'           => $nama_kfa,
                'kode_numerator'     => $kode_numerator,
                'nama_numerator'     => $nama_numerator,
                'jumlah_numerator'   => $jumlah_numerator,
                'kode_denominator'   => $kode_denominator,
                'nama_denominator'   => $nama_denominator,
                'jumlah_denominator' => $jumlah_denominator
            ];
        }
    }

    //------------------------------------------
    // Encode Ingredient
    $ingredient = null;

    if (!empty($ingredientArray)) {

        $ingredient = json_encode(
            $ingredientArray,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );

        if ($ingredient === false) {
            responseError(
                'Gagal membentuk data ingredient.'
            );
        }
    }

    //------------------------------------------
    // Non Compound Harus Tanpa Ingredient
    if ($racikan_code === 'NC') {
        $ingredient = null;
    }

    //------------------------------------------
    // Normalisasi Instruksi
    if ($dosage_inst_text === '') {
        $dosage_inst_text = null;
    }

    //------------------------------------------
    // Mulai Transaction
    $Conn->begin_transaction();

    try {

        //------------------------------------------
        // Update Medication Request
        $sql = "
            UPDATE medication_request SET
                intent                      = ?,
                id_index_medication         = ?,
                name_medication             = ?,
                status                      = ?,
                dosage_inst_text            = ?,
                dosage_inst_frequency       = ?,
                dosage_inst_period          = ?,
                dosage_inst_period_unit     = ?,
                dose_value                  = ?,
                dose_unit                   = ?,
                dose_code                   = ?,
                dose_system                 = ?,
                route_display               = ?,
                route_code                  = ?,
                route_system                = ?,
                dispense_value              = ?,
                dispense_unit               = ?,
                dispense_code               = ?,
                dispense_sys                = ?,
                supply_duration_value       = ?,
                supply_duration_unit        = ?,
                supply_duration_code        = ?,
                supply_duration_sys         = ?,
                racikan_code                = ?,
                racikan_display             = ?,
                ingredient                  = ?
            WHERE MedicationRequestId = ?
        ";

        $stmt = $Conn->prepare($sql);

        if (!$stmt) {
            throw new Exception(
                'Gagal mempersiapkan proses update item resep.'
            );
        }

        $stmt->bind_param(
            "sisssiisdssssssdsssisssssss",
            $intent,
            $id_index_medication,
            $name_medication,
            $status,
            $dosage_inst_text,
            $dosage_inst_frequency,
            $dosage_inst_period,
            $dosage_inst_period_unit,
            $dose_value,
            $dose_unit,
            $dose_code,
            $dose_system,
            $route_display,
            $route_code,
            $route_system,
            $dispense_value,
            $dispense_unit,
            $dispense_code,
            $dispense_sys,
            $supply_duration_value,
            $supply_duration_unit,
            $supply_duration_code,
            $supply_duration_sys,
            $racikan_code,
            $racikan_display,
            $ingredient,
            $MedicationRequestId
        );

        //------------------------------------------
        // Eksekusi
        if (!$stmt->execute()) {
            throw new Exception(
                'Gagal memperbarui item resep.'
            );
        }

        $stmt->close();

        //------------------------------------------
        // Commit
        $Conn->commit();

        //------------------------------------------
        // Response Success
        $response = [
            'status'  => 'success',
            'message' => 'Item resep berhasil diperbarui.',
            'data'    => [
                'MedicationRequestId' => $MedicationRequestId
            ]
        ];

    } catch (Throwable $e) {

        //------------------------------------------
        // Rollback
        $Conn->rollback();

        if (
            isset($stmt) &&
            $stmt instanceof mysqli_stmt
        ) {
            $stmt->close();
        }

        $response['message'] = $e->getMessage();
    }

    //------------------------------------------
    // Response
    echo json_encode(
        $response,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );
?>