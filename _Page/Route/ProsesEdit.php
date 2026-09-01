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
        'id_referensi_route' => 'ID Route Tidak Boleh Kosong.',
        'nama_route'         => 'Nama Route Tidak Boleh Kosong.',
        'display_route'      => 'Display Route Tidak Boleh Kosong.',
        'code_route'         => 'Kode Route Tidak Boleh Kosong.',
        'system_route'       => 'Sistem Route Tidak Boleh Kosong.',
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
    $id_referensi_route = validateAndSanitizeInput($_POST['id_referensi_route']);
    $nama_route         = validateAndSanitizeInput($_POST['nama_route']);
    $display_route      = validateAndSanitizeInput($_POST['display_route']);
    $code_route         = validateAndSanitizeInput($_POST['code_route']);
    $system_route       = validateAndSanitizeInput($_POST['system_route']);

    // ==============================
    // PROSES UPDATE DATA
    // ==============================
    $sql = "UPDATE referensi_route SET
                nama_route = ?,
                display_route = ?,
                code_route = ?,
                system_route = ?
            WHERE id_referensi_route = ?";

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
        $nama_route,
        $display_route,
        $code_route,
        $system_route,
        $id_referensi_route
    );

    if (!$stmt->execute()) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Gagal memperbaharui referensi Route.'
        ]);
        exit;
    }

    $stmt->close();

    // RESPONSE SUKSES
    echo json_encode([
        'status'  => 'success',
        'message' => 'Referensi Route Berhasil Diperbaharui'
    ]);
    exit;
?>
