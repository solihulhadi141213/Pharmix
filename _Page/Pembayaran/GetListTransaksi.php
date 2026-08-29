<?php
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');

    if (empty($SessionIdAkses)) {
        echo json_encode(["results" => [], "pagination" => ["more" => false]]);
        exit;
    }

    $kategori_transaksi = $_GET['kategori_transaksi'] ?? '';
    $search             = trim($_GET['search'] ?? '');
    $page               = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit              = 10;
    $offset             = ($page - 1) * $limit;

    $results    = [];
    $morePages  = false;

    if ($kategori_transaksi === "jual_beli") {
        $where = ["tjb.status != 'Lunas'"];
        $params = [];
        $types  = "";

        if (!empty($search)) {
            $where[] = "(tjb.id_transaksi_jual_beli LIKE ? OR tjb.kategori LIKE ? OR a.nama LIKE ?)";
            $searchTerm = "%" . $search . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types   .= "sss";
        }

        $whereSQL = "WHERE " . implode(" AND ", $where);

        $sqlCount = "
            SELECT COUNT(tjb.id_transaksi_jual_beli) AS total 
            FROM transaksi_jual_beli tjb 
            LEFT JOIN anggota a ON tjb.id_anggota = a.id_anggota 
            $whereSQL
        ";
        $stmtCount = mysqli_prepare($Conn, $sqlCount);
        if (!empty($types)) {
            mysqli_stmt_bind_param($stmtCount, $types, ...$params);
        }
        mysqli_stmt_execute($stmtCount);
        $totalData = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCount))['total'];
        mysqli_stmt_close($stmtCount);

        $sql = "
            SELECT tjb.id_transaksi_jual_beli, tjb.tanggal, tjb.kategori, tjb.total, a.nama AS nama_relasi
            FROM transaksi_jual_beli tjb
            LEFT JOIN anggota a ON tjb.id_anggota = a.id_anggota
            $whereSQL
            ORDER BY tjb.tanggal DESC
            LIMIT ?, ?
        ";
        $stmt = mysqli_prepare($Conn, $sql);
        $typesData  = $types . "ii";
        $paramsData = $params;
        $paramsData[] = $offset;
        $paramsData[] = $limit;

        mysqli_stmt_bind_param($stmt, $typesData, ...$paramsData);
        mysqli_stmt_execute($stmt);
        $query = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($query)) {
            // Kirim data terpisah ke JSON
            $results[] = [
                "id"     => $row['id_transaksi_jual_beli'],
                "text"   => "ID: " . $row['id_transaksi_jual_beli'], // Fallback teks biasa
                "tanggal"=> date('d/m/Y H:i', strtotime($row['tanggal'])),
                "kategori"=> $row['kategori'],
                "nominal"=> 'Rp ' . number_format((float)$row['total'], 0, ',', '.'),
                "relasi" => !empty($row['nama_relasi']) ? $row['nama_relasi'] : 'Tanpa Nama'
            ];
        }
        mysqli_stmt_close($stmt);

        if ($offset + $limit < $totalData) {
            $morePages = true;
        }

    } elseif ($kategori_transaksi === "Operasional") {
        $where = ["t.status != 'Lunas'"];
        $params = [];
        $types  = "";

        if (!empty($search)) {
            $where[] = "(t.id_transaksi LIKE ? OR tj.nama LIKE ? OR t.tanggal LIKE ?)";
            $searchTerm = "%" . $search . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types   .= "sss";
        }

        $whereSQL = "WHERE " . implode(" AND ", $where);

        $sqlCount = "
            SELECT COUNT(t.id_transaksi) AS total 
            FROM transaksi t 
            INNER JOIN transaksi_jenis tj ON t.id_transaksi_jenis = tj.id_transaksi_jenis 
            $whereSQL
        ";
        $stmtCount = mysqli_prepare($Conn, $sqlCount);
        if (!empty($types)) {
            mysqli_stmt_bind_param($stmtCount, $types, ...$params);
        }
        mysqli_stmt_execute($stmtCount);
        $totalData = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCount))['total'];
        mysqli_stmt_close($stmtCount);

        $sql = "
            SELECT t.id_transaksi, t.tanggal, t.jumlah, tj.nama AS nama_transaksi
            FROM transaksi t
            INNER JOIN transaksi_jenis tj ON t.id_transaksi_jenis = tj.id_transaksi_jenis
            $whereSQL
            ORDER BY t.tanggal DESC
            LIMIT ?, ?
        ";
        $stmt = mysqli_prepare($Conn, $sql);
        $typesData  = $types . "ii";
        $paramsData = $params;
        $paramsData[] = $offset;
        $paramsData[] = $limit;

        mysqli_stmt_bind_param($stmt, $typesData, ...$paramsData);
        mysqli_stmt_execute($stmt);
        $query = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($query)) {
            // Kirim data terpisah ke JSON
            $results[] = [
                "id"     => $row['id_transaksi'],
                "text"   => "ID: " . $row['id_transaksi'],
                "tanggal"=> date('d/m/Y H:i', strtotime($row['tanggal'])),
                "kategori"=> $row['nama_transaksi'],
                "nominal"=> 'Rp ' . number_format((float)$row['jumlah'], 0, ',', '.'),
                "relasi" => "-"
            ];
        }
        mysqli_stmt_close($stmt);

        if ($offset + $limit < $totalData) {
            $morePages = true;
        }
    }

    echo json_encode([
        "results"    => $results,
        "pagination" => ["more" => $morePages]
    ], JSON_UNESCAPED_UNICODE);
    exit;
?>