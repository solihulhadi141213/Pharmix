<?php
    header('Content-Type: application/json; charset=utf-8');

    // INCLUDE
    include __DIR__ . "/../../_Config/Connection.php";
    include __DIR__ . "/../../_Config/GlobalFunction.php";
    include __DIR__ . "/../../_Config/Session.php";

    date_default_timezone_set('Asia/Jakarta');

    // FUNCTION RESPONSE
    function responseNakesEdit(string $status, string $message, string $html = ''): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'html'    => $html
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // FUNCTION ESCAPE VALUE
    function editValue($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    // FUNCTION SELECTED
    function editSelected($value, $current): string {
        return ((string)$value === (string)$current) ? 'selected' : '';
    }

    // VALIDASI SESSION
    if (empty($SessionIdAkses)) {
        responseNakesEdit('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
    }

    // VALIDASI METHOD
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseNakesEdit('error', 'Metode request tidak valid.');
    }

    // VALIDASI ID NAKES
    $medicalPersonelId = filter_var(
        $_POST['medicalPersonelId'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($medicalPersonelId === false || $medicalPersonelId === null) {
        responseNakesEdit('error', 'ID tenaga kesehatan tidak valid.');
    }

    // BUKA DATA NAKES
    $stmt = $Conn->prepare("
        SELECT
            medicalPersonelId,
            medicalPersonelCode,
            id_practitioner,
            medicalPersonelCategory,
            medicalPersonelNik,
            medicalPersonelName,
            medicalPersonelGender,
            medicalPersonelEmail,
            medicalPersonelPhone,
            medicalPersonelAddress,
            medicalPersonelStatus
        FROM medical_personel
        WHERE medicalPersonelId = ?
        LIMIT 1
    ");

    if ($stmt === false) {
        responseNakesEdit('error', 'Gagal menyiapkan query data tenaga kesehatan.');
    }

    $stmt->bind_param('i', $medicalPersonelId);

    if (!$stmt->execute()) {
        $stmt->close();
        responseNakesEdit('error', 'Gagal membuka data tenaga kesehatan.');
    }

    $result = $stmt->get_result();
    $data   = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$data) {
        responseNakesEdit('error', 'Data tenaga kesehatan tidak ditemukan.');
    }

    // ESCAPE DATA
    $idPractitioner = editValue($data['id_practitioner']);
    $code            = editValue($data['medicalPersonelCode']);
    $name            = editValue($data['medicalPersonelName']);
    $nik             = editValue($data['medicalPersonelNik']);
    $phone           = editValue($data['medicalPersonelPhone']);
    $email           = editValue($data['medicalPersonelEmail']);
    $address         = editValue($data['medicalPersonelAddress']);

    // GENERATE HTML
    $html = '
        <input type="hidden" name="medicalPersonelId" id="editMedicalPersonelId" value="'.editValue($data['medicalPersonelId']).'">

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="editNakesCode">
                    <small>* Kode Nakes</small>
                </label>
            </div>
            <div class="col-md-8">
                <div class="input-group">
                    <button type="button" class="btn btn-md btn-secondary" id="GenerateKodeNakesEdit">
                        Generate
                    </button>
                    <input type="text"
                        class="form-control"
                        name="medicalPersonelCode"
                        id="editNakesCode"
                        value="'.$code.'"
                        required
                        placeholder="MP-000091">
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="editNakesName">
                    <small>* Nama Nakes</small>
                </label>
            </div>
            <div class="col-md-8">
                <input type="text"
                    class="form-control"
                    name="medicalPersonelName"
                    id="editNakesName"
                    value="'.$name.'"
                    required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="editNakesNik">
                    <small>* Nomor NIK/KTP</small>
                </label>
            </div>
            <div class="col-md-8">
                <input type="text"
                    class="form-control"
                    name="medicalPersonelNik"
                    id="editNakesNik"
                    value="'.$nik.'"
                    required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="edit_id_practitioner">
                    <small><i>ID Practitioner</i></small>
                </label>
            </div>
            <div class="col-md-8">
                <div class="input-group">
                    <input type="text"
                        class="form-control"
                        name="id_practitioner"
                        id="edit_id_practitioner"
                        value="'.$idPractitioner.'">

                    <button type="button"
                        class="btn btn-md btn-secondary"
                        id="TombolCariPractitionerEdit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="editNakesCategory">
                    <small>* Kategori</small>
                </label>
            </div>
            <div class="col-md-8">
                <select class="form-control"
                    name="medicalPersonelCategory"
                    id="editNakesCategory"
                    required>

                    <option value="">Pilih</option>
                    <option value="Dokter Umum" '.editSelected('Dokter Umum', $data['medicalPersonelCategory']).'>Dokter Umum</option>
                    <option value="Dokter Spesialis" '.editSelected('Dokter Spesialis', $data['medicalPersonelCategory']).'>Dokter Spesialis</option>
                    <option value="Perawat" '.editSelected('Perawat', $data['medicalPersonelCategory']).'>Perawat</option>
                    <option value="Bidan" '.editSelected('Bidan', $data['medicalPersonelCategory']).'>Bidan</option>
                    <option value="Rekam Medis" '.editSelected('Rekam Medis', $data['medicalPersonelCategory']).'>Rekam Medis</option>
                    <option value="Administrasi" '.editSelected('Administrasi', $data['medicalPersonelCategory']).'>Administrasi</option>
                    <option value="Apoteker" '.editSelected('Apoteker', $data['medicalPersonelCategory']).'>Apoteker</option>
                    <option value="Analis Laboratorium" '.editSelected('Analis Laboratorium', $data['medicalPersonelCategory']).'>Analis Laboratorium</option>
                    <option value="Radiografer" '.editSelected('Radiografer', $data['medicalPersonelCategory']).'>Radiografer</option>
                    <option value="Terapis" '.editSelected('Terapis', $data['medicalPersonelCategory']).'>Terapis</option>
                    <option value="Gizi" '.editSelected('Gizi', $data['medicalPersonelCategory']).'>Gizi</option>
                    <option value="Penata Anestesi" '.editSelected('Penata Anestesi', $data['medicalPersonelCategory']).'>Penata Anestesi</option>
                    <option value="Elektromedis" '.editSelected('Elektromedis', $data['medicalPersonelCategory']).'>Elektromedis</option>
                    <option value="Sanitarian" '.editSelected('Sanitarian', $data['medicalPersonelCategory']).'>Sanitarian</option>
                    <option value="Epidemiolog" '.editSelected('Epidemiolog', $data['medicalPersonelCategory']).'>Epidemiolog</option>
                    <option value="Kesehatan Lingkungan" '.editSelected('Kesehatan Lingkungan', $data['medicalPersonelCategory']).'>Kesehatan Lingkungan</option>
                    <option value="Kesehatan Masyarakat" '.editSelected('Kesehatan Masyarakat', $data['medicalPersonelCategory']).'>Kesehatan Masyarakat</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="editNakesGender">
                    <small>* Gender Nakes</small>
                </label>
            </div>
            <div class="col-md-8">
                <select class="form-control"
                    name="medicalPersonelGender"
                    id="editNakesGender"
                    required>

                    <option value="">Pilih</option>
                    <option value="Male" '.editSelected('Male', $data['medicalPersonelGender']).'>Male</option>
                    <option value="Female" '.editSelected('Female', $data['medicalPersonelGender']).'>Female</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="editNakesPhone">
                    <small>Nomor Kontak (HP)</small>
                </label>
            </div>
            <div class="col-md-8">
                <input type="text"
                    class="form-control"
                    name="medicalPersonelPhone"
                    id="editNakesPhone"
                    value="'.$phone.'"
                    placeholder="62">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="editNakesEmail">
                    <small>Alamat Email</small>
                </label>
            </div>
            <div class="col-md-8">
                <input type="email"
                    class="form-control"
                    name="medicalPersonelEmail"
                    id="editNakesEmail"
                    value="'.$email.'"
                    placeholder="@">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="editNakesAddress">
                    <small>Alamat/Domisili</small>
                </label>
            </div>
            <div class="col-md-8">
                <textarea
                    name="medicalPersonelAddress"
                    id="editNakesAddress"
                    class="form-control">'.$address.'</textarea>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="editNakesStatus">
                    <small>* Status Nakes</small>
                </label>
            </div>
            <div class="col-md-8">
                <select class="form-control"
                    name="medicalPersonelStatus"
                    id="editNakesStatus"
                    required>

                    <option value="">Pilih</option>
                    <option value="Active" '.editSelected('Active', $data['medicalPersonelStatus']).'>Active</option>
                    <option value="Inactive" '.editSelected('Inactive', $data['medicalPersonelStatus']).'>Inactive</option>
                </select>
            </div>
        </div>
    ';

    responseNakesEdit(
        'success',
        'Form edit tenaga kesehatan berhasil dimuat.',
        $html
    );
?>