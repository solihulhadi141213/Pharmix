<?php
    // Koneksi, Function Dan Session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Format Response
    header('Content-Type: application/json');

    // Validasi Session
    if (empty($SessionIdAkses)) {
        echo json_encode(['results' => [],'pagination' => ['more' => false]]);
        exit;
    }

    // Sanitasi Input
    $keyword = $_GET['keyword'] ?? "";
    $results = [];

    // Hitung Data
    $sql_count = "SELECT id_referensi_numerator FROM referensi_numerator WHERE unit LIKE '%$keyword%' OR code_numerator LIKE '%$keyword%'";
    $jml_data = mysqli_num_rows(mysqli_query($Conn, $sql_count));

    if ($jml_data == 0) {
        echo json_encode(['results' => [],'pagination' => ['more' => false]]);
        exit;
    }

    // Ambil Data (TANPA LIMIT)
    $sql = "SELECT unit, code_numerator FROM referensi_numerator WHERE unit LIKE '%$keyword%' OR code_numerator LIKE '%$keyword%'  ORDER BY unit ASC";

    $query = mysqli_query($Conn, $sql);

    // Response Select2 (WAJIB)
    while ($data = mysqli_fetch_array($query)) {
        $unit           = $data['unit'];
        $code_numerator = $data['code_numerator'];

        $results[] = [
            'id'   => $code_numerator . '|' . $unit,
            'text' => $code_numerator . ' - ' . $unit,
        ];
    }

    // Output JSON
    echo json_encode([
        'results' => $results,
        'pagination' => [
            'more' => false
        ]
    ]);
?>
