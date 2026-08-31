<?php
    date_default_timezone_set('Asia/Jakarta');

    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    if (empty($SessionIdAkses)) {
        http_response_code(401);
        exit('Sesi akses sudah berakhir. Silakan login ulang.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Metode request tidak valid.');
    }

    $akun_pemasukan = trim($_POST['akun_pemasukan'] ?? '');
    $akun_pengeluaran = trim($_POST['akun_pengeluaran'] ?? '');
    $periode1 = trim($_POST['periode1'] ?? '');
    $periode2 = trim($_POST['periode2'] ?? '');

    if ($akun_pemasukan === '' || $akun_pengeluaran === '' || $periode1 === '' || $periode2 === '') {
        exit('Lengkapi akun pemasukan, akun pengeluaran, dan periode laporan.');
    }
    if (!ctype_digit($akun_pemasukan) || !ctype_digit($akun_pengeluaran)) {
        exit('ID akun perkiraan tidak valid.');
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
    $id_pemasukan = (int) $akun_pemasukan;
    $id_pengeluaran = (int) $akun_pengeluaran;

    // Ambil akun sekali agar nama dan saldo normal transaksi dapat dipetakan di PHP.
    $result_akun = mysqli_query($Conn, "
        SELECT id_perkiraan, kode, nama, saldo_normal, level
        FROM akun_perkiraan
        ORDER BY kode ASC
    ");
    if (!$result_akun) {
        exit('Gagal mengambil data akun perkiraan.');
    }

    $akun_list = [];
    $akun_by_id = [];
    $akun_by_kode = [];
    while ($akun = mysqli_fetch_assoc($result_akun)) {
        $akun['id_perkiraan'] = (string) $akun['id_perkiraan'];
        $akun['kode'] = (string) $akun['kode'];
        $akun['level'] = (int) $akun['level'];
        $akun_list[] = $akun;
        $akun_by_id[$akun['id_perkiraan']] = $akun;
        $akun_by_kode[$akun['kode']] = $akun;
    }

    if (!isset($akun_by_id[$akun_pemasukan]) || !isset($akun_by_id[$akun_pengeluaran])) {
        exit('Akun perkiraan yang dipilih tidak ditemukan.');
    }

    $akun_pilihan = [
        'pemasukan' => $akun_by_id[$akun_pemasukan],
        'pengeluaran' => $akun_by_id[$akun_pengeluaran]
    ];

    $kode_conditions = [];
    $kode_params = [];
    foreach ($akun_pilihan as $akun_induk) {
        if ($akun_induk['level'] === 1) {
            // Tabel laporan hanya menampilkan akun detail di bawah akun level 1.
            $kode_conditions[] = '(j.kode_perkiraan LIKE ? AND j.kode_perkiraan <> ?)';
            $kode_params[] = $akun_induk['kode'] . '%';
            $kode_params[] = $akun_induk['kode'];
        } else {
            $kode_conditions[] = 'j.kode_perkiraan = ?';
            $kode_params[] = $akun_induk['kode'];
        }
    }

    $stmt_jurnal = $Conn->prepare(" 
        SELECT j.tanggal, j.kategori, j.kode_perkiraan, j.d_k, j.nilai, j.nama_perkiraan
        FROM jurnal AS j
        WHERE j.tanggal >= ? AND j.tanggal < ?
          AND (" . implode(' OR ', $kode_conditions) . ")
        ORDER BY j.kode_perkiraan ASC, j.id_jurnal DESC
    ");
    if (!$stmt_jurnal) {
        exit('Gagal mempersiapkan query jurnal.');
    }
    $bind_values = array_merge([$periode1, $periode2_exclusive], $kode_params);
    $stmt_jurnal->bind_param(str_repeat('s', count($bind_values)), ...$bind_values);
    if (!$stmt_jurnal->execute()) {
        $stmt_jurnal->close();
        exit('Gagal mengambil data jurnal.');
    }

    $jurnal_by_kode = [];
    $result_jurnal = $stmt_jurnal->get_result();
    while ($jurnal = $result_jurnal->fetch_assoc()) {
        $jurnal_by_kode[(string) $jurnal['kode_perkiraan']][] = $jurnal;
    }
    $stmt_jurnal->close();

    $format_date = static function (string $date): string {
        $timestamp = strtotime($date);
        return $timestamp === false ? '-' : date('d/m/Y', $timestamp);
    };

    require '../../vendor/autoload.php';
    if (!class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
        exit('Pustaka PHPSpreadsheet tidak tersedia.');
    }

    $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Laba Rugi');

    $judul_periode = $format_date($periode1) . ' s/d ' . $format_date($periode2);
    $sheet->mergeCells('A1:G1');
    $sheet->setCellValue('A1', 'LAPORAN LABA RUGI');
    $sheet->mergeCells('A2:G2');
    $sheet->setCellValue('A2', 'Periode: ' . $judul_periode);
    $sheet->mergeCells('A3:G3');
    $sheet->setCellValue(
        'A3',
        'Pemasukan: ' . $akun_pilihan['pemasukan']['kode'] . ' - ' . $akun_pilihan['pemasukan']['nama'] .
        ' | Pengeluaran: ' . $akun_pilihan['pengeluaran']['kode'] . ' - ' . $akun_pilihan['pengeluaran']['nama']
    );

    $headers = [
        'A5' => 'No',
        'B5' => 'Tanggal',
        'C5' => 'Kategori',
        'D5' => 'Akun Perkiraan',
        'E5' => 'Posisi',
        'F5' => 'Nilai',
        'G5' => 'Saldo'
    ];
    foreach ($headers as $cell => $header) {
        $sheet->setCellValue($cell, $header);
    }

    $excel_row = 6;
    $saldo = ['pemasukan' => 0, 'pengeluaran' => 0];
    $nomor = ['pemasukan' => 1, 'pengeluaran' => 1];
    $total = ['pemasukan' => 0, 'pengeluaran' => 0];

    foreach (['pemasukan', 'pengeluaran'] as $jenis) {
        $akun_induk = $akun_pilihan[$jenis];
        $label = $jenis === 'pemasukan' ? 'A.' : 'B.';
        $judul = $jenis === 'pemasukan' ? 'Transaksi Pemasukan' : 'Transaksi Pengeluaran';

        $sheet->mergeCells('A' . $excel_row . ':G' . $excel_row);
        $sheet->setCellValue('A' . $excel_row, $label . ' ' . $judul);
        $sheet->getStyle('A' . $excel_row . ':G' . $excel_row)->getFont()->setBold(true);
        $excel_row++;

        foreach ($akun_list as $akun) {
            $kode = $akun['kode'];
            $termasuk = $akun_induk['level'] === 1
                ? $akun['level'] > 1 && strpos($kode, $akun_induk['kode']) === 0
                : $kode === $akun_induk['kode'];

            if (!$termasuk || empty($jurnal_by_kode[$kode])) {
                continue;
            }

            foreach ($jurnal_by_kode[$kode] as $jurnal) {
                $nilai = (float) ($jurnal['nilai'] ?? 0);
                $d_k = strtoupper(trim((string) ($jurnal['d_k'] ?? '')));
                $posisi = $d_k === 'D' ? 'Debet' : 'Kredit';
                $normal = strcasecmp($posisi, (string) $akun['saldo_normal']) === 0;
                $saldo[$jenis] += $normal ? $nilai : -$nilai;
                $total[$jenis] += $nilai;

                $sheet->setCellValue('A' . $excel_row, $label . $nomor[$jenis]);
                $sheet->setCellValue('B' . $excel_row, $format_date((string) $jurnal['tanggal']));
                $sheet->setCellValue('C' . $excel_row, (string) ($jurnal['kategori'] ?: '-'));
                $sheet->setCellValue('D' . $excel_row, $kode . ' - ' . ($jurnal['nama_perkiraan'] ?: $akun['nama']));
                $sheet->setCellValue('E' . $excel_row, $normal ? $posisi : '(' . $posisi . ')');
                $sheet->setCellValue('F' . $excel_row, $nilai);
                $sheet->setCellValue('G' . $excel_row, $saldo[$jenis]);
                $nomor[$jenis]++;
                $excel_row++;
            }
        }

        $sheet->mergeCells('A' . $excel_row . ':E' . $excel_row);
        $sheet->setCellValue('A' . $excel_row, 'Total ' . ucfirst($jenis));
        $sheet->setCellValue('F' . $excel_row, $total[$jenis]);
        $sheet->setCellValue('G' . $excel_row, $saldo[$jenis]);
        $sheet->getStyle('A' . $excel_row . ':G' . $excel_row)->getFont()->setBold(true);
        $excel_row += 2;
    }

    $laba_rugi = $saldo['pemasukan'] - $saldo['pengeluaran'];
    $sheet->mergeCells('A' . $excel_row . ':F' . $excel_row);
    $sheet->setCellValue('A' . $excel_row, $laba_rugi >= 0 ? 'LABA' : 'RUGI');
    $sheet->setCellValue('G' . $excel_row, abs($laba_rugi));
    $sheet->getStyle('A' . $excel_row . ':G' . $excel_row)->getFont()->setBold(true);

    $sheet->getStyle('A1:G3')->getAlignment()->setHorizontal(
        PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    );
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A2:G2')->getFont()->setBold(true);
    $sheet->getStyle('A5:G5')->getFont()->setBold(true);
    $sheet->getStyle('A5:G5')->getFill()
        ->setFillType(PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setARGB('D9EAF7');
    $sheet->getStyle('F6:G' . $excel_row)->getNumberFormat()->setFormatCode('#,##0');
    $sheet->getStyle('A5:G' . $excel_row)->getBorders()->getAllBorders()->setBorderStyle(
        PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
    );
    $sheet->getStyle('A5:G' . $excel_row)->getAlignment()->setVertical(
        PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
    );

    foreach (range('A', 'G') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }
    $sheet->freezePane('A6');
    $sheet->getPageSetup()->setOrientation(
        PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
    );
    $sheet->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);

    $filename_date = preg_replace('/[^A-Za-z0-9_-]/', '_', $periode1 . '_sd_' . $periode2);
    $filename = 'Laporan_LabaRugi_' . $filename_date . '.xlsx';
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
