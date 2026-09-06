<?php
    // CONNECTION, HELPER & SESSION
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');

    function responseEditDokter(string $status, string $message, string $html = ''): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'html'    => $html
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    function escEditDokter($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    // Validasi Session
    if (empty($SessionIdAkses)) {
        responseEditDokter(
            'error',
            'Sesi akses telah berakhir. Silakan login ulang.'
        );
    }

    // Validasi Method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseEditDokter(
            'error',
            'Metode request tidak valid.'
        );
    }

    // Tangkap ID Group Resep
    $id_medication_request_group = (int)(
        $_POST['id_medication_request_group'] ?? 0
    );

    if ($id_medication_request_group < 1) {
        responseEditDokter(
            'error',
            'ID resep tidak valid.'
        );
    }

    // Ambil Data Resep
    $stmt = $Conn->prepare("
        SELECT
            id_medication_request_group,
            dokter_id,
            dokter_code,
            dokter_ihs,
            dokter_nama
        FROM medication_request_group
        WHERE id_medication_request_group = ?
        LIMIT 1
    ");

    if (!$stmt) {
        responseEditDokter(
            'error',
            'Gagal mempersiapkan data resep.'
        );
    }

    $stmt->bind_param(
        "i",
        $id_medication_request_group
    );

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();

        responseEditDokter(
            'error',
            'Gagal membuka data resep.<br>Keterangan : '.
            escEditDokter($error)
        );
    }

    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$data) {
        responseEditDokter(
            'error',
            'Data resep tidak ditemukan.'
        );
    }

    // Mapping Data
    $dokter_id   = (int)($data['dokter_id'] ?? 0);
    $dokter_code = trim((string)($data['dokter_code'] ?? ''));
    $dokter_nama = trim((string)($data['dokter_nama'] ?? ''));
    $dokter_ihs  = trim((string)($data['dokter_ihs'] ?? ''));

    /*
     * Jika dokter_id tersedia, ambil ulang dari medical_personel.
     * Ini memastikan kode, nama dan IHS sesuai master terbaru.
     */
    if ($dokter_id > 0) {
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
            $stmt->bind_param("i", $dokter_id);

            if ($stmt->execute()) {
                $masterDokter = $stmt->get_result()->fetch_assoc();

                if ($masterDokter) {
                    $dokter_code = trim(
                        (string)($masterDokter['medicalPersonelCode'] ?? $dokter_code)
                    );

                    $dokter_nama = trim(
                        (string)($masterDokter['medicalPersonelName'] ?? $dokter_nama)
                    );

                    $dokter_ihs = trim(
                        (string)($masterDokter['id_practitioner'] ?? $dokter_ihs)
                    );
                }
            }

            $stmt->close();
        }
    }

    // Option default Select2
    $optionDokter = '<option value=""></option>';

    if ($dokter_id > 0) {
        $textDokter = trim(
            $dokter_code.' - '.$dokter_nama,
            ' -'
        );

        $optionDokter = '
            <option
                value="'.$dokter_id.'"
                selected
            >
                '.escEditDokter($textDokter).'
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
                <label for="dokter_id_edit">
                    <small>Kode Dokter</small>
                </label>

                <select
                    name="dokter_id"
                    id="dokter_id_edit"
                    class="form-control"
                    style="width:100%;"
                >
                    '.$optionDokter.'
                </select>

                <input type="hidden"
                    name="dokter_code"
                    id="dokter_code_edit"
                    value="'.escEditDokter($dokter_code).'">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="dokter_nama_edit">
                    <small>Nama Dokter</small>
                </label>

                <input
                    type="text"
                    name="dokter_nama"
                    id="dokter_nama_edit"
                    class="form-control"
                    value="'.escEditDokter($dokter_nama).'"
                    placeholder="Nama dokter"
                    required
                >
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="dokter_ihs_edit">
                    <small>IHS Dokter</small>
                </label>

                <input
                    type="text"
                    name="dokter_ihs"
                    id="dokter_ihs_edit"
                    class="form-control"
                    value="'.escEditDokter($dokter_ihs).'"
                    placeholder="Practitioner IHS"
                >
            </div>
        </div>
    ';

    responseEditDokter(
        'success',
        'Form edit dokter berhasil ditampilkan.',
        $html
    );
?>