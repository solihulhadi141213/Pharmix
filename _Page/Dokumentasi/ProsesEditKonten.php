<?php

// =========================================================
// DEBUG & RESPONSE CONFIGURATION
// =========================================================
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

date_default_timezone_set('Asia/Jakarta');

header('Content-Type: application/json; charset=utf-8');


// =========================================================
// FUNGSI RESPONSE
// =========================================================
function jsonResponse($status, $message, $data = null)
{
    $response = [
        "status"  => $status,
        "message" => $message
    ];

    if ($data !== null) {
        $response["data"] = $data;
    }

    echo json_encode(
        $response,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

    exit;
}


// =========================================================
// ERROR HANDLER
// =========================================================
set_error_handler(function ($severity, $message, $file, $line) {

    // Abaikan error yang tidak fatal
    if (!(error_reporting() & $severity)) {
        return false;
    }

    jsonResponse(
        false,
        "PHP Error: {$message}",
        [
            "file" => $file,
            "line" => $line,
            "severity" => $severity
        ]
    );
});


// =========================================================
// EXCEPTION HANDLER
// =========================================================
set_exception_handler(function ($exception) {

    jsonResponse(
        false,
        "PHP Exception: " . $exception->getMessage(),
        [
            "file" => $exception->getFile(),
            "line" => $exception->getLine()
        ]
    );
});


// =========================================================
// FATAL ERROR HANDLER
// =========================================================
register_shutdown_function(function () {

    $error = error_get_last();

    if ($error !== null) {

        $fatal_types = [
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_COMPILE_ERROR
        ];

        if (in_array($error['type'], $fatal_types, true)) {

            // Pastikan response belum terlanjur dikirim
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }

            echo json_encode([
                "status"  => false,
                "message" => "Fatal PHP Error",
                "debug"   => [
                    "type"    => $error['type'],
                    "message" => $error['message'],
                    "file"    => $error['file'],
                    "line"    => $error['line']
                ]
            ]);
        }
    }
});


// =========================================================
// KONEKSI
// =========================================================
include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";


// =========================================================
// VALIDASI CONNECTION
// =========================================================
if (!isset($Conn) || !($Conn instanceof mysqli)) {

    jsonResponse(
        false,
        "Koneksi database tidak tersedia."
    );
}


// =========================================================
// VALIDASI SESSION
// =========================================================
if (empty($SessionIdAkses)) {

    jsonResponse(
        false,
        "Sesi akses sudah berakhir. Silakan login kembali."
    );
}


// =========================================================
// VALIDASI METHOD
// =========================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    jsonResponse(
        false,
        "Metode request tidak valid."
    );
}


// =========================================================
// DEBUG DATA POST
// =========================================================
/*
 * Jangan tampilkan password/file secara sembarangan.
 * Tetapi untuk kasus ini kita tampilkan field POST
 * agar mudah mengetahui apakah data AJAX terkirim.
 */

$post_debug = $_POST;


// =========================================================
// VALIDASI ID KONTEN
// =========================================================
if (
    !isset($_POST['id_dokumentasi_konten']) ||
    trim($_POST['id_dokumentasi_konten']) === ''
) {

    jsonResponse(
        false,
        "ID Konten Tidak Boleh Kosong.",
        [
            "post" => $post_debug
        ]
    );
}


// =========================================================
// AMBIL ID
// =========================================================
$id_dokumentasi_konten = trim(
    $_POST['id_dokumentasi_konten']
);


// =========================================================
// VALIDASI ID
// =========================================================
if (!ctype_digit($id_dokumentasi_konten)) {

    jsonResponse(
        false,
        "ID konten dokumentasi tidak valid.",
        [
            "id_dokumentasi_konten" => $id_dokumentasi_konten
        ]
    );
}

$id_dokumentasi_konten = (int) $id_dokumentasi_konten;


// =========================================================
// TIPE KONTEN
// =========================================================
$tipe_konten = trim(
    $_POST['tipe_konten'] ?? ''
);


// =========================================================
// VALIDASI TIPE
// =========================================================
$tipe_valid = [
    "Text",
    "List Numbering",
    "List Bullet",
    "Local Image",
    "Url Image"
];

if (!in_array($tipe_konten, $tipe_valid, true)) {

    jsonResponse(
        false,
        "Tipe konten tidak valid.",
        [
            "tipe_konten" => $tipe_konten
        ]
    );
}


