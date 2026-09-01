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

        $code    = trim($rows[$i][1]);
        $display = trim($rows[$i][2]);
        $system  = trim($rows[$i][3]);
        $category= trim($rows[$i][4]);
        $group   = trim($rows[$i][5]);

        // Validasi wajib
        if (empty($code) || empty($display)) {
            $log[] = [
                'status'  => 'error',
                'message' => "Baris ".($i+1)." : Code atau Nama kosong"
            ];
            continue;
        }

        // Validasi Group
        if (!in_array($group, ['Obat', 'Alkes'])) {
            $log[] = [
                'status'  => 'error',
                'message' => "Baris ".($i+1)." : Group harus Obat atau Alkes"
            ];
            continue;
        }

        // Cek apakah code sudah ada
        $cek = mysqli_query(
            $Conn,
            "SELECT id_referensi_sediaan 
            FROM referensi_sediaan 
            WHERE code='".mysqli_real_escape_string($Conn,$code)."'"
        );

        if (mysqli_num_rows($cek) > 0) {

            // UPDATE
            $update = mysqli_query($Conn, "
                UPDATE referensi_sediaan SET
                    display='$display',
                    system_referensi='$system',
                    category='$category',
                    group_name='$group'
                WHERE code='$code'
            ");

            if ($update) {
                $log[] = [
                    'status'  => 'success',
                    'message' => "Baris ".($i+1)." : Update berhasil ($code)"
                ];
            } else {
                $log[] = [
                    'status'  => 'error',
                    'message' => "Baris ".($i+1)." : Gagal update ($code)"
                ];
            }

        } else {

            // INSERT
            $insert = mysqli_query($Conn, "
                INSERT INTO referensi_sediaan
                (code, display, system_referensi, category, group_name)
                VALUES
                ('$code','$display','$system','$category','$group')
            ");

            if ($insert) {
                $log[] = [
                    'status'  => 'success',
                    'message' => "Baris ".($i+1)." : Insert berhasil ($code)"
                ];
            } else {
                $log[] = [
                    'status'  => 'error',
                    'message' => "Baris ".($i+1)." : Gagal insert ($code)"
                ];
            }
        }
    }

    echo json_encode([
        'status' => 'success',
        'log'    => $log
    ]);
    exit;

?>
