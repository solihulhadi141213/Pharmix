<?php
    //---------------------------------------
    // Koneksi, Function Dan Session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    //---------------------------------------
    // Format Response
    header('Content-Type: application/json; charset=utf-8');

    //---------------------------------------
    // Default Response
    $response = [
        'results' => [],
        'pagination' => [
            'more' => false
        ]
    ];

    //---------------------------------------
    // Validasi Session
    if (empty($SessionIdAkses)) {
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //---------------------------------------
    // Tangkap Parameter
    $keyword = trim($_GET['q'] ?? '');
    $page    = (int) ($_GET['page'] ?? 1);
    $limit   = 10;

    //---------------------------------------
    // Validasi Parameter
    if ($page < 1) {
        $page = 1;
    }

    //---------------------------------------
    // Pagination
    $offset      = ($page - 1) * $limit;
    $keywordLike = '%' . $keyword . '%';

    //---------------------------------------
    // Hitung Total Data
    $sqlCount = "
        SELECT COUNT(*) AS total
        FROM referensi_route
        WHERE code_route LIKE ?
        OR display_route LIKE ?
    ";

    $stmtCount = $Conn->prepare($sqlCount);

    if (!$stmtCount) {
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmtCount->bind_param(
        "ss",
        $keywordLike,
        $keywordLike
    );

    //---------------------------------------
    // Eksekusi Hitung
    if (!$stmtCount->execute()) {
        $stmtCount->close();

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $resultCount = $stmtCount->get_result();
    $dataCount   = $resultCount->fetch_assoc();
    $totalData   = (int) ($dataCount['total'] ?? 0);

    $stmtCount->close();

    //---------------------------------------
    // Ambil Data
    $sql = "
        SELECT
            code_route,
            display_route,
            system_route
        FROM referensi_route
        WHERE code_route LIKE ?
        OR display_route LIKE ?
        ORDER BY display_route ASC
        LIMIT ? OFFSET ?
    ";

    $stmt = $Conn->prepare($sql);

    if (!$stmt) {
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt->bind_param(
        "ssii",
        $keywordLike,
        $keywordLike,
        $limit,
        $offset
    );

    //---------------------------------------
    // Eksekusi
    if (!$stmt->execute()) {
        $stmt->close();

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //---------------------------------------
    // Ambil Hasil
    $result  = $stmt->get_result();
    $results = [];

    while ($data = $result->fetch_assoc()) {

        $code_route    = trim($data['code_route'] ?? '');
        $display_route = trim($data['display_route'] ?? '');
        $system_route  = trim($data['system_route'] ?? '');

        $results[] = [
            'id'   => $code_route . '|' . $display_route . '|' . $system_route,
            'text' => $code_route . ' - ' . $display_route
        ];
    }

    $stmt->close();

    //---------------------------------------
    // Pagination More
    $dataTerambil = $offset + count($results);
    $more         = $dataTerambil < $totalData;

    //---------------------------------------
    // Response
    $response = [
        'results' => $results,
        'pagination' => [
            'more' => $more
        ]
    ];

    echo json_encode(
        $response,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
?>