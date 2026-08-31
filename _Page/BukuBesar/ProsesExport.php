<?php

    // ============================================================
    // KONFIGURASI
    // ============================================================
    date_default_timezone_set('Asia/Jakarta');

    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // ============================================================
    // VALIDASI SESSION
    // ============================================================
    if (empty($SessionIdAkses)) {
        http_response_code(401);
        exit('Sesi akses sudah berakhir. Silakan login ulang.');
    }

    // ============================================================
    // VALIDASI REQUEST
    // ============================================================
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Metode request tidak valid.');
    }

    // ============================================================
    // PARAMETER
    // ============================================================
    $id_perkiraan = trim($_POST['id_perkiraan'] ?? '');
    $periode1     = trim($_POST['periode1'] ?? '');
    $periode2     = trim($_POST['periode2'] ?? '');

    if ($id_perkiraan === '' || $periode1 === '' || $periode2 === '') {
        exit('Akun perkiraan dan periode wajib diisi.');
    }

    // ============================================================
    // VALIDASI ID PERKIRAAN
    // ============================================================
    if (!ctype_digit($id_perkiraan) || (int) $id_perkiraan < 1) {
        exit('ID akun perkiraan tidak valid.');
    }

    $id_perkiraan = (int) $id_perkiraan;

    // ============================================================
    // VALIDASI TANGGAL
    // ============================================================
    $valid_date = static function (string $date): bool {

        $date_object = DateTime::createFromFormat('Y-m-d', $date);

        return
            $date_object !== false &&
            $date_object->format('Y-m-d') === $date;
    };

    if (!$valid_date($periode1) || !$valid_date($periode2)) {
        exit('Format periode tidak valid.');
    }

    if ($periode1 > $periode2) {
        exit('Periode awal tidak boleh lebih besar dari periode akhir.');
    }

    // Tanggal akhir exclusive supaya tetap aman apabila field tanggal
    // nantinya menggunakan DATETIME
    $periode2_exclusive = (new DateTime($periode2))
        ->modify('+1 day')
        ->format('Y-m-d');

    // ============================================================
    // AMBIL DATA AKUN
    // ============================================================
    $stmt_akun = $Conn->prepare("
        SELECT
            kode,
            nama,
            saldo_normal
        FROM akun_perkiraan
        WHERE id_perkiraan = ?
        LIMIT 1
    ");

    if (!$stmt_akun) {
        exit('Gagal mempersiapkan query akun perkiraan.');
    }

    $stmt_akun->bind_param('i', $id_perkiraan);

    if (!$stmt_akun->execute()) {
        $stmt_akun->close();
        exit('Gagal mengambil data akun perkiraan.');
    }

    $result_akun = $stmt_akun->get_result();
    $akun        = $result_akun->fetch_assoc();

    $stmt_akun->close();

    if (!$akun) {
        exit('Akun perkiraan tidak ditemukan.');
    }

    // ============================================================
    // VARIABEL AKUN
    // ============================================================
    $kode_akun = (string) ($akun['kode'] ?? '');
    $nama_akun = (string) ($akun['nama'] ?? '');

    $saldo_normal_db = strtoupper(
        trim((string) ($akun['saldo_normal'] ?? ''))
    );

    if ($saldo_normal_db === 'DEBET') {
        $saldo_normal = 'D';
        $nama_saldo_normal = 'Debet';
    } else {
        $saldo_normal = 'K';
        $nama_saldo_normal = 'Kredit';
    }

    // ============================================================
    // HITUNG SALDO SEBELUM PERIODE
    // ============================================================
    $stmt_saldo = $Conn->prepare("
        SELECT
            COALESCE(
                SUM(
                    CASE
                        WHEN d_k = 'D'
                        THEN nilai
                        ELSE 0
                    END
                ),
                0
            ) AS total_debet,

            COALESCE(
                SUM(
                    CASE
                        WHEN d_k = 'K'
                        THEN nilai
                        ELSE 0
                    END
                ),
                0
            ) AS total_kredit

        FROM jurnal

        WHERE kode_perkiraan = ?
        AND tanggal < ?
    ");

    if (!$stmt_saldo) {
        exit('Gagal mempersiapkan query saldo awal.');
    }

    $stmt_saldo->bind_param(
        'ss',
        $kode_akun,
        $periode1
    );

    if (!$stmt_saldo->execute()) {
        $stmt_saldo->close();
        exit('Gagal menghitung saldo awal.');
    }

    $result_saldo = $stmt_saldo->get_result();
    $saldo_awal_data = $result_saldo->fetch_assoc();

    $stmt_saldo->close();

    $total_debet_awal = (float) ($saldo_awal_data['total_debet'] ?? 0);
    $total_kredit_awal = (float) ($saldo_awal_data['total_kredit'] ?? 0);

    // ============================================================
    // SALDO AWAL BERDASARKAN SALDO NORMAL
    // ============================================================
    if ($saldo_normal === 'D') {

        $saldo_awal =
            $total_debet_awal -
            $total_kredit_awal;

    } else {

        $saldo_awal =
            $total_kredit_awal -
            $total_debet_awal;
    }

    // ============================================================
    // AMBIL JURNAL DALAM PERIODE
    // ============================================================
    $stmt_jurnal = $Conn->prepare("
        SELECT
            id_jurnal,
            uuid,
            tanggal,
            kategori,
            d_k,
            nilai

        FROM jurnal

        WHERE kode_perkiraan = ?
        AND tanggal >= ?
        AND tanggal < ?

        ORDER BY
            tanggal ASC,
            id_jurnal ASC
    ");

    if (!$stmt_jurnal) {
        exit('Gagal mempersiapkan query jurnal.');
    }

    $stmt_jurnal->bind_param(
        'sss',
        $kode_akun,
        $periode1,
        $periode2_exclusive
    );

    if (!$stmt_jurnal->execute()) {
        $stmt_jurnal->close();
        exit('Gagal mengambil data jurnal.');
    }

    $result_jurnal = $stmt_jurnal->get_result();

    // ============================================================
    // SUSUN DATA TRANSAKSI + SALDO BERJALAN
    // ============================================================
    $transaksi       = [];
    $saldo_berjalan  = $saldo_awal;

    $total_debet_periode  = 0;
    $total_kredit_periode = 0;

    while ($data = $result_jurnal->fetch_assoc()) {

        $nilai = (float) ($data['nilai'] ?? 0);

        $d_k = strtoupper(
            trim((string) ($data['d_k'] ?? ''))
        );

        $debet  = 0;
        $kredit = 0;

        if ($d_k === 'D') {

            $debet = $nilai;
            $total_debet_periode += $nilai;

        } elseif ($d_k === 'K') {

            $kredit = $nilai;
            $total_kredit_periode += $nilai;
        }

        // ========================================================
        // HITUNG SALDO BERJALAN
        // ========================================================
        if ($d_k === $saldo_normal) {
            $saldo_berjalan += $nilai;
        } else {
            $saldo_berjalan -= $nilai;
        }

        $transaksi[] = [
            'id_jurnal' => (string) ($data['id_jurnal'] ?? ''),
            'uuid'      => (string) ($data['uuid'] ?? '-'),
            'tanggal'   => (string) ($data['tanggal'] ?? ''),
            'kategori'  => (string) ($data['kategori'] ?? '-'),
            'debet'     => $debet,
            'kredit'    => $kredit,
            'saldo'     => $saldo_berjalan,
        ];
    }

    $stmt_jurnal->close();

    // ============================================================
    // FORMAT
    // ============================================================
    $judul_periode =
        date('d/m/Y', strtotime($periode1)) .
        ' s/d ' .
        date('d/m/Y', strtotime($periode2));

    $format_tanggal = static function (string $tanggal): string {

        if ($tanggal === '') {
            return '-';
        }

        $timestamp = strtotime($tanggal);

        return $timestamp === false
            ? '-'
            : date('d/m/Y', $timestamp);
    };

    // ============================================================
    // PHP SPREADSHEET
    // ============================================================
    require '../../vendor/autoload.php';

    if (!class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
        exit('Pustaka PHPSpreadsheet tidak tersedia.');
    }

    $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();

    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Buku Besar');

    // ============================================================
    // JUDUL
    // ============================================================
    $sheet->mergeCells('A1:H1');
    $sheet->setCellValue(
        'A1',
        'LAPORAN BUKU BESAR'
    );

    $sheet->mergeCells('A2:H2');
    $sheet->setCellValue(
        'A2',
        $nama_akun .
        ' (Kode Akun: ' .
        $kode_akun .
        ')'
    );

    $sheet->mergeCells('A3:H3');
    $sheet->setCellValue(
        'A3',
        'Periode: ' .
        $judul_periode .
        ' | Saldo Normal: ' .
        $nama_saldo_normal
    );

    // ============================================================
    // SALDO SEBELUM PERIODE
    // ============================================================
    $sheet->mergeCells('A4:E4');

    $sheet->setCellValue(
        'A4',
        'Saldo Sebelum Periode ' .
        date('d/m/Y', strtotime($periode1))
    );

    $sheet->setCellValue(
        'F4',
        $total_debet_awal
    );

    $sheet->setCellValue(
        'G4',
        $total_kredit_awal
    );

    $sheet->setCellValue(
        'H4',
        $saldo_awal
    );

    // ============================================================
    // HEADER
    // ============================================================
    $headers = [
        'A5' => 'No',
        'B5' => 'ID Jurnal',
        'C5' => 'Tanggal',
        'D5' => 'Referensi',
        'E5' => 'Kategori',
        'F5' => 'Debet',
        'G5' => 'Kredit',
        'H5' => 'Saldo',
    ];

    foreach ($headers as $cell => $header) {
        $sheet->setCellValue(
            $cell,
            $header
        );
    }

    // ============================================================
    // STYLE JUDUL
    // ============================================================
    $sheet->getStyle('A1:H3')
        ->getAlignment()
        ->setHorizontal(
            PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
        );

    $sheet->getStyle('A1')
        ->getFont()
        ->setBold(true)
        ->setSize(14);

    $sheet->getStyle('A2:H3')
        ->getFont()
        ->setBold(true);

    // ============================================================
    // STYLE SALDO AWAL
    // ============================================================
    $sheet->getStyle('A4:H4')
        ->getFont()
        ->setBold(true);

    // ============================================================
    // STYLE HEADER
    // ============================================================
    $sheet->getStyle('A5:H5')
        ->getFont()
        ->setBold(true);

    $sheet->getStyle('A5:H5')
        ->getAlignment()
        ->setHorizontal(
            PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
        );

    $sheet->getStyle('A5:H5')
        ->getFill()
        ->setFillType(
            PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID
        )
        ->getStartColor()
        ->setARGB('D9EAF7');

    // ============================================================
    // DATA TRANSAKSI
    // ============================================================
    foreach ($transaksi as $index => $data) {

        $row = $index + 6;

        // No
        $sheet->setCellValue(
            'A' . $row,
            $index + 1
        );

        // ID Jurnal
        $sheet->setCellValueExplicit(
            'B' . $row,
            $data['id_jurnal'],
            PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
        );

        // Tanggal
        $sheet->setCellValue(
            'C' . $row,
            $format_tanggal($data['tanggal'])
        );

        // UUID / Referensi
        $sheet->setCellValueExplicit(
            'D' . $row,
            $data['uuid'],
            PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
        );

        // Kategori
        $sheet->setCellValue(
            'E' . $row,
            $data['kategori']
        );

        // Debet
        $sheet->setCellValue(
            'F' . $row,
            $data['debet']
        );

        // Kredit
        $sheet->setCellValue(
            'G' . $row,
            $data['kredit']
        );

        // Saldo
        $sheet->setCellValue(
            'H' . $row,
            $data['saldo']
        );
    }

    // ============================================================
    // TOTAL PERIODE
    // ============================================================
    $total_row = count($transaksi) + 6;

    $sheet->mergeCells(
        'A' . $total_row .
        ':E' . $total_row
    );

    $sheet->setCellValue(
        'A' . $total_row,
        'TOTAL MUTASI PERIODE'
    );

    $sheet->setCellValue(
        'F' . $total_row,
        $total_debet_periode
    );

    $sheet->setCellValue(
        'G' . $total_row,
        $total_kredit_periode
    );

    $sheet->setCellValue(
        'H' . $total_row,
        $saldo_berjalan
    );

    $sheet->getStyle(
        'A' . $total_row .
        ':H' . $total_row
    )->getFont()->setBold(true);

    // ============================================================
    // FORMAT ANGKA
    // ============================================================
    $sheet->getStyle(
        'F4:H' . $total_row
    )
    ->getNumberFormat()
    ->setFormatCode('#,##0');

    // ============================================================
    // BORDER
    // ============================================================
    $sheet->getStyle(
        'A4:H' . $total_row
    )
    ->getBorders()
    ->getAllBorders()
    ->setBorderStyle(
        PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
    );

    // ============================================================
    // ALIGNMENT
    // ============================================================
    $sheet->getStyle(
        'A5:A' . $total_row
    )
    ->getAlignment()
    ->setHorizontal(
        PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    );

    $sheet->getStyle(
        'C5:C' . $total_row
    )
    ->getAlignment()
    ->setHorizontal(
        PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    );

    $sheet->getStyle(
        'F4:H' . $total_row
    )
    ->getAlignment()
    ->setHorizontal(
        PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
    );

    // ============================================================
    // FREEZE HEADER
    // ============================================================
    $sheet->freezePane('A6');

    // ============================================================
    // AUTO SIZE
    // ============================================================
    foreach (range('A', 'H') as $column) {
        $sheet
            ->getColumnDimension($column)
            ->setAutoSize(true);
    }

    // ============================================================
    // PRINT SETTING
    // ============================================================
    $sheet->getPageSetup()
        ->setOrientation(
            PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
        );

    $sheet->getPageSetup()
        ->setFitToWidth(1)
        ->setFitToHeight(0);

    // ============================================================
    // FILE NAME
    // ============================================================
    $kode_filename = preg_replace(
        '/[^A-Za-z0-9_-]/',
        '_',
        $kode_akun
    );

    $filename =
        'Laporan_BukuBesar_' .
        $kode_filename .
        '_' .
        $periode1 .
        '_sd_' .
        $periode2 .
        '.xlsx';

    // ============================================================
    // BERSIHKAN OUTPUT BUFFER
    // ============================================================
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    // ============================================================
    // HEADER DOWNLOAD
    // ============================================================
    header(
        'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    );

    header(
        'Content-Disposition: attachment; filename="' .
        $filename .
        '"'
    );

    header('Cache-Control: max-age=0');
    header('Pragma: public');
    header('Expires: 0');

    // ============================================================
    // OUTPUT
    // ============================================================
    $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx(
        $spreadsheet
    );

    $writer->save('php://output');

    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);

    exit;

?>