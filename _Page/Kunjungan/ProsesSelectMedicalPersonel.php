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

    // Filter pencarian berdasarkan medicalPersonelCode atau medicalPersonelName
    $whereClause = "WHERE medicalPersonelStatus = 'Active'"; // Opsional: Hanya tampilkan yang aktif
    $params = [];
    $types = "";

    if (!empty($keyword)) {
        $whereClause .= " AND (medicalPersonelCode LIKE ? OR medicalPersonelName LIKE ?)";
        $searchKey = "%$keyword%";
        $params = [$searchKey, $searchKey];
        $types = "ss";
    }

    // Hitung total data untuk pagination infinite scroll
    $countQuery = "SELECT COUNT(*) as jml FROM medical_personel " . $whereClause;
    $stmtCount = mysqli_prepare($Conn, $countQuery);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmtCount, $types, ...$params);
    }
    mysqli_stmt_execute($stmtCount);
    $totalData = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCount))['jml'];
    mysqli_stmt_close($stmtCount);

    // Query data utama: 10 data terbaru / tergeser berdasarkan offset
    $dataQuery = "SELECT medicalPersonelId, medicalPersonelCode, medicalPersonelName, medicalPersonelCategory FROM medical_personel " . $whereClause . " ORDER BY medicalPersonelId DESC LIMIT ?, ?";
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
        $results[] = [
            'id'       => $row['medicalPersonelId'],
            'code'     => $row['medicalPersonelCode'],
            'name'     => $row['medicalPersonelName'],
            'category' => $row['medicalPersonelCategory']
        ];
    }
    mysqli_stmt_close($stmtData);

    // Cek apakah masih ada halaman selanjutnya untuk infinite scroll
    $more = ($offset + $limit) < $totalData;

    // Kembalikan data dalam format JSON
    echo json_encode([
        'results' => $results,
        'pagination' => [
            'more' => $more
        ]
    ]);
?>