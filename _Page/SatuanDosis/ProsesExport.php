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
        id_referensi_satuan_dosis,
        nama_satuan_dosis,
        unit_satuan_dosis,
        code_satuan_dosis,
        system_satuan_dosis
    FROM referensi_satuan_dosis
    ORDER BY id_referensi_satuan_dosis ASC
";

$result = mysqli_query($Conn, $query);

// Buat Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Referensi Satuan Dosis');

// ==========================
// HEADER KOLOM
// ==========================
$sheet->setCellValue('A1', 'No');
$sheet->setCellValue('B1', 'Nama Satuan');
$sheet->setCellValue('C1', 'Unit');
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
    $sheet->setCellValue('B' . $row, $data['nama_satuan_dosis']);
    $sheet->setCellValue('C' . $row, $data['unit_satuan_dosis']);
    $sheet->setCellValue('D' . $row, $data['code_satuan_dosis']);
    $sheet->setCellValue('E' . $row, $data['system_satuan_dosis']);
    $row++;
}

// Auto Size Kolom
foreach (range('A', 'G') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// ==========================
// OUTPUT FILE EXCEL
// ==========================
$filename = 'Satuan_Dosis' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
