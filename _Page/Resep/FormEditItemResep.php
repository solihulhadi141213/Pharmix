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
    // Helper Escape
    function e($value)
    {
        return htmlspecialchars(
            (string) ($value ?? ''),
            ENT_QUOTES,
            'UTF-8'
        );
    }

    //------------------------------------------
    // Helper Selected
    function selected($value, $current)
    {
        return (string) $value === (string) $current
            ? 'selected'
            : '';
    }

    //------------------------------------------
    // Helper Format Decimal
    function decimalValue($value)
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

        return $value;
    }

    //------------------------------------------
    // Helper Nama Unit Waktu
    function timeUnitDisplay($code)
    {
        $unit = [
            's'  => 'second',
            'm'  => 'minute',
            'h'  => 'hour',
            'd'  => 'day',
            'wk' => 'week',
            'mo' => 'month'
        ];

        return $unit[$code] ?? '';
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
    // Ambil Data Item Resep
    $sql = "
        SELECT
            mr.*,
            m.medication_name AS master_medication_name
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
    $intent                      = $data['intent'];
    $id_index_medication         = $data['id_index_medication'];
    $name_medication             = $data['name_medication'];
    $status                      = $data['status'];
    $dosage_inst_text            = $data['dosage_inst_text'];
    $dosage_inst_frequency       = $data['dosage_inst_frequency'];
    $dosage_inst_period          = $data['dosage_inst_period'];
    $dosage_inst_period_unit     = $data['dosage_inst_period_unit'];

    $dose_value                  = decimalValue($data['dose_value']);
    $dose_unit                   = $data['dose_unit'];
    $dose_code                   = $data['dose_code'];
    $dose_system                 = $data['dose_system'];

    $route_display               = $data['route_display'];
    $route_code                  = $data['route_code'];
    $route_system                = $data['route_system'];

    $dispense_value              = decimalValue($data['dispense_value']);
    $dispense_unit               = $data['dispense_unit'];
    $dispense_code               = $data['dispense_code'];
    $dispense_sys                = $data['dispense_sys'];

    $supply_duration_value       = $data['supply_duration_value'];
    $supply_duration_unit        = $data['supply_duration_unit'];
    $supply_duration_code        = $data['supply_duration_code'];

    $racikan_code                = $data['racikan_code'];
    $ingredient                  = $data['ingredient'];

    //------------------------------------------
    // Nama Medication Untuk Select2
    $master_medication_name = trim(
        $data['master_medication_name'] ?? ''
    );

    if ($master_medication_name === '') {
        $master_medication_name = $name_medication;
    }

    //------------------------------------------
    // Ingredient Table
    $ingredientHtml = '
        <tr>
            <td colspan="6" class="text-center">
                <small>No Data</small>
            </td>
        </tr>
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
            $ingredientHtml = '';
            $no = 1;

            foreach ($ingredientArray as $item) {

                $numerator = '-';
                $denominator = '-';

                if (
                    isset($item['jumlah_numerator']) &&
                    $item['jumlah_numerator'] !== ''
                ) {
                    $numerator =
                        e($item['jumlah_numerator']) .
                        ' ' .
                        e($item['nama_numerator'] ?? '');
                }

                if (
                    isset($item['jumlah_denominator']) &&
                    $item['jumlah_denominator'] !== ''
                ) {
                    $denominator =
                        e($item['jumlah_denominator']) .
                        ' ' .
                        e($item['nama_denominator'] ?? '');
                }

                //------------------------------------------
                // Encode Untuk Hidden Input
                $payload = json_encode(
                    $item,
                    JSON_UNESCAPED_UNICODE |
                    JSON_UNESCAPED_SLASHES
                );

                $ingredientHtml .= '
                    <tr>
                        <td class="text-center">
                            <small>'.$no.'</small>
                        </td>

                        <td>
                            <small>
                                '.e($item['kode_kfa'] ?? '-').'
                            </small>
                        </td>

                        <td>
                            <small>
                                '.e($item['nama_kfa'] ?? '-').'
                            </small>
                        </td>

                        <td class="text-center">
                            <small>'.$numerator.'</small>
                        </td>

                        <td class="text-center">
                            <small>'.$denominator.'</small>
                        </td>

                        <td class="text-center">
                            <button
                                type="button"
                                class="btn btn-danger btn-sm btn-hapus-ingridient-edit"
                            >
                                <i class="bi bi-trash"></i>
                            </button>

                            <input
                                type="hidden"
                                name="payload_ingridient[]"
                                value="'.e($payload).'"
                            >
                        </td>
                    </tr>
                ';

                $no++;
            }
        }
    }

    //------------------------------------------
    // Selected Period
    $periodValue =
        $dosage_inst_period_unit .
        '|' .
        timeUnitDisplay($dosage_inst_period_unit);

    $supplyValue =
        $supply_duration_code .
        '|' .
        timeUnitDisplay($supply_duration_code);

    //------------------------------------------
    // Susun HTML
    $html = '
        <input
            type="hidden"
            name="MedicationRequestId"
            value="'.e($MedicationRequestId).'"
        >

        <!-- A. INFORMASI UMUM -->
        <div class="row mb-3 mt-3">
            <div class="col-md-12">
                <small><b>A. Informasi Umum</b></small>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="intent_edit">
                    <small>* Tujuan Permintaan</small>
                </label>
            </div>

            <div class="col-md-8">
                <select
                    name="intent"
                    id="intent_edit"
                    class="form-control"
                    required
                >
                    <option value="">Pilih</option>

                    <option value="order"
                        '.selected('order', $intent).'>
                        Perintah/resep aktual
                    </option>

                    <option value="plan"
                        '.selected('plan', $intent).'>
                        Rencana pemberian obat
                    </option>

                    <option value="proposal"
                        '.selected('proposal', $intent).'>
                        Usulan pemberian obat
                    </option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="id_index_medication_edit">
                    <small>Index Data Obat</small>
                </label>
            </div>

            <div class="col-md-8">
                <select
                    name="id_index_medication"
                    id="id_index_medication_edit"
                    class="form-control"
                >';

                if (!empty($id_index_medication)) {
                    $html .= '
                        <option
                            value="'.e($id_index_medication).'"
                            selected
                        >
                            '.e($master_medication_name).'
                        </option>
                    ';
                }

    $html .= '
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="name_medication_edit">
                    <small>* Nama Obat</small>
                </label>
            </div>

            <div class="col-md-8">
                <input
                    type="text"
                    name="name_medication"
                    id="name_medication_edit"
                    class="form-control"
                    value="'.e($name_medication).'"
                    required
                >
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="status_item_resep_edit">
                    <small>* Status</small>
                </label>
            </div>

            <div class="col-md-8">
                <select
                    name="status"
                    id="status_item_resep_edit"
                    class="form-control"
                    required
                >
                    <option value="">Pilih</option>

                    <option value="active"
                        '.selected('active', $status).'>
                        Aktif
                    </option>

                    <option value="on-hold"
                        '.selected('on-hold', $status).'>
                        Ditunda sementara
                    </option>

                    <option value="completed"
                        '.selected('completed', $status).'>
                        Selesai
                    </option>

                    <option value="stopped"
                        '.selected('stopped', $status).'>
                        Dihentikan
                    </option>

                    <option value="cancelled"
                        '.selected('cancelled', $status).'>
                        Dibatalkan
                    </option>

                    <option value="entered-in-error"
                        '.selected('entered-in-error', $status).'>
                        Salah input
                    </option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="dosage_inst_text_edit">
                    <small>Instruksi</small>
                </label>
            </div>

            <div class="col-md-8">
                <input
                    type="text"
                    name="dosage_inst_text"
                    id="dosage_inst_text_edit"
                    class="form-control"
                    value="'.e($dosage_inst_text).'"
                >
            </div>
        </div>


        <!-- B. DOSIS -->
        <div class="row mb-3 mt-4">
            <div class="col-md-12">
                <small>
                    <b>B. Dosis, Frekuensi, Interval, Route</b>
                </small>
            </div>
        </div>

        <div class="row mb-3">

            <div class="col-md-4 mb-2">
                <label for="dose_value_edit">
                    <small><i>* Dosis Obat</i></small>
                </label>
            </div>

            <div class="col-md-3 mb-2">
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="dose_value"
                    id="dose_value_edit"
                    value="'.e($dose_value).'"
                    required
                    class="form-control"
                >
            </div>

            <div class="col-md-5 mb-2">
                <select
                    name="dose_code"
                    id="dose_code_edit"
                    class="form-control select_satuan_edit"
                    required
                >';

                if (!empty($dose_code)) {
                    $html .= '
                        <option
                            value="'.e($dose_code).'"
                            selected
                        >
                            '.e($dose_code).' - '.e($dose_unit).'
                        </option>
                    ';
                }

    $html .= '
                </select>
            </div>
        </div>

        <div class="row mb-3">

            <div class="col-md-4 mb-2">
                <label for="dosage_inst_frequency_edit">
                    <small><i>* Frequency</i></small>
                </label>
            </div>

            <div class="col-md-8 mb-2">
                <input
                    type="number"
                    step="1"
                    min="1"
                    name="dosage_inst_frequency"
                    id="dosage_inst_frequency_edit"
                    value="'.e($dosage_inst_frequency).'"
                    class="form-control"
                    required
                >
            </div>

        </div>

        <div class="row mb-3">

            <div class="col-md-4 mb-2">
                <label for="dosage_inst_period_edit">
                    <small><i>* Interval</i></small>
                </label>
            </div>

            <div class="col-md-3 mb-2">
                <input
                    type="number"
                    step="1"
                    min="1"
                    name="dosage_inst_period"
                    id="dosage_inst_period_edit"
                    value="'.e($dosage_inst_period).'"
                    class="form-control"
                    required
                >
            </div>

            <div class="col-md-5 mb-2">
                <select
                    name="dosage_inst_period_unit"
                    id="dosage_inst_period_unit_edit"
                    class="form-control"
                    required
                >
                    <option value="s|second"
                        '.selected('s', $dosage_inst_period_unit).'>
                        Detik (Second)
                    </option>

                    <option value="m|minute"
                        '.selected('m', $dosage_inst_period_unit).'>
                        Menit (Minute)
                    </option>

                    <option value="h|hour"
                        '.selected('h', $dosage_inst_period_unit).'>
                        Jam (Hour)
                    </option>

                    <option value="d|day"
                        '.selected('d', $dosage_inst_period_unit).'>
                        Hari (Day)
                    </option>

                    <option value="wk|week"
                        '.selected('wk', $dosage_inst_period_unit).'>
                        Minggu (Week)
                    </option>

                    <option value="mo|month"
                        '.selected('mo', $dosage_inst_period_unit).'>
                        Bulan (Month)
                    </option>
                </select>
            </div>
        </div>

        <div class="row mb-3">

            <div class="col-md-4 mb-2">
                <label for="route_code_edit">
                    <small><i>* Route</i></small>
                </label>
            </div>

            <div class="col-md-8 mb-2">
                <select
                    name="route_code"
                    id="route_code_edit"
                    class="form-control route_code_edit"
                    required
                >';

                if (!empty($route_code)) {
                    $html .= '
                        <option
                            value="'.e($route_code).'"
                            selected
                        >
                            '.e($route_code).' - '.e($route_display).'
                        </option>
                    ';
                }

    $html .= '
                </select>
            </div>
        </div>

        <div class="row mb-3">

            <div class="col-md-4 mb-2">
                <label for="dispense_value_edit">
                    <small><i>* Dispense Value</i></small>
                </label>
            </div>

            <div class="col-md-3 mb-2">
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="dispense_value"
                    id="dispense_value_edit"
                    value="'.e($dispense_value).'"
                    class="form-control"
                    required
                >
            </div>

            <div class="col-md-5 mb-2">
                <select
                    name="dispense_code"
                    id="dispense_code_edit"
                    class="form-control select_satuan_edit"
                    required
                >';

                if (!empty($dispense_code)) {
                    $html .= '
                        <option
                            value="'.e($dispense_code).'"
                            selected
                        >
                            '.e($dispense_code).' - '.e($dispense_unit).'
                        </option>
                    ';
                }

    $html .= '
                </select>
            </div>
        </div>

        <div class="row mb-3">

            <div class="col-md-4 mb-2">
                <label for="supply_duration_value_edit">
                    <small>
                        <i>* Supply Duration Value</i>
                    </small>
                </label>
            </div>

            <div class="col-md-3 mb-2">
                <input
                    type="number"
                    step="1"
                    min="1"
                    name="supply_duration_value"
                    id="supply_duration_value_edit"
                    value="'.e($supply_duration_value).'"
                    class="form-control"
                    required
                >
            </div>

            <div class="col-md-5 mb-2">
                <select
                    name="supply_duration_code"
                    id="supply_duration_code_edit"
                    class="form-control"
                    required
                >
                    <option value="s|second"
                        '.selected('s', $supply_duration_code).'>
                        Detik (Second)
                    </option>

                    <option value="m|minute"
                        '.selected('m', $supply_duration_code).'>
                        Menit (Minute)
                    </option>

                    <option value="h|hour"
                        '.selected('h', $supply_duration_code).'>
                        Jam (Hour)
                    </option>

                    <option value="d|day"
                        '.selected('d', $supply_duration_code).'>
                        Hari (Day)
                    </option>

                    <option value="wk|week"
                        '.selected('wk', $supply_duration_code).'>
                        Minggu (Week)
                    </option>

                    <option value="mo|month"
                        '.selected('mo', $supply_duration_code).'>
                        Bulan (Month)
                    </option>
                </select>
            </div>
        </div>


        <!-- C. INGREDIENT -->
        <div class="row mb-3 mt-4">
            <div class="col-md-12">
                <small>
                    <b>C. Ingredient (Untuk Obat Racikan)</b>
                </small>
            </div>
        </div>

        <div class="row mb-3">

            <div class="col-md-4">
                <label for="racikan_code_edit">
                    <small>* Kode Racikan</small>
                </label>
            </div>

            <div class="col-md-8">
                <select
                    name="racikan_code"
                    id="racikan_code_edit"
                    class="form-control"
                    required
                >
                    <option value="">Pilih</option>

                    <option value="NC"
                        '.selected('NC', $racikan_code).'>
                        Non-compound
                    </option>

                    <option value="SD"
                        '.selected('SD', $racikan_code).'>
                        Gives of such doses
                    </option>

                    <option value="EP"
                        '.selected('EP', $racikan_code).'>
                        Divide into equal parts
                    </option>
                </select>
            </div>
        </div>

        <div class="row mb-3">

            <div class="col-md-12">

                <button
                    type="button"
                    class="btn btn-md btn-block btn-secondary"
                    id="modal_tambah_ingridient_edit"
                >
                    <i class="bi bi-plus"></i>
                    Tambah Ingredient
                </button>

            </div>
        </div>

        <div class="row mb-2">

            <div class="col-md-12">

                <div class="table-responsive">

                    <table class="table table-bordered table-sm">

                        <thead>
                            <tr>
                                <td class="text-center">
                                    <small><b>No</b></small>
                                </td>
                                <td>
                                    <small><b>Kode</b></small>
                                </td>
                                <td>
                                    <small><b>Nama</b></small>
                                </td>
                                <td class="text-center">
                                    <small><b>Numerator</b></small>
                                </td>
                                <td class="text-center">
                                    <small><b>Denominator</b></small>
                                </td>
                                <td class="text-center">
                                    <small><b>Opsi</b></small>
                                </td>
                            </tr>
                        </thead>

                        <tbody id="table_list_ingridient_edit">
                            '.$ingredientHtml.'
                        </tbody>

                    </table>

                </div>

            </div>

        </div>
    ';

    //------------------------------------------
    // Response
    echo json_encode([
        'status'  => 'success',
        'message' => 'Data berhasil ditemukan.',
        'html'    => $html
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>