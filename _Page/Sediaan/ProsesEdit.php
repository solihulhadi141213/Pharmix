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
        'id_referensi_sediaan' => 'ID Sediaan Tidak Boleh Kosong.',
        'code'                 => 'Kode Sediaan Tidak Boleh Kosong.',
        'display'              => 'Nama Sediaan Tidak Boleh Kosong.',
        'system'               => 'Kode Sistem Sediaan Tidak Boleh Kosong.',
        'category'             => 'Kategori Tidak Boleh Kosong.',
        'group'                => 'Group Sediaan Tidak Boleh Kosong.'
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
    $id_referensi_sediaan = validateAndSanitizeInput($_POST['id_referensi_sediaan']);
    $code                 = validateAndSanitizeInput($_POST['code']);
    $display              = validateAndSanitizeInput($_POST['display']);
    $system               = validateAndSanitizeInput($_POST['system']);
    $category             = validateAndSanitizeInput($_POST['category']);
    $group_name           = validateAndSanitizeInput($_POST['group']);

    // Ambil Kode Lama
    $code_lama = GetDetailData($Conn,'referensi_sediaan','id_referensi_sediaan',$id_referensi_sediaan,'code');

    // Validasi Duplikat Kode
    if ($code_lama !== $code) {
        $validasi_duplikat = GetDetailData($Conn,'referensi_sediaan','code',$code,'id_referensi_sediaan');
    } else {
        $validasi_duplikat = "";
    }

    if (!empty($validasi_duplikat)) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Kode sediaan sudah terdaftar.'
        ]);
        exit;
    }

    // ==============================
    // PROSES UPDATE DATA
    // ==============================
    $sql = "UPDATE referensi_sediaan SET
                code = ?,
                display = ?,
                system_referensi = ?,
                category = ?,
                group_name = ?
            WHERE id_referensi_sediaan = ?";

    $stmt = $Conn->prepare($sql);

    if (!$stmt) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Gagal menyiapkan query.'
        ]);
        exit;
    }

    $stmt->bind_param(
        "sssssi",
        $code,
        $display,
        $system,
        $category,
        $group_name,
        $id_referensi_sediaan
    );

    if (!$stmt->execute()) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Gagal memperbaharui referensi sediaan.'
        ]);
        exit;
    }

    $stmt->close();

    // RESPONSE SUKSES
    echo json_encode([
        'status'  => 'success',
        'message' => 'Referensi Sediaan Berhasil Diperbaharui'
    ]);
    exit;
?>
