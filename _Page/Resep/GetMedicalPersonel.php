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
    $role    = trim($_GET['role'] ?? '');
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

    $offset      = ($page - 1) * $limit;
    $keywordLike = '%' . $keyword . '%';

    //---------------------------------------
    // FILTER ROLE
    $kategori = [];

    if ($role === 'dokter') {
        $kategori = [
            'Dokter Umum',
            'Dokter Spesialis'
        ];
    }

    if ($role === 'apoteker') {
        $kategori = [
            'Apoteker'
        ];
    }

    //---------------------------------------
    // HITUNG TOTAL DATA
    if (!empty($kategori)) {

        if ($role === 'dokter') {
            $sqlCount = "
                SELECT COUNT(*) AS total
                FROM medical_personel
                WHERE medicalPersonelStatus = 'Active'
                AND medicalPersonelCategory IN (?, ?)
                AND (
                    medicalPersonelName LIKE ?
                    OR medicalPersonelCode LIKE ?
                )
            ";

            $stmtCount = $Conn->prepare($sqlCount);

            if (!$stmtCount) {
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit;
            }

            $stmtCount->bind_param(
                "ssss",
                $kategori[0],
                $kategori[1],
                $keywordLike,
                $keywordLike
            );

        } else {
            $sqlCount = "
                SELECT COUNT(*) AS total
                FROM medical_personel
                WHERE medicalPersonelStatus = 'Active'
                AND medicalPersonelCategory = ?
                AND (
                    medicalPersonelName LIKE ?
                    OR medicalPersonelCode LIKE ?
                )
            ";

            $stmtCount = $Conn->prepare($sqlCount);

            if (!$stmtCount) {
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit;
            }

            $stmtCount->bind_param(
                "sss",
                $kategori[0],
                $keywordLike,
                $keywordLike
            );
        }

    } else {
        $sqlCount = "
            SELECT COUNT(*) AS total
            FROM medical_personel
            WHERE medicalPersonelStatus = 'Active'
            AND (
                medicalPersonelName LIKE ?
                OR medicalPersonelCode LIKE ?
            )
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
    }

    //---------------------------------------
    // EKSEKUSI HITUNG
    $stmtCount->execute();

    $resultCount = $stmtCount->get_result();
    $dataCount   = $resultCount->fetch_assoc();
    $totalData   = (int) ($dataCount['total'] ?? 0);

    $stmtCount->close();

    //---------------------------------------
    // AMBIL DATA MEDICAL PERSONEL
    if (!empty($kategori)) {

        if ($role === 'dokter') {
            $sql = "
                SELECT
                    medicalPersonelId,
                    medicalPersonelCode,
                    id_practitioner,
                    medicalPersonelCategory,
                    medicalPersonelName
                FROM medical_personel
                WHERE medicalPersonelStatus = 'Active'
                AND medicalPersonelCategory IN (?, ?)
                AND (
                    medicalPersonelName LIKE ?
                    OR medicalPersonelCode LIKE ?
                )
                ORDER BY medicalPersonelName ASC
                LIMIT ? OFFSET ?
            ";

            $stmt = $Conn->prepare($sql);

            if (!$stmt) {
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit;
            }

            $stmt->bind_param(
                "ssssii",
                $kategori[0],
                $kategori[1],
                $keywordLike,
                $keywordLike,
                $limit,
                $offset
            );

        } else {
            $sql = "
                SELECT
                    medicalPersonelId,
                    medicalPersonelCode,
                    id_practitioner,
                    medicalPersonelCategory,
                    medicalPersonelName
                FROM medical_personel
                WHERE medicalPersonelStatus = 'Active'
                AND medicalPersonelCategory = ?
                AND (
                    medicalPersonelName LIKE ?
                    OR medicalPersonelCode LIKE ?
                )
                ORDER BY medicalPersonelName ASC
                LIMIT ? OFFSET ?
            ";

            $stmt = $Conn->prepare($sql);

            if (!$stmt) {
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit;
            }

            $stmt->bind_param(
                "sssii",
                $kategori[0],
                $keywordLike,
                $keywordLike,
                $limit,
                $offset
            );
        }

    } else {
        $sql = "
            SELECT
                medicalPersonelId,
                medicalPersonelCode,
                id_practitioner,
                medicalPersonelCategory,
                medicalPersonelName
            FROM medical_personel
            WHERE medicalPersonelStatus = 'Active'
            AND (
                medicalPersonelName LIKE ?
                OR medicalPersonelCode LIKE ?
            )
            ORDER BY medicalPersonelName ASC
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
    }

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

        $medicalPersonelId       = (int) $data['medicalPersonelId'];
        $medicalPersonelCode     = trim($data['medicalPersonelCode'] ?? '');
        $id_practitioner         = trim($data['id_practitioner'] ?? '');
        $medicalPersonelCategory = trim($data['medicalPersonelCategory'] ?? '');
        $medicalPersonelName     = trim($data['medicalPersonelName'] ?? '');

        $results[] = [
            'id'                      => $medicalPersonelId,
            'medicalPersonelId'       => $medicalPersonelId,
            'medicalPersonelCode'     => $medicalPersonelCode,
            'id_practitioner'         => $id_practitioner,
            'medicalPersonelCategory' => $medicalPersonelCategory,
            'medicalPersonelName'     => $medicalPersonelName,
            'text'                    => $medicalPersonelName
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