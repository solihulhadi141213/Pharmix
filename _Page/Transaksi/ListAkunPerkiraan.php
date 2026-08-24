<?php
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    header('Content-Type: application/json; charset=utf-8');

    function responseJson($results = [], $more = false) {
        echo json_encode([
            'results' => $results,
            'pagination' => ['more' => $more]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (empty($SessionIdAkses)) {
        responseJson();
    }

    $search = trim($_GET['search'] ?? '');
    $page = (int)($_GET['page'] ?? 1);
    if ($page < 1) {
        $page = 1;
    }

    $limit = 20;
    $offset = ($page - 1) * $limit;

    $where = "";
    $types = "";
    $params = [];

    if ($search !== '') {
        $where = "WHERE kode LIKE ? OR nama LIKE ?";
        $keyword = '%' . $search . '%';
        $types = "ss";
        $params[] = $keyword;
        $params[] = $keyword;
    }

    $sql = "SELECT kode, nama, level, saldo_normal FROM akun_perkiraan $where ORDER BY nama ASC LIMIT ?, ?";
    $stmt = mysqli_prepare($Conn, $sql);
    if (!$stmt) {
        responseJson();
    }

    $types .= "ii";
    $params[] = $offset;
    $params[] = $limit;

    mysqli_stmt_bind_param($stmt, $types, ...$params);

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        responseJson();
    }

    $result = mysqli_stmt_get_result($stmt);
    $results = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $kode = $row['kode'];
        $nama = $row['nama'];
        $level = (int)$row['level'];
        $saldo_normal = $row['saldo_normal'];
        $kolom_level = 'kd' . $level;

        if (!preg_match('/^kd[0-9]+$/', $kolom_level)) {
            continue;
        }

        $sql_child = "SELECT COUNT(*) AS jumlah FROM akun_perkiraan WHERE $kolom_level = ?";
        $stmt_child = mysqli_prepare($Conn, $sql_child);
        if (!$stmt_child) {
            continue;
        }
        mysqli_stmt_bind_param($stmt_child, 's', $kode);
        mysqli_stmt_execute($stmt_child);
        $result_child = mysqli_stmt_get_result($stmt_child);
        $data_child = mysqli_fetch_assoc($result_child);
        mysqli_stmt_close($stmt_child);

        $jumlah_child = (int)($data_child['jumlah'] ?? 0);

        if ($jumlah_child === 1) {
            $results[] = [
                'id' => $kode,
                'text' => $kode . ' - ' . $nama,
                'kode' => $kode,
                'nama' => $nama,
                'saldo_normal' => $saldo_normal
            ];
        }
    }

    $more = count($results) >= $limit;
    mysqli_stmt_close($stmt);

    responseJson($results, $more);
?>