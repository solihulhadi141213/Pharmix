<?php
// =========================================================
// KONEKSI & LIBRARY
// =========================================================
include "../../_Config/Connection.php";
require_once "../../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// =========================================================
// AMBIL & VALIDASI PARAMETER
// =========================================================
$tahun              = isset($_POST['tahun']) ? trim($_POST['tahun']) : '';
$id_transaksi_jenis = isset($_POST['id_transaksi_jenis']) ? trim($_POST['id_transaksi_jenis']) : '';

//------ Validasi Tahun
if ($tahun !== '') {

    if (!preg_match('/^\d{4}$/', $tahun)) {
        die('Format tahun tidak valid.');
    }

    $tahun = (int) $tahun;

} else {

    $tahun = (int) date('Y');

}

//------ Validasi ID Transaksi Jenis
if ($id_transaksi_jenis !== '') {

    if (!ctype_digit($id_transaksi_jenis)) {
        die('ID jenis transaksi tidak valid.');
    }

    $id_transaksi_jenis = (int) $id_transaksi_jenis;

} else {

    $id_transaksi_jenis = null;

}

// =========================================================
// ARRAY BULAN
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

// =========================================================
// AMBIL NAMA JENIS TRANSAKSI
// =========================================================
$nama_transaksi = 'Semua Jenis Transaksi';

if ($id_transaksi_jenis !== null) {

    $sql_jenis = "
        SELECT nama
        FROM transaksi_jenis
        WHERE id_transaksi_jenis = ?
        LIMIT 1
    ";

    $stmt_jenis = $Conn->prepare($sql_jenis);

    if (!$stmt_jenis) {
        die('Gagal mempersiapkan query jenis transaksi.');
    }

    $stmt_jenis->bind_param(
        "i",
        $id_transaksi_jenis
    );

    if (!$stmt_jenis->execute()) {
        die('Gagal mengambil jenis transaksi.');
    }

    $result_jenis = $stmt_jenis->get_result();

    if ($row_jenis = $result_jenis->fetch_assoc()) {
        $nama_transaksi = $row_jenis['nama'];
    }

    $stmt_jenis->close();

}

// =========================================================
// PENYUSUNAN QUERY
// =========================================================
$sql = "
    SELECT
        MONTH(tanggal) AS bulan,
        COALESCE(SUM(jumlah), 0) AS subtotal,
        COALESCE(SUM(pembayaran), 0) AS pembayaran,
        COALESCE(
            SUM(
                CASE
                    WHEN status = 'Lunas'
                    THEN jumlah
                    ELSE 0
                END
            ),
            0
        ) AS total_lunas,
        COALESCE(
            SUM(
                CASE
                    WHEN status = 'Utang'
                    THEN jumlah
                    ELSE 0
                END
            ),
            0
        ) AS total_utang,
        COALESCE(
            SUM(
                CASE
                    WHEN status = 'Piutang'
                    THEN jumlah
                    ELSE 0
                END
            ),
            0
        ) AS total_piutang
    FROM transaksi
    WHERE YEAR(tanggal) = ?
";

$params = [$tahun];
$types  = 'i';

//------ Filter Jenis Transaksi
if ($id_transaksi_jenis !== null) {

    $sql .= "
        AND id_transaksi_jenis = ?
    ";

    $params[] = $id_transaksi_jenis;
    $types   .= 'i';

}

// =========================================================
// GROUP DATA
// =========================================================
$sql .= "
    GROUP BY MONTH(tanggal)
    ORDER BY MONTH(tanggal) ASC
";

// =========================================================
// EKSEKUSI QUERY
// =========================================================
$stmt = $Conn->prepare($sql);

if (!$stmt) {
    die('Query gagal dipersiapkan.');
}

$bindParams = [$types];

foreach ($params as $key => $value) {
    $bindParams[] = &$params[$key];
}

call_user_func_array(
    [$stmt, 'bind_param'],
    $bindParams
);

if (!$stmt->execute()) {
    die('Gagal mengambil data transaksi.');
}

$result = $stmt->get_result();

// =========================================================
// INDEX DATA BERDASARKAN BULAN
// =========================================================
$data_db = [];

while ($row = $result->fetch_assoc()) {

    $bulan = (int) $row['bulan'];

    $data_db[$bulan] = [
        'subtotal'   => (int) $row['subtotal'],
        'pembayaran' => (int) $row['pembayaran'],
        'lunas'      => (int) $row['total_lunas'],
        'utang'      => (int) $row['total_utang'],
        'piutang'    => (int) $row['total_piutang']
    ];

}

$stmt->close();

// =========================================================
// BUAT FILE EXCEL
// =========================================================
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setTitle('Rekap Transaksi');

// =========================================================
// JUDUL
// =========================================================
$sheet->mergeCells('A1:H1');

$sheet->setCellValue(
    'A1',
    'REKAP TRANSAKSI OPERASIONAL'
);

