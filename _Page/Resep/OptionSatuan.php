<?php
    // Koneksi & Session
    include "../../_Config/Connection.php";

    header('Content-Type: application/json; charset=utf-8');

    // Ambil parameter
    $q    = isset($_GET['q']) ? trim($_GET['q']) : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

    // Pagination
    $limit  = 10;
    $offset = ($page - 1) * $limit;

    // Escape LIKE
    $like = "%{$q}%";

    // QUERY (LIMIT & OFFSET LANGSUNG)
    $sql = "
        SELECT 
            code_satuan_dosis,
            nama_satuan_dosis,
            unit_satuan_dosis
        FROM referensi_satuan_dosis
        WHERE 
            nama_satuan_dosis LIKE ?
            OR unit_satuan_dosis LIKE ?
            OR code_satuan_dosis LIKE ?
        ORDER BY nama_satuan_dosis ASC
        LIMIT $limit OFFSET $offset
    ";

    $stmt = $Conn->prepare($sql);
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();

    // Format Select2
    $results = [];
    while ($row = $result->fetch_assoc()) {
        $results[] = [
            'id'   => $row['code_satuan_dosis'],
            'text' => $row['nama_satuan_dosis']
                . (!empty($row['unit_satuan_dosis'])
                    ? ' (' . $row['unit_satuan_dosis'] . ')'
                    : '')
        ];
    }

    // Hitung total data
    $countSql = "
        SELECT COUNT(*) AS total
        FROM referensi_satuan_dosis
        WHERE 
            nama_satuan_dosis LIKE ?
            OR unit_satuan_dosis LIKE ?
            OR code_satuan_dosis LIKE ?
    ";

    $countStmt = $Conn->prepare($countSql);
    $countStmt->bind_param("sss", $like, $like, $like);
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];

    $more = ($offset + $limit) < $total;

    // Output JSON
    echo json_encode([
        'results' => $results,
        'pagination' => ['more' => $more]
    ]);

?>
