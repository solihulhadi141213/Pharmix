<?php
    // Koneksi
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');

    if(empty($SessionIdAkses)){
        echo json_encode([
            "status" => "error",
            "message" => "Sesi Akses Sudah Berakhir. Silahkan Login Ulang!",
            "html" => ""
        ]);
        exit;
    }

    if(empty($_POST['id_barang'])){
        echo json_encode([
            "status" => "error",
            "message" => "ID Barang Tidak Boleh Kosong!",
            "html" => ""
        ]);
        exit;
    }

    if(empty($_POST['id_stock_opname'])){
        echo json_encode([
            "status" => "error",
            "message" => "ID Sesi Stock Opename Tidak Boleh Kosong!",
            "html" => ""
        ]);
        exit;
    }

    $id_barang = validateAndSanitizeInput($_POST['id_barang']);
    $id_stock_opname = validateAndSanitizeInput($_POST['id_stock_opname']);

    // Ambil data barang
    $StmtBarang = mysqli_prepare($Conn, "SELECT kode_barang, nama_barang, stok_barang, harga_beli FROM barang WHERE id_barang=? LIMIT 1");
    mysqli_stmt_bind_param($StmtBarang, "i", $id_barang);
    mysqli_stmt_execute($StmtBarang);
    $ResultBarang = mysqli_stmt_get_result($StmtBarang);
    $DataBarang = mysqli_fetch_assoc($ResultBarang);
    mysqli_stmt_close($StmtBarang);

    if(empty($DataBarang)){
        echo json_encode([
            "status" => "error",
            "message" => "Data Barang Tidak Ditemukan!",
            "html" => ""
        ]);
        exit;
    }

    // Cek apakah barang ini sudah memiliki data stock_opname pada sesi aktif
    $StmtSO = mysqli_prepare($Conn, "
        SELECT id_stock_opname_barang, stok_awal, stok_akhir, harga_beli, keterangan
        FROM stock_opname_barang
        WHERE id_stock_opname=? AND id_barang=?
        LIMIT 1
    ");
    mysqli_stmt_bind_param($StmtSO, "ii", $id_stock_opname, $id_barang);
    mysqli_stmt_execute($StmtSO);
    $ResultSO = mysqli_stmt_get_result($StmtSO);
    $DataSO = mysqli_fetch_assoc($ResultSO);
    mysqli_stmt_close($StmtSO);

    if(!empty($DataSO)){
        $id_stock_opname_barang = $DataSO['id_stock_opname_barang'];
        $stok_awal              = $DataSO['stok_awal'];
        $stok_akhir             = $DataSO['stok_akhir'];
        $harga                  = $DataSO['harga_beli'];
        $keterangan                  = $DataSO['keterangan'];
    }else{
        $id_stock_opname_barang = "";
        $stok_awal              = $DataBarang['stok_barang'];
        $stok_akhir             = $DataBarang['stok_barang'];
        $harga                  = $DataBarang['harga_beli'];
        $keterangan             = "";
    }

    $stok_awal = (float) $stok_awal;
    $stok_akhir = (float) $stok_akhir;
    $harga = (float) $harga;

    $stok_awal = ($stok_awal == floor($stok_awal)) ? (int)$stok_awal : $stok_awal;
    $stok_akhir = ($stok_akhir == floor($stok_akhir)) ? (int)$stok_akhir : $stok_akhir;
    $harga = ($harga == floor($harga)) ? (int)$harga : $harga;

    $html = '
        <input type="hidden" name="id_barang" value="'.$id_barang.'">
        <input type="hidden" name="id_stock_opname" value="'.$id_stock_opname.'">
        <input type="hidden" name="id_stock_opname_barang" value="'.$id_stock_opname_barang.'">
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="stok_awal">Stock Awal</label>
                <input type="text" name="stok_awal" id="stok_awal" class="form-control" maxlength="15" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9.]/g, \'\').replace(/(\\..*?)\\..*/g, \'$1\');" value="'.$stok_awal.'">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="stok_akhir">Stock Akhir</label>
                <input type="text" name="stok_akhir" id="stok_akhir" class="form-control" maxlength="15" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9.]/g, \'\').replace(/(\\..*?)\\..*/g, \'$1\');" value="'.$stok_akhir.'">
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 mb-3">
                <label for="harga">Harga Beli (Rp)</label>
                <input type="text" name="harga" id="harga" class="form-control form-money" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, \'\');" value="'.$harga.'">
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 mb-3">
                <label for="keterangan">Keterangan</label>
                <textarea class="form-control" name="keterangan" id="keterangan">'.$keterangan.'</textarea>
            </div>
        </div>
    ';

    echo json_encode([
        "status" => "success",
        "message" => "Data berhasil ditampilkan.",
        "html" => $html
    ]);
?>
