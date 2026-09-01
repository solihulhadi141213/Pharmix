<?php
// Koneksi Session Dan Function
include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";

// Autoload PhpSpreadsheet
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Zona Waktu
date_default_timezone_set('Asia/Jakarta');

// Validasi Sesi
if (empty($SessionIdAkses)) {
    echo 'Sesi Akses Sudah Berakhir. Silahkan Login Ulang!';
    exit;
}

// Query Data
$query = "
    SELECT 
        id_referensi_sediaan,
        code,
        display,
        system_referensi,
        category,
        group_name
    FROM referensi_sediaan
    ORDER BY id_referensi_sediaan ASC
";

$result = mysqli_query($Conn, $query);

// Buat Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Referensi Sediaan');

// ==========================
// HEADER KOLOM
// ==========================
$sheet->setCellValue('A1', 'No');
$sheet->setCellValue('B1', 'ID Referensi');
$sheet->setCellValue('C1', 'Code');
$sheet->setCellValue('D1', 'Nama Sediaan');
$sheet->setCellValue('E1', 'System Referensi');
$sheet->setCellValue('F1', 'Kategori');
$sheet->setCellValue('G1', 'Group');

// Styling Header (opsional tapi disarankan)
$sheet->getStyle('A1:G1')->getFont()->setBold(true);
$sheet->getStyle('A1:G1')->getAlignment()->setHorizontal('center');

// ==========================
// ISI DATA
// ==========================
$row = 2;
$no  = 1;

while ($data = mysqli_fetch_assoc($result)) {
    $sheet->setCellValue('A' . $row, $no++);
    $sheet->setCellValue('B' . $row, $data['id_referensi_sediaan']);
    $sheet->setCellValue('C' . $row, $data['code']);
    $sheet->setCellValue('D' . $row, $data['display']);
    $sheet->setCellValue('E' . $row, $data['system_referensi']);
    $sheet->setCellValue('F' . $row, $data['category']);
    $sheet->setCellValue('G' . $row, $data['group_name']);
    $row++;
}

// Auto Size Kolom
foreach (range('A', 'G') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// ==========================
// OUTPUT FILE EXCEL
// ==========================
$filename = 'Referensi_Sediaan_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
