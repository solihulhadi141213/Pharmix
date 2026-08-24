<?php

date_default_timezone_set('Asia/Jakarta');

include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";

header(
    'Content-Type: application/json; charset=utf-8'
);


// ============================================================
// Response error
// ============================================================
function responseError($message)
{
    echo json_encode([
        'status'   => 'error',
        'message'  => $message,
        'kategori' => ''
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ============================================================
// Validasi session
// ============================================================
if (empty($SessionIdAkses)) {

    responseError(
        'Sesi akses sudah berakhir.'
    );
}


// ============================================================
// Validasi method
// ============================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    responseError(
        'Metode request tidak valid.'
    );
}


// ============================================================
// Ambil ID
// ============================================================
$id_transaksi_jenis =
    trim(
        $_POST['id_transaksi_jenis'] ?? ''
    );


if (
    $id_transaksi_jenis === '' ||
    !ctype_digit($id_transaksi_jenis)
) {

    responseError(
        'ID jenis transaksi tidak valid.'
    );
}


$id_transaksi_jenis =
    (int) $id_transaksi_jenis;


if ($id_transaksi_jenis <= 0) {

    responseError(
        'ID jenis transaksi tidak valid.'
    );
}


// ============================================================
// Query
// ============================================================
$sql = "
    SELECT
        id_transaksi_jenis,
        nama,
        kategori

    FROM transaksi_jenis

    WHERE id_transaksi_jenis = ?

    LIMIT 1
";


$stmt =
    mysqli_prepare(
        $Conn,
        $sql
    );


if (!$stmt) {

    responseError(
        'Gagal mempersiapkan query.'
    );
}


mysqli_stmt_bind_param(
    $stmt,
    'i',
    $id_transaksi_jenis
);


if (
    !mysqli_stmt_execute($stmt)
) {

    mysqli_stmt_close($stmt);

    responseError(
        'Gagal mengambil data jenis transaksi.'
    );
}


$result =
    mysqli_stmt_get_result($stmt);


if (
    !$result ||
    mysqli_num_rows($result) === 0
) {

    mysqli_stmt_close($stmt);

    responseError(
        'Jenis transaksi tidak ditemukan.'
    );
}


$data =
    mysqli_fetch_assoc($result);


mysqli_stmt_close($stmt);


// ============================================================
// Response
// ============================================================
echo json_encode([
    'status'   => 'success',
    'message'  => 'Kategori berhasil ditemukan.',
    'kategori' => $data['kategori'],
    'nama'     => $data['nama']
], JSON_UNESCAPED_UNICODE);

exit;
?>