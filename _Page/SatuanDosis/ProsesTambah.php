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
        'nama_satuan_dosis'   => 'Nama Satuan Tidak Boleh Kosong.',
        'unit_satuan_dosis'   => 'Unit Satuan Tidak Boleh Kosong.',
        'code_satuan_dosis'   => 'Kode Satuan Tidak Boleh Kosong.',
        'system_satuan_dosis' => 'Kode Sistem Satuan Tidak Boleh Kosong.'
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
    $nama_satuan_dosis   = validateAndSanitizeInput($_POST['nama_satuan_dosis']);
    $unit_satuan_dosis   = validateAndSanitizeInput($_POST['unit_satuan_dosis']);
    $code_satuan_dosis   = validateAndSanitizeInput($_POST['code_satuan_dosis']);
    $system_satuan_dosis = validateAndSanitizeInput($_POST['system_satuan_dosis']);

    // ======================================================
    // VALIDASI DUPLIKAT CODE
    // ======================================================
    $validasi_duplikat = GetDetailData($Conn,'referensi_satuan_dosis','code_satuan_dosis',$code_satuan_dosis,'id_referensi_satuan_dosis');

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
    $stmt = $Conn->prepare("INSERT INTO referensi_satuan_dosis (nama_satuan_dosis, unit_satuan_dosis, code_satuan_dosis, system_satuan_dosis) VALUES (?, ?, ?, ?)");
    $stmt->bind_param(
        "ssss",
        $nama_satuan_dosis,
        $unit_satuan_dosis,
        $code_satuan_dosis,
        $system_satuan_dosis
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
