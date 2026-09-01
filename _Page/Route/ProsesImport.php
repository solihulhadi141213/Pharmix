<?php
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    require '../../vendor/autoload.php';

    use PhpOffice\PhpSpreadsheet\IOFactory;

    header('Content-Type: application/json');

    if (empty($SessionIdAkses)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Sesi akses telah berakhir'
        ]);
        exit;
    }

    if (empty($_FILES['file_import']['name'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'File tidak ditemukan'
        ]);
        exit;
    }

    $file = $_FILES['file_import']['tmp_name'];

    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    $totalRows = count($rows);
    $log = [];

    // Mulai dari baris ke-2 (skip header)
    for ($i = 1; $i < $totalRows; $i++) {

        $nama_route    = trim($rows[$i][1]);
        $display_route = trim($rows[$i][2]);
        $code_route    = trim($rows[$i][3]);
        $system_route  = trim($rows[$i][4]);

        // Validasi wajib
        if (empty($code_route) || empty($nama_route) || empty($display_route) || empty($system_route)) {
            $log[] = [
                'status'  => 'error',
                'message' => "Baris ".($i+1)." : Kolom Harus Terisi Semua"
            ];
            continue;
        }


        // INSERT
        $insert = mysqli_query($Conn, "INSERT INTO referensi_route 
            (nama_route, display_route, code_route, system_route)
            VALUES
            ('$nama_route','$display_route','$code_route','$system_route')
        ");

        if ($insert) {
            $log[] = [
                'status'  => 'success',
                'message' => "Baris ".($i+1)." : Insert berhasil ($code_route)"
            ];
        } else {
            $log[] = [
                'status'  => 'error',
                'message' => "Baris ".($i+1)." : Gagal insert ($code_route)"
            ];
        }
    }

    echo json_encode([
        'status' => 'success',
        'log'    => $log
    ]);
    exit;

?>
