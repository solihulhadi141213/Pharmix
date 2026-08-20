<?php
    use PhpOffice\PhpSpreadsheet\IOFactory;
    use PhpOffice\PhpSpreadsheet\Spreadsheet;

    // JSON Format
    header('Content-Type: application/json; charset=utf-8');

    // Koneksi Dan Library Composser
    require "../../_Config/Connection.php";
    require "../../_Config/GlobalFunction.php";
    require "../../_Config/Session.php";
    require "../../vendor/autoload.php";

    // Helper Response
    function sendResponse($status, $message, $html) {
        echo json_encode([
            'status' => $status,
            'message' => $message,
            'html' => $html
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Validasi Metode Pengiriman Data
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse('error', 'Metode request tidak valid!', '');
    }

    // Validasi Sesi Akses
    if (empty($SessionIdAkses)) {
        sendResponse('error', 'Sesi Akses Sudah Berakhir, Silahkan Login Ulang!', '');
    }

    // Validasi Mandatory
    if(empty($_FILES['file_supplier'])){
        sendResponse('error', 'File Tidak Dimuat atau Tidak Terbaca!', '');
    }

    // Buat Variabel
    $file         = $_FILES['file_supplier'];
    $allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
    $maxSize      = 10 * 1024 * 1024;

    // Validasi tipe file
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (!in_array($ext, ['xls', 'xlsx'])) {
        sendResponse('error', 'Format file tidak valid. Hanya diperbolehkan file Excel (.xls, .xlsx).', '');
    }

    // Validasi ukuran file
    if ($file['size'] > $maxSize) {
        sendResponse('error', 'Ukuran file terlalu besar. Maksimal 10 MB', '');
    }

    $filePath    = $file['tmp_name'];
    $spreadsheet = IOFactory::load($filePath);
    $sheet       = $spreadsheet->getActiveSheet();
    $rows        = $sheet->toArray();

    if (count($rows) <= 1) {
        sendResponse('error', 'File Excel kosong atau tidak sesuai format.', '');
    }
    $html ="";
    foreach ($rows as $index => $row) {
        if ($index == 0) continue; // Lewati baris pertama (judul kolom)

        $nama_supplier   = trim($row[1] ?? '');
        $alamat_supplier = trim($row[2] ?? '');
        $email_supplier  = trim($row[3] ?? '');
        $kontak_supplier = trim($row[4] ?? '');
        $pic             = trim($row[5] ?? '');
        $npwp            = trim($row[6] ?? '');

        if (empty($nama_supplier)) {
            $html .= '<tr class="table-danger"><td colspan="8">Data pada baris '.($index+1).' tidak valid: Nama Supplier wajib diisi.</td></tr>';
            continue;
        }

        if (strlen($kontak_supplier) > 20) {
            $html .= '<tr class="table-danger"><td colspan="8">Data pada baris '.($index+1).' tidak valid: Kontak Supplier tidak boleh lebih dari 20 karakter.</td></tr>';
            continue;
        }

        // Nama supplier tidak boleh duplikat.
        $stmt = $Conn->prepare("SELECT COUNT(*) FROM supplier WHERE nama_supplier = ?");
        $stmt->bind_param("s", $nama_supplier);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $html .= '
                <tr class="table-warning">
                    <td>'.$row[0].'</td>
                    <td>'.$nama_supplier.'</td>
                    <td>'.$alamat_supplier.'</td>
                    <td>'.$email_supplier.'</td>
                    <td>'.$kontak_supplier.'</td>
                    <td>'.$pic.'</td>
                    <td>'.$npwp.'</td>
                </tr>
            ';
            continue;
        } else {
            // NPWP hanya divalidasi jika diisi.
            if (!empty($npwp)) {
                $stmt = $Conn->prepare("SELECT COUNT(*) FROM supplier WHERE npwp = ?");
                $stmt->bind_param("s", $npwp);
                $stmt->execute();
                $stmt->bind_result($countNpwp);
                $stmt->fetch();
                $stmt->close();

                if ($countNpwp > 0) {
                    $html .= '
                        <tr class="table-warning">
                            <td>'.$row[0].'</td>
                            <td>'.$nama_supplier.'</td>
                            <td>'.$alamat_supplier.'</td>
                            <td>'.$email_supplier.'</td>
                            <td>'.$kontak_supplier.'</td>
                            <td>'.$pic.'</td>
                            <td>'.$npwp.'</td>
                        </tr>
                    ';
                    continue;
                }
            }

            $stmt = $Conn->prepare("INSERT INTO supplier (nama_supplier, alamat_supplier, email_supplier, kontak_supplier, pic, npwp) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $nama_supplier, $alamat_supplier, $email_supplier, $kontak_supplier, $pic, $npwp);
            if ($stmt->execute()) {
                $html .= '
                    <tr class="table-success">
                        <td>'.$row[0].'</td>
                        <td>'.$nama_supplier.'</td>
                        <td>'.$alamat_supplier.'</td>
                        <td>'.$email_supplier.'</td>
                        <td>'.$kontak_supplier.'</td>
                        <td>'.$pic.'</td>
                        <td>'.$npwp.'</td>
                    </tr>
                ';
            } else {
                $html .= '
                    <tr class="table-danger">
                       <td colspan="8">Gagal mengimport data pada baris '.($index+1).'</td>
                    </tr>
                ';
            }
            $stmt->close();
        }
    }

    sendResponse('success', 'Proses Import Selesai', $html);
?>