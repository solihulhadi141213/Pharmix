<?php
    // KONEKSI DAN SESSION
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // JSON Default Format
    header('Content-Type: application/json; charset=utf-8');

    // Time Zone
    date_default_timezone_set('Asia/Jakarta');

    // Default HTML
    $html='';

    // Default Response
    $response = [
        "status"  => "error",
        "message" => "error",
        "html"    => ""
    ];

    // VALIDASI SESI AKSES
    if (empty($SessionIdAkses)) {
        $response = [
            "status"  => "error",
            "message" => "Sesi Akses Sudah Berakhir! Silahkan Login Ulang!",
            "html"    => ""
        ];
        echo json_encode($response);
        exit;
    }

    // VALIDASI ID
    if (empty($_POST['id'])) {
        $response = [
            "status"  => "error",
            "message" => "ID Transaksi Tidak Boleh Kosong!",
            "html"    => ""
        ];
        echo json_encode($response);
        exit;
    }

    // VALIDASI KATEGORI
    if (empty($_POST['kategori'])) {
        $response = [
            "status"  => "error",
            "message" => "Kategori Transaksi Tidak Boleh Kosong!",
            "html"    => ""
        ];
        echo json_encode($response);
        exit;
    }

    // VARIABEL DAN SANITASI
    $id       = validateAndSanitizeInput($_POST['id']);
    $kategori = validateAndSanitizeInput($_POST['kategori']);

    // Default Variabel
    $id_transaksi           = "";
    $id_transaksi_jual_beli = "";
    $kategori_tempo         = "";
    $tanggal_tempo          = "";

    // ==========================================
    // ROUTING TRANSAKSI
    // ==========================================
    if ($kategori == "jual_beli") {
        $id_transaksi_jual_beli = $id;
        $kategori_tempo = GetDetailData($Conn, 'transaksi_jual_beli', 'id_transaksi_jual_beli', $id_transaksi_jual_beli, 'kategori');
        
        if (empty($kategori_tempo)) {
            $response = [
                "status"  => "error",
                "message" => "ID Transaksi Jual/Beli Tidak Valid!",
                "html"    => ""
            ];
            echo json_encode($response);
            exit;
        }

        // Ambil 'id_transaksi_tempo' dari database
        $id_transaksi_tempo = GetDetailData($Conn, 'transaksi_tempo', 'id_transaksi_jual_beli', $id, 'id_transaksi_tempo');

    } else {
        $id_transaksi = $id;
        $id_transaksi_jenis = GetDetailData($Conn, 'transaksi', 'id_transaksi', $id_transaksi, 'id_transaksi_jenis');

        // Validasi 'id_transaksi_jenis'
        if (empty($id_transaksi_jenis)) {
            $response = [
                "status"  => "error",
                "message" => "ID Transaksi Operasional Tidak Valid!",
                "html"    => ""
            ];
            echo json_encode($response);
            exit;
        }

        $kategori_tempo = GetDetailData($Conn, 'transaksi_jenis', 'id_transaksi_jenis', $id_transaksi_jenis, 'kategori');
        
        // Validasi 'kategori_tempo'
        if (empty($kategori_tempo)) {
            $response = [
                "status"  => "error",
                "message" => "Kategori Operasional Tidak Valid!",
                "html"    => ""
            ];
            echo json_encode($response);
            exit;
        }

        $id_transaksi_tempo = GetDetailData($Conn, 'transaksi_tempo', 'id_transaksi', $id, 'id_transaksi_tempo');
    }

    $tanggal_tempo = GetDetailData($Conn, 'transaksi_tempo', 'id_transaksi_tempo', $id_transaksi_tempo, 'tanggal_tempo');

    // Buat HTML
    $html.='
        <input type="hidden" name="id_transaksi_tempo" value="'.$id_transaksi_tempo.'">

        <div class="row mb-2">
            <div class="col-4">
                <small>ID Transaksi</small>
            </div>
            <div class="col-8 text-end">
                <small class="text-muted">'.$id.'</small>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-4">
                <small>Kategori Transaksi</small>
            </div>
            <div class="col-8 text-end">
                <small class="text-muted">'.$kategori_tempo.'</small>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-4">
                <small>Tanggal Tempo</small>
            </div>
            <div class="col-8 text-end">
                <small class="text-muted">'.$tanggal_tempo.'</small>
            </div>
        </div>
    ';

    $response = [
        "status"  => "success",
        "message" => "Data Berhasil Ditampilkan",
        "html"    => $html
    ];
    echo json_encode($response);
?>
