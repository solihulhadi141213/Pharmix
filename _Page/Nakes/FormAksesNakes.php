<?php
    header('Content-Type: application/json; charset=utf-8');

    // INCLUDE
    include __DIR__ . "/../../_Config/Connection.php";
    include __DIR__ . "/../../_Config/GlobalFunction.php";
    include __DIR__ . "/../../_Config/Session.php";

    date_default_timezone_set('Asia/Jakarta');

    // FUNCTION RESPONSE
    function responseAksesNakes(string $status, string $message, string $html = ''): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'html'    => $html
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // FUNCTION ESCAPE
    function aksesValue($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    // VALIDASI SESSION
    if (empty($SessionIdAkses)) {
        responseAksesNakes('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
    }

    // VALIDASI METHOD
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseAksesNakes('error', 'Metode request tidak valid.');
    }

    // VALIDASI ID NAKES
    $medicalPersonelId = filter_var(
        $_POST['medicalPersonelId'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($medicalPersonelId === false || $medicalPersonelId === null) {
        responseAksesNakes('error', 'ID tenaga kesehatan tidak valid.');
    }

    // BUKA DATA NAKES
    $stmtNakes = $Conn->prepare("
        SELECT
            medicalPersonelId,
            medicalPersonelCode,
            medicalPersonelName,
            medicalPersonelCategory,
            id_akses
        FROM medical_personel
        WHERE medicalPersonelId = ?
        LIMIT 1
    ");

    if ($stmtNakes === false) {
        responseAksesNakes('error', 'Gagal menyiapkan query tenaga kesehatan.');
    }

    $stmtNakes->bind_param('i', $medicalPersonelId);

    if (!$stmtNakes->execute()) {
        $stmtNakes->close();
        responseAksesNakes('error', 'Gagal membuka data tenaga kesehatan.');
    }

    $resultNakes = $stmtNakes->get_result();
    $dataNakes   = $resultNakes ? $resultNakes->fetch_assoc() : null;
    $stmtNakes->close();

    if (!$dataNakes) {
        responseAksesNakes('error', 'Data tenaga kesehatan tidak ditemukan.');
    }

    // ID AKSES YANG SEDANG DIGUNAKAN
    $currentAksesId = !empty($dataNakes['id_akses'])
        ? (int)$dataNakes['id_akses']
        : 0;

    // BUKA DATA AKSES
    $queryAkses = $Conn->query("
        SELECT
            id_akses,
            nama_akses
        FROM akses
        ORDER BY nama_akses ASC
    ");

    if ($queryAkses === false) {
        responseAksesNakes('error', 'Gagal membuka daftar akun akses.');
    }

    // INFORMASI NAKES
    $html = '
        <input type="hidden" name="medicalPersonelId" value="'.(int)$medicalPersonelId.'">

        <div class="mb-3">
            <div class="row mb-1">
                <div class="col-4"><small>Nama Nakes</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7">
                    <small class="text-muted">'.aksesValue($dataNakes['medicalPersonelName']).'</small>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-4"><small>Kategori</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7">
                    <small class="text-muted">'.aksesValue($dataNakes['medicalPersonelCategory']).'</small>
                </div>
            </div>
        </div>

        <div class="mb-2">
            <small><b>Pilih Akun Akses</b></small>
        </div>

        <div class="list-group">
    ';

    // PILIHAN TIDAK MEMILIKI AKSES
    $checkedNull = $currentAksesId === 0 ? 'checked' : '';

    $html .= '
        <label class="list-group-item list-group-item-action">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <input
                        class="form-check-input"
                        type="radio"
                        name="id_akses"
                        value=""
                        '.$checkedNull.'
                    >
                </div>
                <div>
                    <div>
                        <strong>Tidak Memiliki Akses</strong>
                    </div>
                    <small class="text-muted">
                        Nakes tidak dihubungkan dengan akun pengguna.
                    </small>
                </div>
            </div>
        </label>
    ';

    // LOOP DATA AKSES
    $jumlahAkses = 0;

    while ($dataAkses = $queryAkses->fetch_assoc()) {
        $idAkses   = (int)$dataAkses['id_akses'];
        $namaAkses = aksesValue($dataAkses['nama_akses']);
        $checked   = $currentAksesId === $idAkses ? 'checked' : '';

        $html .= '
            <label class="list-group-item list-group-item-action">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <input
                            class="form-check-input"
                            type="radio"
                            name="id_akses"
                            value="'.$idAkses.'"
                            '.$checked.'
                        >
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                            <strong>'.$namaAkses.'</strong>
                            <small class="text-muted">ID '.$idAkses.'</small>
                        </div>
                    </div>
                </div>
            </label>
        ';

        $jumlahAkses++;
    }

    $html .= '</div>';

    // JIKA BELUM ADA AKUN
    if ($jumlahAkses === 0) {
        $html .= '
            <div class="alert alert-warning text-center mt-3 mb-0">
                <small>
                    Belum terdapat akun akses yang dapat dipilih.
                </small>
            </div>
        ';
    }

    responseAksesNakes(
        'success',
        'Daftar akses tenaga kesehatan berhasil dimuat.',
        $html
    );
?>