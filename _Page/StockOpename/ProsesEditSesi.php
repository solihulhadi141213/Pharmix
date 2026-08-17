<?php
    // Koneksi
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Time Zone
    date_default_timezone_set('Asia/Jakarta');

    // Time Now Tmp
    $now = date('Y-m-d H:i:s');

    header('Content-Type: application/json; charset=utf-8');

    // Validasi Akses
    if(empty($SessionIdAkses)){
        echo json_encode([
            "status" => "error",
            "message" => "Sesi Akses Sudah Berakhir. Silahkan Login Ulang!",
            "html" => ""
        ]);
        exit;
    }

    // Validasi Input Utama
    if(empty($_POST['id_stock_opname'])){
        echo json_encode([
            "status" => "error",
            "message" => "ID Stock Opname Tidak Boleh Kosong!",
            "html" => ""
        ]);
        exit;
    }
    if(empty($_POST['start_at_date']) || empty($_POST['start_at_time'])){
        echo json_encode([
            "status" => "error",
            "message" => "Tanggal dan waktu mulai tidak boleh kosong!",
            "html" => ""
        ]);
        exit;
    }
    if(empty($_POST['status'])){
        echo json_encode([
            "status" => "error",
            "message" => "Status Tidak Boleh Kosong!",
            "html" => ""
        ]);
        exit;
    }

    $id_stock_opname = validateAndSanitizeInput($_POST['id_stock_opname']);
    $start_at_date   = validateAndSanitizeInput($_POST['start_at_date']);
    $start_at_time   = validateAndSanitizeInput($_POST['start_at_time']);
    $finish_at_date  = !empty($_POST['finish_at_date']) ? validateAndSanitizeInput($_POST['finish_at_date']) : "";
    $finish_at_time  = !empty($_POST['finish_at_time']) ? validateAndSanitizeInput($_POST['finish_at_time']) : "";
    $status          = validateAndSanitizeInput($_POST['status']);

    $start_at = $start_at_date . " " . $start_at_time;
    $finish_at = "";
    if(!empty($finish_at_date) && !empty($finish_at_time)){
        $finish_at = $finish_at_date . " " . $finish_at_time;
    }

    // Validasi format waktu
    $start_at_timestamp = strtotime($start_at);
    $finish_at_timestamp = !empty($finish_at) ? strtotime($finish_at) : false;

    if($start_at_timestamp === false){
        echo json_encode([
            "status" => "error",
            "message" => "Format tanggal mulai tidak valid!",
            "html" => ""
        ]);
        exit;
    }

    // Validasi start_at tidak lebih besar dari finish_at
    if(!empty($finish_at) && $finish_at_timestamp !== false && $start_at_timestamp > $finish_at_timestamp){
        echo json_encode([
            "status" => "error",
            "message" => "Waktu mulai tidak boleh lebih besar dari waktu selesai!",
            "html" => ""
        ]);
        exit;
    }

    // Jika status Finished maka finish_at wajib diisi
    if($status === "Finished" && empty($finish_at)){
        echo json_encode([
            "status" => "error",
            "message" => "Jika status Finished, waktu selesai wajib diisi!",
            "html" => ""
        ]);
        exit;
    }

    // Pastikan data yang diubah memang ada
    $StmtCek = mysqli_prepare($Conn, "SELECT id_stock_opname FROM stock_opname WHERE id_stock_opname=? LIMIT 1");
    mysqli_stmt_bind_param($StmtCek, "i", $id_stock_opname);
    mysqli_stmt_execute($StmtCek);
    $ResultCek = mysqli_stmt_get_result($StmtCek);
    $DataCek = mysqli_fetch_assoc($ResultCek);
    mysqli_stmt_close($StmtCek);

    if(empty($DataCek)){
        echo json_encode([
            "status" => "error",
            "message" => "Data Tidak Ditemukan!",
            "html" => ""
        ]);
        exit;
    }

    // Update data dengan prepared statement
    if(!empty($finish_at)){
        $StmtUpdate = mysqli_prepare($Conn, "
            UPDATE stock_opname
            SET
                start_at=?,
                finish_at=?,
                status=?,
                updateAt=?,
                updateBy=?
            WHERE id_stock_opname=?
        ");
        mysqli_stmt_bind_param($StmtUpdate, "ssssii", $start_at, $finish_at, $status, $now, $SessionIdAkses, $id_stock_opname);
    }else{
        $StmtUpdate = mysqli_prepare($Conn, "
            UPDATE stock_opname
            SET
                start_at=?,
                finish_at=NULL,
                status=?,
                updateAt=?,
                updateBy=?
            WHERE id_stock_opname=?
        ");
        mysqli_stmt_bind_param($StmtUpdate, "sssii", $start_at, $status, $now, $SessionIdAkses, $id_stock_opname);
    }

    if(!$StmtUpdate){
        echo json_encode([
            "status" => "error",
            "message" => "Gagal mempersiapkan query update!",
            "html" => ""
        ]);
        exit;
    }

    $UpdateStockOpname = mysqli_stmt_execute($StmtUpdate);
    mysqli_stmt_close($StmtUpdate);

    if($UpdateStockOpname){
        $kategori_log = "Barang";
        $deskripsi_log = "Edit Sesi Stock Opename";
        $InputLog = addLog($Conn, $SessionIdAkses, $now, $kategori_log, $deskripsi_log);

        if($InputLog=="Success"){
            echo json_encode([
                "status" => "success",
                "message" => "Data berhasil disimpan.",
                "html" => ""
            ]);
        }else{
            echo json_encode([
                "status" => "error",
                "message" => "Terjadi kesalahan pada saat menyimpan Log",
                "html" => ""
            ]);
        }
    }else{
        echo json_encode([
            "status" => "error",
            "message" => "Terjadi kesalahan pada saat menyimpan data",
            "html" => ""
        ]);
    }
?>
