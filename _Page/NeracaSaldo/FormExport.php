<?php
    //Koneksi & Sesi Akses
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/SettingGeneral.php";
    include "../../_Config/Session.php";

    // Default Header JSON
    header('Content-Type: application/json; charset=utf-8');

    // Default Response
    $response = [
        "status"     => "error",
        "message"    => "",
        "html"       => "",
        "title"      => "",
        "data_count" => 0,
    ];
    
    // Validasi Sesi Akses
    if(empty($SessionIdAkses)){
        $response = [
            "status"     => "error",
            "message"    => "Sesi Akses Sudah Berakhir, Silahkan Login Ulang!",
            "html"       => "",
        ];
        echo json_encode($response);
        exit;
    }

    // Validasi Periode Awal dan Akhir
    if(empty($_POST['periode1']) || empty($_POST['periode2'])){
        $response = [
            "status"     => "error",
            "message"    => "Lengkapi Periode Data Yang Akan Ditampilkan!",
            "html"       => "",
        ];
        echo json_encode($response);
        exit;
    }

    // Buat Variabel
    $periode1     = $_POST['periode1'];
    $periode2     = $_POST['periode2'];

    // Hitung Jumlah Data
    $jml_data = mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM akun_perkiraan"));

    $response = [
        "status"     => "success",
        "message"    => "Data Siap Di Export",
        "html"       => '
            <input type="hidden" name="periode1" value="'.$periode1.'">
            <input type="hidden" name="periode2" value="'.$periode2.'">
            <div class="row">
                <div class="col-12 text-center">
                    <div class="alert alert-info">
                        <small>Jumlah Record</small>
                        <h1>'.$jml_data.'</h1>
                        <i class="bi bi-check-circle"></i> Data Siap Di Export!
                    </div>
                </div>
            </div>

        ',
    ];
    echo json_encode($response);
    exit;
?>

