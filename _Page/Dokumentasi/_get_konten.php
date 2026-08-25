<?php

include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";

date_default_timezone_set('Asia/Jakarta');

header('Content-Type: application/json; charset=utf-8');

$response = [
    "status"  => "error",
    "message" => "Belum ada proses."
];

// ========================================
// VALIDASI SESSION
// ========================================

if (empty($SessionIdAkses)) {

    $response = [
        "status"  => "error",
        "message" => "Sesi akses sudah berakhir. Silakan login kembali."
    ];

    echo json_encode($response);
    exit;
}


// ========================================
// VALIDASI ID
// ========================================

if (
    !isset($_POST['id_dokumentasi_konten']) ||
    empty($_POST['id_dokumentasi_konten'])
) {

    $response = [
        "status"  => "error",
        "message" => "ID konten dokumentasi tidak boleh kosong."
    ];

    echo json_encode($response);
    exit;
}


$id_dokumentasi_konten = validateAndSanitizeInput(
    $_POST['id_dokumentasi_konten']
);


// ========================================
// QUERY
// ========================================

$stmt = $Conn->prepare("
    SELECT
        id_dokumentasi_konten,
        id_dokumentasi,
        sequence,
        tipe_konten,
        text_konten,
        list_konten,
        local_image_konten,
        url_image_konten
    FROM dokumentasi_konten
    WHERE id_dokumentasi_konten = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $id_dokumentasi_konten
);


if (!$stmt->execute()) {

    $error = $stmt->error;

    $stmt->close();

    $response = [
        "status"  => "error",
        "message" => "Gagal mengambil data konten. Pesan: " . $error
    ];

    echo json_encode($response);
    exit;
}


$result = $stmt->get_result();

if ($result->num_rows === 0) {

    $stmt->close();

    $response = [
        "status"  => "error",
        "message" => "Konten dokumentasi tidak ditemukan."
    ];

    echo json_encode($response);
    exit;
}


$data = $result->fetch_assoc();

$stmt->close();


// ========================================
// PARSE LIST JSON
// ========================================

$list_konten = [];

if (!empty($data['list_konten'])) {

    $decoded = json_decode(
        $data['list_konten'],
        true
    );

    if (
        json_last_error() === JSON_ERROR_NONE &&
        is_array($decoded)
    ) {
        $list_konten = $decoded;
    }
}


// ========================================
// RESPONSE
// ========================================

$response = [
    "status"  => "success",
    "message" => "Data konten ditemukan.",
    "data"    => [
        "id_dokumentasi_konten" => $data['id_dokumentasi_konten'],
        "id_dokumentasi"        => $data['id_dokumentasi'],
        "sequence"              => $data['sequence'],
        "tipe_konten"           => $data['tipe_konten'],
        "text_konten"           => $data['text_konten'],
        "list_konten"           => $list_konten,
        "local_image_konten"    => $data['local_image_konten'],
        "url_image_konten"      => $data['url_image_konten']
    ]
];

echo json_encode(
    $response,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);

exit;