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
        'id_referensi_numerator' => 'ID Satuan Tidak Boleh Kosong.',
        'unit'                   => 'Unit Satuan Tidak Boleh Kosong.',
        'code_numerator'         => 'Kode Satuan Tidak Boleh Kosong.',
        'system_numerator'       => 'Kode Sistem Satuan Tidak Boleh Kosong.',
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
    $unit                   = validateAndSanitizeInput($_POST['unit']);
    $code_numerator         = validateAndSanitizeInput($_POST['code_numerator']);
    $system_numerator       = validateAndSanitizeInput($_POST['system_numerator']);

    // Ambil Kode Lama
    $code_lama = GetDetailData($Conn,'referensi_numerator','id_referensi_numerator',$id_referensi_numerator,'code_numerator');

    // Validasi Duplikat Kode
    if ($code_lama !== $code_numerator) {
        $validasi_duplikat = GetDetailData($Conn,'referensi_numerator','code_numerator',$code_numerator,'id_referensi_numerator');
    } else {
        $validasi_duplikat = "";
    }

    if (!empty($validasi_duplikat)) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Kode Satuan sudah terdaftar.'
        ]);
        exit;
    }

    // ==============================
    // PROSES UPDATE DATA
    // ==============================
    $sql = "UPDATE referensi_numerator SET
                unit = ?,
                code_numerator = ?,
                system_numerator = ?
            WHERE id_referensi_numerator = ?";

    $stmt = $Conn->prepare($sql);

    if (!$stmt) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Gagal menyiapkan query.'
        ]);
        exit;
    }

    $stmt->bind_param(
        "sssi",
        $unit,
        $code_numerator,
        $system_numerator,
        $id_referensi_numerator
    );

    if (!$stmt->execute()) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Gagal memperbaharui referensi satuan.'
        ]);
        exit;
    }

    $stmt->close();

    // RESPONSE SUKSES
    echo json_encode([
        'status'  => 'success',
        'message' => 'Referensi satuan Berhasil Diperbaharui'
    ]);
    exit;
?>
