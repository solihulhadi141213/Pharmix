<?php
    // Koneksi dan session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Validasi Sesi Akses
    if (empty($SessionIdAkses)) {
        echo json_encode(['results' => [], 'pagination' => ['more' => false]]);
        exit;
    }

    // Tangkap parameter dari Select2
    $keyword = $_POST['keyword'] ?? '';
    $page    = (int)($_POST['page'] ?? 1);
    $limit   = 10; // Batas 10 data per load
    $offset  = ($page - 1) * $limit;

    // Filter pencarian berdasarkan nama atau id_pasien (No RM)
    $whereClause = "";
    $params = [];
    $types = "";

    if (!empty($keyword)) {
        $whereClause = "WHERE nama LIKE ? OR id_pasien LIKE ?";
        $searchKey = "%$keyword%";
        $params = [$searchKey, $searchKey];
        $types = "ss";
    }

    // Hitung total data yang cocok untuk logika pagination infinite scroll
    $countQuery = "SELECT COUNT(*) as jml FROM anggota " . $whereClause;
    $stmtCount = mysqli_prepare($Conn, $countQuery);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmtCount, $types, ...$params);
    }
    mysqli_stmt_execute($stmtCount);
    $totalData = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCount))['jml'];
    mysqli_stmt_close($stmtCount);

    // Query data utama: 10 data terbaru / tergeser berdasarkan offset
    $dataQuery = "SELECT id_anggota, id_pasien, nama FROM anggota " . $whereClause . " ORDER BY id_anggota DESC LIMIT ?, ?";
    $stmtData = mysqli_prepare($Conn, $dataQuery);

    if (!empty($params)) {
        $limitParams = array_merge($params, [$offset, $limit]);
        $limitTypes = $types . "ii";
        mysqli_stmt_bind_param($stmtData, $limitTypes, ...$limitParams);
    } else {
        mysqli_stmt_bind_param($stmtData, "ii", $offset, $limit);
    }

    mysqli_stmt_execute($stmtData);
    $resultData = mysqli_stmt_get_result($stmtData);

    $results = [];
    while ($row = mysqli_fetch_assoc($resultData)) {
        // Format teks yang akan tampil di pilihan dropdown Select2
        $displayText = $row['id_pasien'] . ' - ' . $row['nama'];
        
        $results[] = [
            'id'   => $row['id_anggota'], // Nilai value yang disimpan (id_anggota)
            'text' => $displayText        // Teks yang dilihat user
        ];
    }
    mysqli_stmt_close($stmtData);

    // Tentukan apakah masih ada halaman selanjutnya untuk infinite scroll
    $more = ($offset + $limit) < $totalData;

    // Kembalikan data dalam format JSON yang dipahami oleh Select2
    echo json_encode([
        'results' => $results,
        'pagination' => [
            'more' => $more
        ]
    ]);
?>