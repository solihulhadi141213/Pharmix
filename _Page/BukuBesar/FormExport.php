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

    // Validasi id_perkiraan
    if(empty($_POST['id_perkiraan'])){
        $response = [
            "status"     => "error",
            "message"    => "ID Perkiraan Belum Dipilih",
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
    $id_perkiraan = $_POST['id_perkiraan'];
    $periode1     = $_POST['periode1'];
    $periode2     = $_POST['periode2'];

    // Buka Akun Perkiraan
    $id_perkiraan=validateAndSanitizeInput($_POST['id_perkiraan']);

    $Qry = $Conn->prepare("SELECT * FROM akun_perkiraan WHERE id_perkiraan = ?");
    $Qry->bind_param("i", $id_perkiraan);
    if (!$Qry->execute()) {
        $error=$Conn->error;
        $response = [
            "status"     => "error",
            "message"    => "Terjadi kesalahan pada saat membuka data akun perkiraan. Keterangan : $error",
            "html"       => "",
            "title"      => "",
            "data_count" => 0,
        ];
        echo json_encode($response);
        exit;
    }

    $Result = $Qry->get_result();
    $Data = $Result->fetch_assoc();
    $Qry->close();

    // Validasi Apakah Akun Perkiraan Ada pada Database
    if(empty($Data['id_perkiraan'])){
        $response = [
            "status"     => "error",
            "message"    => "ID Akun Yang Anda Pilih Tidak Ditemukan Pada Database",
            "html"       => "",
            "title"      => "",
            "data_count" => 0,
        ];
        echo json_encode($response);
        exit;
    }

    //Buat Variabel
    $kode         = $Data['kode'];
    $nama         = $Data['nama'];
    $saldo_normal = $Data['saldo_normal'];

    // Hitung Jumlah Data
    $jml_data = mysqli_num_rows(mysqli_query($Conn, "SELECT id_jurnal FROM jurnal WHERE kode_perkiraan='$kode' AND tanggal>='$periode1' AND tanggal<='$periode2'"));

    $response = [
        "status"     => "success",
        "message"    => "Data Siap Di Export",
        "html"       => '
            <input type="hidden" name="id_perkiraan" value="'.$id_perkiraan.'">
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

