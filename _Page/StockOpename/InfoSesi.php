<?php
    //Koneksi
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

     $html = '';

    // Validasi Sesi Akses
    if(empty($SessionIdAkses)){
        echo json_encode([
            "status" => "error",
            "message" => "Sesi akses sudah berakhir. Silakan login ulang.",
            "html"   => '',
        ]);
        exit;
    }

    //Tangkap id_stock_opname
    if(empty($_POST['id_stock_opname'])){
        echo json_encode([
            "status" => "error",
            "message" => "Tidak ada data yang dipilih untuk ditampilkan!.",
            "html"   => '',
        ]);
        exit;
    }
    $id_stock_opname = validateAndSanitizeInput($_POST['id_stock_opname']);

    //Buka data Stock Opename dengan prepared statement
    $QryStockOpename = mysqli_prepare($Conn, "
        SELECT 
            so.*,
            ac.nama_akses AS creatBy_nama_akses,
            au.nama_akses AS updateBy_nama_akses
        FROM stock_opname AS so
        LEFT JOIN akses AS ac ON so.creatBy = ac.id_akses
        LEFT JOIN akses AS au ON so.updateBy = au.id_akses
        WHERE so.id_stock_opname=?
    ");
    mysqli_stmt_bind_param($QryStockOpename, "i", $id_stock_opname);
    mysqli_stmt_execute($QryStockOpename);
    $ResultStockOpename = mysqli_stmt_get_result($QryStockOpename);
    $DataStockOpename = mysqli_fetch_assoc($ResultStockOpename);

    // Jika Data Tidak Ditemukan
    if(empty($DataStockOpename)){
        echo json_encode([
            "status" => "error",
            "message" => "Data Tidak Ditemukan!.",
            "html"   => '',
        ]);
        exit;
    }

    // Buat Variabel Data
    $start_at  = $DataStockOpename['start_at'];
    $finish_at = $DataStockOpename['finish_at'];
    $creatAt   = $DataStockOpename['creatAt'];
    $creatBy   = $DataStockOpename['creatBy'];
    $creatByNama = $DataStockOpename['creatBy_nama_akses'];
    $updateAt  = $DataStockOpename['updateAt'];
    $updateBy  = $DataStockOpename['updateBy'];
    $updateByNama = $DataStockOpename['updateBy_nama_akses'];
    $status    = $DataStockOpename['status'];

    //Format Tanggal
    $start_at  = date('d F Y H:i:s T',strtotime($start_at));
    if(!empty($DataStockOpename['finish_at'])){
        $finish_at = date('d F Y H:i:s T',strtotime($finish_at));
    }else{
        $finish_at = "-";
    }
    
    $creatAt   = date('d/m/Y H:i:s T',strtotime($creatAt));
    $updateAt  = date('d/m/Y H:i:s T',strtotime($updateAt));

    if(!empty($creatBy)){
        $creatBy = !empty($creatByNama) ? htmlspecialchars($creatByNama) : htmlspecialchars($creatBy);
    }else{
        $creatBy = "-";
    }
    if(!empty($updateBy)){
        $updateBy = !empty($updateByNama) ? htmlspecialchars($updateByNama) : htmlspecialchars($updateBy);
    }else{
        $updateBy = "-";
    }

    //Hitung jumlah item
    $StmtJumlahItem = mysqli_prepare($Conn, "SELECT COUNT(id_stock_opname_barang) AS jumlah FROM stock_opname_barang WHERE id_stock_opname=?");
    mysqli_stmt_bind_param($StmtJumlahItem, "i", $id_stock_opname);
    mysqli_stmt_execute($StmtJumlahItem);
    $ResultJumlahItem = mysqli_stmt_get_result($StmtJumlahItem);
    $DataJumlahItem = mysqli_fetch_assoc($ResultJumlahItem);
    $JumlahItem = !empty($DataJumlahItem['jumlah']) ? $DataJumlahItem['jumlah'] : 0;

    //Menghitung jumlah kelebihan
    $StmtKelebihan = mysqli_prepare($Conn, "SELECT COALESCE(SUM(jumlah),0) AS jumlah FROM stock_opname_barang WHERE id_stock_opname=? AND jumlah>0");
    mysqli_stmt_bind_param($StmtKelebihan, "i", $id_stock_opname);
    mysqli_stmt_execute($StmtKelebihan);
    $ResultKelebihan = mysqli_stmt_get_result($StmtKelebihan);
    $DataKelebihan = mysqli_fetch_assoc($ResultKelebihan);
    $JumlahKelebihan = !empty($DataKelebihan['jumlah']) ? $DataKelebihan['jumlah'] : 0;
    $JumlahKelebihan_rp = number_format($JumlahKelebihan,0,',','.');
    
    //Menghitung jumlah Kekurangan
    $StmtKekurangan = mysqli_prepare($Conn, "SELECT COALESCE(SUM(jumlah),0) AS jumlah FROM stock_opname_barang WHERE id_stock_opname=? AND jumlah<0");
    mysqli_stmt_bind_param($StmtKekurangan, "i", $id_stock_opname);
    mysqli_stmt_execute($StmtKekurangan);
    $ResultKekurangan = mysqli_stmt_get_result($StmtKekurangan);
    $DataKekurangan = mysqli_fetch_assoc($ResultKekurangan);
    $JumlahKekurangan = !empty($DataKekurangan['jumlah']) ? $DataKekurangan['jumlah'] : 0;
    $JumlahKekurangan_rp = number_format($JumlahKekurangan,0,',','.');

    // Routing status
    if($status=="Finished"){
        $label_status = '<span class="badge  badge-success"><small>Finished</small></span>';
    }else{
        $label_status = '<span class="badge  badge-danger"><small>On-Progress</small></span>';
    }

    $html .= '
        <div class="row mb-3 mt-3">

            <div class="col-md-4 mb-3">
                <div class="row mb-2">
                    <div class="col-12"><small><b># Pelaksanaan</b></small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4"><small>Dimulai Pada</small></div>
                    <div class="col-8"><small class="text text-grayish">'.$start_at.'</small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4"><small>Selesai Pada</small></div>
                    <div class="col-8"><small class="text text-grayish">'.$finish_at.'</small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4"><small>Status</small></div>
                    <div class="col-8">'.$label_status.'</div>
                </div>
                <div class="row mb-2">
                    <div class="col-4"><small>Diinput Oleh</small></div>
                    <div class="col-8"><small class="text text-grayish">'.$creatBy.'</small></div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="row mb-2">
                    <div class="col-12"><small><b># Rekapitulasi Rincian</b></small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4"><small>Jumlah Item</small></div>
                    <div class="col-8"><small class="text text-grayish">'.$JumlahItem.' Item</small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4"><small>Selisih (+)</small></div>
                    <div class="col-8"><small class="text text-grayish">'.$JumlahKelebihan_rp.'</small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4"><small>Selisih (-)</small></div>
                    <div class="col-8"><small class="text text-grayish">'.$JumlahKekurangan_rp.'</small></div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="row mb-2">
                    <div class="col-12"><small><b># Metadata</b></small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4"><small>Diinput Pada</small></div>
                    <div class="col-8"><small class="text text-grayish">'.$creatAt.'</small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4"><small>Update Terakhir</small></div>
                    <div class="col-8"><small class="text text-grayish">'.$updateAt.'</small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4"><small>Update Oleh</small></div>
                    <div class="col-8"><small class="text text-grayish">'.$updateBy.'</small></div>
                </div>
            </div>
        </div>
    ';
    echo json_encode([
        "status"  => "success",
        "message" => "Data Berhasil Ditampilkan",
        "html"    => $html
    ]);
?>
