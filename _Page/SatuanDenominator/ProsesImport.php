<?php
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    require '../../vendor/autoload.php';

    use PhpOffice\PhpSpreadsheet\IOFactory;

    header('Content-Type: application/json');

    // ============================
    // VALIDASI SESSION
    // ============================
    if (empty($SessionIdAkses)) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Sesi akses telah berakhir'
        ]);
        exit;
    }

    // ============================
    // VALIDASI FILE
    // ============================
    if (empty($_FILES['file_import']['name'])) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'File import tidak ditemukan'
        ]);
        exit;
    }

    $file_tmp = $_FILES['file_import']['tmp_name'];

    try {
        $spreadsheet = IOFactory::load($file_tmp);
    } catch (Exception $e) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'File excel tidak valid'
        ]);
        exit;
    }

    $sheet = $spreadsheet->getActiveSheet();
    $data  = $sheet->toArray();

    $totalRow = count($data);
    $log = [];

    // ============================
    // PROSES PER BARIS (SKIP HEADER)
    // ============================
    for ($i = 1; $i < $totalRow; $i++) {

        $code    = trim($data[$i][1]);  // Kolom B
        $display = trim($data[$i][2]);  // Kolom C
        $system  = trim($data[$i][3]);  // Kolom D

        // Validasi wajib
        if (empty($display) || empty($code)) {
            $log[] = [
                'status'  => 'error',
                'message' => "Baris ".($i+1)." : Code atau Nama Satuan kosong"
            ];
            continue;
        }

        // Escape string
        $code    = mysqli_real_escape_string($Conn, $code);
        $display = mysqli_real_escape_string($Conn, $display);
        $system  = mysqli_real_escape_string($Conn, $system);

        // ============================
        // CEK DUPLIKAT CODE
        // ============================
        $cek = mysqli_query($Conn,"SELECT id_referensi_denominator FROM referensi_denominator WHERE code_denominator='$code'");

        if (mysqli_num_rows($cek) > 0) {

            // UPDATE DATA
            $update = mysqli_query($Conn, "UPDATE referensi_denominator SET display_denominator = '$display', system_denominator = '$system' WHERE code_denominator = '$code'");

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

            // INSERT DATA
            $insert = mysqli_query($Conn, "INSERT INTO referensi_denominator (display_denominator, code_denominator, system_denominator) VALUES ('$display', '$code', '$system')");

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

    // ============================
    // RESPONSE AKHIR
    // ============================
    echo json_encode([
        'status' => 'success',
        'log'    => $log
    ]);
    exit;

?>