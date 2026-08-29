<?php
    // ==========================================
    // KONEKSI DAN SESSION
    // ==========================================
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // JSON Default Format
    header('Content-Type: application/json; charset=utf-8');

    // Time Zone
    date_default_timezone_set('Asia/Jakarta');

    // Default Response
    $response = [
        "status"  => "Error",
        "message" => "Terjadi kesalahan yang tidak diketahui."
    ];

    // ==========================================
    // VALIDASI SESI AKSES
    // ==========================================
    if (empty($SessionIdAkses)) {
        $response["message"] = "Sesi akses sudah berakhir! Silahkan Login Ulang!";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ==========================================
    // VALIDASI METHOD REQUEST
    // ==========================================
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response["message"] = "Metode request tidak valid.";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ==========================================
    // VALIDASI ID TRANSAKSI TEMPO
    // ==========================================
    $id_transaksi_tempo = $_POST['id_transaksi_tempo'] ?? '';
    $id_transaksi_tempo = trim($id_transaksi_tempo);

    if (empty($id_transaksi_tempo) || !ctype_digit($id_transaksi_tempo)) {
        $response["message"] = "ID Transaksi Tempo tidak valid atau kosong.";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $id_transaksi_tempo = (int) $id_transaksi_tempo;

    // ==========================================
    // PROSES HAPUS DATA MENGGUNAKAN PREPARED STATEMENT
    // ==========================================
    $sql = "DELETE FROM transaksi_tempo WHERE id_transaksi_tempo = ? LIMIT 1";
    $stmt = mysqli_prepare($Conn, $sql);

    if (!$stmt) {
        $response["message"] = "Gagal mempersiapkan query penghapusan tempo.";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "i", $id_transaksi_tempo);

    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            $response["status"] = "Success";
            $response["message"] = "Tanggal tempo berhasil dihapus.";
        } else {
            $response["message"] = "Data tanggal tempo tidak ditemukan atau sudah terhapus.";
        }
    } else {
        $response["message"] = "Gagal mengeksekusi penghapusan data: " . mysqli_stmt_error($stmt);
    }

    mysqli_stmt_close($stmt);

    // ==========================================
    // KEMBALIKAN RESPONSE JSON
    // ==========================================
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
?>