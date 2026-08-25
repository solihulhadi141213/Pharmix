<?php

include "../../_Config/Connection.php";

require "../../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// =========================================================
// AMBIL & VALIDASI PARAMETER
// =========================================================
$id_transaksi_jenis = isset($_POST['id_transaksi_jenis'])
    ? trim($_POST['id_transaksi_jenis'])
    : '';

$tahun = isset($_POST['tahun'])
    ? trim($_POST['tahun'])
    : '';

$bulan = isset($_POST['bulan'])
    ? trim($_POST['bulan'])
    : '';

//------ Validasi Tahun
if ($tahun === '' || !preg_match('/^\d{4}$/', $tahun)) {
    die('Tahun tidak valid.');
}

$tahun = (int) $tahun;

//------ Validasi Bulan
if ($bulan === '' || !ctype_digit($bulan)) {
    die('Bulan tidak valid.');
}

$bulan = (int) $bulan;

if ($bulan < 1 || $bulan > 12) {
    die('Bulan tidak valid.');
}

//------ Validasi Jenis Transaksi
if ($id_transaksi_jenis !== '') {

    if (!ctype_digit($id_transaksi_jenis)) {
        die('ID jenis transaksi tidak valid.');
    }

    $id_transaksi_jenis = (int) $id_transaksi_jenis;

} else {
    $id_transaksi_jenis = null;
}

// =========================================================
// NAMA BULAN
// =========================================================
$nama_bulan = [
    1  => 'Januari',
    2  => 'Februari',
    3  => 'Maret',
    4  => 'April',
    5  => 'Mei',
    6  => 'Juni',
    7  => 'Juli',
    8  => 'Agustus',
    9  => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];

$nama_bulan_export = $nama_bulan[$bulan];

// =========================================================
// QUERY DATA TRANSAKSI RINCIAN
// =========================================================
$sql = "
    SELECT
        t.tanggal,
        tj.nama AS nama_transaksi,
        tr.rincian_transaksi,
        tr.harga,
        tr.qty,
        tr.satuan,
        tr.jumlah AS subtotal
    FROM transaksi_rincian tr
    INNER JOIN transaksi t
        ON tr.id_transaksi = t.id_transaksi
    LEFT JOIN transaksi_jenis tj
        ON t.id_transaksi_jenis = tj.id_transaksi_jenis
    WHERE YEAR(t.tanggal) = ?
    AND MONTH(t.tanggal) = ?
";

$params = [
    $tahun,
    $bulan
];

$types = 'ii';

//------ Filter Jenis Transaksi
if ($id_transaksi_jenis !== null) {

    $sql .= "
        AND t.id_transaksi_jenis = ?
    ";

    $params[] = $id_transaksi_jenis;
    $types .= 'i';
}

$sql .= "
    ORDER BY
        t.tanggal ASC,
        tr.id_transaksi_rincian ASC
";

// =========================================================
// PREPARED STATEMENT
// =========================================================
$stmt = $Conn->prepare($sql);

if (!$stmt) {
    die('Query gagal dipersiapkan: ' . $Conn->error);
}

//------ Bind Parameter
$bindParams = [$types];

foreach ($params as $key => $value) {
    $bindParams[] = &$params[$key];
}

call_user_func_array(
    [$stmt, 'bind_param'],
    $bindParams
);

//------ Execute
if (!$stmt->execute()) {
    die('Gagal mengambil data transaksi: ' . $stmt->error);
}

$result = $stmt->get_result();

// =========================================================
// BUAT FILE EXCEL
// =========================================================
$spreadsheet = new Spreadsheet();

$sheet = $spreadsheet->getActiveSheet();

$sheet->setTitle('Rincian Transaksi');

// =========================================================
// JUDUL
// =========================================================
$sheet->mergeCells('A1:H1');

$sheet->setCellValue(
    'A1',
    'RINCIAN TRANSAKSI OPERASIONAL'
);

$sheet->getStyle('A1')->getFont()->setBold(true);
$sheet->getStyle('A1')->getFont()->setSize(14);

$sheet->getStyle('A1')->getAlignment()->setHorizontal(
    Alignment::HORIZONTAL_CENTER
);

// =========================================================
// INFORMASI FILTER
// =========================================================
$sheet->mergeCells('A2:H2');

$informasi = 'Periode: ' . $nama_bulan_export . ' ' . $tahun;

