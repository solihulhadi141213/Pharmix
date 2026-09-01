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
        'nama_route'    => 'Nama Route Tidak Boleh Kosong.',
        'display_route' => 'Display Route Tidak Boleh Kosong.',
        'code_route'    => 'Kode Route Tidak Boleh Kosong.',
        'system_route'  => 'Kode Sistem Route Tidak Boleh Kosong.'
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
    $nama_route    = validateAndSanitizeInput($_POST['nama_route']);
    $display_route = validateAndSanitizeInput($_POST['display_route']);
    $code_route    = validateAndSanitizeInput($_POST['code_route']);
    $system_route  = validateAndSanitizeInput($_POST['system_route']);

    // ======================================================
    // VALIDASI DUPLIKAT CODE
    // ======================================================
    $validasi_duplikat = GetDetailData($Conn,'referensi_route','nama_route',$nama_route,'id_referensi_route');

    if (!empty($validasi_duplikat)) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Nama route sudah terdaftar.'
        ]);
        exit;
    }

    // ======================================================
    // INSERT DATA
    // ======================================================
    $stmt = $Conn->prepare("INSERT INTO referensi_route (nama_route, display_route, code_route, system_route) VALUES (?, ?, ?, ?)");
    $stmt->bind_param(
        "ssss",
        $nama_route,
        $display_route,
        $code_route,
        $system_route
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
        'message' => 'Referensi Route Berhasil Disimpan'
    ]);
    exit;
