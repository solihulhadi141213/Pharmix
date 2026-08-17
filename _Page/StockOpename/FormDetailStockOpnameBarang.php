<?php
    // Koneksi
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    if(empty($SessionIdAkses)){
        echo '
            <div class="alert alert-danger">
                <small>Sesi Akses Sudah Berakhir! Silahkan Login Ulang!</small>
            </div>
        ';
        exit;
    }

    if(empty($_POST['id_barang'])){
        echo '
            <div class="alert alert-danger">
                <small>ID Barang Tidak Boleh Kosong!</small>
            </div>
        ';
        exit;
    }

    if(empty($_POST['id_stock_opname'])){
        echo '
            <div class="alert alert-danger">
                <small>ID Stock Opname Tidak Boleh Kosong!</small>
            </div>
        ';
        exit;
    }

    // Variabel Data dan Sanitasi
    $id_barang       = validateAndSanitizeInput($_POST['id_barang']);
    $id_stock_opname = validateAndSanitizeInput($_POST['id_stock_opname']);

    // Ambil data barang
    $StmtBarang = mysqli_prepare($Conn, "SELECT * FROM barang WHERE id_barang=? LIMIT 1");
    mysqli_stmt_bind_param($StmtBarang, "i", $id_barang);
    mysqli_stmt_execute($StmtBarang);
    $ResultBarang = mysqli_stmt_get_result($StmtBarang);
    $DataBarang = mysqli_fetch_assoc($ResultBarang);
    mysqli_stmt_close($StmtBarang);

    if(empty($DataBarang)){
        echo '
            <div class="alert alert-danger">
                <small>Barang Tidak Ditemukan!</small>
            </div>
        ';
        exit;
    }

    // Cek apakah barang ini sudah memiliki data stock_opname pada sesi aktif
    $StmtSO = mysqli_prepare($Conn, "SELECT * FROM stock_opname_barang WHERE id_stock_opname=? AND id_barang=? LIMIT 1");
    mysqli_stmt_bind_param($StmtSO, "ii", $id_stock_opname, $id_barang);
    mysqli_stmt_execute($StmtSO);
    $ResultSO = mysqli_stmt_get_result($StmtSO);
    $DataSO = mysqli_fetch_assoc($ResultSO);
    mysqli_stmt_close($StmtSO);

    if(!empty($DataSO)){
        $id_stock_opname_barang = $DataSO['id_stock_opname_barang'];
        $stok_awal              = number_format($DataSO['stok_awal'], 2, ',', '.');
        $stok_akhir             = number_format($DataSO['stok_akhir'], 2, ',', '.');
        $stok_gap               = number_format($DataSO['stok_gap'], 2, ',', '.');
        
        $harga_beli_so_val      = $DataSO['harga_beli'];
        $harga_beli_so          = "Rp " . number_format($harga_beli_so_val, 0, ',', '.');

        $jumlah_val             = $DataSO['jumlah'];
        $jumlah                 = "Rp " . number_format($jumlah_val, 0, ',', '.');

        $keterangan             = !empty($DataSO['keterangan']) ? $DataSO['keterangan'] : "-";
        
        // Format Tanggal d F Y (disertai jam jika diperlukan, atau cukup tanggal saja)
        $creatAt                = (!empty($DataSO['creatAt']) && $DataSO['creatAt'] != '0000-00-00 00:00:00') ? date('d F Y', strtotime($DataSO['creatAt'])) : "-";
        $updateAt               = (!empty($DataSO['updateAt']) && $DataSO['updateAt'] != '0000-00-00 00:00:00') ? date('d F Y', strtotime($DataSO['updateAt'])) : "-";
        
        $creatBy_id             = $DataSO['creatBy'];
        $updateBy_id            = $DataSO['updateBy'];
    }else{
        $id_stock_opname_barang = "-";
        $stok_awal              = "-";
        $stok_akhir             = "-";
        $stok_gap               = "-";
        $harga_beli_so          = "-";
        $jumlah                 = "-";
        $keterangan             = "-";
        $creatAt                = "-";
        $updateAt               = "-";
        $creatBy_id             = null;
        $updateBy_id            = null;
    }
    
    // Ambil Nama User CreatBy dari tabel akses
    $creatBy = "-";
    if(!empty($creatBy_id)){
        $StmtUserC = mysqli_prepare($Conn, "SELECT nama_akses FROM akses WHERE id_akses=? LIMIT 1");
        mysqli_stmt_bind_param($StmtUserC, "i", $creatBy_id);
        mysqli_stmt_execute($StmtUserC);
        $ResUserC = mysqli_stmt_get_result($StmtUserC);
        if($RowUserC = mysqli_fetch_assoc($ResUserC)){
            $creatBy = $RowUserC['nama_akses'];
        }
        mysqli_stmt_close($StmtUserC);
    }

    // Ambil Nama User UpdateBy dari tabel akses
    $updateBy = "-";
    if(!empty($updateBy_id)){
        $StmtUserU = mysqli_prepare($Conn, "SELECT nama_akses FROM akses WHERE id_akses=? LIMIT 1");
        mysqli_stmt_bind_param($StmtUserU, "i", $updateBy_id);
        mysqli_stmt_execute($StmtUserU);
        $ResUserU = mysqli_stmt_get_result($StmtUserU);
        if($RowUserU = mysqli_fetch_assoc($ResUserU)){
            $updateBy = $RowUserU['nama_akses'];
        }
        mysqli_stmt_close($StmtUserU);
    }

    // Data Barang
    $kode_barang     = $DataBarang['kode_barang'];
    $nama_barang     = $DataBarang['nama_barang'];
    $kategori_barang = $DataBarang['kategori_barang'];
    $satuan_barang   = $DataBarang['satuan_barang'];
    $konversi        = $DataBarang['konversi'];
    
    $harga_beli_val  = $DataBarang['harga_beli'];
    $harga_beli_rp   = "Rp " . number_format($harga_beli_val, 0, ',', '.');
    
    $stok_barang     = number_format($DataBarang['stok_barang'], 2, ',', '.');

    // Tampilkan Informasi
    echo '
        <div class="row mb-2">
            <div class="col-12"><small><b># Informasi Barang</b></small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Kode Barang</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small class="text-muted">'.$kode_barang.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Nama Barang</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small class="text-muted">'.$nama_barang.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Kategori</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small class="text-muted">'.$kategori_barang.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Satuan</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small class="text-muted">'.$satuan_barang.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Harga Beli</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small class="text-muted">'.$harga_beli_rp.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Stock Aktual</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small class="text-muted">'.$stok_barang.'</small></div>
        </div>

        <div class="row mt-3 mb-2">
            <div class="col-12"><small><b># Stock Opname Barang</b></small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Stock Awal</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small class="text-muted">'.$stok_awal.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Stock Akhir</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small class="text-muted">'.$stok_akhir.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Margin</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small class="text-muted">'.$stok_gap.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Harga Beli</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small class="text-muted">'.$harga_beli_so.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Jumlah Margin</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small class="text-muted">'.$jumlah.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Keterangan</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small class="text-muted">'.$keterangan.'</small></div>
        </div>

        <div class="row mt-3 mb-2">
            <div class="col-12"><small><b># Metadata</b></small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Creat At</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small class="text-muted">'.$creatAt.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Creat By</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small class="text-muted">'.$creatBy.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Update At</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small class="text-muted">'.$updateAt.'</small></div>
        </div>
        <div class="row mb-2">
            <div class="col-5"><small>Update By</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6"><small class="text-muted">'.$updateBy.'</small></div>
        </div>
    ';
?>