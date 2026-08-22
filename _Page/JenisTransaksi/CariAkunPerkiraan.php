<?php

    // ============================================================
    // KONEKSI DAN SESSION
    // ============================================================
    date_default_timezone_set('Asia/Jakarta');

    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');

    // ============================================================
    // VALIDASI SESSION
    // ============================================================
    if (empty($SessionIdAkses)) {

        echo json_encode([
            "results" => [],
            "pagination" => [
                "more" => false
            ]
        ]);

        exit;
    }

    // ============================================================
    // PARAMETER
    // ============================================================
    $keyword = trim($_POST['keyword'] ?? '');
    $page    = (int)($_POST['page'] ?? 1);

    if ($page < 1) {
        $page = 1;
    }

    // ============================================================
    // LIMIT
    // ============================================================
    $limit = 10;

    $offset = ($page - 1) * $limit;

    // ============================================================
    // SEARCH
    // ============================================================
    $where = "";
    $bindTypes = "";
    $bindValues = [];

    if ($keyword !== '') {

        $where = "
            WHERE
                nama LIKE ?
                OR kode LIKE ?
        ";

        $keywordLike = "%" . $keyword . "%";

        $bindTypes = "ss";

        $bindValues[] = $keywordLike;
        $bindValues[] = $keywordLike;
    }

    // ============================================================
    // QUERY
    // ============================================================
    // Ambil 1 data tambahan untuk menentukan apakah masih ada
    // halaman berikutnya.
    // ============================================================
    $sql = "
        SELECT
            id_perkiraan,
            kode,
            nama,
            level,
            saldo_normal
        FROM akun_perkiraan
        $where
        ORDER BY nama ASC
        LIMIT ?, ?
    ";

    $stmt = $Conn->prepare($sql);

    if (!$stmt) {

        echo json_encode([
            "results" => [],
            "pagination" => [
                "more" => false
            ]
        ]);

        exit;
    }

    // ============================================================
    // BIND
    // ============================================================
    $bindTypesData = $bindTypes . "ii";

    $bindValuesData = $bindValues;

    $bindValuesData[] = $offset;
    $bindValuesData[] = $limit + 1;

    $stmt->bind_param(
        $bindTypesData,
        ...$bindValuesData
    );

    // ============================================================
    // EXECUTE
    // ============================================================
    if (!$stmt->execute()) {

        $stmt->close();

        echo json_encode([
            "results" => [],
            "pagination" => [
                "more" => false
            ]
        ]);

        exit;
    }

    // ============================================================
    // RESULT
    // ============================================================
    $result = $stmt->get_result();

    $results = [];

    while ($row = $result->fetch_assoc()) {

        /*
        * Ambil maksimal 11 data.
        *
        * Data ke-11 hanya digunakan untuk mengetahui
        * apakah masih ada halaman berikutnya.
        */
        if (count($results) >= $limit) {
            break;
        }

        $id_perkiraan = (int)$row['id_perkiraan'];

        $kode = htmlspecialchars(
            $row['kode'] ?? '',
            ENT_QUOTES,
            'UTF-8'
        );

        $nama = htmlspecialchars(
            $row['nama'] ?? '',
            ENT_QUOTES,
            'UTF-8'
        );

        $saldo_normal = htmlspecialchars(
            $row['saldo_normal'] ?? '',
            ENT_QUOTES,
            'UTF-8'
        );

        // ========================================================
        // TEXT YANG DITAMPILKAN SELECT2
        // ========================================================
        $text = $nama;

        if ($kode !== '') {
            $text = $kode . ' - ' . $nama;
        }

        if ($saldo_normal !== '') {
            $text .= ' (' . $saldo_normal . ')';
        }

        $results[] = [
            "id"   => $id_perkiraan,
            "text" => $text
        ];
    }

    // ============================================================
    // CEK MASIH ADA DATA
    // ============================================================
    $more = ($result->num_rows > $limit);

    $stmt->close();

    // ============================================================
    // RESPONSE
    // ============================================================
    echo json_encode([
        "results" => $results,
        "pagination" => [
            "more" => $more
        ]
    ], JSON_UNESCAPED_UNICODE);

?>