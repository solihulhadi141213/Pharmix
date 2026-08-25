<?php

    // =========================================================
    // KONFIGURASI
    // =========================================================

    date_default_timezone_set('Asia/Jakarta');

    // Koneksi database
    include "../../_Config/Connection.php";


    // =========================================================
    // RESPONSE JSON
    // =========================================================

    header('Content-Type: application/json; charset=utf-8');


    // =========================================================
    // VALIDASI REQUEST
    // =========================================================

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        echo json_encode([
            "status"  => false,
            "message" => "Metode request tidak valid.",
            "results" => []
        ]);

        exit;
    }


    // =========================================================
    // KEYWORD
    // =========================================================

    $keyword = isset($_POST['keyword'])
        ? trim($_POST['keyword'])
        : '';


    // =========================================================
    // QUERY
    // =========================================================

    try {

        if ($keyword === '') {

            /*
             * Jika keyword kosong,
             * tampilkan tag terbaru/alfabetis.
             */

            $sql = "
                SELECT DISTINCT
                    TRIM(tags) AS tags
                FROM dokumentasi_tags
                WHERE tags IS NOT NULL
                AND TRIM(tags) <> ''
                ORDER BY tags ASC
                LIMIT 50
            ";

            $stmt = $Conn->prepare($sql);

        } else {

            /*
             * Jika ada keyword,
             * cari tag yang mengandung keyword.
             */

            $sql = "
                SELECT DISTINCT
                    TRIM(tags) AS tags
                FROM dokumentasi_tags
                WHERE tags IS NOT NULL
                AND TRIM(tags) <> ''
                AND tags LIKE ?
                ORDER BY tags ASC
                LIMIT 50
            ";

            $stmt = $Conn->prepare($sql);

            $search = '%' . $keyword . '%';

            $stmt->bind_param(
                "s",
                $search
            );
        }


        // =====================================================
        // EKSEKUSI
        // =====================================================

        if (!$stmt->execute()) {

            throw new Exception(
                "Gagal menjalankan query database."
            );
        }


        // =====================================================
        // AMBIL DATA
        // =====================================================

        $result = $stmt->get_result();

        $data = [];

        while ($row = $result->fetch_assoc()) {

            $tag = trim($row['tags']);

            if ($tag === '') {
                continue;
            }

            $data[] = [
                "id"   => $tag,
                "text" => $tag
            ];
        }


        // =====================================================
        // TUTUP STATEMENT
        // =====================================================

        $stmt->close();


        // =====================================================
        // RESPONSE
        // =====================================================

        echo json_encode([
            "status"  => true,
            "message" => "Data tag berhasil ditemukan.",
            "results" => $data
        ], JSON_UNESCAPED_UNICODE);

        exit;


    } catch (Throwable $e) {

        // =====================================================
        // ERROR
        // =====================================================

        echo json_encode([
            "status"  => false,
            "message" => "Terjadi kesalahan saat mengambil data tag.",
            "results" => []
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
?>