<?php
    // ==== KONFIGURASI
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // ==== RESPONSE JSON
    header('Content-Type: application/json; charset=utf-8');

    // ==== FUNGSI RESPONSE
    function response_json($status, $message, $data = []) {
        echo json_encode(array_merge([
            'status'  => $status,
            'message' => $message
        ], $data), JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ==== VALIDASI REQUEST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        response_json(false, 'Metode request tidak valid.');
    }

    // ==== VALIDASI SESSION
    if (empty($SessionIdAkses)) {
        response_json(false, 'Sesi akses sudah berakhir. Silakan login kembali.');
    }

    // ==== AMBIL DATA POST
    $judul = isset($_POST['judul_dokumentasi']) ? trim($_POST['judul_dokumentasi']) : '';
    $deskripsi = isset($_POST['deskripsi_dokumentasi']) ? trim($_POST['deskripsi_dokumentasi']) : '';
    $tags = isset($_POST['tags_dokumentasi']) ? $_POST['tags_dokumentasi'] : [];

    // ==== VALIDASI JUDUL
    if ($judul === '') {
        response_json(false, 'Judul dokumentasi wajib diisi.');
    }
    if (mb_strlen($judul) > 255) {
        response_json(false, 'Judul dokumentasi maksimal 255 karakter.');
    }

    // ==== VALIDASI DESKRIPSI
    if (mb_strlen($deskripsi) > 255) {
        response_json(false, 'Deskripsi maksimal 255 karakter.');
    }

    // ==== VALIDASI TAG
    if (!is_array($tags) || count($tags) === 0) {
        response_json(false, 'Minimal satu tag harus dipilih.');
    }

    // ==== BERSIHKAN DAN NORMALISASI TAG
    $tags_clean = [];
    foreach ($tags as $tag) {
        if (!is_string($tag)) {
            continue;
        }
        $tag = trim($tag);
        if ($tag === '') {
            continue;
        }
        $tag = preg_replace('/\s+/', ' ', $tag);
        if (mb_strlen($tag) > 255) {
            continue;
        }
        //------ Normalisasi huruf untuk perbandingan (PHP, php, Php dianggap sama)
        $tag_key = mb_strtolower($tag, 'UTF-8');
        if (!isset($tags_clean[$tag_key])) {
            $tags_clean[$tag_key] = $tag;
        }
    }

    // ==== UBAH ASSOCIATIVE ARRAY MENJADI INDEX ARRAY
    $tags_clean = array_values($tags_clean);

    // ==== VALIDASI HASIL TAG
    if (count($tags_clean) === 0) {
        response_json(false, 'Tag yang diberikan tidak valid.');
    }

    // ==== DATA USER
    $id_akses = (int)$SessionIdAkses;
    $author_name = '';

    if (isset($SessionNamaAkses)) {
        $author_name = trim($SessionNamaAkses);
    }

    //------ Jika nama user tidak tersedia di session, ambil langsung dari tabel akses
    if ($author_name === '') {
        $sql_user = "
            SELECT nama_akses
            FROM akses
            WHERE id_akses = ?
            LIMIT 1
        ";
        $stmt_user = $Conn->prepare($sql_user);
        if (!$stmt_user) {
            response_json(false, 'Gagal mempersiapkan data pengguna.');
        }
        $stmt_user->bind_param('i', $id_akses);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();
        if ($result_user->num_rows > 0) {
            $data_user = $result_user->fetch_assoc();
            $author_name = trim($data_user['nama_akses']);
        }
        $stmt_user->close();
    }

    // ==== VALIDASI AUTHOR
    if ($author_name === '') {
        $author_name = 'System';
    }

    // ==== WAKTU
    $datetime = date('Y-m-d H:i:s');

    // ==== MULAI TRANSACTION
    $Conn->begin_transaction();

    try {
        // ==== INSERT DOKUMENTASI
        $sql_dokumentasi = "
            INSERT INTO dokumentasi (
                judul,
                deskripsi,
                status,
                id_akses,
                author_name,
                creat_at,
                update_at
            ) VALUES (
                ?,
                ?,
                'Draft',
                ?,
                ?,
                ?,
                ?
            )
        ";
        $stmt_dokumentasi = $Conn->prepare($sql_dokumentasi);
        if (!$stmt_dokumentasi) {
            throw new Exception('Gagal mempersiapkan query dokumentasi.');
        }
        $stmt_dokumentasi->bind_param(
            'ssisss',
            $judul,
            $deskripsi,
            $id_akses,
            $author_name,
            $datetime,
            $datetime
        );
        if (!$stmt_dokumentasi->execute()) {
            throw new Exception('Gagal menyimpan dokumentasi.');
        }

        // ==== ID DOKUMENTASI
        $id_dokumentasi = $Conn->insert_id;
        $stmt_dokumentasi->close();

        // ==== INSERT TAG
        $sql_tag = "
            INSERT INTO dokumentasi_tags (
                id_dokumentasi,
                tags
            ) VALUES (
                ?,
                ?
            )
        ";
        $stmt_tag = $Conn->prepare($sql_tag);
        if (!$stmt_tag) {
            throw new Exception('Gagal mempersiapkan query tag.');
        }
        foreach ($tags_clean as $tag) {
            $stmt_tag->bind_param('is', $id_dokumentasi, $tag);
            if (!$stmt_tag->execute()) {
                throw new Exception('Gagal menyimpan tag dokumentasi.');
            }
        }
        $stmt_tag->close();

        // ==== COMMIT
        $Conn->commit();

        // ==== RESPONSE BERHASIL
        response_json(
            true,
            'Dokumentasi berhasil ditambahkan.',
            [
                'id_dokumentasi' => $id_dokumentasi,
                'jumlah_tag'     => count($tags_clean)
            ]
        );

    } catch (Throwable $e) {
        // ==== ROLLBACK
        $Conn->rollback();

        //------ LOG ERROR
        error_log('ProsesTambah Dokumentasi: ' . $e->getMessage());

        // ==== RESPONSE ERROR
        response_json(false, 'Dokumentasi gagal disimpan. Silakan coba kembali.');
    }