<?php
    //------------------------------------------
    // Koneksi, Session dan Helper
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    include "../../_Config/FungsiAkses.php";

    //------------------------------------------
    // Default JSON Response
    header('Content-Type: application/json; charset=utf-8');

    //------------------------------------------
    // Default Datetime Zone
    date_default_timezone_set('Asia/Jakarta');
    $now = date('Y-m-d H:i:s');

    //------------------------------------------
    // Default Response
    $response = [
        'status'  => 'error',
        'message' => 'Terjadi kesalahan.'
    ];

    //------------------------------------------
    // Validasi Session
    if (empty($SessionIdAkses)) {
        $response['message'] = 'Sesi akses sudah berakhir.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //------------------------------------------
    // Validasi Method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response['message'] = 'Metode request tidak valid.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //------------------------------------------
    // Tangkap Parameter
    $id_medication_request_group = (int) ($_POST['id_medication_request_group'] ?? 0);
    $tanggal_resep               = trim($_POST['tanggal_resep'] ?? '');
    $jam_resep                   = trim($_POST['jam_resep'] ?? '');
    $priority                    = trim($_POST['priority'] ?? '');
    $sumber_resep                = trim($_POST['sumber_resep'] ?? '');
    $no_resep_nasional           = trim($_POST['no_resep_nasional'] ?? '');
    $status_resep                = trim($_POST['status_resep'] ?? '');

    //------------------------------------------
    // Validasi ID Resep
    if ($id_medication_request_group < 1) {
        $response['message'] = 'ID resep tidak valid.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //------------------------------------------
    // Validasi Tanggal Resep
    if ($tanggal_resep === '') {
        $response['message'] = 'Tanggal resep tidak boleh kosong.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //------------------------------------------
    // Validasi Jam Resep
    if ($jam_resep === '') {
        $response['message'] = 'Jam resep tidak boleh kosong.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //------------------------------------------
    // Validasi Priority
    $priorityValid = [
        'routine',
        'urgent',
        'asap',
        'stat'
    ];

    if (!in_array($priority, $priorityValid, true)) {
        $response['message'] = 'Priority resep tidak valid.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //------------------------------------------
    // Validasi Sumber Resep
    if ($sumber_resep === '') {
        $response['message'] = 'Sumber resep tidak boleh kosong.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //------------------------------------------
    // Validasi Status Resep
    $statusValid = [
        'Draft',
        'Verified',
        'Partially',
        'Completed',
        'Cancelled'
    ];

    if (!in_array($status_resep, $statusValid, true)) {
        $response['message'] = 'Status resep tidak valid.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //------------------------------------------
    // Validasi Format Datetime
    $datetimeObject = DateTime::createFromFormat(
        'Y-m-d H:i',
        $tanggal_resep . ' ' . $jam_resep
    );

    if (
        !$datetimeObject ||
        $datetimeObject->format('Y-m-d H:i') !== $tanggal_resep . ' ' . $jam_resep
    ) {
        $response['message'] = 'Format tanggal atau jam resep tidak valid.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $datetime_creat = $datetimeObject->format('Y-m-d H:i:s');

    //------------------------------------------
    // Normalisasi NRN
    if ($no_resep_nasional === '') {
        $no_resep_nasional = null;
    }

    //------------------------------------------
    // Ambil Data Resep Sebelumnya
    $sql = "
        SELECT
            status_resep,
            datetime_verified,
            datetime_completed
        FROM medication_request_group
        WHERE id_medication_request_group = ?
        LIMIT 1
    ";

    $stmt = $Conn->prepare($sql);

    if (!$stmt) {
        $response['message'] = 'Gagal mempersiapkan data resep.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt->bind_param("i", $id_medication_request_group);

    //------------------------------------------
    // Eksekusi
    if (!$stmt->execute()) {
        $response['message'] = 'Gagal mengambil data resep.';
        $stmt->close();

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //------------------------------------------
    // Ambil Data
    $result   = $stmt->get_result();
    $dataLama = $result->fetch_assoc();
    $stmt->close();

    if (!$dataLama) {
        $response['message'] = 'Data resep tidak ditemukan.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //------------------------------------------
    // Datetime Status Sebelumnya
    $datetime_verified  = $dataLama['datetime_verified'] ?? null;
    $datetime_completed = $dataLama['datetime_completed'] ?? null;

    //------------------------------------------
    // Atur Datetime Verified
    if (
        in_array($status_resep, ['Verified', 'Partially', 'Completed'], true) &&
        empty($datetime_verified)
    ) {
        $datetime_verified = $now;
    }

    //------------------------------------------
    // Atur Datetime Completed
    if ($status_resep === 'Completed') {
        if (empty($datetime_completed)) {
            $datetime_completed = $now;
        }
    } else {
        $datetime_completed = null;
    }

    //------------------------------------------
    // Update Data Resep
    $sql = "
        UPDATE medication_request_group SET
            datetime_creat      = ?,
            priority            = ?,
            sumber_resep        = ?,
            no_resep_nasional   = ?,
            status_resep        = ?,
            datetime_verified   = ?,
            datetime_completed  = ?,
            update_at           = ?,
            update_by_id        = ?,
            update_by_name      = ?
        WHERE id_medication_request_group = ?
    ";

    $stmt = $Conn->prepare($sql);

    if (!$stmt) {
        $response['message'] = 'Gagal mempersiapkan proses update resep.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt->bind_param(
        "ssssssssisi",
        $datetime_creat,
        $priority,
        $sumber_resep,
        $no_resep_nasional,
        $status_resep,
        $datetime_verified,
        $datetime_completed,
        $now,
        $SessionIdAkses,
        $SessionNama,
        $id_medication_request_group
    );

    //------------------------------------------
    // Eksekusi Update
    if (!$stmt->execute()) {
        $response['message'] = 'Gagal memperbarui data resep.';
        $stmt->close();

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt->close();

    //------------------------------------------
    // Response Success
    $response = [
        'status'  => 'success',
        'message' => 'Data resep berhasil diperbarui.'
    ];

    echo json_encode(
        $response,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
?>