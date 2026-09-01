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
if (empty($SessionIdAccess)) {
    echo 'Sesi Akses Sudah Berakhir. Silahkan Login Ulang!';
    exit;
}

// Query Data
$query = "SELECT * FROM referensi_route ORDER BY id_referensi_route ASC";

$result = mysqli_query($Conn, $query);

// Buat Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Referensi Route');

// ==========================
// HEADER KOLOM
// ==========================
$sheet->setCellValue('A1', 'No');
$sheet->setCellValue('B1', 'Nama Route');
$sheet->setCellValue('C1', 'Display');
$sheet->setCellValue('D1', 'Code');
$sheet->setCellValue('E1', 'System Referensi');

// Styling Header (opsional tapi disarankan)
$sheet->getStyle('A1:E1')->getFont()->setBold(true);
$sheet->getStyle('A1:E1')->getAlignment()->setHorizontal('center');

// ==========================
// ISI DATA
// ==========================
$row = 2;
$no  = 1;

while ($data = mysqli_fetch_assoc($result)) {
    $sheet->setCellValue('A' . $row, $no++);
    $sheet->setCellValue('B' . $row, $data['nama_route']);
    $sheet->setCellValue('C' . $row, $data['display_route']);
    $sheet->setCellValue('D' . $row, $data['code_route']);
    $sheet->setCellValue('E' . $row, $data['system_route']);
    $row++;
}

// Auto Size Kolom
foreach (range('A', 'G') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// ==========================
// OUTPUT FILE EXCEL
// ==========================
$filename = 'Route' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
