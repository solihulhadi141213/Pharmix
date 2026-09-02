<?php
    header('Content-Type: application/json; charset=utf-8');

    include __DIR__ . "/../../_Config/Connection.php";
    include __DIR__ . "/../../_Config/GlobalFunction.php";
    include __DIR__ . "/../../_Config/Session.php";

    date_default_timezone_set('Asia/Jakarta');

    function responsePoliklinikDetail(string $status, string $message, string $html = ''): void
    {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'html'    => $html
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    function detailValue($value): string
    {
        if ($value === null || trim((string) $value) === '') {
            return '-';
        }

        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    function detailDate($value): string
    {
        if ($value === null || trim((string) $value) === '') {
            return '-';
        }

        $timestamp = strtotime((string) $value);
        return $timestamp === false
            ? detailValue($value)
            : date('d/m/Y H:i', $timestamp);
    }

    if (empty($SessionIdAkses)) {
        responsePoliklinikDetail(
            'error',
            'Sesi akses sudah berakhir. Silakan login ulang.'
        );
    }

    $polyclinicId = filter_var(
        $_POST['polyclinicId'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($polyclinicId === false || $polyclinicId === null) {
        responsePoliklinikDetail('error', 'ID poliklinik tidak valid.');
    }

    $stmt = $Conn->prepare(
        'SELECT
            polyclinicId,
            satuSehatCode,
            polyclinicCode,
            polyclinicName,
            polyclinicStatus,
            creat_at,
            creat_by_id,
            creat_by_name,
            update_at,
            update_by_id,
            update_by_name
        FROM polyclinic
        WHERE polyclinicId = ?
        LIMIT 1'
    );

    if ($stmt === false) {
        responsePoliklinikDetail('error', 'Gagal menyiapkan query detail poliklinik.');
    }

    $stmt->bind_param('i', $polyclinicId);

    if (!$stmt->execute()) {
        $stmt->close();
        responsePoliklinikDetail('error', 'Gagal mengambil detail poliklinik.');
    }

    $result = $stmt->get_result();
    $data = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$data) {
        responsePoliklinikDetail('error', 'Data poliklinik tidak ditemukan.');
    }

    $status = $data['polyclinicStatus'] ?? '';
    $statusBadge = $status === 'Active'
        ? '<span class="badge bg-success">Active</span>'
        : '<span class="badge bg-danger">Inactive</span>';

    // Cari Creator dan Updater
    if(!empty($data['creat_by_id'])){
        $creator = GetDetailData($Conn, 'akses', 'id_akses', $data['creat_by_id'], 'nama_akses');
    }else{
        $creator = $data['creat_by_name'];
    }
    if(!empty($data['update_by_id'])){
        $updater = GetDetailData($Conn, 'akses', 'id_akses', $data['update_by_id'], 'nama_akses');
    }else{
        $updater = $data['update_by_id'];
    }

    $html = '
        <div class="container-fluid px-0">
            <div class="row mb-2">
                <div class="col-5"><small>ID Poliklinik</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6"><small class="text-muted">' . detailValue($data['polyclinicId']) . '</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><small>ID Location SATUSEHAT</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6"><small class="text-muted">' . detailValue($data['satuSehatCode']) . '</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><small>Kode Poliklinik</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6"><small class="text-muted">' . detailValue($data['polyclinicCode']) . '</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><small>Nama Poliklinik</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6"><small class="text-muted">' . detailValue($data['polyclinicName']) . '</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><small>Status</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6">' . $statusBadge . '</div>
            </div>

            <div class="row mt-4 mb-2">
                <div class="col-12"><small><b># Metadata</b></small></div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><small>Creat At</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6"><small class="text-muted">' . detailDate($data['creat_at']) . '</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><small>Update At</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6"><small class="text-muted">' . detailDate($data['update_at']) . '</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><small>Creator</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6"><small class="text-muted">' . $creator . '</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-5"><small>Updater</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-6"><small class="text-muted">' . $updater . '</small></div>
            </div>
        </div>
    ';

    responsePoliklinikDetail('success', 'Detail poliklinik berhasil dimuat.', $html);
?>
