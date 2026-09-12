<?php
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Cegah output dari file konfigurasi masuk ke file Excel.
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
        'SELECT a.id_pasien AS no_rm, a.nama AS nama_pasien,
                k.id_encounter, k.tanggal_kunjungan, k.priority, k.keluhan,
                k.jenis_kunjungan, k.nama_dokter_penerima, k.nama_dpjp,
                k.nama_poli, k.ruang_inap, k.status
         FROM kunjungan AS k
         LEFT JOIN anggota AS a ON a.id_anggota = k.id_anggota
         ORDER BY k.tanggal_kunjungan DESC, k.id_kunjungan DESC'
    );
    if (!$query) {
        throw new RuntimeException('Query export kunjungan gagal.');
    }
} catch (Throwable $exception) {
    http_response_code(500);
    exit('Gagal mengambil data kunjungan. Silakan coba lagi.');
}

if ($query->num_rows === 0) {
    $query->free();
    exit('Tidak ada data kunjungan yang dapat diexport.');
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Kunjungan');
$sheet->fromArray([
    'No', 'No.RM', 'Nama Pasien', 'ID Encounter', 'Tanggal', 'Priority',
    'Keluhan', 'Jenis Kunjungan', 'Nama Dokter Penerima', 'Nama Dokter DPJP',
    'Nama Poli', 'Ruangan', 'Status',
], null, 'A1');

$fields = [
    'no_rm', 'nama_pasien', 'id_encounter', 'tanggal_kunjungan', 'priority',
    'keluhan', 'jenis_kunjungan', 'nama_dokter_penerima', 'nama_dpjp',
    'nama_poli', 'ruang_inap', 'status',
];
$row = 2;
while ($data = $query->fetch_assoc()) {
    $sheet->setCellValue('A' . $row, $row - 1);
    $column = 'B';
    foreach ($fields as $field) {
        // Pertahankan nol awal No.RM dan cegah data dibaca sebagai rumus Excel.
        $value = (string) ($data[$field] ?? '');
        $sheet->setCellValueExplicit($column . $row, $value, DataType::TYPE_STRING);
        $column++;
    }
    $row++;
}
$query->free();

$lastRow = $row - 1;
$sheet->getStyle('A1:M1')->getFont()->setBold(true);
$sheet->getStyle('A1:M1')->getFill()
    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE9ECEF');
$sheet->getStyle('A1:M' . $lastRow)->getAlignment()
    ->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
$sheet->setAutoFilter('A1:M' . $lastRow);
$sheet->freezePane('A2');

$widths = [
    'A' => 7, 'B' => 22, 'C' => 35, 'D' => 40, 'E' => 23, 'F' => 16,
    'G' => 55, 'H' => 22, 'I' => 35, 'J' => 35, 'K' => 30, 'L' => 25, 'M' => 22,
];
foreach ($widths as $column => $width) {
    $sheet->getColumnDimension($column)->setWidth($width);
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Kunjungan_' . date('Ymd_His') . '.xlsx"');
header('Cache-Control: no-store, max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
$spreadsheet->disconnectWorksheets();
exit;
