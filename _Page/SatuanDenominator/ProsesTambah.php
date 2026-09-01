<?php
    // ======================================================
    // KONEKSI, SESSION, GLOBAL FUNCTION
    // ======================================================
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    include "../../_Config/SettingGeneral.php";

    date_default_timezone_set("Asia/Jakarta");
    header('Content-Type: application/json');

    // ======================================================
    // VALIDASI SESSION
    // ======================================================
    if (empty($SessionIdAkses)) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Sesi akses telah berakhir. Silakan login ulang.'
        ]);
        exit;
    }

    // ======================================================
    // VALIDASI INPUT WAJIB
    // ======================================================
    $requiredFields = [
        'code_denominator'    => 'Kode Satuan Tidak Boleh Kosong.',
        'display_denominator' => 'Display/Nama Satuan Tidak Boleh Kosong.',
        'system_denominator'  => 'Kode Sistem Satuan Tidak Boleh Kosong.'
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

    // ======================================================
    // SANITASI INPUT
    // ======================================================
    $code_denominator    = validateAndSanitizeInput($_POST['code_denominator']);
    $display_denominator = validateAndSanitizeInput($_POST['display_denominator']);
    $system_denominator  = validateAndSanitizeInput($_POST['system_denominator']);

    // ======================================================
    // VALIDASI DUPLIKAT CODE
    // ======================================================
    $validasi_duplikat = GetDetailData($Conn,'referensi_denominator','code_denominator',$code_denominator,'id_referensi_denominator');

    if (!empty($validasi_duplikat)) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Kode satuan sudah terdaftar.'
        ]);
        exit;
    }

    // ======================================================
    // INSERT DATA
    // ======================================================
    $stmt = $Conn->prepare("INSERT INTO referensi_denominator (code_denominator, display_denominator, system_denominator) VALUES (?, ?, ?)");
    $stmt->bind_param(
        "sss",
        $code_denominator,
        $display_denominator,
        $system_denominator
    );
    $stmt->execute();
    

    if(!$stmt){
        echo json_encode([
            'status' => 'error',
            'message' => 'Prepare failed: '.$Conn->error
        ]);
        exit;
    }
    $stmt->close();
    // ======================================================
    // RESPONSE SUKSES
    // ======================================================
    echo json_encode([
        'status'  => 'success',
        'message' => 'Referensi Satuan Berhasil Disimpan'
    ]);
    exit;
