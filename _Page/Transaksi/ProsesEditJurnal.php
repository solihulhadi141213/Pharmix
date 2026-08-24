<?php

    date_default_timezone_set('Asia/Jakarta');

    // =========================================================
    // KONEKSI, HELPER & SESSION
    // =========================================================
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/SettingGeneral.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');


    // =========================================================
    // RESPONSE ERROR
    // =========================================================
    function responseError($message)
    {
        echo json_encode([
            'status'  => 'error',
            'message' => $message
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    // =========================================================
    // VALIDASI SESI
    // =========================================================
    if (empty($SessionIdAkses)) {
        responseError('Sesi akses sudah berakhir. Silakan login kembali.');
    }


    // =========================================================
    // VALIDASI METHOD
    // =========================================================
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseError('Metode request tidak valid.');
    }


    // =========================================================
    // AMBIL DATA FORM
    // =========================================================
    $id_jurnal      = trim($_POST['id_jurnal'] ?? '');
    $kode_perkiraan = trim($_POST['kode_perkiraan'] ?? '');
    $d_k            = strtoupper(trim($_POST['d_k'] ?? ''));
    $nilai          = trim($_POST['nilai'] ?? '');


    // =========================================================
    // VALIDASI ID JURNAL
    // =========================================================
    if ($id_jurnal === '' || !ctype_digit($id_jurnal)) {
        responseError('ID jurnal tidak valid.');
    }

    $id_jurnal = (int) $id_jurnal;

    if ($id_jurnal <= 0) {
        responseError('ID jurnal tidak valid.');
    }


    // =========================================================
    // VALIDASI AKUN PERKIRAAN
    // =========================================================
    if ($kode_perkiraan === '') {
        responseError('Akun perkiraan wajib dipilih.');
    }


    // =========================================================
    // VALIDASI D/K
    // =========================================================
    if (!in_array($d_k, ['D', 'K'], true)) {
        responseError('Posisi jurnal harus Debet atau Kredit.');
    }


    // =========================================================
    // BERSIHKAN NILAI
    // Contoh:
    // 1.000     -> 1000
    // 15.000    -> 15000
    // 1.500.000 -> 1500000
    // =========================================================
    $nilai = str_replace('.', '', $nilai);

    // Hanya angka
    $nilai = preg_replace('/[^0-9]/', '', $nilai);

    if ($nilai === '') {
        responseError('Nilai jurnal wajib diisi.');
    }

    $nilai = (int) $nilai;

    if ($nilai <= 0) {
        responseError('Nilai jurnal harus lebih besar dari 0.');
    }


    // =========================================================
    // CEK DATA JURNAL
    // =========================================================
    $sql_jurnal = "
        SELECT
            id_jurnal,
            id_transaksi
        FROM jurnal
        WHERE id_jurnal = ?
        LIMIT 1
    ";

    $stmt_jurnal = mysqli_prepare($Conn, $sql_jurnal);

    if (!$stmt_jurnal) {
        responseError('Gagal mempersiapkan query jurnal.');
    }

    mysqli_stmt_bind_param(
        $stmt_jurnal,
        'i',
        $id_jurnal
    );

    if (!mysqli_stmt_execute($stmt_jurnal)) {

        mysqli_stmt_close($stmt_jurnal);

        responseError('Gagal mengambil data jurnal.');
    }

    $result_jurnal = mysqli_stmt_get_result($stmt_jurnal);

    if (!$result_jurnal || mysqli_num_rows($result_jurnal) === 0) {

        mysqli_stmt_close($stmt_jurnal);

        responseError('Data jurnal tidak ditemukan.');
    }

    $data_jurnal = mysqli_fetch_assoc($result_jurnal);

    mysqli_stmt_close($stmt_jurnal);


    // =========================================================
    // CEK AKUN PERKIRAAN
    // =========================================================
    $sql_akun = "
        SELECT
            kode,
            nama
        FROM akun_perkiraan
        WHERE kode = ?
        LIMIT 1
    ";

    $stmt_akun = mysqli_prepare($Conn, $sql_akun);

    if (!$stmt_akun) {
        responseError('Gagal mempersiapkan query akun perkiraan.');
    }

    mysqli_stmt_bind_param(
        $stmt_akun,
        's',
        $kode_perkiraan
    );

    if (!mysqli_stmt_execute($stmt_akun)) {

        mysqli_stmt_close($stmt_akun);

        responseError('Gagal mengambil data akun perkiraan.');
    }

    $result_akun = mysqli_stmt_get_result($stmt_akun);

    if (!$result_akun || mysqli_num_rows($result_akun) === 0) {

        mysqli_stmt_close($stmt_akun);

        responseError('Akun perkiraan tidak ditemukan.');
    }

    $data_akun = mysqli_fetch_assoc($result_akun);

    mysqli_stmt_close($stmt_akun);


    // =========================================================
    // AMBIL NAMA PERKIRAAN
    // =========================================================
    $nama_perkiraan = $data_akun['nama'];


    // =========================================================
    // UPDATE JURNAL
    // =========================================================
    $sql_update = "
        UPDATE jurnal
        SET
            kode_perkiraan = ?,
            nama_perkiraan = ?,
            d_k            = ?,
            nilai          = ?
        WHERE id_jurnal = ?
        LIMIT 1
    ";

    $stmt_update = mysqli_prepare($Conn, $sql_update);

    if (!$stmt_update) {
        responseError('Gagal mempersiapkan proses update jurnal.');
    }

    mysqli_stmt_bind_param(
        $stmt_update,
        'sssii',
        $kode_perkiraan,
        $nama_perkiraan,
        $d_k,
        $nilai,
        $id_jurnal
    );


    // =========================================================
    // EKSEKUSI UPDATE
    // =========================================================
    if (!mysqli_stmt_execute($stmt_update)) {

        mysqli_stmt_close($stmt_update);

        responseError('Gagal memperbarui data jurnal.');
    }


    // =========================================================
    // CEK HASIL UPDATE
    // =========================================================
    $affected_rows = mysqli_stmt_affected_rows($stmt_update);

    mysqli_stmt_close($stmt_update);


    // =========================================================
    // RESPONSE SUCCESS
    // =========================================================
    if ($affected_rows > 0) {

        echo json_encode([
            'status'  => 'success',
            'message' => 'Data jurnal berhasil diperbarui.'
        ], JSON_UNESCAPED_UNICODE);

    } else {

        echo json_encode([
            'status'  => 'success',
            'message' => 'Data jurnal tidak mengalami perubahan.'
        ], JSON_UNESCAPED_UNICODE);
    }

    exit;
?>