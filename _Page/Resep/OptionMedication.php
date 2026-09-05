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

    //---------------------------------------
    // Validasi Parameter
    if ($page < 1) {
        $page = 1;
    }

    if ($limit < 1 || $limit > 50) {
        $limit = 10;
    }

    //---------------------------------------
    // Pagination
    $offset      = ($page - 1) * $limit;
    $keywordLike = '%' . $keyword . '%';

    //---------------------------------------
    // Hitung Total Data
    $sqlCount = "
        SELECT COUNT(*) AS total
        FROM medication
        WHERE medication_name LIKE ?
        OR medication_code LIKE ?
        OR kfa_code LIKE ?
        OR kfa_display LIKE ?
    ";

    $stmtCount = $Conn->prepare($sqlCount);

    if (!$stmtCount) {
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmtCount->bind_param(
        "ssss",
        $keywordLike,
        $keywordLike,
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
    // Ambil Data Medication
    $sql = "
    SELECT
            id_index_medication,
            id_medication,
            medication_code,
            medication_name,
            medication_category,
            kfa_code,
            kfa_display,
            sediaan_code,
            sediaan_display,
            racikan_code,
            racikan_display,
            ingredient
        FROM medication
        WHERE medication_name LIKE ?
        OR medication_code LIKE ?
        OR kfa_code LIKE ?
        OR kfa_display LIKE ?
        ORDER BY medication_name ASC
        LIMIT ? OFFSET ?
    ";

    $stmt = $Conn->prepare($sql);

    if (!$stmt) {
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt->bind_param(
        "ssssii",
        $keywordLike,
        $keywordLike,
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

        $id_index_medication = (int) $data['id_index_medication'];
        $id_medication       = trim($data['id_medication'] ?? '');
        $medication_code     = trim($data['medication_code'] ?? '');
        $medication_name     = trim($data['medication_name'] ?? '');
        $medication_category = trim($data['medication_category'] ?? '');
        $kfa_code            = trim($data['kfa_code'] ?? '');
        $kfa_display         = trim($data['kfa_display'] ?? '');
        $sediaan_code        = trim($data['sediaan_code'] ?? '');
        $sediaan_display     = trim($data['sediaan_display'] ?? '');
        $racikan_code        = trim($data['racikan_code'] ?? '');
        $racikan_display     = trim($data['racikan_display'] ?? '');
        $ingredient          = $data['ingredient'] ?? null;
        //---------------------------------------
        // Ingredient
        $ingredient = [];

        if (!empty($data['ingredient'])) {

            $ingredientDecode = json_decode(
                $data['ingredient'],
                true
            );

            if (is_array($ingredientDecode)) {
                $ingredient = $ingredientDecode;
            }
        }

        $results[] = [
            'id'                  => $id_index_medication,
            'id_index_medication' => $id_index_medication,
            'id_medication'       => $id_medication,
            'medication_code'     => $medication_code,
            'medication_name'     => $medication_name,
            'medication_category' => $medication_category,
            'kfa_code'            => $kfa_code,
            'kfa_display'         => $kfa_display,
            'sediaan_code'        => $sediaan_code,
            'sediaan_display'     => $sediaan_display,
            'racikan_code'        => $racikan_code,
            'racikan_display'     => $racikan_display,
            'ingredient'          => $ingredient,
            'text'                => $medication_name
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