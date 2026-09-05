<?php
    // Koneksi dan session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Header output JSON
    header('Content-Type: application/json; charset=utf-8');

    // Validasi Sesi Akses
    if (empty($SessionIdAkses)) {
        echo json_encode([
            "status" => "error",
            "message" => "Sesi akses sudah berakhir. Silakan login ulang."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Tangkap ID Resep
    $id_medication_request_group = (int) ($_POST['id_medication_request_group'] ?? 0);

    if ($id_medication_request_group < 1) {
        echo json_encode([
            "status" => "error",
            "message" => "ID Resep Tidak Boleh Kosong."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Query Data Resep
    $query = "
        SELECT *
        FROM medication_request_group
        WHERE id_medication_request_group = ?
        LIMIT 1
    ";

    $stmt = $Conn->prepare($query);
    $stmt->bind_param("i", $id_medication_request_group);
    $stmt->execute();

    $result = $stmt->get_result();
    $data   = $result->fetch_assoc();

    $stmt->close();

    if (!$data) {
        echo json_encode([
            "status" => "error",
            "message" => "ID Resep Tidak Valid."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    //---------------------------------------
    // HELPER SELECTED
    function selected($value, $current)
    {
        return $value === $current ? 'selected' : '';
    }

    //---------------------------------------
    // INFORMASI RESEP
    $datetime_creat    = $data['datetime_creat'];
    $priority          = $data['priority'];
    $reason_code       = $data['reason_code'];
    $reason_display    = $data['reason_display'];
    $sumber_resep      = $data['sumber_resep'];
    $status_resep      = $data['status_resep'];
    $no_resep_nasional = $data['no_resep_nasional'];

    //---------------------------------------
    // TANGGAL & JAM RESEP
    $tanggal_resep = date('Y-m-d', strtotime($datetime_creat));
    $jam_resep     = date('H:i', strtotime($datetime_creat));

    //---------------------------------------
    // ESCAPE OUTPUT
    $sumber_resep      = htmlspecialchars($sumber_resep ?? '', ENT_QUOTES, 'UTF-8');
    $no_resep_nasional = htmlspecialchars($no_resep_nasional ?? '', ENT_QUOTES, 'UTF-8');

    //---------------------------------------
    // SUSUN HTML
    $html = '
        <input type="hidden" name="id_medication_request_group" value="'.$id_medication_request_group.'">

        <div class="row mb-3">
            <div class="col-12">
                <label for="tanggal_resep_edit"><small>* Tanggal Resep</small></label>
                <input type="date" name="tanggal_resep" id="tanggal_resep_edit" class="form-control" value="'.$tanggal_resep.'" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12">
                <label for="jam_resep_edit"><small>* Jam Resep</small></label>
                <input type="time" name="jam_resep" id="jam_resep_edit" class="form-control" value="'.$jam_resep.'" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="priority_edit"><small><i>* Priority</i></small></label>
                <select name="priority" id="priority_edit" class="form-control" required>
                    <option value="">Pilih</option>
                    <option value="routine" '.selected('routine', $priority).'>Biasa</option>
                    <option value="urgent" '.selected('urgent', $priority).'>Segera</option>
                    <option value="asap" '.selected('asap', $priority).'>Darurat</option>
                    <option value="stat" '.selected('stat', $priority).'>Gawat</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="sumber_resep_edit"><small>* Sumber Resep</small></label>
                <input type="text" class="form-control" name="sumber_resep" id="sumber_resep_edit" value="'.$sumber_resep.'" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="no_resep_nasional_edit"><small>Nomor Resep Nasional (NRN)</small></label>
                <input type="text" class="form-control" name="no_resep_nasional" id="no_resep_nasional_edit" value="'.$no_resep_nasional.'">
                <small class="text-muted">Hanya jika sudah dibuatkan NRN sebelumnya</small>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="status_resep_edit"><small>* Status Resep</small></label>
                <select class="form-control" name="status_resep" id="status_resep_edit" required>
                    <option value="">Pilih</option>
                    <option value="Draft" '.selected('Draft', $status_resep).'>Draft</option>
                    <option value="Verified" '.selected('Verified', $status_resep).'>Verified</option>
                    <option value="Partially" '.selected('Partially', $status_resep).'>Partially</option>
                    <option value="Completed" '.selected('Completed', $status_resep).'>Completed</option>
                    <option value="Cancelled" '.selected('Cancelled', $status_resep).'>Cancelled</option>
                </select>
            </div>
        </div>
    ';

    //---------------------------------------
    // RESPONSE
    echo json_encode([
        "status" => "success",
        "html"   => $html
    ], JSON_UNESCAPED_UNICODE);
?>