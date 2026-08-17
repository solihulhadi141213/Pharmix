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

    if(empty($SessionIdAkses)){
        echo json_encode([
            "status" => "error",
            "message" => "Sesi Akses Sudah Berakhir. Silahkan Login Ulang!",
            "html" => ""
        ]);
        exit;
    }

    if(empty($_POST['id_stock_opname'])){
        echo json_encode([
            "status" => "error",
            "message" => "ID Stock Opename Tidak Boleh Kosong!",
            "html" => ""
        ]);
        exit;
    }

    $id_stock_opname = validateAndSanitizeInput($_POST['id_stock_opname']);

    // Ambil data sesi terlebih dahulu
    $StmtCek = mysqli_prepare($Conn, "SELECT id_stock_opname, status FROM stock_opname WHERE id_stock_opname=? LIMIT 1");
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

    // Validasi status Finished tidak boleh dihapus
    if($DataCek['status'] == 'Finished'){
        echo json_encode([
            "status" => "error",
            "message" => "Data dengan status Finished tidak bisa dihapus!",
            "html" => ""
        ]);
        exit;
    }

    // Hapus data dengan prepared statement
    $StmtDelete = mysqli_prepare($Conn, "DELETE FROM stock_opname WHERE id_stock_opname=?");
    if(!$StmtDelete){
        echo json_encode([
            "status" => "error",
            "message" => "Gagal mempersiapkan query hapus!",
            "html" => ""
        ]);
        exit;
    }

    mysqli_stmt_bind_param($StmtDelete, "i", $id_stock_opname);
    $DeleteStockOpename = mysqli_stmt_execute($StmtDelete);
    mysqli_stmt_close($StmtDelete);

    if($DeleteStockOpename){
        $kategori_log = "Barang";
        $deskripsi_log = "Hapus Sesi Stock Opename";
        $InputLog = addLog($Conn, $SessionIdAkses, $now, $kategori_log, $deskripsi_log);

        if($InputLog=="Success"){
            echo json_encode([
                "status" => "success",
                "message" => "Data berhasil dihapus.",
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
            "message" => "Terjadi kesalahan pada saat menghapus data",
            "html" => ""
        ]);
    }
?>
