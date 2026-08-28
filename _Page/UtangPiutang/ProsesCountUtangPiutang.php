<?php
    // Koneksi
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Time Zone
    date_default_timezone_set('Asia/Jakarta');

    // Time Now Tmp
    $now = date('Y-m-d H:i:s');

     //Validasi Akses
    if(empty($SessionIdAkses)){
        $response = [
            "status" => "Error",
            "message" => "Sesi Akses Sudah Berakhir! Silahkan Login Ulang!",
            "data" => ""
        ];
        echo json_encode($response);
        exit;
    }

    //-------------------------------------------------------------------------------
    //Data Jual/Beli
    $query_penjualan = "
        SELECT
            SUM(CASE WHEN kategori='Pembelian' AND status='Kredit' THEN total ELSE 0 END) AS utang_pembelian,
            SUM(CASE WHEN kategori='Retur Pembelian' AND status='Kredit' THEN total ELSE 0 END) AS piutang_retur_pembelian,
            SUM(CASE WHEN kategori='Penjualan' AND status='Kredit' THEN total ELSE 0 END) AS piutang_penjualan,
            SUM(CASE WHEN kategori='Retur Penjualan' AND status='Kredit' THEN total ELSE 0 END) AS utang_retur_penjualan
        FROM transaksi_jual_beli
    ";
    $stmt_penjualan = mysqli_prepare($Conn, $query_penjualan);
    mysqli_stmt_execute($stmt_penjualan);
    $result_penjualan = mysqli_stmt_get_result($stmt_penjualan);
    $data_penjualan_ringkas = mysqli_fetch_assoc($result_penjualan);
    mysqli_stmt_close($stmt_penjualan);

    // Variabel Utang-Piutang Jual/Beli
    $utang_pembelian         = (float) ($data_penjualan_ringkas['utang_pembelian'] ?? 0);
    $piutang_retur_pembelian = (float) ($data_penjualan_ringkas['piutang_retur_pembelian'] ?? 0);
    $piutang_penjualan       = (float) ($data_penjualan_ringkas['piutang_penjualan'] ?? 0);
    $utang_retur_penjualan   = (float) ($data_penjualan_ringkas['utang_retur_penjualan'] ?? 0);
    $utang_jual_beli         = $utang_pembelian + $utang_retur_penjualan;
    $piutang_jual_beli       = $piutang_retur_pembelian + $piutang_penjualan;
    
    //-------------------------------------------------------------------------------
    //Data operasional
    $query_operasional = "
        SELECT
            SUM(CASE WHEN status='Utang' THEN jumlah ELSE 0 END) AS utang_operasional,
            SUM(CASE WHEN status='Piutang' THEN jumlah ELSE 0 END) AS piutang_operasional
        FROM transaksi
    ";
    $stmt_operasional = mysqli_prepare($Conn, $query_operasional);
    mysqli_stmt_execute($stmt_operasional);
    $result_operasional = mysqli_stmt_get_result($stmt_operasional);
    $data_operasional = mysqli_fetch_assoc($result_operasional);
    mysqli_stmt_close($stmt_operasional);

    // Variabel Utang-Piutang Operasional
    $utang_operasional   = (float) ($data_operasional['utang_operasional'] ?? 0);
    $piutang_operasional = (float) ($data_penjualan_ringkas['piutang_operasional'] ?? 0);

    //-------------------------------------------------------------------------------
    // Menghitung Utang-piutang total
    $total_utang   = $utang_operasional + $utang_jual_beli;
    $total_piutang = $piutang_operasional + $piutang_jual_beli;

    //-------------------------------------------------------------------------------
    //Format Jual-Beli
    $utang_pembelian         = "Rp " . number_format($utang_pembelian,0,',','.');
    $piutang_retur_pembelian = "Rp " . number_format($piutang_retur_pembelian,0,',','.');
    $piutang_penjualan       = "Rp " . number_format($piutang_penjualan,0,',','.');
    $utang_retur_penjualan   = "Rp " . number_format($utang_retur_penjualan,0,',','.');
    $utang_jual_beli         = "Rp " . number_format($utang_jual_beli,0,',','.');
    $piutang_jual_beli       = "Rp " . number_format($piutang_jual_beli,0,',','.');

    // Operasional Format
    $utang_operasional   = "Rp " . number_format($utang_operasional,0,',','.');
    $piutang_operasional = "Rp " . number_format($piutang_operasional,0,',','.');

    // Total Format
    $total_utang   = "Rp " . number_format($total_utang,0,',','.');
    $total_piutang = "Rp " . number_format($total_piutang,0,',','.');

    //-------------------------------------------------------------------------------
    // Response
    $response = [
        "status"  => "success",
        "message" => "Data Berhasil Dimuat",
        "data"    => [
            "utang_pembelian"         => $utang_pembelian,
            "piutang_retur_pembelian" => $piutang_retur_pembelian,
            "piutang_penjualan"       => $piutang_penjualan,
            "utang_retur_penjualan"   => $utang_retur_penjualan,
            "utang_jual_beli"         => $utang_jual_beli,
            "piutang_jual_beli"       => $piutang_jual_beli,
            "utang_operasional"       => $utang_operasional,
            "piutang_operasional"     => $piutang_operasional,
            "total_utang"             => $total_utang,
            "total_piutang"           => $total_piutang,
        ]
    ];
    echo json_encode($response);
    exit;
    
?>