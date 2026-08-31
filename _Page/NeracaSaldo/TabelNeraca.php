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
    $periode1     = $_POST['periode1'];
    $periode2     = $_POST['periode2'];

    // Format Parameter
    $strtotime1=strtotime($periode1);
    $strtotime2=strtotime($periode2);
    $tanggal1_format=date('d F Y',$strtotime1);
    $tanggal2_format=date('d F Y',$strtotime2);
   
    // Persiapkan title
    $html_title = '
        <b>LAPORAN NERACA SALDO</b><br>
        <span>Periode : <b class="text text-grayish">'.$tanggal1_format.' s/d '.$tanggal2_format.'</b></span>
    ';

    $jml_data = mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM akun_perkiraan"));
    if(empty($jml_data)){

        $html = '
            <tr>
                <td colspan="8" class="text-center">
                    <small class="text-danger">
                        Tidak Ada Data Neraca Yang Dapat Ditampilkan
                    </small>
                </td>
            </tr>
        ';
        $response = [
            "status"     => "success",
            "message"    => "Tidak Ada Data Neraca Yang Dapat Ditampilkan",
            "html"       => $html,
            "title"      => $html_title,
            "data_count" => $jml_data,
        ];
        echo json_encode($response);
        exit;
    }
    $html = '';
    // Query untuk mengambil akun level 1 (group utama)
    $NoUtama=1;
    $NoAnak=1;
    $QryGroupUtama = mysqli_query($Conn, "SELECT * FROM akun_perkiraan WHERE level='1' ORDER BY nama");
    while ($GroupUtama = mysqli_fetch_array($QryGroupUtama)) {
        $id_perkiraan_utama = $GroupUtama['id_perkiraan'];
        $kode_utama = $GroupUtama['kode'];
        $nama_utama = $GroupUtama['nama'];
        $saldo_normal_utama = $GroupUtama['saldo_normal'];
        // Tampilkan group utama
        $html.= '
            <tr>
                <td class="bg-secondary" align="left"><b>'.$kode_utama.'</b></td>
                <td class="bg-secondary" align="left" colspan="6"><b>'.$nama_utama.'</b></td>
            </tr>
        ';
        // Query untuk mengambil anak group dari group utama berdasarkan kode
        $QryAnakGroup = mysqli_query($Conn, "SELECT * FROM akun_perkiraan WHERE kode LIKE '$kode_utama%' AND level != '1' ORDER BY nama");
        while ($AnakGroup = mysqli_fetch_array($QryAnakGroup)) {
            $id_perkiraan_anak = $AnakGroup['id_perkiraan'];
            $nama_anak = $AnakGroup['nama'];
            $saldo_normal_anak = $AnakGroup['saldo_normal'];
            $kode = $AnakGroup['kode'];
            $level = $AnakGroup['level'];
            $LevelTerbawah = mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM akun_perkiraan WHERE kd$level='$kode'"));
            // Tampilkan anak group
            if($LevelTerbawah=="1"){
                //Jumlah Debet
                $SumDebet = mysqli_fetch_array(mysqli_query($Conn, "SELECT SUM(nilai) AS nilai FROM jurnal WHERE kode_perkiraan='$kode' AND d_k='D' AND tanggal>='$periode1' AND tanggal<='$periode2'"));
                $JumlahDebet = $SumDebet['nilai'];
                if(empty($JumlahDebet)){
                    $JumlahDebet=0;
                }
                $JumlahDebetFormat = "" . number_format($JumlahDebet,0,',','.');
                //Jumlah Kredit
                $SumKredit = mysqli_fetch_array(mysqli_query($Conn, "SELECT SUM(nilai) AS nilai FROM jurnal WHERE kode_perkiraan='$kode' AND d_k='K' AND tanggal>='$periode1' AND tanggal<='$periode2'"));
                $JumlahKredit = $SumKredit['nilai'];
                if(empty($JumlahKredit)){
                    $JumlahKredit=0;
                }
                $JumlahKreditFormat = "" . number_format($JumlahKredit,0,',','.');
                //Hitung Saldo Berdasarkan Saldo Normal
                if($saldo_normal_anak=="Debet"){
                    $JumlahSaldo=$JumlahDebet-$JumlahKredit;
                }else{
                    $JumlahSaldo=$JumlahKredit-$JumlahDebet;
                }
                $JumlahSaldoFormat="" . number_format($JumlahSaldo,0,',','.');
                
                $html.= '
                    <tr>
                        <td>'.$NoAnak.'</td>
                        <td>'.$kode.'</td>
                        <td>'.$nama_anak.'</td>
                        <td>'.$saldo_normal_anak.'</td>
                        <td>'.$JumlahDebetFormat.'</td>
                        <td>'.$JumlahKreditFormat.'</td>
                        <td>'.$JumlahSaldoFormat.'</td>
                    </tr>
                ';
                $NoAnak++;
            }
        }
    }

    $response = [
        "status"     => "success",
        "message"    => "Data Neraca Ditampilkan",
        "html"       => $html,
        "title"      => $html_title,
        "data_count" => $jml_data,
    ];
    echo json_encode($response);
    exit;
?>
 