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
    $keyword = trim($_GET['keyword'] ?? '');
    $page    = (int) ($_GET['page'] ?? 1);
    $limit   = (int) ($_GET['limit'] ?? 10);

    if ($page < 1) {
        $page = 1;
    }

    if ($limit < 1 || $limit > 50) {
        $limit = 10;
    }

    $offset      = ($page - 1) * $limit;
    $keywordLike = '%' . $keyword . '%';

    //---------------------------------------
    // Hitung Total Data
    $sqlCount = "
        SELECT COUNT(*) AS total
        FROM referensi_numerator
        WHERE unit LIKE ?
        OR code_numerator LIKE ?
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
            unit,
            code_numerator
        FROM referensi_numerator
        WHERE unit LIKE ?
        OR code_numerator LIKE ?
        ORDER BY unit ASC
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

        $unit           = trim($data['unit'] ?? '');
        $code_numerator = trim($data['code_numerator'] ?? '');

        $results[] = [
            'id'   => $code_numerator . '|' . $unit,
            'text' => $code_numerator . ' - ' . $unit
        ];
    }

    $stmt->close();

    //---------------------------------------
    // Pagination
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