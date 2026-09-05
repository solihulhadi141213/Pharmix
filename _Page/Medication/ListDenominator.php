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
    $keyword             = validateAndSanitizeInput($_GET['keyword'] ?? "");
    $results = [];

    // Hitung Data
    $sql_count = "SELECT id_referensi_denominator FROM referensi_denominator WHERE code_denominator LIKE '%$keyword%' OR display_denominator LIKE '%$keyword%'";
    $jml_data = mysqli_num_rows(mysqli_query($Conn, $sql_count));

    if ($jml_data == 0) {
        echo json_encode(['results' => [],'pagination' => ['more' => false]]);
        exit;
    }

    // Ambil Data (TANPA LIMIT)
    $sql = "SELECT code_denominator, display_denominator FROM referensi_denominator WHERE code_denominator LIKE '%$keyword%' OR display_denominator LIKE '%$keyword%'  ORDER BY display_denominator ASC";

    $query = mysqli_query($Conn, $sql);

    // Response Select2 (WAJIB)
    while ($data = mysqli_fetch_array($query)) {
        $code_denominator           = $data['code_denominator'];
        $display_denominator = $data['display_denominator'];

        $results[] = [
            'id'   => $code_denominator . '|' . $display_denominator,
            'text' => $code_denominator . ' - ' . $display_denominator,
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