if ($id_transaksi_jenis !== null) {

    $stmt_jenis = $Conn->prepare("
        SELECT nama
        FROM transaksi_jenis
        WHERE id_transaksi_jenis = ?
        LIMIT 1
    ");

    $stmt_jenis->bind_param(
        'i',
        $id_transaksi_jenis
    );

    $stmt_jenis->execute();

    $result_jenis = $stmt_jenis->get_result();

    if ($row_jenis = $result_jenis->fetch_assoc()) {
        $informasi .= ' | Jenis Transaksi: ' . $row_jenis['nama'];
    }

    $stmt_jenis->close();

} else {

    $informasi .= ' | Jenis Transaksi: Semua';

}

$sheet->setCellValue('A2', $informasi);

$sheet->getStyle('A2')->getAlignment()->setHorizontal(
    Alignment::HORIZONTAL_CENTER
);

// =========================================================
// HEADER TABEL
// =========================================================
$header = [
    'No',
    'Tanggal',
    'Jenis Transaksi',
    'Uraian/Rincian',
    'Harga',
    'QTY',
    'Satuan',
    'Subtotal'
];

$kolom = 'A';

foreach ($header as $judul) {

    $sheet->setCellValue(
        $kolom . '4',
        $judul
    );

    $kolom++;
}

//------ Style Header
$sheet->getStyle('A4:H4')->getFont()->setBold(true);

$sheet->getStyle('A4:H4')->getAlignment()->setHorizontal(
    Alignment::HORIZONTAL_CENTER
);

$sheet->getStyle('A4:H4')->getFill()->setFillType(
    Fill::FILL_SOLID
);

$sheet->getStyle('A4:H4')->getFill()->getStartColor()
    ->setARGB('D9EAF7');

$sheet->getStyle('A4:H4')->getBorders()->getAllBorders()
    ->setBorderStyle(Border::BORDER_THIN);

// =========================================================
// ISI DATA
// =========================================================
$baris = 5;
$no = 1;

$total_subtotal = 0;

while ($data = $result->fetch_assoc()) {

    $tanggal = date(
        'd-m-Y H:i',
        strtotime($data['tanggal'])
    );

    $sheet->setCellValue(
        'A' . $baris,
        $no
    );

    $sheet->setCellValue(
        'B' . $baris,
        $tanggal
    );

    $sheet->setCellValue(
        'C' . $baris,
        $data['nama_transaksi']
    );

    $sheet->setCellValue(
        'D' . $baris,
        $data['rincian_transaksi']
    );

    $sheet->setCellValue(
        'E' . $baris,
        (int) $data['harga']
    );

    $sheet->setCellValue(
        'F' . $baris,
        (int) $data['qty']
    );

    $sheet->setCellValue(
        'G' . $baris,
        $data['satuan']
    );

    $sheet->setCellValue(
        'H' . $baris,
        (int) $data['subtotal']
    );

    $total_subtotal += (int) $data['subtotal'];

    $baris++;
    $no++;
}

$stmt->close();

// =========================================================
// BARIS TOTAL
// =========================================================
$sheet->setCellValue(
    'A' . $baris,
    'TOTAL'
);

$sheet->mergeCells(
    'A' . $baris . ':G' . $baris
);

$sheet->setCellValue(
    'H' . $baris,
    $total_subtotal
);

$sheet->getStyle(
    'A' . $baris . ':H' . $baris
)->getFont()->setBold(true);

$sheet->getStyle(
    'A' . $baris . ':H' . $baris
)->getFill()->setFillType(
    Fill::FILL_SOLID
);

$sheet->getStyle(
    'A' . $baris . ':H' . $baris
)->getFill()->getStartColor()
    ->setARGB('E9ECEF');

// =========================================================
// FORMAT ANGKA
// =========================================================
if ($baris >= 5) {

    $sheet->getStyle('E5:E' . $baris)
        ->getNumberFormat()
        ->setFormatCode('#,##0');

    $sheet->getStyle('H5:H' . $baris)
        ->getNumberFormat()
        ->setFormatCode('#,##0');
}

// =========================================================
// BORDER TABEL
// =========================================================
$sheet->getStyle(
    'A4:H' . $baris
)->getBorders()->getAllBorders()
    ->setBorderStyle(Border::BORDER_THIN);

// =========================================================
// ALIGNMENT
// =========================================================
$sheet->getStyle('A5:A' . $baris)
    ->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->getStyle('F5:F' . $baris)
    ->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

// =========================================================
// LEBAR KOLOM
// =========================================================
$lebar = [
    'A' => 8,
    'B' => 20,
    'C' => 30,
    'D' => 40,
    'E' => 18,
    'F' => 10,
    'G' => 15,
    'H' => 18
];

foreach ($lebar as $kolom => $nilai) {

    $sheet->getColumnDimension($kolom)
        ->setWidth($nilai);

}

// =========================================================
// FREEZE HEADER
// =========================================================
$sheet->freezePane('A5');

// =========================================================
// OUTPUT EXCEL
// =========================================================
$nama_file = 'Rincian_Transaksi_' .
    $tahun . '_' .
    str_pad($bulan, 2, '0', STR_PAD_LEFT) .
    '.xlsx';

header(
    'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
);

header(
    'Content-Disposition: attachment; filename="' . $nama_file . '"'
);

header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);

$writer->save('php://output');

exit;