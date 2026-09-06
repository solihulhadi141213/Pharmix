<?php
    // CONNECTION, HELPER & SESSION
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');

    function responseEditApoteker(string $status, string $message, string $html = ''): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'html'    => $html
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    function escEditApoteker($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    // Validasi Session & Method
    if (empty($SessionIdAkses)) {
        responseEditApoteker('error', 'Sesi akses telah berakhir. Silakan login ulang.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseEditApoteker('error', 'Metode request tidak valid.');
    }

    // Tangkap ID Group
    $id_medication_request_group = (int)(
        $_POST['id_medication_request_group'] ?? 0
    );

    if ($id_medication_request_group < 1) {
        responseEditApoteker('error', 'ID resep tidak valid.');
    }

    // Ambil Data Group
    $stmt = $Conn->prepare("
        SELECT
            id_medication_request_group,
            apoteker_id,
            apoteker_code,
            apoteker_nama,
            apoteker_ihs
        FROM medication_request_group
        WHERE id_medication_request_group = ?
        LIMIT 1
    ");

    if (!$stmt) {
        responseEditApoteker('error', 'Gagal mempersiapkan data resep.');
    }

    $stmt->bind_param("i", $id_medication_request_group);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();

        responseEditApoteker(
            'error',
            'Gagal membuka data resep.<br>Keterangan : '.
            escEditApoteker($error)
        );
    }

    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$data) {
        responseEditApoteker('error', 'Data resep tidak ditemukan.');
    }

    // Mapping
    $apoteker_id   = (int)($data['apoteker_id'] ?? 0);
    $apoteker_code = trim((string)($data['apoteker_code'] ?? ''));
    $apoteker_nama = trim((string)($data['apoteker_nama'] ?? ''));
    $apoteker_ihs  = trim((string)($data['apoteker_ihs'] ?? ''));

    /*
     * Jika apoteker sudah terhubung dengan master medical_personel,
     * ambil ulang data master.
     */
    if ($apoteker_id > 0) {
        $stmt = $Conn->prepare("
            SELECT
                medicalPersonelId,
                medicalPersonelCode,
                medicalPersonelName,
                id_practitioner
            FROM medical_personel
            WHERE medicalPersonelId = ?
            LIMIT 1
        ");

        if ($stmt) {
            $stmt->bind_param("i", $apoteker_id);

            if ($stmt->execute()) {
                $master = $stmt->get_result()->fetch_assoc();

                if ($master) {
                    $apoteker_code = trim(
                        (string)($master['medicalPersonelCode'] ?? $apoteker_code)
                    );

                    $apoteker_nama = trim(
                        (string)($master['medicalPersonelName'] ?? $apoteker_nama)
                    );

                    $apoteker_ihs = trim(
                        (string)($master['id_practitioner'] ?? $apoteker_ihs)
                    );
                }
            }

            $stmt->close();
        }
    }

    // Default option Select2
    $optionApoteker = '<option value=""></option>';

    if ($apoteker_id > 0) {
        $textApoteker = trim(
            $apoteker_code.' - '.$apoteker_nama,
            ' -'
        );

        $optionApoteker = '
            <option value="'.$apoteker_id.'" selected>
                '.escEditApoteker($textApoteker).'
            </option>
        ';
    }

    // Form
    $html = '
        <input type="hidden"
            name="id_medication_request_group"
            value="'.$id_medication_request_group.'">

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="apoteker_id_edit">
                    <small>Kode Apoteker</small>
                </label>

                <select
                    name="apoteker_id"
                    id="apoteker_id_edit"
                    class="form-control"
                    style="width:100%;"
                >
                    '.$optionApoteker.'
                </select>

                <input type="hidden"
                    name="apoteker_code"
                    id="apoteker_code_edit"
                    value="'.escEditApoteker($apoteker_code).'">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="apoteker_nama_edit">
                    <small>Nama Apoteker</small>
                </label>

                <input
                    type="text"
                    name="apoteker_nama"
                    id="apoteker_nama_edit"
                    class="form-control"
                    value="'.escEditApoteker($apoteker_nama).'"
                    placeholder="Nama apoteker"
                    required
                >
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="apoteker_ihs_edit">
                    <small>IHS Apoteker</small>
                </label>

                <input
                    type="text"
                    name="apoteker_ihs"
                    id="apoteker_ihs_edit"
                    class="form-control"
                    value="'.escEditApoteker($apoteker_ihs).'"
                    placeholder="Practitioner IHS"
                >
            </div>
        </div>
    ';

    responseEditApoteker(
        'success',
        'Form edit apoteker berhasil ditampilkan.',
        $html
    );
?>