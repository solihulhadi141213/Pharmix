<?php
    //---------------------------------------
    // KONFIGURASI
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');

    //---------------------------------------
    // RESPONSE DEFAULT
    $response = [
        'results'    => [],
        'pagination' => [
            'more' => false
        ]
    ];

    //---------------------------------------
    // VALIDASI SESSION
    if (empty($SessionIdAkses)) {
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //---------------------------------------
    // TANGKAP PARAMETER
    $keyword = trim($_GET['keyword'] ?? '');
    $icd     = trim($_GET['icd'] ?? 'ICD10');
    $page    = (int) ($_GET['page'] ?? 1);
    $limit   = (int) ($_GET['limit'] ?? 10);

    //---------------------------------------
    // VALIDASI PARAMETER
    if ($page < 1) {
        $page = 1;
    }

    if ($limit < 1 || $limit > 50) {
        $limit = 10;
    }

    if (!in_array($icd, ['ICD9', 'ICD10', 'ICD11'], true)) {
        $icd = 'ICD10';
    }

    $offset      = ($page - 1) * $limit;
    $keywordLike = '%' . $keyword . '%';

    //---------------------------------------
    // HITUNG TOTAL DATA
    $sqlCount = "
        SELECT COUNT(*) AS total
        FROM icd
        WHERE icd = ?
        AND (
            kode LIKE ?
            OR long_des LIKE ?
            OR short_des LIKE ?
        )
    ";

    $stmtCount = $Conn->prepare($sqlCount);

    if (!$stmtCount) {
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmtCount->bind_param(
        "ssss",
        $icd,
        $keywordLike,
        $keywordLike,
        $keywordLike
    );

    //---------------------------------------
    // EKSEKUSI HITUNG
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
    // AMBIL DATA ICD
    $sql = "
        SELECT
            id_icd,
            kode,
            long_des,
            short_des,
            icd
        FROM icd
        WHERE icd = ?
        AND (
            kode LIKE ?
            OR long_des LIKE ?
            OR short_des LIKE ?
        )
        ORDER BY kode ASC
        LIMIT ? OFFSET ?
    ";

    $stmt = $Conn->prepare($sql);

    if (!$stmt) {
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt->bind_param(
        "ssssii",
        $icd,
        $keywordLike,
        $keywordLike,
        $keywordLike,
        $limit,
        $offset
    );

    //---------------------------------------
    // EKSEKUSI
    if (!$stmt->execute()) {
        $stmt->close();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    //---------------------------------------
    // AMBIL HASIL
    $result  = $stmt->get_result();
    $results = [];

    while ($data = $result->fetch_assoc()) {

        $id_icd    = (int) $data['id_icd'];
        $kode      = trim($data['kode'] ?? '');
        $long_des  = trim($data['long_des'] ?? '');
        $short_des = trim($data['short_des'] ?? '');
        $jenis_icd = trim($data['icd'] ?? '');

        $results[] = [
            'id'        => $kode,
            'id_icd'    => $id_icd,
            'kode'      => $kode,
            'long_des'  => $long_des,
            'short_des' => $short_des,
            'icd'       => $jenis_icd,
            'text'      => $kode . ' - ' . $long_des
        ];
    }

    $stmt->close();

    //---------------------------------------
    // PAGINATION
    $dataTerambil = $offset + count($results);
    $more         = $dataTerambil < $totalData;

    //---------------------------------------
    // RESPONSE
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