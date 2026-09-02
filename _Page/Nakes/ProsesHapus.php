<?php
    header('Content-Type: application/json; charset=utf-8');

    // INCLUDE
    include __DIR__ . "/../../_Config/Connection.php";
    include __DIR__ . "/../../_Config/GlobalFunction.php";
    include __DIR__ . "/../../_Config/Session.php";

    date_default_timezone_set('Asia/Jakarta');

    // FUNCTION RESPONSE
    function responseNakesHapus(string $status, string $message): void {
        echo json_encode([
            'status'  => $status,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // VALIDASI SESSION
    if (empty($SessionIdAkses)) {
        responseNakesHapus('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
    }

    // VALIDASI METHOD
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseNakesHapus('error', 'Metode request tidak valid.');
    }

    // TANGKAP DAN VALIDASI ID NAKES
    $medicalPersonelId = filter_var(
        $_POST['medicalPersonelId'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($medicalPersonelId === false || $medicalPersonelId === null) {
        responseNakesHapus('error', 'ID tenaga kesehatan tidak valid.');
    }

    // PASTIKAN DATA MASIH ADA
    $stmtCheck = $Conn->prepare("
        SELECT medicalPersonelId
        FROM medical_personel
        WHERE medicalPersonelId = ?
        LIMIT 1
    ");

    if ($stmtCheck === false) {
        responseNakesHapus('error', 'Gagal menyiapkan validasi data tenaga kesehatan.');
    }

    $stmtCheck->bind_param('i', $medicalPersonelId);

    if (!$stmtCheck->execute()) {
        $stmtCheck->close();
        responseNakesHapus('error', 'Gagal memvalidasi data tenaga kesehatan.');
    }

    $resultCheck = $stmtCheck->get_result();
    $dataExists  = $resultCheck && $resultCheck->num_rows > 0;
    $stmtCheck->close();

    if (!$dataExists) {
        responseNakesHapus('error', 'Data tenaga kesehatan tidak ditemukan atau sudah dihapus.');
    }

    // HAPUS DATA NAKES
    $stmtDelete = $Conn->prepare("
        DELETE FROM medical_personel
        WHERE medicalPersonelId = ?
        LIMIT 1
    ");

    if ($stmtDelete === false) {
        responseNakesHapus('error', 'Gagal menyiapkan proses hapus tenaga kesehatan.');
    }

    $stmtDelete->bind_param('i', $medicalPersonelId);

    if (!$stmtDelete->execute()) {
        $error = $stmtDelete->error;
        $stmtDelete->close();

        responseNakesHapus(
            'error',
            'Data tenaga kesehatan gagal dihapus. '.$error
        );
    }

    $affectedRows = $stmtDelete->affected_rows;
    $stmtDelete->close();

    // VALIDASI HASIL DELETE
    if ($affectedRows < 1) {
        responseNakesHapus('error', 'Data tenaga kesehatan gagal dihapus.');
    }

    // RESPONSE SUCCESS
    responseNakesHapus(
        'success',
        'Data tenaga kesehatan berhasil dihapus.'
    );
?>