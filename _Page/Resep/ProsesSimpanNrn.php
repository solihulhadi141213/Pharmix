<?php
    // CONNECTION, HELPER, SESSION & AKSES
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    include "../../_Config/FungsiAkses.php";

    date_default_timezone_set('Asia/Jakarta');
    header('Content-Type: application/json; charset=utf-8');

    function responseNrn(string $status, string $message, array $data = []): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'data'    => $data
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // VALIDASI SESSION & METHOD
    if (empty($SessionIdAkses)) {
        responseNrn('error', 'Sesi akses telah berakhir. Silakan login ulang.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseNrn('error', 'Metode request tidak valid.');
    }

    // TANGKAP DATA FORM
    $id_medication_request_group = (int)($_POST['id_medication_request_group'] ?? 0);
    $id_document_reference       = trim((string)($_POST['id_document_reference'] ?? ''));
    $no_resep_nasional           = trim((string)($_POST['no_resep_nasional'] ?? ''));

    // VALIDASI PARAMETER
    if ($id_medication_request_group < 1) {
        responseNrn('error', 'ID resep tidak valid.');
    }

    if ($id_document_reference === '') {
        responseNrn('error', 'ID DocumentReference tidak boleh kosong.');
    }

    if ($no_resep_nasional === '') {
        responseNrn('error', 'Nomor Resep Nasional tidak boleh kosong.');
    }

    // VALIDASI FORMAT NRN
    // Contoh: 241203-DDG2BM2
    if (!preg_match('/^[A-Za-z0-9\-]+$/', $no_resep_nasional)) {
        responseNrn('error', 'Format Nomor Resep Nasional tidak valid.');
    }

    // CEK DATA RESEP
    $stmt = $Conn->prepare("
        SELECT
            id_medication_request_group,
            id_document_reference,
            no_resep_nasional
        FROM medication_request_group
        WHERE id_medication_request_group = ?
        LIMIT 1
    ");

    if (!$stmt) {
        responseNrn('error', 'Gagal mempersiapkan data resep.');
    }

    $stmt->bind_param("i", $id_medication_request_group);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();

        responseNrn(
            'error',
            'Gagal membuka data resep.<br>Keterangan : '.
            htmlspecialchars($error, ENT_QUOTES, 'UTF-8')
        );
    }

    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$data) {
        responseNrn('error', 'Data resep tidak ditemukan.');
    }

    // VALIDASI DOCUMENT REFERENCE
    $id_document_reference_db = trim(
        (string)($data['id_document_reference'] ?? '')
    );

    if ($id_document_reference_db === '') {
        responseNrn(
            'error',
            'Resep belum memiliki ID DocumentReference SATUSEHAT.'
        );
    }

    if ($id_document_reference_db !== $id_document_reference) {
        responseNrn(
            'error',
            'ID DocumentReference tidak sesuai dengan data resep.'
        );
    }

    // CEGAH SIMPAN ULANG NRN
    $nrn_existing = trim(
        (string)($data['no_resep_nasional'] ?? '')
    );

    if ($nrn_existing !== '') {
        if ($nrn_existing === $no_resep_nasional) {
            responseNrn(
                'success',
                'Nomor Resep Nasional sudah tersimpan sebelumnya.',
                [
                    'id_medication_request_group' => $id_medication_request_group,
                    'id_document_reference'       => $id_document_reference,
                    'no_resep_nasional'           => $nrn_existing
                ]
            );
        }

        responseNrn(
            'error',
            'Resep sudah memiliki Nomor Resep Nasional yang berbeda: '.
            htmlspecialchars($nrn_existing, ENT_QUOTES, 'UTF-8')
        );
    }

    // METADATA UPDATE
    $update_at      = date('Y-m-d H:i:s');
    $update_by_id   = (int)$SessionIdAkses;
    $update_by_name = trim((string)($SessionNama ?? ''));

    if ($update_by_name === '') {
        $update_by_name = 'System';
    }

    // SIMPAN NRN
    $stmt = $Conn->prepare("
        UPDATE medication_request_group
        SET
            no_resep_nasional = ?,
            update_at         = ?,
            update_by_id      = ?,
            update_by_name    = ?
        WHERE id_medication_request_group = ?
        AND id_document_reference = ?
        AND (
            no_resep_nasional IS NULL
            OR TRIM(no_resep_nasional) = ''
        )
    ");

    if (!$stmt) {
        responseNrn('error', 'Gagal mempersiapkan proses penyimpanan NRN.');
    }

    $stmt->bind_param(
        "ssisis",
        $no_resep_nasional,
        $update_at,
        $update_by_id,
        $update_by_name,
        $id_medication_request_group,
        $id_document_reference
    );

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();

        responseNrn(
            'error',
            'Gagal menyimpan Nomor Resep Nasional.<br>Keterangan : '.
            htmlspecialchars($error, ENT_QUOTES, 'UTF-8')
        );
    }

    if ($stmt->affected_rows !== 1) {
        $stmt->close();

        responseNrn(
            'error',
            'Nomor Resep Nasional tidak berhasil disimpan.'
        );
    }

    $stmt->close();

    // RESPONSE SUCCESS
    responseNrn(
        'success',
        'Nomor Resep Nasional berhasil disimpan.',
        [
            'id_medication_request_group' => $id_medication_request_group,
            'id_document_reference'       => $id_document_reference,
            'no_resep_nasional'           => $no_resep_nasional
        ]
    );
?>