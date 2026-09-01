<?php

    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    include "../../_Config/FungsiAkses.php";

    date_default_timezone_set("Asia/Jakarta");
    header('Content-Type: application/json; charset=utf-8');

    function prosesEditResponse($status, $message)
    {
        echo json_encode([
            'status'  => $status,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    function getEditPost($key)
    {
        return trim((string) ($_POST[$key] ?? ''));
    }

    function isDuplicateAnggota($Conn, $field, $value, $id_anggota)
    {
        $allowedFields = ['id_ihs', 'nik', 'kontak', 'email'];

        if (!in_array($field, $allowedFields, true) || $value === '') {
            return false;
        }

        $sql = "SELECT id_anggota
                FROM anggota
                WHERE {$field} = ?
                AND id_anggota != ?
                LIMIT 1";
        $stmt = mysqli_prepare($Conn, $sql);

        if (!$stmt) {
            prosesEditResponse('error', 'Gagal menyiapkan validasi data pasien.');
        }

        mysqli_stmt_bind_param($stmt, 'si', $value, $id_anggota);

        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            prosesEditResponse('error', 'Gagal memvalidasi data pasien.');
        }

        $result = mysqli_stmt_get_result($stmt);
        $duplicate = mysqli_num_rows($result) > 0;
        mysqli_stmt_close($stmt);

        return $duplicate;
    }

    if (empty($SessionIdAkses)) {
        prosesEditResponse('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
    }

    $id_anggota    = (int) ($_POST['id_anggota'] ?? 0);
    $id_pasien     = getEditPost('id_pasien');
    $id_ihs        = getEditPost('id_ihs');
    $nik           = getEditPost('nik');
    $nama          = getEditPost('nama');
    $email         = getEditPost('email');
    $kontak        = getEditPost('kontak');
    $alamat        = getEditPost('alamat');
    $gender        = getEditPost('gender');
    $tempat_lahir  = getEditPost('tempat_lahir');
    $tanggal_lahir = getEditPost('tanggal_lahir');

    if ($id_anggota <= 0) {
        prosesEditResponse('error', 'ID pasien tidak valid.');
    }

    if ($id_pasien === '') {
        prosesEditResponse('error', 'ID pasien wajib diisi.');
    }

    if ($nama === '') {
        prosesEditResponse('error', 'Nama pasien wajib diisi.');
    }

    if (!in_array($gender, ['Male', 'Female'], true)) {
        prosesEditResponse('error', 'Jenis kelamin tidak valid.');
    }

    if ($tanggal_lahir !== '') {
        $date = DateTime::createFromFormat('Y-m-d', $tanggal_lahir);

        if (!$date || $date->format('Y-m-d') !== $tanggal_lahir) {
            prosesEditResponse('error', 'Format tanggal lahir tidak valid.');
        }
    }

    $stmt = mysqli_prepare(
        $Conn,
        "SELECT id_anggota FROM anggota WHERE id_anggota = ? LIMIT 1"
    );

    if (!$stmt) {
        prosesEditResponse('error', 'Gagal memeriksa data pasien.');
    }

    mysqli_stmt_bind_param($stmt, 'i', $id_anggota);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $dataExists = mysqli_num_rows($result) > 0;
    mysqli_stmt_close($stmt);

    if (!$dataExists) {
        prosesEditResponse('error', 'Data pasien tidak ditemukan.');
    }

    $duplicateMessages = [
        'id_ihs' => 'IHS tersebut sudah terdaftar pada pasien lain.',
        'nik'    => 'NIK tersebut sudah terdaftar pada pasien lain.',
        'kontak' => 'Nomor kontak tersebut sudah terdaftar pada pasien lain.',
        'email'  => 'Email tersebut sudah terdaftar pada pasien lain.'
    ];

    foreach ($duplicateMessages as $field => $message) {
        if (isDuplicateAnggota($Conn, $field, $$field, $id_anggota)) {
            prosesEditResponse('error', $message);
        }
    }

    $now = date('Y-m-d H:i:s');
    $stmt = mysqli_prepare(
        $Conn,
        "UPDATE anggota SET
            id_pasien      = ?,
            id_ihs         = ?,
            nik            = ?,
            nama           = ?,
            email          = ?,
            kontak         = ?,
            alamat         = ?,
            gender         = ?,
            tempat_lahir   = ?,
            tanggal_lahir  = ?,
            update_at      = ?,
            update_by_id   = ?,
            update_by_name = ?
        WHERE id_anggota = ?
        LIMIT 1"
    );

    if (!$stmt) {
        prosesEditResponse('error', 'Gagal menyiapkan proses update data.');
    }

    mysqli_stmt_bind_param(
        $stmt,
        'sssssssssssisi',
        $id_pasien,
        $id_ihs,
        $nik,
        $nama,
        $email,
        $kontak,
        $alamat,
        $gender,
        $tempat_lahir,
        $tanggal_lahir,
        $now,
        $SessionIdAkses,
        $SessionNama,
        $id_anggota
    );

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        prosesEditResponse('error', 'Gagal memperbarui data pasien.');
    }

    $affectedRows = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    prosesEditResponse(
        'success',
        $affectedRows > 0
            ? 'Data pasien berhasil diperbarui.'
            : 'Data pasien tidak mengalami perubahan.'
    );
?>