$sheet->getStyle('A1')->applyFromArray([
    'font' => [
        'bold' => true,
        'size' => 16
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER
    ]
]);

$sheet->getRowDimension(1)->setRowHeight(25);

// =========================================================
// INFORMASI FILTER
// =========================================================
$sheet->setCellValue('A3', 'Jenis Transaksi');
$sheet->setCellValue('B3', $nama_transaksi);

$sheet->setCellValue('A4', 'Periode Tahun');
$sheet->setCellValue('B4', $tahun);

$sheet->getStyle('A3:A4')->getFont()->setBold(true);

// =========================================================
// HEADER TABEL
// =========================================================
$baris_header = 6;

$header = [
    'No',
    'Tahun',
    'Bulan',
    'Subtotal',
    'Pembayaran',
    'Lunas',
    'Utang',
    'Piutang'
];

foreach ($header as $kolom => $judul) {

    $kolom_excel = chr(65 + $kolom);

    $sheet->setCellValue(
        $kolom_excel . $baris_header,
        $judul
    );

}

$sheet->getStyle(
    'A' . $baris_header . ':H' . $baris_header
)->applyFromArray([
    'font' => [
        'bold' => true
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'D9EAF7'
        ]
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN
        ]
    ]
]);

// =========================================================
// DATA 12 BULAN
// =========================================================
$baris = 7;

$total_subtotal   = 0;
$total_pembayaran = 0;
$total_lunas      = 0;
$total_utang      = 0;
$total_piutang    = 0;

for ($i = 1; $i <= 12; $i++) {

    $subtotal   = $data_db[$i]['subtotal'] ?? 0;
    $pembayaran = $data_db[$i]['pembayaran'] ?? 0;
    $lunas      = $data_db[$i]['lunas'] ?? 0;
    $utang      = $data_db[$i]['utang'] ?? 0;
    $piutang    = $data_db[$i]['piutang'] ?? 0;

    $sheet->setCellValue('A' . $baris, $i);
    $sheet->setCellValue('B' . $baris, $tahun);
    $sheet->setCellValue('C' . $baris, $nama_bulan[$i]);

    $sheet->setCellValue('D' . $baris, $subtotal);
    $sheet->setCellValue('E' . $baris, $pembayaran);
    $sheet->setCellValue('F' . $baris, $lunas);
    $sheet->setCellValue('G' . $baris, $utang);
    $sheet->setCellValue('H' . $baris, $piutang);

    $total_subtotal   += $subtotal;
    $total_pembayaran += $pembayaran;
    $total_lunas      += $lunas;
    $total_utang      += $utang;
    $total_piutang    += $piutang;

    $baris++;

}

// =========================================================
// BARIS TOTAL
// =========================================================
$sheet->setCellValue('A' . $baris, '');
$sheet->setCellValue('B' . $baris, '');
$sheet->setCellValue('C' . $baris, 'TOTAL');

$sheet->setCellValue('D' . $baris, $total_subtotal);
$sheet->setCellValue('E' . $baris, $total_pembayaran);
$sheet->setCellValue('F' . $baris, $total_lunas);
$sheet->setCellValue('G' . $baris, $total_utang);
$sheet->setCellValue('H' . $baris, $total_piutang);

$sheet->getStyle(
    'A' . $baris . ':H' . $baris
)->applyFromArray([
    'font' => [
        'bold' => true
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'E2E3E5'
        ]
    ]
]);

// =========================================================
// FORMAT DATA
// =========================================================
$sheet->getStyle(
    'A' . $baris_header . ':H' . $baris
)->getBorders()->getAllBorders()->setBorderStyle(
    Border::BORDER_THIN
);

$sheet->getStyle(
    'A' . ($baris_header + 1) . ':C' . $baris
)->getAlignment()->setHorizontal(
    Alignment::HORIZONTAL_CENTER
);

$sheet->getStyle(
    'D' . ($baris_header + 1) . ':H' . $baris
)->getNumberFormat()->setFormatCode(
    '#,##0'
);

$sheet->getStyle(
    'D' . ($baris_header + 1) . ':H' . $baris
)->getAlignment()->setHorizontal(
    Alignment::HORIZONTAL_RIGHT
);

// =========================================================
// AUTO WIDTH
// =========================================================
foreach (range('A', 'H') as $kolom) {
    $sheet->getColumnDimension($kolom)->setAutoSize(true);
}

// =========================================================
// FREEZE HEADER
// =========================================================
$sheet->freezePane('A7');

// =========================================================
// NAMA FILE
// =========================================================
$nama_file = 'Rekap_Transaksi_' . $tahun;

if ($id_transaksi_jenis !== null) {
    $nama_file .= '_Jenis_' . $id_transaksi_jenis;
}

$nama_file .= '_' . date('YmdHis') . '.xlsx';

// =========================================================
// OUTPUT FILE
// =========================================================
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $nama_file . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

$spreadsheet->disconnectWorksheets();
unset($spreadsheet);

exit;