<?php
    date_default_timezone_set('Asia/Jakarta');

    // Koneksi dan Konfigurasi
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    include "../../_Config/FungsiAkses.php";

    if (empty($SessionIdAkses)) {
        http_response_code(401);
        exit('Sesi akses sudah berakhir. Silakan login ulang.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Metode request tidak valid.');
    }

    $periode_awal  = trim($_POST['periode_awal'] ?? '');
    $periode_akhir = trim($_POST['periode_akhir'] ?? '');
    $format_data   = strtoupper(trim($_POST['format_data'] ?? 'HTML'));

    if (!in_array($format_data, ['HTML', 'EXCEL'], true)) {
        exit('Format export tidak valid.');
    }

    $valid_date = static function (string $date): bool {
        $date_object = DateTime::createFromFormat('Y-m-d', $date);
        return $date_object !== false && $date_object->format('Y-m-d') === $date;
    };

    if (($periode_awal !== '' && !$valid_date($periode_awal)) ||
        ($periode_akhir !== '' && !$valid_date($periode_akhir))) {
        exit('Format periode tanggal tidak valid.');
    }

    if (($periode_awal === '') !== ($periode_akhir === '')) {
        exit('Periode awal dan periode akhir harus diisi bersama.');
    }

    if ($periode_awal !== '' && $periode_awal > $periode_akhir) {
        exit('Periode awal tidak boleh lebih besar dari periode akhir.');
    }

    $where = [];
    $bind_types = '';
    $bind_values = [];

    if ($periode_awal !== '') {
        $periode_akhir_exclusive = (new DateTime($periode_akhir))
            ->modify('+1 day')
            ->format('Y-m-d');
        $where[] = 'tp.tanggal >= ? AND tp.tanggal < ?';
        $bind_types = 'ss';
        $bind_values = [$periode_awal, $periode_akhir_exclusive];
    }

    $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $sql = "
        SELECT
            tp.id_transaksi_pembayaran,
            tp.id_transaksi,
            tp.id_transaksi_jual_beli,
            tp.kategori_transaksi,
            tp.tanggal,
            tp.jumlah,
            COALESCE(NULLIF(a.nama_akses, ''), NULLIF(tp.creat_by_name, ''), '-') AS nama_petugas
        FROM transaksi_pembayaran AS tp
        LEFT JOIN akses AS a ON a.id_akses = tp.creat_by_id
        $where_sql
        ORDER BY tp.tanggal ASC, tp.id_transaksi_pembayaran ASC
    ";

    $stmt = $Conn->prepare($sql);
    if (!$stmt) {
        exit('Gagal mempersiapkan query export.');
    }

    if ($bind_values) {
        $stmt->bind_param($bind_types, ...$bind_values);
    }

    if (!$stmt->execute()) {
        $stmt->close();
        exit('Gagal mengambil data pembayaran.');
    }

    $result = $stmt->get_result();
    $rows = [];
    while ($data = $result->fetch_assoc()) {
        $rows[] = [
            'id_transaksi_pembayaran' => (string) ($data['id_transaksi_pembayaran'] ?? '-'),
            'tanggal'                 => $data['tanggal'] ?? '',
            'referensi'               => !empty($data['id_transaksi'])
                ? (string) $data['id_transaksi']
                : (!empty($data['id_transaksi_jual_beli'])
                    ? (string) $data['id_transaksi_jual_beli']
                    : '-'),
            'kategori_transaksi'      => (string) ($data['kategori_transaksi'] ?? '-'),
            'jumlah'                  => (float) ($data['jumlah'] ?? 0),
            'nama_petugas'            => (string) ($data['nama_petugas'] ?? '-'),
        ];
    }
    $stmt->close();

    $format_tanggal = static function (string $tanggal): string {
        $timestamp = strtotime($tanggal);
        return $timestamp === false ? '-' : date('d/m/Y H:i', $timestamp);
    };

    $judul_periode = $periode_awal === ''
        ? 'Periode: Semua Data'
        : 'Periode: ' . date('d/m/Y', strtotime($periode_awal)) .
          ' s/d ' . date('d/m/Y', strtotime($periode_akhir));

    if ($format_data === 'HTML') {
        $esc = static function ($value): string {
            return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        };

        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html>
            <html lang="id">
            <head>
                <meta charset="UTF-8">
                <title>Laporan Pembayaran</title>
                <style>
                    body { font-family: Arial, sans-serif; color: #222; margin: 24px; }
                    h1 { margin: 0 0 6px; text-align: center; font-size: 22px; }
                    .periode { margin-bottom: 20px; text-align: center; color: #555; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #777; padding: 8px; font-size: 13px; }
                    th { background: #e9ecef; text-align: center; }
                    td:first-child { text-align: center; }
                    td.nominal { text-align: right; }
                    @media print { body { margin: 0; } }
                </style>
            </head>
            <body>
                <h1>LAPORAN PEMBAYARAN</h1>
                <div class="periode">' . $esc($judul_periode) . '</div>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Pembayaran</th>
                            <th>Tanggal Pembayaran</th>
                            <th>Referensi</th>
                            <th>Kategori Transaksi</th>
                            <th>Nominal</th>
                            <th>Nama Petugas (Creat By)</th>
                        </tr>
                    </thead>
                    <tbody>';

        if (!$rows) {
            echo '<tr><td colspan="7" style="text-align:center;">Tidak ada data pembayaran.</td></tr>';
        } else {
            foreach ($rows as $index => $row) {
                echo '<tr>
                    <td>' . ($index + 1) . '</td>
                    <td>' . $esc($row['id_transaksi_pembayaran']) . '</td>
                    <td>' . $esc($format_tanggal($row['tanggal'])) . '</td>
                    <td>' . $esc($row['referensi']) . '</td>
                    <td>' . $esc($row['kategori_transaksi']) . '</td>
                    <td class="nominal">Rp ' . number_format($row['jumlah'], 0, ',', '.') . '</td>
                    <td>' . $esc($row['nama_petugas']) . '</td>
                </tr>';
            }
        }

        echo '</tbody>
                </table>
            </body>
            </html>';
        exit;
    }
    require '../../vendor/autoload.php';

    if (!class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
        exit('Pustaka PHPSpreadsheet tidak tersedia.');
    }

    $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Pembayaran');

    // =====================================================
    // JUDUL
    // =====================================================
    $sheet->mergeCells('A1:G1');
    $sheet->setCellValue('A1', 'LAPORAN PEMBAYARAN');

    $sheet->mergeCells('A2:G2');
    $sheet->setCellValue('A2', $judul_periode);

    // =====================================================
    // HEADER
    // =====================================================
    $headers = [
        'A4' => 'No',
        'B4' => 'ID Pembayaran',
        'C4' => 'Tanggal Pembayaran',
        'D4' => 'Referensi',
        'E4' => 'Kategori Transaksi',
        'F4' => 'Nominal',
        'G4' => 'Nama Petugas (Creat By)'
    ];

    foreach ($headers as $cell => $header) {
        $sheet->setCellValue($cell, $header);
    }

    // =====================================================
    // STYLE JUDUL
    // =====================================================
    $sheet->getStyle('A1:G1')
        ->getFont()
        ->setBold(true)
        ->setSize(14);

    $sheet->getStyle('A1:G2')
        ->getAlignment()
        ->setHorizontal(
            PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
        );

    // =====================================================
    // STYLE HEADER
    // =====================================================
    $sheet->getStyle('A4:G4')
        ->getFont()
        ->setBold(true);

    $sheet->getStyle('A4:G4')
        ->getAlignment()
        ->setHorizontal(
            PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
        );

    $sheet->getStyle('A4:G4')
        ->getFill()
        ->setFillType(
            PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID
        )
        ->getStartColor()
        ->setARGB('D9EAF7');

    // =====================================================
    // ISI DATA
    // =====================================================
    foreach ($rows as $index => $row) {

        $excel_row = $index + 5;

        // No
        $sheet->setCellValue(
            'A' . $excel_row,
            $index + 1
        );

        // ID Pembayaran
        $sheet->setCellValueExplicit(
            'B' . $excel_row,
            $row['id_transaksi_pembayaran'],
            PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
        );

        // Tanggal
        $sheet->setCellValue(
            'C' . $excel_row,
            $format_tanggal($row['tanggal'])
        );

        // Referensi
        $sheet->setCellValueExplicit(
            'D' . $excel_row,
            $row['referensi'],
            PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
        );

        // Kategori
        $sheet->setCellValue(
            'E' . $excel_row,
            $row['kategori_transaksi']
        );

        // Nominal
        $sheet->setCellValue(
            'F' . $excel_row,
            $row['jumlah']
        );

        $sheet->getStyle('F' . $excel_row)
            ->getNumberFormat()
            ->setFormatCode('#,##0');

        // Nama Petugas
        $sheet->setCellValue(
            'G' . $excel_row,
            $row['nama_petugas']
        );
    }

    // =====================================================
    // AUTOSIZE
    // =====================================================
    foreach (range('A', 'G') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    // =====================================================
    // BORDER
    // =====================================================
    $lastRow = count($rows) + 4;

    if ($lastRow >= 4) {
        $sheet->getStyle('A4:G' . $lastRow)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(
                PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
            );
    }

    // =====================================================
    // FORMAT KOLOM
    // =====================================================
    $sheet->getStyle('A5:A' . max(5, $lastRow))
        ->getAlignment()
        ->setHorizontal(
            PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
        );

    $sheet->getStyle('F5:F' . max(5, $lastRow))
        ->getAlignment()
        ->setHorizontal(
            PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
        );

    // =====================================================
    // FREEZE HEADER
    // =====================================================
    $sheet->freezePane('A5');

    // =====================================================
    // FILE NAME
    // =====================================================
    $filename = 'Laporan-Pembayaran';

    if ($periode_awal !== '') {
        $filename .= '-' . $periode_awal . '-sd-' . $periode_akhir;
    }

    $filename .= '.xlsx';

    // =====================================================
    // BERSIHKAN OUTPUT BUFFER
    // =====================================================
    if (ob_get_length()) {
        ob_end_clean();
    }

    // =====================================================
    // HEADER DOWNLOAD
    // =====================================================
    header(
        'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    );

    header(
        'Content-Disposition: attachment; filename="' . $filename . '"'
    );

    header('Cache-Control: max-age=0');
    header('Expires: 0');
    header('Pragma: public');

    // =====================================================
    // OUTPUT
    // =====================================================
    $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');

    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);

    exit;
?>
