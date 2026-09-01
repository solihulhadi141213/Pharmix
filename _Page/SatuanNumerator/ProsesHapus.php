<?php
    // Koneksi, Global Function, Session Dan Setting General
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    include "../../_Config/SettingGeneral.php";

    // Zona Waktu
    date_default_timezone_set("Asia/Jakarta");

    // Header JSON
    header('Content-Type: application/json');

    // Validasi Session
    if (empty($SessionIdAkses)) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Sesi akses telah berakhir. Silakan login ulang.'
        ]);
        exit;
    }

    // Validasi Input Wajib
    $requiredFields = [
        'id_referensi_numerator' => 'ID Satuan Tidak Boleh Kosong.'
    ];

    foreach ($requiredFields as $field => $message) {
        if (empty($_POST[$field])) {
            echo json_encode([
                'status'  => 'error',
                'message' => $message
            ]);
            exit;
        }
    }

    // Sanitasi Input
    $id_referensi_numerator = validateAndSanitizeInput($_POST['id_referensi_numerator']);

    $HapusSediaan = mysqli_query($Conn, "DELETE FROM referensi_numerator WHERE id_referensi_numerator='$id_referensi_numerator'") or die(mysqli_error($Conn));
    if ($HapusSediaan) {

        // RESPONSE SUKSES
        echo json_encode([
            'status'  => 'success',
            'message' => 'Referensi Satuan Berhasil Dihapus'
        ]);
    }else{
        echo json_encode([
            'status'  => 'error',
            'message' => 'Terjadi kesalahan pada saat hapus data satuan'
        ]);
    }
?>
