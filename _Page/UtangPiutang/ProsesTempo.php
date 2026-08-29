<?php
    // ==========================================
    // KONEKSI, FUNGSI DAN SESSION
    // ==========================================
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Zona waktu
    date_default_timezone_set("Asia/Jakarta");

    // Header Response JSON
    header('Content-Type: application/json; charset=utf-8');

    // ==========================================
    // DEFAULT RESPONSE
    // ==========================================
    $response = [
        "status"  => "Error",
        "message" => "Terjadi kesalahan yang tidak diketahui."
    ];

    // ==========================================
    // VALIDASI SESSION
    // ==========================================
    if (empty($SessionIdAkses)) {
        $response["message"] = "Sesi akses sudah berakhir. Silakan login ulang.";
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
    // AMBIL DAN VALIDASI INPUT
    // ==========================================
    $id_param       = $_POST['id'] ?? '';
    $kategori_param = $_POST['kategori'] ?? '';
    $tanggal_tempo  = $_POST['tanggal_tempo'] ?? '';

    $id_transaksi           = $_POST['id_transaksi'] ?? '';
    $id_transaksi_jual_beli = $_POST['id_transaksi_jual_beli'] ?? '';

    if (empty($id_param)) {
        if (!empty($id_transaksi_jual_beli)) {
            $id_param = $id_transaksi_jual_beli;
            $kategori_param = 'jual_beli';
        } elseif (!empty($id_transaksi)) {
            $id_param = $id_transaksi;
            $kategori_param = 'operasional';
        }
    }

    $id_param       = trim($id_param);
    $kategori_param = trim($kategori_param);
    $tanggal_tempo  = trim($tanggal_tempo);

    if (empty($id_param)) {
        $response["message"] = "ID Transaksi tidak boleh kosong.";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (empty($tanggal_tempo)) {
        $response["message"] = "Tanggal jatuh tempo tidak boleh kosong.";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ==========================================
    // TENTUKAN KOLOM BERDASARKAN KATEGORI
    // ==========================================
    $is_jual_beli = ($kategori_param === 'jual_beli');
    $kolom_id = $is_jual_beli ? 'id_transaksi_jual_beli' : 'id_transaksi';
    $type_id  = $is_jual_beli ? 's' : 'i';

    // ==========================================
    // CEK APAKAH DATA TEMPO SUDAH ADA
    // ==========================================
    $sql_cek = "SELECT id_transaksi_tempo FROM transaksi_tempo WHERE $kolom_id = ? LIMIT 1";
    $stmt_cek = mysqli_prepare($Conn, $sql_cek);

    if (!$stmt_cek) {
        $response["message"] = "Gagal mempersiapkan query pengecekan tempo.";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    mysqli_stmt_bind_param($stmt_cek, $type_id, $id_param);
    mysqli_stmt_execute($stmt_cek);
    $result_cek = mysqli_stmt_get_result($stmt_cek);
    $data_ada = mysqli_fetch_assoc($result_cek);
    mysqli_stmt_close($stmt_cek);

    // ==========================================
    // PROSES INSERT ATAU UPDATE
    // ==========================================
    if ($data_ada) {
        // --- UPDATE DATA ---
        $sql_action = "UPDATE transaksi_tempo SET tanggal_tempo = ? WHERE $kolom_id = ?";
        $stmt_action = mysqli_prepare($Conn, $sql_action);

        if (!$stmt_action) {
            $response["message"] = "Gagal mempersiapkan query update tempo.";
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }

        mysqli_stmt_bind_param($stmt_action, "s" . $type_id, $tanggal_tempo, $id_param);
    } else {
        // --- INSERT DATA ---
        if ($is_jual_beli) {
            $sql_action = "INSERT INTO transaksi_tempo (id_transaksi_jual_beli, tanggal_tempo) VALUES (?, ?)";
        } else {
            $sql_action = "INSERT INTO transaksi_tempo (id_transaksi, tanggal_tempo) VALUES (?, ?)";
        }

        $stmt_action = mysqli_prepare($Conn, $sql_action);

        if (!$stmt_action) {
            $response["message"] = "Gagal mempersiapkan query insert tempo.";
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($is_jual_beli) {
            mysqli_stmt_bind_param($stmt_action, "ss", $id_param, $tanggal_tempo);
        } else {
            mysqli_stmt_bind_param($stmt_action, "is", $id_param, $tanggal_tempo);
        }
    }

    // Eksekusi aksi
    if (mysqli_stmt_execute($stmt_action)) {
        $response["status"] = "Success";
        $response["message"] = "Tempo pembayaran berhasil diperbaharui.";
    } else {
        $response["message"] = "Gagal menyimpan data ke database: " . mysqli_stmt_error($stmt_action);
    }

    mysqli_stmt_close($stmt_action);

    // ==========================================
    // KEMBALIKAN RESPONSE JSON
    // ==========================================
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
?>