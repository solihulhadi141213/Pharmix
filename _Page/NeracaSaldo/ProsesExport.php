<?php
    date_default_timezone_set('Asia/Jakarta');

    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/SettingGeneral.php";
    include "../../_Config/Session.php";

    if (empty($SessionIdAkses)) {
        http_response_code(401);
        exit('Sesi akses sudah berakhir. Silakan login ulang.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Metode request tidak valid.');
    }

    $periode1 = trim($_POST['periode1'] ?? '');
    $periode2 = trim($_POST['periode2'] ?? '');

    if ($periode1 === '' || $periode2 === '') {
        exit('Periode awal dan periode akhir wajib diisi.');
    }

    $valid_date = static function (string $date): bool {
        $date_object = DateTime::createFromFormat('Y-m-d', $date);
        return $date_object !== false && $date_object->format('Y-m-d') === $date;
    };

    if (!$valid_date($periode1) || !$valid_date($periode2)) {
        exit('Format periode tidak valid.');
    }

    if ($periode1 > $periode2) {
        exit('Periode awal tidak boleh lebih besar dari periode akhir.');
    }

    $periode2_exclusive = (new DateTime($periode2))->modify('+1 day')->format('Y-m-d');

    // Ambil akun level 1 dan akun detail dalam satu query.
    $result_akun = mysqli_query($Conn, "
        SELECT *
        FROM akun_perkiraan
        ORDER BY kode ASC, nama ASC
    ");

    if (!$result_akun) {
        exit('Gagal mengambil data akun perkiraan.');
    }

    $akun_list = [];
    $akun_parent = [];
    while ($akun = mysqli_fetch_assoc($result_akun)) {
        $level = (int) ($akun['level'] ?? 0);
        $kode = (string) ($akun['kode'] ?? '');
        $akun_list[] = $akun;

        $kolom_parent = 'kd' . $level;
        if ($level > 0 && !empty($akun[$kolom_parent])) {
            $akun_parent[$level][(string) $akun[$kolom_parent]] = true;
        }
    }

    $detail_akun = [];
    foreach ($akun_list as $akun) {
        $level = (int) ($akun['level'] ?? 0);
        $kode = (string) ($akun['kode'] ?? '');

        if ($level > 1 && !isset($akun_parent[$level][$kode])) {
            $detail_akun[] = $akun;
        }
    }

    // Ambil seluruh mutasi akun detail dalam satu query dan agregasikan di PHP.
    $stmt_jurnal = $Conn->prepare("
        SELECT kode_perkiraan, d_k, nilai
        FROM jurnal
        WHERE tanggal >= ? AND tanggal < ?
    ");
    if (!$stmt_jurnal) {
        exit('Gagal mempersiapkan query jurnal.');
    }
    $stmt_jurnal->bind_param('ss', $periode1, $periode2_exclusive);
    if (!$stmt_jurnal->execute()) {
        $stmt_jurnal->close();
        exit('Gagal mengambil data jurnal.');
    }

    $result_jurnal = $stmt_jurnal->get_result();
    $mutasi = [];
    while ($jurnal = $result_jurnal->fetch_assoc()) {
        $kode = (string) ($jurnal['kode_perkiraan'] ?? '');
        if (!isset($mutasi[$kode])) {
            $mutasi[$kode] = ['debet' => 0, 'kredit' => 0];
        }

        $nilai = (float) ($jurnal['nilai'] ?? 0);
        if (strtoupper(trim((string) $jurnal['d_k'])) === 'D') {
            $mutasi[$kode]['debet'] += $nilai;
        } elseif (strtoupper(trim((string) $jurnal['d_k'])) === 'K') {
            $mutasi[$kode]['kredit'] += $nilai;
        }
    }
    $stmt_jurnal->close();

    // Susun group dan akun detail mengikuti urutan pada TabelNeraca.php.
    $group_akun = array_values(array_filter($akun_list, static function ($akun): bool {
        return (int) ($akun['level'] ?? 0) === 1;
    }));
    usort($group_akun, static function ($a, $b): int {
        return strcasecmp((string) ($a['nama'] ?? ''), (string) ($b['nama'] ?? ''));
    });

    $display_rows = [];
    foreach ($group_akun as $group) {
        $kode_group = (string) ($group['kode'] ?? '');
        $display_rows[] = [
            'type' => 'group',
            'kode' => $kode_group,
            'nama' => (string) ($group['nama'] ?? ''),
            'saldo_normal' => (string) ($group['saldo_normal'] ?? '')
        ];

        $children = array_values(array_filter($detail_akun, static function ($akun) use ($kode_group): bool {
            return strpos((string) ($akun['kode'] ?? ''), $kode_group) === 0;
        }));
        usort($children, static function ($a, $b): int {
            return strcasecmp((string) ($a['nama'] ?? ''), (string) ($b['nama'] ?? ''));
        });

        foreach ($children as $akun) {
            $kode = (string) ($akun['kode'] ?? '');
            $debet = (float) ($mutasi[$kode]['debet'] ?? 0);
            $kredit = (float) ($mutasi[$kode]['kredit'] ?? 0);
            $saldo_normal = strtoupper(trim((string) ($akun['saldo_normal'] ?? '')));
            $saldo = $saldo_normal === 'DEBET' ? $debet - $kredit : $kredit - $debet;

            $display_rows[] = [
                'type' => 'detail',
                'kode' => $kode,
                'nama' => (string) ($akun['nama'] ?? ''),
                'saldo_normal' => (string) ($akun['saldo_normal'] ?? ''),
                'debet' => $debet,
                'kredit' => $kredit,
                'saldo' => $saldo
            ];
        }
    }

    $filename_date = $periode1 . '_sd_' . $periode2;
    $filename_date = preg_replace('/[^A-Za-z0-9_-]/', '_', $filename_date);

    require '../../vendor/autoload.php';
    if (!class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
        exit('Pustaka PHPSpreadsheet tidak tersedia.');
    }

    $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Neraca Saldo');

    $sheet->mergeCells('A1:G1');
    $sheet->setCellValue('A1', 'LAPORAN NERACA SALDO');
    $sheet->mergeCells('A2:G2');
    $sheet->setCellValue(
        'A2',
        'Periode: ' . date('d/m/Y', strtotime($periode1)) .
        ' s/d ' . date('d/m/Y', strtotime($periode2))
    );

    $headers = [
        'A4' => 'No',
        'B4' => 'Kode Akun',
        'C4' => 'Nama Akun',
        'D4' => 'Saldo Normal',
        'E4' => 'Debet',
        'F4' => 'Kredit',
        'G4' => 'Saldo'
    ];
    foreach ($headers as $cell => $header) {
        $sheet->setCellValue($cell, $header);
    }

    $total_debet = 0;
    $total_kredit = 0;
    $total_saldo = 0;
    $nomor_detail = 1;
    $excel_row = 5;
    foreach ($display_rows as $row_data) {
        if ($row_data['type'] === 'group') {
            $sheet->mergeCells('A' . $excel_row . ':G' . $excel_row);
            $sheet->setCellValue(
                'A' . $excel_row,
                $row_data['kode'] . ' - ' . $row_data['nama']
            );
            $sheet->getStyle('A' . $excel_row . ':G' . $excel_row)
                ->getFont()->setBold(true);
            $excel_row++;
            continue;
        }

        $sheet->setCellValue('A' . $excel_row, $nomor_detail);
        $sheet->setCellValueExplicit(
            'B' . $excel_row,
            $row_data['kode'],
            PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
        );
        $sheet->setCellValue('C' . $excel_row, $row_data['nama']);
        $sheet->setCellValue('D' . $excel_row, $row_data['saldo_normal']);
        $sheet->setCellValue('E' . $excel_row, $row_data['debet']);
        $sheet->setCellValue('F' . $excel_row, $row_data['kredit']);
        $sheet->setCellValue('G' . $excel_row, $row_data['saldo']);

        $total_debet += $row_data['debet'];
        $total_kredit += $row_data['kredit'];
        $total_saldo += $row_data['saldo'];
        $nomor_detail++;
        $excel_row++;
    }

    $total_row = $excel_row;
    $sheet->mergeCells('A' . $total_row . ':D' . $total_row);
    $sheet->setCellValue('A' . $total_row, 'TOTAL');
    $sheet->setCellValue('E' . $total_row, $total_debet);
    $sheet->setCellValue('F' . $total_row, $total_kredit);
    $sheet->setCellValue('G' . $total_row, $total_saldo);

    $sheet->getStyle('A1:G2')->getAlignment()->setHorizontal(
        PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    );
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A2')->getFont()->setBold(true);
    $sheet->getStyle('A4:G4')->getFont()->setBold(true);
    $sheet->getStyle('A4:G4')->getAlignment()->setHorizontal(
        PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    );
    $sheet->getStyle('A4:G4')->getFill()
        ->setFillType(PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setARGB('D9EAF7');
    $sheet->getStyle('A' . $total_row . ':G' . $total_row)->getFont()->setBold(true);
    $sheet->getStyle('E5:G' . $total_row)->getNumberFormat()->setFormatCode('#,##0');
    $sheet->getStyle('A4:G' . $total_row)->getBorders()->getAllBorders()->setBorderStyle(
        PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
    );
    $sheet->getStyle('A5:A' . $total_row)->getAlignment()->setHorizontal(
        PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    );
    $sheet->getStyle('D5:D' . $total_row)->getAlignment()->setHorizontal(
        PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    );
    $sheet->getStyle('E5:G' . $total_row)->getAlignment()->setHorizontal(
        PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
    );

    foreach (range('A', 'G') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }
    $sheet->freezePane('A5');
    $sheet->getPageSetup()->setOrientation(
        PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
    );
    $sheet->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);

    $filename = 'Laporan_NeracaSaldo_' . $filename_date . '.xlsx';
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);
    exit;
?>
