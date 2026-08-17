<?php
    //Koneksi
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Validasi Sesi Akses
    if(empty($SessionIdAkses)){
        echo '
            <div class="alert alert-danger text-center">
                <small>Sesi Akses Sudah Berakhir! Silahkan Login Ulang!</small>
            </div>
        ';
        exit;
    }

    // Validasi 'id_stock_opname'
    if(empty($_POST['id_stock_opname'])){
        echo '
            <div class="alert alert-danger text-center">
                <small>Tidak ada data yang dipilih!</small>
            </div>
        ';
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
        echo '
            <div class="alert alert-danger text-center">
                <small>Data Tidak Ditemukan!</small>
            </div>
        ';
        exit;
    }

    // Buat Variabel Data
    $start_at     = $DataStockOpename['start_at'];
    $finish_at    = $DataStockOpename['finish_at'];
    $creatAt      = $DataStockOpename['creatAt'];
    $creatBy      = $DataStockOpename['creatBy'];
    $creatByNama  = $DataStockOpename['creatBy_nama_akses'];
    $updateAt     = $DataStockOpename['updateAt'];
    $updateBy     = $DataStockOpename['updateBy'];
    $updateByNama = $DataStockOpename['updateBy_nama_akses'];
    $status       = $DataStockOpename['status'];

    // Finish Time
    if(!empty($DataStockOpename['finish_at'])){
        $finish_at_date = date('Y-m-d', strtotime($finish_at));
        $finish_at_time = date('H:i:s', strtotime($finish_at));
    }else{
        $finish_at_date = "";
        $finish_at_time = "";
    }

    // Tampilkan Form
?>

<input type="hidden" name="id_stock_opname" value="<?php echo $id_stock_opname; ?>">
<div class="row mb-3">
    <div class="col-12">
        <label for="start_at">Mulai</label>
        <div class="input-group">
            <input type="date" name="start_at_date" class="form-control" value="<?php echo date('Y-m-d', strtotime($start_at)); ?>">
            <input type="time" name="start_at_time" class="form-control" value="<?php echo date('H:i:s', strtotime($start_at)); ?>">
        </div>
    </div>
</div>
<div class="row mb-3">
    <div class="col-12">
        <label for="finish_at">Selesai</label>
        <div class="input-group">
            <input type="date" name="finish_at_date" class="form-control" value="<?php echo $finish_at_date; ?>">
            <input type="time" name="finish_at_time" class="form-control" value="<?php echo $finish_at_time; ?>">
        </div>
    </div>
</div>
<div class="row mb-3">
    <div class="col-12">
        <label for="status_edit">Status</label>
        <select name="status" id="status_edit" class="form-control" required>
            <option <?php if($status==""){echo "selected";} ?> value="">Pilih</option>
            <option <?php if($status=="On-Progress"){echo "selected";} ?> value="On-Progress">On-Progress</option>
            <option <?php if($status=="Finished"){echo "selected";} ?> value="Finished">Finished</option>
        </select>
    </div>
</div>