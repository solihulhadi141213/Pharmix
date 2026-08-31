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
            "title"      => "",
            "data_count" => 0,
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
            "title"      => "",
            "data_count" => 0,
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
            "title"      => "",
            "data_count" => 0,
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
    
    // Format Parameter
    $strtotime1=strtotime($periode1);
    $strtotime2=strtotime($periode2);
    $tanggal1_format=date('d F Y',$strtotime1);
    $tanggal2_format=date('d F Y',$strtotime2);
    if($saldo_normal=="Debet"){
        $SaldoNormal="D";
    }else{
        $SaldoNormal="K";
    }

    // Persiapkan title
    $html_title = '
        <b>LAPORAN BUKU BESAR</b><br>
        <b>'.$nama.' (Kode Akun : '.$kode.')</b><br>
        <span>Periode : <b class="text text-grayish">'.$tanggal1_format.' s/d '.$tanggal2_format.'</b></span><br>
        <small>Saldo Normal : <b class="text text-grayish">'.$saldo_normal.'</b></small><br>
    ';

    // Hitung Jumlah Data
    $jml_data = mysqli_num_rows(mysqli_query($Conn, "SELECT id_jurnal FROM jurnal WHERE kode_perkiraan='$kode' AND tanggal>='$periode1' AND tanggal<='$periode2'"));
    if(empty($jml_data)){

        $html = '
            <tr>
                <td colspan="8" class="text-center">
                    <small class="text-danger">
                        Tidak Ada Data Buku Besar Yang Dapat Ditampilkan
                    </small>
                </td>
            </tr>
        ';
        $response = [
            "status"     => "success",
            "message"    => "Tidak Ada Data Buku Besar Yang Dapat Ditampilkan",
            "html"       => $html,
            "title"      => $html_title,
            "data_count" => $jml_data,
        ];
        echo json_encode($response);
        exit;
    }

    // Jika Ada Hitung Jumlah Saldo sebelum '$periode1'
    // Hitung saldo sebelum periode
    $NilaiDebetBefore  = 0;
    $NilaiKreditBefore = 0;
    $SaldoBefore       = 0;

    $QrySaldoBefore = $Conn->prepare("
        SELECT
            COALESCE(SUM(CASE WHEN d_k = 'D' THEN nilai ELSE 0 END), 0) AS total_debet,
            COALESCE(SUM(CASE WHEN d_k = 'K' THEN nilai ELSE 0 END), 0) AS total_kredit
        FROM jurnal
        WHERE kode_perkiraan = ?
        AND tanggal < ?
    ");

    $QrySaldoBefore->bind_param("ss", $kode, $periode1);

    if (!$QrySaldoBefore->execute()) {
        $response = [
            "status"     => "error",
            "message"    => "Gagal menghitung saldo sebelum periode.",
            "html"       => "",
            "title"      => $html_title,
            "data_count" => 0,
        ];
        echo json_encode($response);
        exit;
    }

    $ResultSaldoBefore = $QrySaldoBefore->get_result();
    $DataSaldoBefore   = $ResultSaldoBefore->fetch_assoc();

    $QrySaldoBefore->close();

    $NilaiDebetBefore  = (float) $DataSaldoBefore['total_debet'];
    $NilaiKreditBefore = (float) $DataSaldoBefore['total_kredit'];

    // Hitung saldo berdasarkan saldo normal akun
    if ($SaldoNormal === "D") {
        $SaldoBefore = $NilaiDebetBefore - $NilaiKreditBefore;
    } else {
        $SaldoBefore = $NilaiKreditBefore - $NilaiDebetBefore;
    }

    // Format rupiah
    $RpDebetBefore  = "Rp" . number_format($NilaiDebetBefore, 0, ',', '.');
    $RpKreditBefore = "Rp" . number_format($NilaiKreditBefore, 0, ',', '.');
    $RpSaldoBefore  = "Rp" . number_format($SaldoBefore, 0, ',', '.');
    $html= '
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="2">
                Saldo Sebelum Periode <i>'.$periode1.'</i>
            </td>
            <td>'.$RpDebetBefore.'</td>
            <td>'.$RpKreditBefore.'</td>
            <td><b>'.$RpSaldoBefore.'</b></td>
        </tr>
    ';

    // Mulai Looping Hingga Periode Berikutnya
    $no = 1;

    // Saldo berjalan dimulai dari saldo sebelum periode
    $JumlahSaldo = $SaldoBefore;

    $query = mysqli_query(
        $Conn,
        "
            SELECT *
            FROM jurnal
            WHERE kode_perkiraan='$kode'
            AND tanggal >= '$periode1'
            AND tanggal <= '$periode2'
            ORDER BY tanggal ASC, id_jurnal ASC
        "
    );

    while ($data = mysqli_fetch_array($query)) {

        $id_jurnal = $data['id_jurnal'];
        $uuid      = $data['uuid'];
        $tanggal   = $data['tanggal'];
        $kategori  = $data['kategori'];
        $d_k       = $data['d_k'];
        $nilai     = (float) $data['nilai'];

        if ($d_k == "D") {
            $NilaiDebet  = "Rp" . number_format($nilai, 0, ',', '.');
            $NilaiKredit = "-";
        } else {
            $NilaiDebet  = "-";
            $NilaiKredit = "Rp" . number_format($nilai, 0, ',', '.');
        }

        // Saldo berjalan
        if ($d_k == $SaldoNormal) {
            $JumlahSaldo += $nilai;
        } else {
            $JumlahSaldo -= $nilai;
        }

        $tanggal_format = date('d/m/y', strtotime($tanggal));
        $RpSaldo        = "Rp" . number_format($JumlahSaldo, 0, ',', '.');

        $html .= '
            <tr>
                <td>'.$no.'</td>
                <td>'.$id_jurnal.'</td>
                <td>'.$tanggal_format.'</td>
                <td>'.$uuid.'</td>
                <td>'.$kategori.'</td>
                <td>'.$NilaiDebet.'</td>
                <td>'.$NilaiKredit.'</td>
                <td><b>'.$RpSaldo.'</b></td>
            </tr>
        ';

        $no++;
    }

    $response = [
        "status"     => "success",
        "message"    => "Tidak Ada Data Buku Besar Yang Dapat Ditampilkan",
        "html"       => $html,
        "title"      => $html_title,
        "data_count" => $jml_data,
    ];
    echo json_encode($response);

?>
 