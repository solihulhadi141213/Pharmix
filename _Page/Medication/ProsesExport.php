<?php
    // Koneksi, Function, Session Dan Composer
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    require "../../vendor/autoload.php";

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\Writer\Csv;
    use PhpOffice\PhpSpreadsheet\Style\Alignment;
    use PhpOffice\PhpSpreadsheet\Style\Border;
    use PhpOffice\PhpSpreadsheet\Style\Fill;

    date_default_timezone_set('Asia/Jakarta');

    // Validasi Session
    if (empty($SessionIdAkses)) {
        http_response_code(401);
        exit('Sesi akses telah berakhir. Silakan login ulang.');
    }

    // Validasi Method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Metode request tidak valid.');
    }

    // Tangkap Parameter
    $type_file = strtoupper(trim($_POST['type_file'] ?? 'XLSX'));

    if (!in_array($type_file, ['XLSX', 'CSV'], true)) {
        exit('Format file tidak valid.');
    }

    // Ambil Medication
    $sql = "
        SELECT
            id_index_medication,
            id_medication,
            medication_code,
            medication_name,
            medication_category,
            kfa_code,
            kfa_display,
            sediaan_code,
            sediaan_display,
            racikan_code,
            racikan_display,
            manufacturer_id,
            manufacturer_name,
            ingredient
        FROM medication
        ORDER BY medication_name ASC
    ";

    $query = $Conn->query($sql);

    if (!$query) {
        exit('Gagal mengambil data medication.');
    }

    if ($query->num_rows < 1) {
        exit('Tidak ada data medication yang dapat diexport.');
    }

    // Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Medication');

    // Header
    $header = [
        'No',
        'ID Index',
        'ID Medication SATUSEHAT',
        'Kode Lokal',
        'Nama Medication',
        'Kategori',
        'Kode KFA',
        'Display KFA',
        'Kode Sediaan',
        'Display Sediaan',
        'Kode Racikan',
        'Display Racikan',
        'ID Manufacturer',
        'Nama Manufacturer',
        'Ingredient'
    ];

    $column = 'A';

    foreach ($header as $title) {
        $sheet->setCellValue($column.'1', $title);
        $column++;
    }

    // Isi Data
    $row = 2;
    $no  = 1;

    while ($data = $query->fetch_assoc()) {

        // Ingredient tetap sebagai JSON
        $ingredient = trim((string)($data['ingredient'] ?? ''));

        $sheet->setCellValue('A'.$row, $no);
        $sheet->setCellValue('B'.$row, $data['id_index_medication']);
        $sheet->setCellValueExplicit(
            'C'.$row,
            (string)($data['id_medication'] ?? ''),
            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
        );
        $sheet->setCellValueExplicit(
            'D'.$row,
            (string)($data['medication_code'] ?? ''),
            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
        );
        $sheet->setCellValue('E'.$row, $data['medication_name'] ?? '');
        $sheet->setCellValue('F'.$row, $data['medication_category'] ?? '');
        $sheet->setCellValueExplicit(
            'G'.$row,
            (string)($data['kfa_code'] ?? ''),
            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
        );
        $sheet->setCellValue('H'.$row, $data['kfa_display'] ?? '');
        $sheet->setCellValueExplicit(
            'I'.$row,
            (string)($data['sediaan_code'] ?? ''),
            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
        );
        $sheet->setCellValue('J'.$row, $data['sediaan_display'] ?? '');
        $sheet->setCellValue('K'.$row, $data['racikan_code'] ?? '');
        $sheet->setCellValue('L'.$row, $data['racikan_display'] ?? '');
        $sheet->setCellValueExplicit(
            'M'.$row,
            (string)($data['manufacturer_id'] ?? ''),
            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
        );
        $sheet->setCellValue('N'.$row, $data['manufacturer_name'] ?? '');
        $sheet->setCellValue('O'.$row, $ingredient);

        $row++;
        $no++;
    }

    // Style Header
    $lastRow = $row - 1;

    $sheet->getStyle('A1:O1')->getFont()->setBold(true);

    $sheet->getStyle('A1:O1')->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER);

    $sheet->getStyle('A1:O1')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()
        ->setARGB('FFE9ECEF');

    // Border
    $sheet->getStyle('A1:O'.$lastRow)->getBorders()->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN);

    // Alignment
    $sheet->getStyle('A1:O'.$lastRow)
        ->getAlignment()
        ->setVertical(Alignment::VERTICAL_TOP);

    // Wrap Text
    $sheet->getStyle('E2:O'.$lastRow)
        ->getAlignment()
        ->setWrapText(true);

    // Auto Filter
    $sheet->setAutoFilter('A1:O'.$lastRow);

    // Freeze Header
    $sheet->freezePane('A2');

    // Width Column
    $sheet->getColumnDimension('A')->setWidth(7);
    $sheet->getColumnDimension('B')->setWidth(12);
    $sheet->getColumnDimension('C')->setWidth(38);
    $sheet->getColumnDimension('D')->setWidth(18);
    $sheet->getColumnDimension('E')->setWidth(45);
    $sheet->getColumnDimension('F')->setWidth(15);
    $sheet->getColumnDimension('G')->setWidth(18);
    $sheet->getColumnDimension('H')->setWidth(45);
    $sheet->getColumnDimension('I')->setWidth(18);
    $sheet->getColumnDimension('J')->setWidth(30);
    $sheet->getColumnDimension('K')->setWidth(15);
    $sheet->getColumnDimension('L')->setWidth(25);
    $sheet->getColumnDimension('M')->setWidth(38);
    $sheet->getColumnDimension('N')->setWidth(35);
    $sheet->getColumnDimension('O')->setWidth(60);

    // Nama File
    $filename = 'Medication_'.date('Ymd_His');

    // XLSX
    if ($type_file === 'XLSX') {

        $filename .= '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        $spreadsheet->disconnectWorksheets();
        exit;
    }

    // CSV
    $filename .= '.csv';

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    header('Cache-Control: max-age=0');

    // BOM UTF-8 Agar Excel Membaca Karakter Dengan Benar
    echo "\xEF\xBB\xBF";

    $writer = new Csv($spreadsheet);
    $writer->setDelimiter(';');
    $writer->setEnclosure('"');
    $writer->setLineEnding("\r\n");
    $writer->setSheetIndex(0);
    $writer->save('php://output');

    $spreadsheet->disconnectWorksheets();
    exit;
?>