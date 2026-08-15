<?php

// Koneksi dan session
include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";

date_default_timezone_set("Asia/Jakarta");

// Response JSON
header('Content-Type: application/json');


// =========================================================
// VALIDASI SESSION
// =========================================================

if (empty($SessionIdAkses)) {

    echo json_encode([
        'status'  => 'error',
        'message' => 'Sesi akses sudah berakhir. Silahkan login ulang.'
    ]);

    exit;
}


// =========================================================
// AMBIL ID
// =========================================================

$id_anggota = isset($_POST['id_anggota'])
    ? (int) $_POST['id_anggota']
    : 0;


// =========================================================
// VALIDASI ID
// =========================================================

if ($id_anggota <= 0) {

    echo json_encode([
        'status'  => 'error',
        'message' => 'ID pasien tidak valid.'
    ]);

    exit;
}


// =========================================================
// CEK DATA
// =========================================================

$stmt = mysqli_prepare(
    $Conn,
    "SELECT id_anggota, nama
     FROM anggota
     WHERE id_anggota = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_anggota
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


// =========================================================
// DATA TIDAK DITEMUKAN
// =========================================================

if (!$data) {

    echo json_encode([
        'status'  => 'error',
        'message' => 'Data pasien tidak ditemukan.'
    ]);

    exit;
}


// =========================================================
// PROSES DELETE
// =========================================================

$stmt = mysqli_prepare(
    $Conn,
    "DELETE FROM anggota
     WHERE id_anggota = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_anggota
);


// =========================================================
// EKSEKUSI
// =========================================================

if (mysqli_stmt_execute($stmt)) {

    $affected_rows = mysqli_stmt_affected_rows($stmt);

    mysqli_stmt_close($stmt);

    if ($affected_rows > 0) {

        echo json_encode([
            'status'  => 'success',
            'message' => 'Data pasien "' . $data['nama'] . '" berhasil dihapus.'
        ]);

    } else {

        echo json_encode([
            'status'  => 'error',
            'message' => 'Data pasien gagal dihapus.'
        ]);
    }

    exit;

} else {

    mysqli_stmt_close($stmt);

    echo json_encode([
        'status'  => 'error',
        'message' => 'Terjadi kesalahan saat menghapus data pasien.'
    ]);

    exit;
}