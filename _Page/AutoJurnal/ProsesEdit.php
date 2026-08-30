<?php
    // KONEKSI DAN SESSION
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Default Response Format
    header('Content-Type: application/json; charset=utf-8');

    $response = [
        "status"  => "error",
        "message" => "Terjadi kesalahan yang tidak diketahui."
    ];

    // 1. Validasi Sesi Akses
    if (empty($SessionIdAkses)) {
        $response["message"] = "Sesi akses sudah berakhir! Silahkan Login Ulang!";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 2. Validasi Metode Request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response["message"] = "Metode request tidak valid.";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 3. Tangkap dan Sanitasi Data
    $id_autojurnal_jual_beli = $_POST['id_autojurnal_jual_beli'] ?? '';
    $debet                   = $_POST['debet'] ?? '';
    $kredit                  = $_POST['kredit'] ?? '';
    $utang_piutang           = $_POST['utang_piutang'] ?? '';

    $id_autojurnal_jual_beli = trim($id_autojurnal_jual_beli);
    $debet                   = trim($debet);
    $kredit                  = trim($kredit);
    $utang_piutang           = trim($utang_piutang);

    // Validasi Data Mandatory (ID wajib ada, akun debet/kredit/utang-piutang boleh kosong atau diisi, sesuaikan kebutuhan)
    if (empty($id_autojurnal_jual_beli)) {
        $response["message"] = "ID Auto Jurnal tidak valid.";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Konversi nilai kosong menjadi NULL agar aman untuk tipe data INT UNSIGNED di database
    $val_debet        = ($debet !== '') ? (int)$debet : null;
    $val_kredit       = ($kredit !== '') ? (int)$kredit : null;
    $val_utang_piutang = ($utang_piutang !== '') ? (int)$utang_piutang : null;

    // 4. Proses Update ke Database Menggunakan Prepared Statement
    $sql = "UPDATE setting_autojurnal_jual_beli 
            SET debet = ?, kredit = ?, utang_piutang = ? 
            WHERE id_autojurnal_jual_beli = ?";
            
    $stmt = mysqli_prepare($Conn, $sql);
    
    // Tipe parameter: i = integer, i = integer, i = integer, i = integer
    // Jika ada nilai yang bisa NULL, gunakan 'i' namun pastikan variabel bernilai null (bukan string kosong)
    mysqli_stmt_bind_param($stmt, "iiii", $val_debet, $val_kredit, $val_utang_piutang, $id_autojurnal_jual_beli);

    if (mysqli_stmt_execute($stmt)) {
        $response = [
            "status"  => "success",
            "message" => "Pengaturan auto jurnal berhasil diperbarui."
        ];
    } else {
        $response = [
            "status"  => "error",
            "message" => "Gagal memperbarui database: " . mysqli_stmt_error($stmt)
        ];
    }

    mysqli_stmt_close($stmt);
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
?>