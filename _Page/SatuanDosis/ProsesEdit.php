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
        'id_referensi_satuan_dosis' => 'ID Satuan Tidak Boleh Kosong.',
        'nama_satuan_dosis'         => 'Nama Satuan Tidak Boleh Kosong.',
        'unit_satuan_dosis'         => 'Unit Satuan Tidak Boleh Kosong.',
        'code_satuan_dosis'         => 'Kode satuan Tidak Boleh Kosong.',
        'system_satuan_dosis'       => 'Sistem Satuan Tidak Boleh Kosong.',
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
    $id_referensi_satuan_dosis = validateAndSanitizeInput($_POST['id_referensi_satuan_dosis']);
    $nama_satuan_dosis         = validateAndSanitizeInput($_POST['nama_satuan_dosis']);
    $unit_satuan_dosis         = validateAndSanitizeInput($_POST['unit_satuan_dosis']);
    $code_satuan_dosis         = validateAndSanitizeInput($_POST['code_satuan_dosis']);
    $system_satuan_dosis       = validateAndSanitizeInput($_POST['system_satuan_dosis']);

    // Ambil Kode Lama
    $code_lama = GetDetailData($Conn,'referensi_satuan_dosis','id_referensi_satuan_dosis',$id_referensi_satuan_dosis,'code_satuan_dosis');

    // Validasi Duplikat Kode
    if ($code_lama !== $code_satuan_dosis) {
        $validasi_duplikat = GetDetailData($Conn,'referensi_satuan_dosis','code_satuan_dosis',$code_satuan_dosis,'id_referensi_satuan_dosis');
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
    $sql = "UPDATE referensi_satuan_dosis SET
                code_satuan_dosis = ?,
                nama_satuan_dosis = ?,
                unit_satuan_dosis = ?,
                system_satuan_dosis = ?
            WHERE id_referensi_satuan_dosis = ?";

    $stmt = $Conn->prepare($sql);

    if (!$stmt) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Gagal menyiapkan query.'
        ]);
        exit;
    }

    $stmt->bind_param(
        "ssssi",
        $code_satuan_dosis,
        $nama_satuan_dosis,
        $unit_satuan_dosis,
        $system_satuan_dosis,
        $id_referensi_satuan_dosis
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
        'message' => 'Referensi Satuan Berhasil Diperbaharui'
    ]);
    exit;
?>
