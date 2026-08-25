<?php
    // Koneksi
    include "../../_Config/Connection.php";

    // Default JSON
    header('Content-Type: application/json; charset=utf-8');


    // Parameter Select2
    $keyword = isset($_GET['q'])
        ? trim($_GET['q'])
        : '';

    $page = isset($_GET['page'])
        ? (int) $_GET['page']
        : 1;

    if ($page < 1) {
        $page = 1;
    }

    // Konfigurasi Pagination
    $limit  = 20;
    $offset = ($page - 1) * $limit;


    // Escape Keyword
    $keywordSql = $Conn->real_escape_string($keyword);

    // Query
    $sql = "
        SELECT
            id_transaksi_jenis,
            nama,
            kategori
        FROM transaksi_jenis
        WHERE 1=1
    ";


    // Filter Keyword
    if ($keyword !== '') {
        $sql .= "
            AND (
                nama LIKE '%{$keywordSql}%'
                OR kategori LIKE '%{$keywordSql}%'
            )
        ";
    }

    // Urutan
    $sql .= "ORDER BY nama ASC LIMIT {$limit} OFFSET {$offset}";

    // Eksekusi
    $result = $Conn->query($sql);

    // Response
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'id'   => $row['id_transaksi_jenis'],
                'text' => $row['nama'] . ' (' . $row['kategori'] . ')'
            ];
        }
    }


    // Cek halaman berikutnya
    $sqlCount = "
        SELECT COUNT(*) AS total
        FROM transaksi_jenis
        WHERE 1=1
    ";
    if ($keyword !== '') {
        $sqlCount .= "
            AND (
                nama LIKE '%{$keywordSql}%'
                OR kategori LIKE '%{$keywordSql}%'
            )
        ";
    }
    $resultCount = $Conn->query($sqlCount);
    $total = 0;
    if ($resultCount) {
        $rowCount = $resultCount->fetch_assoc();
        $total = (int) $rowCount['total'];
    }
    $more = ($offset + $limit) < $total;


    // ======================================================
    // Output JSON
    // ======================================================

    echo json_encode([
        'results' => $data,
        'pagination' => [
            'more' => $more
        ]
    ], JSON_UNESCAPED_UNICODE);