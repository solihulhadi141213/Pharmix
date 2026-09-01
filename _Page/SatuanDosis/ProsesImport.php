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

        $nama_satuan_dosis   = trim($rows[$i][1]);
        $unit_satuan_dosis   = trim($rows[$i][2]);
        $code_satuan_dosis   = trim($rows[$i][3]);
        $system_satuan_dosis = trim($rows[$i][4]);

        // Validasi wajib
        if (empty($code_satuan_dosis) || empty($nama_satuan_dosis)) {
            $log[] = [
                'status'  => 'error',
                'message' => "Baris ".($i+1)." : Code atau Nama kosong"
            ];
            continue;
        }


        // Cek apakah code sudah ada
        $cek = mysqli_query($Conn,"SELECT id_referensi_satuan_dosis FROM referensi_satuan_dosis WHERE code_satuan_dosis='".mysqli_real_escape_string($Conn,$code_satuan_dosis)."'");

        if (mysqli_num_rows($cek) > 0) {

            // UPDATE
            $update = mysqli_query($Conn, "
                UPDATE referensi_satuan_dosis SET
                    nama_satuan_dosis   = '$nama_satuan_dosis',
                    unit_satuan_dosis   = '$unit_satuan_dosis',
                    code_satuan_dosis   = '$code_satuan_dosis',
                    system_satuan_dosis = '$system_satuan_dosis'
                WHERE code_satuan_dosis='$code_satuan_dosis'
            ");

            if ($update) {
                $log[] = [
                    'status'  => 'success',
                    'message' => "Baris ".($i+1)." : Update berhasil ($code_satuan_dosis)"
                ];
            } else {
                $log[] = [
                    'status'  => 'error',
                    'message' => "Baris ".($i+1)." : Gagal update ($code_satuan_dosis)"
                ];
            }

        } else {

            // INSERT
            $insert = mysqli_query($Conn, "
                INSERT INTO referensi_satuan_dosis
                (nama_satuan_dosis, unit_satuan_dosis, code_satuan_dosis, system_satuan_dosis)
                VALUES
                ('$nama_satuan_dosis','$unit_satuan_dosis','$code_satuan_dosis','$system_satuan_dosis')
            ");

            if ($insert) {
                $log[] = [
                    'status'  => 'success',
                    'message' => "Baris ".($i+1)." : Insert berhasil ($code_satuan_dosis)"
                ];
            } else {
                $log[] = [
                    'status'  => 'error',
                    'message' => "Baris ".($i+1)." : Gagal insert ($code_satuan_dosis)"
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
