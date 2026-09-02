<?php
    // HEADER
    header('Content-Type: application/json; charset=utf-8');

    // INCLUDE
    include __DIR__ . "/../../_Config/Connection.php";
    include __DIR__ . "/../../_Config/GlobalFunction.php";
    include __DIR__ . "/../../_Config/Session.php";
    include __DIR__ . "/../../_Config/FungsiAkses.php";

    date_default_timezone_set('Asia/Jakarta');

    // FUNCTION RESPONSE
    function responseAksesNakes(string $status, string $message, array $metadata = []): void {
        echo json_encode([
            'status'   => $status,
            'message'  => $message,
            'metadata' => $metadata
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // VALIDASI SESSION
    if (empty($SessionIdAkses)) {
        responseAksesNakes('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
    }

    // VALIDASI METHOD
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseAksesNakes('error', 'Metode request tidak valid.');
    }

    // TANGKAP INPUT
    $medicalPersonelId = filter_var(
        $_POST['medicalPersonelId'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    $idAksesRaw = trim((string)($_POST['id_akses'] ?? ''));

    // VALIDASI ID NAKES
    if ($medicalPersonelId === false || $medicalPersonelId === null) {
        responseAksesNakes('error', 'ID tenaga kesehatan tidak valid.');
    }

    // PASTIKAN DATA NAKES ADA
    $stmtNakes = $Conn->prepare("
        SELECT medicalPersonelId, id_akses
        FROM medical_personel
        WHERE medicalPersonelId = ?
        LIMIT 1
    ");

    if ($stmtNakes === false) {
        responseAksesNakes('error', 'Gagal menyiapkan validasi tenaga kesehatan.');
    }

    $stmtNakes->bind_param('i', $medicalPersonelId);

    if (!$stmtNakes->execute()) {
        $stmtNakes->close();
        responseAksesNakes('error', 'Gagal memvalidasi tenaga kesehatan.');
    }

    $resultNakes = $stmtNakes->get_result();
    $dataNakes   = $resultNakes ? $resultNakes->fetch_assoc() : null;
    $stmtNakes->close();

    if (!$dataNakes) {
        responseAksesNakes('error', 'Data tenaga kesehatan tidak ditemukan.');
    }

    // JIKA AKSES DIPILIH
    if ($idAksesRaw !== '') {

        $idAkses = filter_var(
            $idAksesRaw,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($idAkses === false || $idAkses === null) {
            responseAksesNakes('error', 'ID akses tidak valid.');
        }

        // PASTIKAN AKUN AKSES ADA
        $stmtAkses = $Conn->prepare("
            SELECT id_akses, nama_akses
            FROM akses
            WHERE id_akses = ?
            LIMIT 1
        ");

        if ($stmtAkses === false) {
            responseAksesNakes('error', 'Gagal menyiapkan validasi akun akses.');
        }

        $stmtAkses->bind_param('i', $idAkses);

        if (!$stmtAkses->execute()) {
            $stmtAkses->close();
            responseAksesNakes('error', 'Gagal memvalidasi akun akses.');
        }

        $resultAkses = $stmtAkses->get_result();
        $dataAkses   = $resultAkses ? $resultAkses->fetch_assoc() : null;
        $stmtAkses->close();

        if (!$dataAkses) {
            responseAksesNakes('error', 'Akun akses tidak ditemukan.');
        }

        $idAksesValue = (int)$idAkses;
        $namaAkses    = $dataAkses['nama_akses'] ?? '-';

    } else {

        // PILIHAN "TIDAK MEMILIKI AKSES"
        $idAksesValue = null;
        $namaAkses    = '-';
    }

    // CEK APAKAH AKSES SUDAH DIGUNAKAN NAKES LAIN
    if ($idAksesValue !== null) {

        $stmtDuplicate = $Conn->prepare("
            SELECT medicalPersonelId, medicalPersonelName
            FROM medical_personel
            WHERE id_akses = ?
              AND medicalPersonelId <> ?
            LIMIT 1
        ");

        if ($stmtDuplicate === false) {
            responseAksesNakes('error', 'Gagal menyiapkan validasi penggunaan akun akses.');
        }

        $stmtDuplicate->bind_param('ii', $idAksesValue, $medicalPersonelId);

        if (!$stmtDuplicate->execute()) {
            $stmtDuplicate->close();
            responseAksesNakes('error', 'Gagal memvalidasi penggunaan akun akses.');
        }

        $resultDuplicate = $stmtDuplicate->get_result();
        $dataDuplicate   = $resultDuplicate ? $resultDuplicate->fetch_assoc() : null;
        $stmtDuplicate->close();

        if ($dataDuplicate) {
            responseAksesNakes(
                'error',
                'Akun akses tersebut sudah digunakan oleh tenaga kesehatan '.$dataDuplicate['medicalPersonelName'].'.'
            );
        }
    }

    // DATA AUDIT
    $now = date('Y-m-d H:i:s');

    // TRANSACTION
    $Conn->begin_transaction();

    try {

        // UPDATE AKSES NAKES
        $stmtUpdate = $Conn->prepare("
            UPDATE medical_personel SET
                id_akses       = ?,
                update_by_id   = ?,
                update_by_name = ?,
                update_at      = ?
            WHERE medicalPersonelId = ?
            LIMIT 1
        ");

        if ($stmtUpdate === false) {
            throw new Exception('Gagal menyiapkan proses update akses tenaga kesehatan.');
        }

        $stmtUpdate->bind_param(
            'iissi',
            $idAksesValue,
            $SessionIdAkses,
            $SessionNama,
            $now,
            $medicalPersonelId
        );

        if (!$stmtUpdate->execute()) {
            $errorUpdate = $stmtUpdate->error;
            $stmtUpdate->close();
            throw new Exception('Akses tenaga kesehatan gagal diperbarui. '.$errorUpdate);
        }

        $affectedRows = $stmtUpdate->affected_rows;
        $stmtUpdate->close();

        // COMMIT
        $Conn->commit();

        // RESPONSE SUCCESS
        responseAksesNakes(
            'success',
            $affectedRows > 0
                ? 'Akses tenaga kesehatan berhasil diperbarui.'
                : 'Tidak ada perubahan akses tenaga kesehatan.',
            [
                'medicalPersonelId' => $medicalPersonelId,
                'id_akses'          => $idAksesValue,
                'nama_akses'        => $namaAkses
            ]
        );

    } catch (Throwable $e) {

        // ROLLBACK
        $Conn->rollback();

        responseAksesNakes(
            'error',
            $e->getMessage()
        );
    }
?>