<?php
    include '../../vendor/autoload.php';
    if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
        die('Autoloader tidak berfungsi dengan benar. Kelas PhpOffice\PhpSpreadsheet\Spreadsheet tidak ditemukan.');
    }

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\Cell\DataType;
    use PhpOffice\PhpSpreadsheet\Style\Alignment;
    use PhpOffice\PhpSpreadsheet\Style\Fill;

    // Koneksi ke database
    include "../../_Config/Connection.php";

    // Validasi id_stock_opname
    if(empty($_POST['id_stock_opname'])){
        echo "ID Sesi Stock Opname Tidak Boleh Kosong";
        exit;
    }
    $id_stock_opname = $_POST['id_stock_opname'];

    // Cek jumlah data
    $StmtCek = mysqli_prepare($Conn, "SELECT id_stock_opname FROM stock_opname WHERE id_stock_opname = ?");
    mysqli_stmt_bind_param($StmtCek, "i", $id_stock_opname);
    mysqli_stmt_execute($StmtCek);
    $ResultCek = mysqli_stmt_get_result($StmtCek);
    if (mysqli_num_rows($ResultCek) == 0) {
        echo "Data Sesi Stock Opname Tidak Valid";
        exit;
    }
    mysqli_stmt_close($StmtCek);

    // Buka Data Sesi (Menggunakan start_at karena tidak ada kolom tanggal)
    $StmtSesi = mysqli_prepare($Conn, "SELECT start_at, finish_at, status FROM stock_opname WHERE id_stock_opname = ?");
    mysqli_stmt_bind_param($StmtSesi, "i", $id_stock_opname);
    mysqli_stmt_execute($StmtSesi);
    $ResultSesi = mysqli_stmt_get_result($StmtSesi);
    $DataStockOpname = mysqli_fetch_assoc($ResultSesi);
    mysqli_stmt_close($StmtSesi);

    // Ambil tanggal mulai (start_at) sebagai tanggal laporan
    $tanggal = !empty($DataStockOpname['start_at']) ? date('Y-m-d', strtotime($DataStockOpname['start_at'])) : date('Y-m-d');

    // Membuat objek Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Menulis judul (Ditambahkan kolom Keterangan agar informasi rincian lengkap)
    $headers = ['No', 'Tanggal', 'Kode', 'Nama Barang', 'Stok Awal', 'Stok Akhir', 'Selisih', 'Harga', 'Jumlah', 'Keterangan'];
    $columnIndex = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($columnIndex . '1', $header);
        $columnIndex++;
    }

    // Mengatur gaya baris judul (A1 sampai J1)
    $sheet->getStyle('A1:J1')->getFont()->setBold(true);
    $sheet->getStyle('A1:J1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Query untuk mendapatkan data dengan JOIN
    $query = "SELECT sob.id_stock_opname_barang, sob.id_stock_opname, sob.id_barang, sob.stok_awal, sob.stok_akhir, sob.stok_gap, sob.harga_beli, sob.jumlah, sob.keterangan,
                     b.kode_barang, b.nama_barang, b.satuan_barang
              FROM stock_opname_barang AS sob
              JOIN barang AS b ON sob.id_barang = b.id_barang 
              WHERE sob.id_stock_opname = ?
              ORDER BY sob.id_barang ASC";

    $StmtData = mysqli_prepare($Conn, $query);
    mysqli_stmt_bind_param($StmtData, "i", $id_stock_opname);
    mysqli_stmt_execute($StmtData);
    $result = mysqli_stmt_get_result($StmtData);

    // Mengisi data ke dalam Spreadsheet
    $no = 1;
    $row = 2; // Mulai dari baris ke-2
    while ($data = mysqli_fetch_assoc($result)) {
        $sheet->setCellValueExplicit('A' . $row, $no, DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('B' . $row, $tanggal, DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('C' . $row, $data['kode_barang'], DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('D' . $row, $data['nama_barang'], DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('E' . $row, $data['stok_awal'], DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('F' . $row, $data['stok_akhir'], DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('G' . $row, $data['stok_gap'], DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('H' . $row, $data['harga_beli'], DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('I' . $row, $data['jumlah'], DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('J' . $row, $data['keterangan'], DataType::TYPE_STRING);

        $row++;
        $no++;
    }
    mysqli_stmt_close($StmtData);

    // Menyesuaikan lebar kolom otomatis (dari A sampai J)
    foreach (range('A', 'J') as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }

    // Membuat file Excel dan mengirim ke output
    $filename = 'stock_opname_' . $id_stock_opname . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
?>