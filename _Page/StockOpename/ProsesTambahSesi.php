<?php
    // JSON Response Default
    header('Content-Type: application/json');

    // Koneksi
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Time Zone
    date_default_timezone_set('Asia/Jakarta');

    // Time Now Tmp
    $now   = date('Y-m-d H:i:s');
    $today = date('Y-m-d');

    // Response Helper
    function Response($status, $message){
        echo json_encode([
            "status"  => $status,
            "message" => $message
        ]);
        exit;
    }

    // Session validation
    if(empty($SessionIdAkses)){
        Response("error", "Sesi akses sudah berakhir. Silakan login ulang.");
    }

    // Mandatory Validation
    if(empty($_POST['start_at_date'])){
        Response("error", "Tanggal Pelaksanaan Tidak Boleh Kosong!");
    }

    if(empty($_POST['start_at_time'])){
        Response("error", "Jam Pelaksanaan Tidak Boleh Kosong!");
    }

    // Buat Variabel dan Sanitasi
    $start_at_date=validateAndSanitizeInput($_POST['start_at_date']);
    $start_at_time=validateAndSanitizeInput($_POST['start_at_time']);

    // Default status
    $status = "On-Progress";

    // Tanggal + Waktu Pelaksanaan
    $start_at = "$start_at_date $start_at_time";

    //Validasi tanggal tidak boleh lebih dari tanggal hari ini
    if($start_at > $now){
        Response("error", "Waktu Pelaksanaan Tidak Boleh Lebih Dari Tanggal Sekarang!");
    }
        
    //Validasi data duplikat dengan prepared statement
    $StmtDuplikat = mysqli_prepare($Conn, "SELECT id_stock_opname FROM stock_opname WHERE start_at=? LIMIT 1");
    mysqli_stmt_bind_param($StmtDuplikat, "s", $start_at);
    mysqli_stmt_execute($StmtDuplikat);
    $ResultDuplikat = mysqli_stmt_get_result($StmtDuplikat);
    $ValidasiDuplikat = mysqli_num_rows($ResultDuplikat);
    mysqli_stmt_close($StmtDuplikat);

    if(!empty($ValidasiDuplikat)){
        Response("error", "Data Sesi Pada Tanggal $start_at sudah ada!");
    }

    //Validasi Jika Masih Ada Yang Dilaksanakan Dan Belum Selesai
    $StmtProgress = mysqli_prepare($Conn, "SELECT id_stock_opname FROM stock_opname WHERE status=? LIMIT 1");
    mysqli_stmt_bind_param($StmtProgress, "s", $status);
    mysqli_stmt_execute($StmtProgress);
    $ResultProgress = mysqli_stmt_get_result($StmtProgress);
    $ValidasiProgress = mysqli_num_rows($ResultProgress);
    mysqli_stmt_close($StmtProgress);

    if(!empty($ValidasiProgress)){
        Response("error", "Masih Ada Sesi Yang Belum Selesai!");
    }

    //Simpan data dengan prepared statement
    $StmtInsert = mysqli_prepare($Conn, "INSERT INTO stock_opname (start_at, creatAt, creatBy, updateAt, updateBy, status) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($StmtInsert, "ssisis", $start_at, $now, $SessionIdAkses, $now, $SessionIdAkses, $status);
    $Input = mysqli_stmt_execute($StmtInsert);
    mysqli_stmt_close($StmtInsert);

    // Jika Gagal Menyimpan Data
    if(!$Input){
        Response("error", "Terjadi kesalahan pada saat menyimpan data!");
    }

    // Simpan Log
    $kategori_log="Stock Opname";
    $deskripsi_log="Tambah Sesi Stock Opname";
    $InputLog=addLog($Conn,$SessionIdAkses,$now,$kategori_log,$deskripsi_log);
    if($InputLog!=="Success"){
        Response("error", "Terjadi kesalahan pada saat menyimpan log!");
    }

    Response("success", "Data Berhasil Disimpan");
?>
