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
    function responseError($message){
        echo json_encode([
            'status'  => 'error',
            'message' => $message,
            'html'    => ''
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
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
    $MedicationRequestId = $_POST['MedicationRequestId'] ?? '';

    if ($MedicationRequestId === '') {
        responseError('ID item resep tidak boleh kosong.');
    }

    //------------------------------------------
    // Ambil Data Item Resep
    $sql = "SELECT*FROM medication_request WHERE MedicationRequestId = ? LIMIT 1";
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
    $id_medication_request = $data['id_medication_request'] ?? '';
    $name_medication       = $data['name_medication'] ?? '';
    $status                = $data['status'] ?? '';
    $intent                = $data['intent'] ?? '';
    $dosage_inst_text      = $data['indosage_inst_texttent'] ?? '';
    $frequency             = $data['dosage_inst_frequency'] ?? 0;
    $dose_value            = $data['dose_value'] ?? 0;
    $dose_unit             = $data['dose_unit'] ?? '';
    $dose_unit             = $data['dose_unit'] ?? '';
    $dispense_code         = $data['dispense_code'] ?? '';
    $dispense_unit         = $data['dispense_unit'] ?? '';
    $racikan_code          = $data['racikan_code'] ?? '';
    $racikan_display       = $data['racikan_display'] ?? '';
    $ingredient            = $data['ingredient'] ?? '';

    // Kode lokal untuk 'medication_code' 
    $randome_angka = GenerateKodeBarang(8);
    $medication_code  = "MED-$randome_angka";
    $medication_category = "Obat";
    //------------------------------------------
    // HTML Preview
    $html = '
        <input type="hidden" name="MedicationRequestId" value="'.$MedicationRequestId.'">
        <div class="row mb-3">
            <div class="col-4">
                <label for="medication_code"><i>Medication Code</i></label>
            </div>
            <div class="col-8">
                <input type="text" name="medication_code" id="medication_code" class="form-control" value="'.$medication_code.'">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-4">
                <label for="medication_name"><i>Medication Name</i></label>
            </div>
            <div class="col-8">
                <input type="text" name="medication_name" id="medication_name" class="form-control" value="'.$name_medication.'">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-4">
                <label for="medication_category"><i>Medication Category</i></label>
            </div>
            <div class="col-8">
                <select name="medication_category" id="medication_category" class="form-control">
                    <option value=""></option>
                    <option selected value="Obat">Obat</option>
                    <option value="Alkes">Alkes</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-4">
                <label for="kfa_code_to_medication"><i>KFA Code</i></label>
            </div>
            <div class="col-8">
                <select name="kfa_code" id="kfa_code_to_medication" class="form-control">
                    <option value=""></option>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-4">
                <label for="kfa_display"><i>KFA Display</i></label>
            </div>
            <div class="col-8">
                <input type="text" name="kfa_display" id="kfa_display" class="form-control">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-4">
                <label for="sediaan_code">Kode Sediaan</label>
            </div>
            <div class="col-8">
                <select name="sediaan_code" id="sediaan_code" class="form-control">
                    <option value=""></option>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-4">
                <label for="sediaan_display">Nama Sediaan</label>
            </div>
            <div class="col-8">
                <input type="text" name="sediaan_display" id="sediaan_display" class="form-control">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-4">
                <label for="racikan_code">Kode Racikan</label>
            </div>
            <div class="col-8">
                <input type="text" name="racikan_code" id="racikan_code" class="form-control" value="'.$racikan_code.'">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-4">
                <label for="racikan_display">Nama Racikan</label>
            </div>
            <div class="col-8">
                <input type="text" name="racikan_display" id="racikan_display" class="form-control" value="'.$racikan_display.'">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-4">
                <label for="manufacturer_id">ID Manufaktur</label>
            </div>
            <div class="col-8">
                <select name="manufacturer_id" id="manufacturer_id" class="form-control">
                    <option value=""></option>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-4">
                <label for="manufacturer_name">Nama Manufaktur</label>
            </div>
            <div class="col-8">
                <input type="text" name="manufacturer_name" id="manufacturer_name" class="form-control">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-4">
                <label for="ingredient">Ingredient</label>
            </div>
            <div class="col-8">
                <textarea name="ingredient" id="ingredient" class="form-control">'.$ingredient.'</textarea>
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

