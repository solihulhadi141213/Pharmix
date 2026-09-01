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
        'id_referensi_denominator' => 'ID Satuan Tidak Boleh Kosong.',
        'code_denominator'         => 'Kode Satuan Tidak Boleh Kosong.',
        'display_denominator'      => 'Nama/Display Satuan Tidak Boleh Kosong.',
        'system_denominator'       => 'Kode Sistem Satuan Tidak Boleh Kosong.',
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
    $id_referensi_denominator = validateAndSanitizeInput($_POST['id_referensi_denominator']);
    $code_denominator         = validateAndSanitizeInput($_POST['code_denominator']);
    $display_denominator      = validateAndSanitizeInput($_POST['display_denominator']);
    $system_denominator       = validateAndSanitizeInput($_POST['system_denominator']);

    // Ambil Kode Lama
    $code_lama = GetDetailData($Conn,'referensi_denominator','id_referensi_denominator',$id_referensi_denominator,'code_denominator');

    // Validasi Duplikat Kode
    if ($code_lama !== $code_denominator) {
        $validasi_duplikat = GetDetailData($Conn,'referensi_denominator','code_denominator',$code_denominator,'id_referensi_denominator');
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
    $sql = "UPDATE referensi_denominator SET
                code_denominator = ?,
                display_denominator = ?,
                system_denominator = ?
            WHERE id_referensi_denominator = ?";

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
        $code_denominator,
        $display_denominator,
        $system_denominator,
        $id_referensi_denominator
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
