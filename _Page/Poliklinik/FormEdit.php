<?php
    // HEADER JSON
    header('Content-Type: application/json; charset=utf-8');

    // INCLUDE
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // DEFAULT RESPONSE
    $response = [
        'status'  => 'error',
        'message' => 'Terjadi kesalahan.',
        'html'    => ''
    ];

    // VALIDASI SESSION
    if (empty($SessionIdAkses)) {
        $response['message'] = 'Sesi akses sudah berakhir. Silakan login ulang.';
        echo json_encode($response);
        exit;
    }

    // VALIDASI METHOD
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response['message'] = 'Metode request tidak valid.';
        echo json_encode($response);
        exit;
    }

    // VALIDASI POLYCLINIC ID
    $polyclinicId = trim($_POST['polyclinicId'] ?? '');
    if ($polyclinicId === '') {
        $response['message'] = 'ID poliklinik tidak boleh kosong.';
        echo json_encode($response);
        exit;
    }
    if (!ctype_digit($polyclinicId)) {
        $response['message'] = 'ID poliklinik tidak valid.';
        echo json_encode($response);
        exit;
    }
    $polyclinicId = (int) $polyclinicId;

    // BUKA DATA POLIKLINIK
    $stmt = $Conn->prepare("SELECT polyclinicId, satuSehatCode, polyclinicCode, polyclinicName, polyclinicStatus FROM polyclinic WHERE polyclinicId = ? LIMIT 1");
    if ($stmt === false) {
        $response['message'] = 'Gagal mempersiapkan data poliklinik.';
        echo json_encode($response);
        exit;
    }
    $stmt->bind_param('i', $polyclinicId);
    if (!$stmt->execute()) {
        $stmt->close();
        $response['message'] = 'Gagal membuka data poliklinik.';
        echo json_encode($response);
        exit;
    }
    $result = $stmt->get_result();
    $data = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    // VALIDASI DATA
    if (!$data) {
        $response['message'] = 'Data poliklinik tidak ditemukan.';
        echo json_encode($response);
        exit;
    }

    // VARIABLE
    $id       = (int) $data['polyclinicId'];
    $code     = trim((string) ($data['polyclinicCode'] ?? ''));
    $name     = trim((string) ($data['polyclinicName'] ?? ''));
    $status   = trim((string) ($data['polyclinicStatus'] ?? ''));
    $location = trim((string) ($data['satuSehatCode'] ?? ''));

    // ESCAPE
    $idHtml       = htmlspecialchars((string) $id, ENT_QUOTES, 'UTF-8');
    $codeHtml     = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
    $nameHtml     = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $locationHtml = htmlspecialchars($location, ENT_QUOTES, 'UTF-8');

    // STATUS OPTION
    $selectedActive   = ($status === 'Active') ? 'selected' : '';
    $selectedInactive = ($status === 'Inactive') ? 'selected' : '';

    // HTML FORM
    $html = '
        <input type="hidden" name="polyclinicId" id="editPolyclinicId" value="' . $idHtml . '">
        <!-- KODE POLIKLINIK -->
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="editPolyclinicCode"><small>* Kode Poliklinik</small></label>
                <div class="input-group">
                    <button type="button" class="btn btn-secondary" id="GenerateKodePoliEdit">Generate</button>
                    <input type="text" class="form-control" name="polyclinicCode" id="editPolyclinicCode" value="' . $codeHtml . '" required>
                </div>
            </div>
        </div>
        <!-- NAMA POLIKLINIK -->
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="editPolyclinicName"><small>* Nama Poliklinik</small></label>
                <input type="text" class="form-control" name="polyclinicName" id="editPolyclinicName" value="' . $nameHtml . '" required>
            </div>
        </div>
        <!-- LOCATION SATUSEHAT -->
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="editSatuSehatCode"><small><i>ID Location</i></small></label>
                <div class="input-group">
                    <input type="text" class="form-control" name="satuSehatCode" id="editSatuSehatCode" value="' . $locationHtml . '">
                    <button type="button" class="btn btn-secondary" id="TombolCariLocationEdit"><i class="bi bi-search"></i></button>
                </div>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="update_insert_location_satusehat" name="update_insert_location_satusehat" value="1">
                    <label class="form-check-label" for="update_insert_location_satusehat"><small class="text-muted">Update / Insert Location SATUSEHAT</small></label>
                </div>
                <div class="mt-1">
                    <small class="text-muted">Jika ID Location terisi, sistem akan melakukan <b>update Location</b>. Jika ID Location kosong, sistem akan melakukan <b>insert Location baru</b>.</small>
                </div>
            </div>
        </div>
        <!-- STATUS -->
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="editPolyclinicStatus"><small>* Status Poliklinik</small></label>
                <select class="form-control" name="polyclinicStatus" id="editPolyclinicStatus" required>
                    <option value="">Pilih</option>
                    <option value="Active" ' . $selectedActive . '>Active</option>
                    <option value="Inactive" ' . $selectedInactive . '>Inactive</option>
                </select>
            </div>
        </div>
    ';

    // RESPONSE
    echo json_encode([
        'status'  => 'success',
        'message' => 'Form berhasil ditampilkan.',
        'html'    => $html
    ], JSON_UNESCAPED_UNICODE);

    exit;
?>