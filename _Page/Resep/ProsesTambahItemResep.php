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
    // Helper Ambil Referensi Satuan Dosis
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
    // Default Timezone
    date_default_timezone_set('Asia/Jakarta');

    //------------------------------------------
    // Default Response
    $response = [
        'status'  => 'error',
        'message' => 'Terjadi kesalahan.'
    ];

    //------------------------------------------
    // Helper Response
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
    $id_medication_request_group = (int) ($_POST['id_medication_request_group'] ?? 0);
    $intent                      = trim($_POST['intent'] ?? '');
    $id_index_medication         = (int) ($_POST['id_index_medication'] ?? 0);
    $name_medication             = trim($_POST['name_medication'] ?? '');
    $status                      = trim($_POST['status'] ?? '');
    $dosage_inst_text            = trim($_POST['dosage_inst_text'] ?? '');

    $dosage_inst_frequency       = (int) ($_POST['dosage_inst_frequency'] ?? 0);
    $dosage_inst_period          = (int) ($_POST['dosage_inst_period'] ?? 0);
    $dosage_inst_period_unit_raw = trim($_POST['dosage_inst_period_unit'] ?? '');

    $dose_value                  = (float) ($_POST['dose_value'] ?? 0);
    $dose_code_raw               = trim($_POST['dose_code'] ?? '');

    $route_code_raw              = trim($_POST['route_code'] ?? '');

    $dispense_value              = (float) ($_POST['dispense_value'] ?? 0);
    $dispense_code_raw           = trim($_POST['dispense_code'] ?? '');

    $supply_duration_value       = (int) ($_POST['supply_duration_value'] ?? 0);
    $supply_duration_code_raw    = trim($_POST['supply_duration_code'] ?? '');

    $racikan_code                = trim($_POST['racikan_code'] ?? '');

    //------------------------------------------
    // Validasi Resep Group
    if ($id_medication_request_group < 1) {
        responseError('ID resep tidak valid.');
    }

    //------------------------------------------
    // Validasi Intent
    if (!in_array($intent, ['order', 'plan', 'proposal'], true)) {
        responseError('Tujuan permintaan obat tidak valid.');
    }

    //------------------------------------------
    // Validasi Nama Obat
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
        responseError('Interval waktu minimal 1.');
    }

    //------------------------------------------
    // Validasi Dose
    if ($dose_value <= 0) {
        responseError('Dosis obat harus lebih dari 0.');
    }

    //------------------------------------------
    // Validasi Dispense
    if ($dispense_value <= 0) {
        responseError('Jumlah obat yang diserahkan harus lebih dari 0.');
    }

    //------------------------------------------
    // Validasi Supply Duration
    if ($supply_duration_value < 1) {
        responseError('Durasi penggunaan obat minimal 1.');
    }

    //------------------------------------------
    // Validasi Racikan
    if (!in_array($racikan_code, ['NC', 'SD', 'EP'], true)) {
        responseError('Kode racikan tidak valid.');
    }

    //------------------------------------------
    // Cek Medication Request Group
    $stmt = $Conn->prepare("
        SELECT id_medication_request_group
        FROM medication_request_group
        WHERE id_medication_request_group = ?
        LIMIT 1
    ");

    if (!$stmt) {
        responseError('Gagal mempersiapkan data resep.');
    }

    $stmt->bind_param("i", $id_medication_request_group);
    $stmt->execute();

    $result = $stmt->get_result();
    $dataGroup = $result->fetch_assoc();

    $stmt->close();

    if (!$dataGroup) {
        responseError('Data resep tidak ditemukan.');
    }

    //------------------------------------------
    // Validasi Index Medication
    if ($id_index_medication > 0) {

        $stmt = $Conn->prepare("
            SELECT id_index_medication
            FROM medication
            WHERE id_index_medication = ?
            LIMIT 1
        ");

        if (!$stmt) {
            responseError('Gagal mempersiapkan data medication.');
        }

        $stmt->bind_param("i", $id_index_medication);
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
    $periodUnit = parseSelectValue($dosage_inst_period_unit_raw);

    $dosage_inst_period_unit = $periodUnit['code'];

    if ($dosage_inst_period_unit === '') {
        responseError('Satuan interval tidak boleh kosong.');
    }

    //------------------------------------------
    // Dose Code
    $dose = parseSelectValue($dose_code_raw);
    $dose_code = $dose['code'];

    if ($dose_code === '') {
        responseError('Satuan dosis tidak boleh kosong.');
    }

    //------------------------------------------
    // Ambil Referensi Dose Unit
    $referensiDose = getReferensiSatuanDosis(
        $Conn,
        $dose_code
    );

    if (!$referensiDose) {
        responseError(
            'Kode satuan dosis "'.$dose_code.'" tidak ditemukan pada referensi satuan dosis.'
        );
    }

    //------------------------------------------
    // Dose Unit
    $dose_code = trim(
        $referensiDose['code_satuan_dosis'] ?? ''
    );

    $dose_unit = trim(
        $referensiDose['unit_satuan_dosis'] ?? ''
    );

    $dose_system = trim(
        $referensiDose['system_satuan_dosis'] ?? ''
    );

    if ($dose_unit === '') {
        responseError(
            'Unit satuan dosis untuk kode "'.$dose_code.'" belum dikonfigurasi.'
        );
    }

    //------------------------------------------
    // Route
    $route = parseSelectValue($route_code_raw);

    $route_code    = $route['code'];
    $route_display = $route['display'];
    $route_system  = $route['system'];

    if ($route_code === '') {
        responseError('Route pemberian obat tidak boleh kosong.');
    }

    if ($route_display === '') {
        $route_display = $route_code;
    }

    //------------------------------------------
    // Dispense Code
    $dispense = parseSelectValue($dispense_code_raw);
    $dispense_code = $dispense['code'];

    if ($dispense_code === '') {
        responseError('Satuan dispense tidak boleh kosong.');
    }

    //------------------------------------------
    // Ambil Referensi Dispense Unit
    $referensiDispense = getReferensiSatuanDosis(
        $Conn,
        $dispense_code
    );

    if (!$referensiDispense) {
        responseError(
            'Kode satuan dispense "'.$dispense_code.'" tidak ditemukan pada referensi satuan dosis.'
        );
    }

    //------------------------------------------
    // Dispense Unit
    $dispense_code = trim(
        $referensiDispense['code_satuan_dosis'] ?? ''
    );

    $dispense_unit = trim(
        $referensiDispense['unit_satuan_dosis'] ?? ''
    );

    $dispense_sys = trim(
        $referensiDispense['system_satuan_dosis'] ?? ''
    );

    if ($dispense_unit === '') {
        responseError(
            'Unit satuan dispense untuk kode "'.$dispense_code.'" belum dikonfigurasi.'
        );
    }

    //------------------------------------------
    // Supply Duration Unit
    $supplyDuration = parseSelectValue($supply_duration_code_raw);

    $supply_duration_code = $supplyDuration['code'];
    $supply_duration_unit = $supplyDuration['display'];
    $supply_duration_sys  = $supplyDuration['system'];

    if ($supply_duration_code === '') {
        responseError('Satuan durasi penggunaan obat tidak boleh kosong.');
    }

    if ($supply_duration_unit === '') {
        $supply_duration_unit = $supply_duration_code;
    }

    if ($supply_duration_sys === '') {
        $supply_duration_sys = 'http://unitsofmeasure.org';
    }

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
    $ingredientArray = [];
    $payloadIngredient = $_POST['payload_ingridient'] ?? [];

    if (!is_array($payloadIngredient)) {
        $payloadIngredient = [];
    }

    //------------------------------------------
    // Obat Racikan Wajib Memiliki Ingredient
    if (in_array($racikan_code, ['SD', 'EP'], true) && empty($payloadIngredient)) {
        responseError('Obat racikan wajib memiliki minimal satu ingredient.');
    }

    //------------------------------------------
    // Decode Ingredient
    if ($racikan_code !== 'NC') {

        foreach ($payloadIngredient as $item) {

            $item = json_decode($item, true);

            if (!is_array($item)) {
                responseError('Format data ingredient tidak valid.');
            }

            $kode_kfa           = trim($item['kode_kfa'] ?? '');
            $nama_kfa           = trim($item['nama_kfa'] ?? '');
            $jumlah_numerator   = trim((string) ($item['jumlah_numerator'] ?? ''));
            $kode_numerator     = trim($item['kode_numerator'] ?? '');
            $nama_numerator     = trim($item['nama_numerator'] ?? '');
            $jumlah_denominator = trim((string) ($item['jumlah_denominator'] ?? ''));
            $kode_denominator   = trim($item['kode_denominator'] ?? '');
            $nama_denominator   = trim($item['nama_denominator'] ?? '');

            //------------------------------------------
            // Validasi Ingredient
            if ($kode_kfa === '' || $nama_kfa === '') {
                responseError('Kode KFA dan nama ingredient tidak boleh kosong.');
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
    // Encode Ingredient JSON
    $ingredient = null;

    if (!empty($ingredientArray)) {

        $ingredient = json_encode(
            $ingredientArray,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($ingredient === false) {
            responseError('Gagal membentuk data ingredient.');
        }
    }

    //------------------------------------------
    // Normalisasi Instruksi
    if ($dosage_inst_text === '') {
        $dosage_inst_text = null;
    }

    //------------------------------------------
    // Generate MedicationRequestId
    try {
        $MedicationRequestId =
            'MR-' .
            date('YmdHis') .
            '-' .
            strtoupper(bin2hex(random_bytes(4)));
    } catch (Throwable $e) {
        responseError('Gagal membuat ID item resep.');
    }

    //------------------------------------------
    // Mulai Transaction
    $Conn->begin_transaction();

    try {

        //------------------------------------------
        // Insert Medication Request
        $sql = "
            INSERT INTO medication_request (
                MedicationRequestId,
                id_medication_request_group,
                intent,
                id_index_medication,
                name_medication,
                status,
                dosage_inst_text,
                dosage_inst_frequency,
                dosage_inst_period,
                dosage_inst_period_unit,
                dose_value,
                dose_unit,
                dose_code,
                dose_system,
                route_display,
                route_code,
                route_system,
                dispense_value,
                dispense_unit,
                dispense_code,
                dispense_sys,
                supply_duration_value,
                supply_duration_unit,
                supply_duration_code,
                supply_duration_sys,
                racikan_code,
                racikan_display,
                ingredient
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?
            )
        ";

        $stmt = $Conn->prepare($sql);

        if (!$stmt) {
            throw new Exception('Gagal mempersiapkan penyimpanan item resep.');
        }

        $stmt->bind_param(
            "sisisssiisdssssssdsssissssss",
            $MedicationRequestId,
            $id_medication_request_group,
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
            $ingredient
        );

        //------------------------------------------
        // Eksekusi
        if (!$stmt->execute()) {
            throw new Exception('Gagal menyimpan item resep.');
        }

        $stmt->close();

        //------------------------------------------
        // Commit
        $Conn->commit();

        //------------------------------------------
        // Response Success
        $response = [
            'status'  => 'success',
            'message' => 'Item resep berhasil ditambahkan.',
            'data'    => [
                'MedicationRequestId' => $MedicationRequestId
            ]
        ];

    } catch (Throwable $e) {

        //------------------------------------------
        // Rollback
        $Conn->rollback();

        if (isset($stmt) && $stmt instanceof mysqli_stmt) {
            $stmt->close();
        }

        $response['message'] = $e->getMessage();
    }

    //------------------------------------------
    // Response
    echo json_encode(
        $response,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
?>