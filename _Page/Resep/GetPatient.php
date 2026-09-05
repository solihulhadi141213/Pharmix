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

    $offset = ($page - 1) * $limit;

    //---------------------------------------
    // PERSIAPKAN KEYWORD
    $keywordLike = '%' . $keyword . '%';

    //---------------------------------------
    // HITUNG TOTAL DATA
    $sqlCount = "
        SELECT COUNT(*) AS total
        FROM anggota
        WHERE nama LIKE ?
        OR id_pasien LIKE ?
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

    $stmtCount->execute();

    $resultCount = $stmtCount->get_result();
    $dataCount   = $resultCount->fetch_assoc();
    $totalData   = (int) ($dataCount['total'] ?? 0);

    $stmtCount->close();

    //---------------------------------------
    // AMBIL DATA PASIEN
    $sql = "
        SELECT
            id_anggota,
            id_pasien,
            id_ihs,
            nik,
            nama,
            gender,
            tanggal_lahir
        FROM anggota
        WHERE nama LIKE ?
        OR id_pasien LIKE ?
        ORDER BY nama ASC
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

        $id_anggota   = (int) $data['id_anggota'];
        $id_pasien    = trim($data['id_pasien'] ?? '');
        $id_ihs       = trim($data['id_ihs'] ?? '');
        $nama         = trim($data['nama'] ?? '');
        $gender       = trim($data['gender'] ?? '');
        $tanggal_lahir = $data['tanggal_lahir'] ?? null;

        $results[] = [
            'id'            => $id_anggota,
            'id_anggota'    => $id_anggota,
            'id_pasien'     => $id_pasien,
            'id_ihs'        => $id_ihs,
            'nama'          => $nama,
            'gender'        => $gender,
            'tanggal_lahir' => $tanggal_lahir,
            'text'          => $id_pasien . ' - ' . $nama
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