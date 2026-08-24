<?php
    date_default_timezone_set('Asia/Jakarta');

    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/SettingGeneral.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');

    function responseError($message) {
        echo json_encode([
            'status' => 'error',
            'message' => $message
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    if (empty($SessionIdAkses)) {
        responseError('Sesi akses sudah berakhir. Silakan login kembali.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseError('Metode request tidak valid.');
    }

    $id_jurnal = trim($_POST['id_jurnal'] ?? '');

    if ($id_jurnal === '' || !ctype_digit($id_jurnal)) {
        responseError('ID jurnal tidak valid.');
    }

    $id_jurnal = (int)$id_jurnal;

    if ($id_jurnal <= 0) {
        responseError('ID jurnal tidak valid.');
    }

    // Cek data jurnal
    $sql = "
        SELECT id_jurnal
        FROM jurnal
        WHERE id_jurnal = ?
        LIMIT 1
    ";

    $stmt = mysqli_prepare($Conn, $sql);

    if (!$stmt) {
        responseError('Gagal mempersiapkan query jurnal.');
    }

    mysqli_stmt_bind_param($stmt, 'i', $id_jurnal);

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        responseError('Gagal mengambil data jurnal.');
    }

    $result = mysqli_stmt_get_result($stmt);

    if (!$result || mysqli_num_rows($result) === 0) {
        mysqli_stmt_close($stmt);
        responseError('Data jurnal tidak ditemukan atau sudah dihapus.');
    }

    mysqli_stmt_close($stmt);

    // Hapus jurnal
    $sql = "
        DELETE FROM jurnal
        WHERE id_jurnal = ?
        LIMIT 1
    ";

    $stmt = mysqli_prepare($Conn, $sql);

    if (!$stmt) {
        responseError('Gagal mempersiapkan proses hapus jurnal.');
    }

    mysqli_stmt_bind_param($stmt, 'i', $id_jurnal);

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        responseError('Gagal menghapus data jurnal.');
    }

    $affected_rows = mysqli_stmt_affected_rows($stmt);

    mysqli_stmt_close($stmt);

    if ($affected_rows <= 0) {
        responseError('Data jurnal gagal dihapus.');
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Data jurnal berhasil dihapus.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
?>