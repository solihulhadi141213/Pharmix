<?php

    // =========================================================
    // KONEKSI & KONFIGURASI
    // =========================================================
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Time Zone
    date_default_timezone_set('Asia/Jakarta');

    // Response default
    $response = [
        "status"  => false,
        "message" => "Belum ada proses yang dilakukan."
    ];

    // =========================================================
    // VALIDASI SESSION
    // =========================================================
    if (empty($SessionIdAkses)) {
        $response = [
            "status"  => false,
            "message" => "Sesi akses sudah berakhir. Silakan login kembali."
        ];

        echo json_encode($response);
        exit;
    }

    // =========================================================
    // VALIDASI REQUEST
    // =========================================================
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response = [
            "status"  => false,
            "message" => "Metode request tidak valid."
        ];

        echo json_encode($response);
        exit;
    }

    // =========================================================
    // AMBIL DATA DASAR
    // =========================================================

    $id_dokumentasi = $_POST['id_dokumentasi'] ?? '';
    $tipe_konten    = $_POST['tipe_konten'] ?? '';

    $id_dokumentasi = validateAndSanitizeInput($id_dokumentasi);
    $tipe_konten    = validateAndSanitizeInput($tipe_konten);

    // =========================================================
    // VALIDASI ID DOKUMENTASI
    // =========================================================

    if (empty($id_dokumentasi) || !is_numeric($id_dokumentasi)) {
        $response = [
            "status"  => false,
            "message" => "ID dokumentasi tidak valid."
        ];

        echo json_encode($response);
        exit;
    }

    $id_dokumentasi = (int) $id_dokumentasi;

    // =========================================================
    // VALIDASI TIPE KONTEN
    // =========================================================

    $tipe_valid = [
        "Text",
        "List Numbering",
        "List Bullet",
        "Local Image",
        "Url Image"
    ];

    if (!in_array($tipe_konten, $tipe_valid, true)) {
        $response = [
            "status"  => false,
            "message" => "Tipe konten tidak valid."
        ];

        echo json_encode($response);
        exit;
    }

    // =========================================================
    // CEK DOKUMENTASI
    // =========================================================

    $Qry = $Conn->prepare("
        SELECT id_dokumentasi
        FROM dokumentasi
        WHERE id_dokumentasi = ?
        LIMIT 1
    ");

    $Qry->bind_param("i", $id_dokumentasi);

    if (!$Qry->execute()) {

        $error = $Qry->error;

        $Qry->close();

        $response = [
            "status"  => false,
            "message" => "Gagal memeriksa dokumentasi. Pesan: " . $error
        ];

        echo json_encode($response);
        exit;
    }

    $Result = $Qry->get_result();

    if ($Result->num_rows === 0) {

        $Qry->close();

        $response = [
            "status"  => false,
            "message" => "Dokumentasi tidak ditemukan."
        ];

        echo json_encode($response);
        exit;
    }

    $Qry->close();

    // =========================================================
    // TENTUKAN SEQUENCE
    // =========================================================

    $sequence = 1;

    $QrySequence = $Conn->prepare("
        SELECT COALESCE(MAX(sequence), 0) + 1 AS next_sequence
        FROM dokumentasi_konten
        WHERE id_dokumentasi = ?
    ");

    $QrySequence->bind_param("i", $id_dokumentasi);

    if (!$QrySequence->execute()) {

        $error = $QrySequence->error;

        $QrySequence->close();

        $response = [
            "status"  => false,
            "message" => "Gagal menentukan urutan konten. Pesan: " . $error
        ];

        echo json_encode($response);
        exit;
    }

    $ResultSequence = $QrySequence->get_result();
    $DataSequence   = $ResultSequence->fetch_assoc();

    if (!empty($DataSequence['next_sequence'])) {
        $sequence = (int) $DataSequence['next_sequence'];
    }

    $QrySequence->close();

    // =========================================================
    // INISIALISASI FIELD
    // =========================================================

    $text_konten       = null;
    $list_konten       = null;
    $local_image_konten = null;
    $url_image_konten   = null;

    // =========================================================
    // TIPE TEXT
    // =========================================================

    if ($tipe_konten === "Text") {

        $text_konten = $_POST['text_konten'] ?? '';

        // Jangan trim HTML secara berlebihan.
        // HTML dari Quill dipertahankan.
        $text_konten = trim($text_konten);

        if ($text_konten === '' || $text_konten === '<p><br></p>') {

            $response = [
                "status"  => false,
                "message" => "Konten text tidak boleh kosong."
            ];

            echo json_encode($response);
            exit;
        }
    }

    // =========================================================
    // TIPE LIST
    // =========================================================

    elseif (
        $tipe_konten === "List Numbering" ||
        $tipe_konten === "List Bullet"
    ) {

        /*
         * Diharapkan dari form:
         *
         * list_konten[]
         * list_konten[]
         * list_konten[]
         */

        $list = $_POST['list_konten'] ?? [];

        if (!is_array($list)) {
            $list = [];
        }

        // Bersihkan item kosong
        $list_bersih = [];

        foreach ($list as $item) {

            $item = trim($item);

            if ($item !== '') {
                $list_bersih[] = $item;
            }
        }

        if (count($list_bersih) === 0) {

            $response = [
                "status"  => false,
                "message" => "Minimal satu item list harus diisi."
            ];

            echo json_encode($response);
            exit;
        }

        // Encode menjadi JSON
        $list_konten = json_encode(
            $list_bersih,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($list_konten === false) {

            $response = [
                "status"  => false,
                "message" => "Gagal mengubah data list menjadi JSON."
            ];

            echo json_encode($response);
            exit;
        }
    }

    // =========================================================
    // TIPE LOCAL IMAGE
    // =========================================================

    elseif ($tipe_konten === "Local Image") {

        if (
            !isset($_FILES['local_image_konten']) ||
            $_FILES['local_image_konten']['error'] !== UPLOAD_ERR_OK
        ) {

            $response = [
                "status"  => false,
                "message" => "File gambar belum dipilih."
            ];

            echo json_encode($response);
            exit;
        }

        $file = $_FILES['local_image_konten'];

        // -----------------------------------------------------
        // BATAS UKURAN
        // 5 MB
        // -----------------------------------------------------

        $max_size = 5 * 1024 * 1024;

        if ($file['size'] > $max_size) {

            $response = [
                "status"  => false,
                "message" => "Ukuran gambar maksimal 5 MB."
            ];

            echo json_encode($response);
            exit;
        }

        // -----------------------------------------------------
        // VALIDASI MIME TYPE
        // -----------------------------------------------------

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if ($finfo === false) {

            $response = [
                "status"  => false,
                "message" => "Tidak dapat memeriksa tipe file."
            ];

            echo json_encode($response);
            exit;
        }

        $mime_type = finfo_file($finfo, $file['tmp_name']);

        finfo_close($finfo);

        $allowed_mime = [
            "image/jpeg" => "jpg",
            "image/png"  => "png",
            "image/webp" => "webp",
            "image/gif"  => "gif"
        ];

        if (!array_key_exists($mime_type, $allowed_mime)) {

            $response = [
                "status"  => false,
                "message" => "Format gambar tidak didukung. Gunakan JPG, PNG, WEBP atau GIF."
            ];

            echo json_encode($response);
            exit;
        }

        // -----------------------------------------------------
        // BUAT FOLDER
        // -----------------------------------------------------

        $upload_dir = "../../assets/img/dokumentasi/";

        if (!is_dir($upload_dir)) {

            if (!mkdir($upload_dir, 0755, true)) {

                $response = [
                    "status"  => false,
                    "message" => "Folder upload gambar tidak dapat dibuat."
                ];

                echo json_encode($response);
                exit;
            }
        }

        // -----------------------------------------------------
        // NAMA FILE
        // -----------------------------------------------------

        $extension = $allowed_mime[$mime_type];

        $nama_file = bin2hex(random_bytes(16))
                   . "_"
                   . date('YmdHis')
                   . "."
                   . $extension;

        $target_file = $upload_dir . $nama_file;

        // -----------------------------------------------------
        // PINDAHKAN FILE
        // -----------------------------------------------------

        if (!move_uploaded_file($file['tmp_name'], $target_file)) {

            $response = [
                "status"  => false,
                "message" => "Gagal menyimpan file gambar."
            ];

            echo json_encode($response);
            exit;
        }

        $local_image_konten = $nama_file;
    }

    // =========================================================
    // TIPE URL IMAGE
    // =========================================================

    elseif ($tipe_konten === "Url Image") {

        $url_image_konten = trim($_POST['url_image_konten'] ?? '');

        if ($url_image_konten === '') {

            $response = [
                "status"  => false,
                "message" => "URL gambar wajib diisi."
            ];

            echo json_encode($response);
            exit;
        }

        // Validasi URL
        if (!filter_var($url_image_konten, FILTER_VALIDATE_URL)) {

            $response = [
                "status"  => false,
                "message" => "Format URL gambar tidak valid."
            ];

            echo json_encode($response);
            exit;
        }

        // Hanya izinkan HTTP/HTTPS
        $scheme = strtolower(parse_url($url_image_konten, PHP_URL_SCHEME));

        if (!in_array($scheme, ['http', 'https'], true)) {

            $response = [
                "status"  => false,
                "message" => "URL hanya boleh menggunakan HTTP atau HTTPS."
            ];

            echo json_encode($response);
            exit;
        }
    }

    // =========================================================
    // SIMPAN DATABASE
    // =========================================================

    $QryInsert = $Conn->prepare("
        INSERT INTO dokumentasi_konten (
            id_dokumentasi,
            sequence,
            tipe_konten,
            text_konten,
            list_konten,
            local_image_konten,
            url_image_konten
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$QryInsert) {

        // Hapus file jika sebelumnya berhasil diupload
        if (
            $tipe_konten === "Local Image" &&
            !empty($local_image_konten) &&
            file_exists($upload_dir . $local_image_konten)
        ) {
            unlink($upload_dir . $local_image_konten);
        }

        $response = [
            "status"  => false,
            "message" => "Gagal menyiapkan query database. Pesan: " . $Conn->error
        ];

        echo json_encode($response);
        exit;
    }

    $QryInsert->bind_param(
        "iisssss",
        $id_dokumentasi,
        $sequence,
        $tipe_konten,
        $text_konten,
        $list_konten,
        $local_image_konten,
        $url_image_konten
    );

    if (!$QryInsert->execute()) {

        $error = $QryInsert->error;

        $QryInsert->close();

        // Hapus file jika database gagal
        if (
            $tipe_konten === "Local Image" &&
            !empty($local_image_konten) &&
            file_exists($upload_dir . $local_image_konten)
        ) {
            unlink($upload_dir . $local_image_konten);
        }

        $response = [
            "status"  => false,
            "message" => "Gagal menyimpan konten dokumentasi. Pesan: " . $error
        ];

        echo json_encode($response);
        exit;
    }

    // ID konten baru
    $id_dokumentasi_konten = $QryInsert->insert_id;

    $QryInsert->close();

    // =========================================================
    // RESPONSE
    // =========================================================

    $response = [
        "status" => true,
        "message" => "Konten dokumentasi berhasil ditambahkan.",
        "data" => [
            "id_dokumentasi_konten" => $id_dokumentasi_konten,
            "id_dokumentasi"        => $id_dokumentasi,
            "sequence"              => $sequence,
            "tipe_konten"            => $tipe_konten
        ]
    ];

    echo json_encode($response);
    exit;

?>