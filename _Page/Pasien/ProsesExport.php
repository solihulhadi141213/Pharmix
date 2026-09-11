<?php
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Tampung output include agar tidak merusak file Excel.
ob_start();
require __DIR__ . '/../../_Config/Connection.php';
require __DIR__ . '/../../_Config/GlobalFunction.php';
require __DIR__ . '/../../_Config/Session.php';
ob_end_clean();

date_default_timezone_set('Asia/Jakarta');

if (empty($SessionIdAkses)) {
    http_response_code(401);
    exit('Sesi akses telah berakhir. Silakan login ulang.');
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Metode request tidak valid.');
}

require __DIR__ . '/../../vendor/autoload.php';

try {
    $query = $Conn->query(
        'SELECT id_pasien, id_ihs, nik, nama, email, kontak, alamat,
                gender, tempat_lahir, tanggal_lahir
         FROM anggota ORDER BY nama ASC, id_anggota ASC'
    );
    if (!$query) {
        throw new RuntimeException('Query export pasien gagal.');
    }
} catch (Exception $exception) {
    http_response_code(500);
    exit('Gagal mengambil data pasien. Silakan coba lagi.');
}

if ($query->num_rows === 0) {
    $query->free();
    exit('Tidak ada data pasien yang dapat diexport.');
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Pasien');
$sheet->fromArray([
    'No', 'ID Pasien', 'IHS', 'NIK', 'Nama', 'Email', 'Kontak',
    'Alamat', 'Gender', 'Tempat Lahir', 'Tanggal Lahir'
], null, 'A1');

$fields = [
    'id_pasien', 'id_ihs', 'nik', 'nama', 'email', 'kontak',
    'alamat', 'gender', 'tempat_lahir', 'tanggal_lahir'
];
$row = 2;
while ($data = $query->fetch_assoc()) {
    $sheet->setCellValue('A' . $row, $row - 1);
    $column = 'B';
    foreach ($fields as $field) {
        // Teks mempertahankan nol awal/digit panjang dan mencegah pembacaan sebagai rumus.
        $value = (string) ($data[$field] ?? '');
        if ($field === 'tanggal_lahir' && $value === '0000-00-00') {
            $value = '';
        }
        $sheet->setCellValueExplicit($column . $row, $value, DataType::TYPE_STRING);
        $column++;
    }
    $row++;
}
$query->free();

$lastRow = $row - 1;
$sheet->getStyle('A1:K1')->getFont()->setBold(true);
$sheet->getStyle('A1:K1')->getFill()
    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE9ECEF');
$sheet->getStyle('A1:K' . $lastRow)->getAlignment()
    ->setVertical(Alignment::VERTICAL_TOP);
$sheet->getStyle('E2:K' . $lastRow)->getAlignment()->setWrapText(true);
$sheet->setAutoFilter('A1:K' . $lastRow);
$sheet->freezePane('A2');

$widths = ['A' => 7, 'B' => 22, 'C' => 25, 'D' => 22, 'E' => 35,
    'F' => 35, 'G' => 20, 'H' => 55, 'I' => 15, 'J' => 25, 'K' => 18];
foreach ($widths as $column => $width) {
    $sheet->getColumnDimension($column)->setWidth($width);
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Pasien_' . date('Ymd_His') . '.xlsx"');
header('Cache-Control: no-store, max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
$spreadsheet->disconnectWorksheets();
exit;
