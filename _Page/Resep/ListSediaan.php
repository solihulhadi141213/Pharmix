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



// ==============================
// Sanitasi Input
// ==============================
$keyword             = $_GET['keyword'] ?? "";
$medication_category = "Obat";

$results = [];

// ==============================
// Hitung Data
// ==============================
$sql_count = "
    SELECT id_referensi_sediaan
    FROM referensi_sediaan
    WHERE group_name = '$medication_category'
      AND (
            code LIKE '%$keyword%'
         OR display LIKE '%$keyword%'
      )
";

$jml_data = mysqli_num_rows(mysqli_query($Conn, $sql_count));

if ($jml_data == 0) {
    echo json_encode([
        'results' => [],
        'pagination' => ['more' => false]
    ]);
    exit;
}

// ==============================
// Ambil Data (TANPA LIMIT)
// ==============================
$sql = "
    SELECT code, display
    FROM referensi_sediaan
    WHERE group_name = '$medication_category'
      AND (
            code LIKE '%$keyword%'
         OR display LIKE '%$keyword%'
      )
    ORDER BY display ASC
";

$query = mysqli_query($Conn, $sql);

// ==============================
// Response Select2 (WAJIB)
// ==============================
while ($data = mysqli_fetch_array($query)) {
    $code    = $data['code'];
    $display = $data['display'];

    $results[] = [
        'id'      => $code . '|' . $display,
        'text'    => $code . ' - ' . $display,
        'code'    => $code,        // Tambahkan ini
        'display' => $display     // Tambahkan ini
    ];
}

// ==============================
// Output JSON
// ==============================
echo json_encode([
    'results' => $results,
    'pagination' => [
        'more' => false
    ]
]);