// =========================================================
// AMBIL DATA KONTEN LAMA
// =========================================================
$Qry = $Conn->prepare("
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

if (!$Qry) {

    jsonResponse(
        false,
        "Gagal menyiapkan query data lama.",
        [
            "mysql_error" => $Conn->error
        ]
    );
}


// =========================================================
// BIND ID
// =========================================================
if (!$Qry->bind_param(
    "i",
    $id_dokumentasi_konten
)) {

    $error = $Qry->error;

    $Qry->close();

    jsonResponse(
        false,
        "Gagal bind parameter query data lama.",
        [
            "error" => $error
        ]
    );
}


// =========================================================
// EXECUTE
// =========================================================
if (!$Qry->execute()) {

    $error = $Qry->error;

    $Qry->close();

    jsonResponse(
        false,
        "Gagal mengambil data konten lama.",
        [
            "error" => $error
        ]
    );
}


// =========================================================
// RESULT
// =========================================================
$Result = $Qry->get_result();

if (!$Result) {

    $error = $Qry->error;

    $Qry->close();

    jsonResponse(
        false,
        "Gagal mendapatkan hasil query.",
        [
            "error" => $error
        ]
    );
}


// =========================================================
// CEK DATA
// =========================================================
if ($Result->num_rows === 0) {

    $Qry->close();

    jsonResponse(
        false,
        "Konten dokumentasi tidak ditemukan.",
        [
            "id_dokumentasi_konten" => $id_dokumentasi_konten
        ]
    );
}


$DataLama = $Result->fetch_assoc();

$Qry->close();


// =========================================================
// DATA LAMA
// =========================================================
$tipe_lama        = $DataLama['tipe_konten'];
$text_lama        = $DataLama['text_konten'];
$list_lama        = $DataLama['list_konten'];
$local_image_lama = $DataLama['local_image_konten'];
$url_image_lama   = $DataLama['url_image_konten'];


// =========================================================
// DATA BARU
// =========================================================
$text_konten        = null;
$list_konten        = null;
$local_image_konten = null;
$url_image_konten   = null;


// =========================================================
// FOLDER UPLOAD
// =========================================================
$upload_dir = "../../assets/img/dokumentasi/";

$file_baru = null;


// =========================================================
// TEXT
// =========================================================
if ($tipe_konten === "Text") {

    $text_konten = trim(
        $_POST['text_konten'] ?? ''
    );

    // Bersihkan kondisi Quill kosong
    $text_check = strip_tags(
        str_replace(
            ['&nbsp;', ' '],
            '',
            $text_konten
        )
    );

    if (
        $text_konten === '' ||
        $text_check === '' ||
        $text_konten === '<p><br></p>'
    ) {

        jsonResponse(
            false,
            "Konten text tidak boleh kosong."
        );
    }
}


// =========================================================
// LIST
// =========================================================
elseif (
    $tipe_konten === "List Numbering" ||
    $tipe_konten === "List Bullet"
) {

    $list = $_POST['list_konten'] ?? [];

    if (!is_array($list)) {

        jsonResponse(
            false,
            "Format data list tidak valid."
        );
    }


    $list_bersih = [];

    foreach ($list as $index => $item) {

        $item = trim($item);

        if ($item !== '') {

            $list_bersih[] = $item;
        }
    }


    if (count($list_bersih) === 0) {

        jsonResponse(
            false,
            "Minimal satu item list harus diisi."
        );
    }


    $list_konten = json_encode(
        $list_bersih,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );


    if ($list_konten === false) {

        jsonResponse(
            false,
            "Gagal mengubah list menjadi JSON.",
            [
                "json_error" => json_last_error_msg()
            ]
        );
    }
}


// =========================================================
// LOCAL IMAGE
// =========================================================
elseif ($tipe_konten === "Local Image") {

    // ---------------------------------------------
    // Ada file baru
    // ---------------------------------------------
    if (
        isset($_FILES['local_image_konten']) &&
        $_FILES['local_image_konten']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        $file = $_FILES['local_image_konten'];


        // -----------------------------------------
        // Upload error
        // -----------------------------------------
        if ($file['error'] !== UPLOAD_ERR_OK) {

            jsonResponse(
                false,
                "Upload gambar gagal.",
                [
                    "upload_error" => $file['error']
                ]
            );
        }


        // -----------------------------------------
        // Maksimal 5 MB
        // -----------------------------------------
        $max_size = 5 * 1024 * 1024;

        if ($file['size'] > $max_size) {

            jsonResponse(
                false,
                "Ukuran gambar maksimal 5 MB."
            );
        }


        // -----------------------------------------
        // MIME
        // -----------------------------------------
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if ($finfo === false) {

            jsonResponse(
                false,
                "Tidak dapat memeriksa tipe file."
            );
        }


        $mime_type = finfo_file(
            $finfo,
            $file['tmp_name']
        );

        finfo_close($finfo);


        $allowed_mime = [
            "image/jpeg" => "jpg",
            "image/png"  => "png",
            "image/webp" => "webp",
            "image/gif"  => "gif"
        ];


        if (!isset($allowed_mime[$mime_type])) {

            jsonResponse(
                false,
                "Format gambar tidak didukung."
            );
        }


        // -----------------------------------------
        // Folder
        // -----------------------------------------
        if (!is_dir($upload_dir)) {

            if (!mkdir($upload_dir, 0755, true)) {

                jsonResponse(
                    false,
                    "Folder upload gambar tidak dapat dibuat."
                );
            }
        }


        // -----------------------------------------
        // Nama file
        // -----------------------------------------
        $extension = $allowed_mime[$mime_type];

        $nama_file =
            bin2hex(random_bytes(16))
            . "_"
            . date('YmdHis')
            . "."
            . $extension;


        $target_file =
            $upload_dir . $nama_file;


        // -----------------------------------------
        // Move
        // -----------------------------------------
        if (!move_uploaded_file(
            $file['tmp_name'],
            $target_file
        )) {

            jsonResponse(
                false,
                "Gagal menyimpan gambar baru."
            );
        }


        $local_image_konten = $nama_file;

        $file_baru = $target_file;
    }


    // ---------------------------------------------
    // Tidak ada file baru
    // ---------------------------------------------
    else {

        if (!empty($local_image_lama)) {

            $local_image_konten =
                $local_image_lama;

        } else {

            jsonResponse(
                false,
                "Gambar belum tersedia. Silakan pilih gambar."
            );
        }
    }
}


// =========================================================
// URL IMAGE
// =========================================================
elseif ($tipe_konten === "Url Image") {

    $url_image_konten = trim(
        $_POST['url_image_konten'] ?? ''
    );


    if ($url_image_konten === '') {

        jsonResponse(
            false,
            "URL gambar wajib diisi."
        );
    }


    if (!filter_var(
        $url_image_konten,
        FILTER_VALIDATE_URL
    )) {

        jsonResponse(
            false,
            "Format URL gambar tidak valid."
        );
    }


    $scheme = strtolower(
        parse_url(
            $url_image_konten,
            PHP_URL_SCHEME
        )
    );


    if (!in_array(
        $scheme,
        ['http', 'https'],
        true
    )) {

        jsonResponse(
            false,
            "URL hanya boleh menggunakan HTTP atau HTTPS."
        );
    }
}


// =========================================================
// UPDATE DATABASE
// =========================================================
$QryUpdate = $Conn->prepare("
    UPDATE dokumentasi_konten
    SET
        tipe_konten = ?,
        text_konten = ?,
        list_konten = ?,
        local_image_konten = ?,
        url_image_konten = ?
    WHERE
        id_dokumentasi_konten = ?
    LIMIT 1
");


if (!$QryUpdate) {

    // Hapus file baru
    if (
        !empty($file_baru) &&
        file_exists($file_baru)
    ) {

        unlink($file_baru);
    }


    jsonResponse(
        false,
        "Gagal menyiapkan query update.",
        [
            "mysql_error" => $Conn->error
        ]
    );
}


// =========================================================
// BIND PARAMETER
// =========================================================
/*
 * 5 parameter string + 1 integer
 *
 * s s s s s i
 *
 */
if (!$QryUpdate->bind_param(
    "sssssi",
    $tipe_konten,
    $text_konten,
    $list_konten,
    $local_image_konten,
    $url_image_konten,
    $id_dokumentasi_konten
)) {

    $error = $QryUpdate->error;

    $QryUpdate->close();


    // Hapus file baru
    if (
        !empty($file_baru) &&
        file_exists($file_baru)
    ) {

        unlink($file_baru);
    }


    jsonResponse(
        false,
        "Gagal bind parameter update.",
        [
            "error" => $error
        ]
    );
}


// =========================================================
// EXECUTE UPDATE
// =========================================================
if (!$QryUpdate->execute()) {

    $error = $QryUpdate->error;

    $QryUpdate->close();


    // Hapus file baru
    if (
        !empty($file_baru) &&
        file_exists($file_baru)
    ) {

        unlink($file_baru);
    }


    jsonResponse(
        false,
        "Gagal memperbarui konten dokumentasi.",
        [
            "error" => $error
        ]
    );
}


$affected_rows =
    $QryUpdate->affected_rows;


$QryUpdate->close();


// =========================================================
// HAPUS GAMBAR LAMA
// =========================================================
if (
    $tipe_lama === "Local Image" &&
    $tipe_konten === "Local Image" &&
    !empty($local_image_lama) &&
    !empty($file_baru) &&
    $local_image_konten !== $local_image_lama
) {

    $file_lama =
        $upload_dir . $local_image_lama;


    if (file_exists($file_lama)) {

        unlink($file_lama);
    }
}


// =========================================================
// SUCCESS
// =========================================================
jsonResponse(
    true,
    "Konten dokumentasi berhasil diperbarui.",
    [
        "id_dokumentasi_konten" => $id_dokumentasi_konten,
        "tipe_konten"           => $tipe_konten,
        "affected_rows"         => $affected_rows
    ]
);