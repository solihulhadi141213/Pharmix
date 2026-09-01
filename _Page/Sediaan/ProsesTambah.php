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
        'code'     => 'Kode Sediaan Tidak Boleh Kosong.',
        'display'  => 'Nama Sediaan Tidak Boleh Kosong.',
        'system'   => 'Kode Sistem Sediaan Tidak Boleh Kosong.',
        'category' => 'Kategori Tidak Boleh Kosong.',
        'group'    => 'Group Sediaan Tidak Boleh Kosong.'
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
    $code       = validateAndSanitizeInput($_POST['code']);
    $display    = validateAndSanitizeInput($_POST['display']);
    $system     = validateAndSanitizeInput($_POST['system']);
    $category   = validateAndSanitizeInput($_POST['category']);
    $group_name = validateAndSanitizeInput($_POST['group']);

    // ======================================================
    // VALIDASI DUPLIKAT CODE
    // ======================================================
    $validasi_duplikat = GetDetailData($Conn,'referensi_sediaan','code',$code,'id_referensi_sediaan'
    );

    if (!empty($validasi_duplikat)) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Kode sediaan sudah terdaftar.'
        ]);
        exit;
    }

    // ======================================================
    // INSERT DATA
    // ======================================================
    $stmt = $Conn->prepare("
        INSERT INTO referensi_sediaan (
            code, display, system_referensi, category, group_name
        ) VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sssss",
        $code,
        $display,
        $system,
        $category,
        $group_name
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
        'message' => 'Referensi Sediaan Berhasil Disimpan'
    ]);
    exit;
