<?php
    //Koneksi
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Default Response JSON
    header('Content-Type: application/json; charset=utf-8');

    // Default $html
    $html ="";
    
    // Validasi 'id_jurnal'
    if(empty($_POST['id_jurnal'])){
        $response = [
            "status"  => "error",
            "message" => "ID Pembayaran Tidak Boleh Kosong",
            "html"    => ""
        ];
        echo json_encode($response);
        exit;
    }

    // Variabel dan sanitasi
    $id_jurnal = validateAndSanitizeInput($_POST['id_jurnal']);

    // Buka Data 'jurnal'
    $Qry = $Conn->prepare("SELECT * FROM jurnal WHERE id_jurnal = ?");
    $Qry->bind_param("i", $id_jurnal);
    if (!$Qry->execute()) {
        $response = [
            "status"  => "error",
            "message" => 'Terjadi Kesalahan : '.$Conn->error,
            "html"    => ""
        ];
        echo json_encode($response);
        exit;
    }
    $Result = $Qry->get_result();
    $Data = $Result->fetch_assoc();
    $Qry->close();

    if (!$Data) {
        $response = [
            "status"  => "error",
            "message" => 'ID Jurnal <i>'.$id_jurnal.'</i> Tidak Ditemukan Pada Database',
            "html"    => ""
        ];
        echo json_encode($response);
        exit;
    }

    // Ambil Data Transaksi Pembayaran
    $tanggal                = $Data['tanggal'];
    $kode_perkiraan = trim($Data['kode_perkiraan'] ?? '');
    $nama_perkiraan = trim($Data['nama_perkiraan'] ?? '');
    $d_k            = strtoupper(trim($Data['d_k'] ?? ''));
    $tanggal        = date('d/m/Y', strtotime($tanggal));
    $nilai          = pembulatan_nilai($Data['nilai']);
    $nilai_rp = 'Rp '.number_format($nilai, 0, ',', '.');

    // Nilai pada tabel jurnal menggunakan D/K, sedangkan option form menggunakan Debet/Kredit.
    $debet_selected  = ($d_k === 'D' || $d_k === 'DEBET') ? ' selected' : '';
    $kredit_selected = ($d_k === 'K' || $d_k === 'KREDIT') ? ' selected' : '';

    // Ambil daftar akun perkiraan yang bisa dipilih
    $html_akun_perkiraan = '<option value="">Pilih Akun Perkiraan</option>';
    $QryAkun = mysqli_query($Conn, "
        SELECT id_perkiraan, kode, nama, level
        FROM akun_perkiraan
        ORDER BY kode ASC
    ");

    if($QryAkun){
        while($DataAkun = mysqli_fetch_array($QryAkun)){
            $id_perkiraan = htmlspecialchars($DataAkun['id_perkiraan'], ENT_QUOTES, 'UTF-8');
            $kode_raw     = $DataAkun['kode'];
            $kode         = htmlspecialchars($kode_raw, ENT_QUOTES, 'UTF-8');
            $nama         = htmlspecialchars($DataAkun['nama'], ENT_QUOTES, 'UTF-8');
            $level        = (int) $DataAkun['level'];

            // Hanya tampilkan akun pada level terbawah agar pilihan lebih relevan
            $kolom_level = 'kd' . $level;
            $LevelTerbawah = mysqli_num_rows(mysqli_query($Conn, "SELECT id_perkiraan FROM akun_perkiraan WHERE $kolom_level='$kode_raw'"));

            if($LevelTerbawah == 1){
                $akun_terpilih = (
                    (string) $kode_raw === (string) $kode_perkiraan &&
                    (string) $DataAkun['nama'] === (string) $nama_perkiraan
                ) ? ' selected' : '';

                $html_akun_perkiraan .= '
                    <option value="'.$id_perkiraan.'"'.$akun_terpilih.'>'.$kode.' - '.$nama.'</option>
                ';
            }
        }
    }

    $response = [
        "status"  => "success",
        "message" => "Jurnal Berhasil Ditampilkan",
        "html"    => '
            <input type="hidden" name="id_jurnal" value="'.$id_jurnal.'">
            <div class="row mb-2">
                <div class="col-4"><small>ID Jurnal</small></div>
                <div class="col-8 text-end"><small class="text-muted">'.$id_jurnal.'</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-4"><small>Tanggal</small></div>
                <div class="col-8 text-end"><small class="text-muted">'.$tanggal.'</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-4"><small>Nominal</small></div>
                <div class="col-8 text-end"><small class="text-muted">'.$nilai_rp.'</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-4"><small>Debet/Kredit</small></div>
                <div class="col-8 text-end"><small class="text-muted">'.$d_k.'</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-4"><small>Akun</small></div>
                <div class="col-8 text-end"><small class="text-muted">'.$kode_perkiraan.'. '.$nama_perkiraan.'</small></div>
            </div>
            <div class="row mb-2 mt-3">
                <div class="col-12 text-center">
                    <div class="alert alert-warning">
                        <small>
                            <b>Penting!</b><br>
                            Data yang sudah dihapus tidak bisa dikembalikan lagi!<br>
                            <i>Apakah anda yakin akan menghapus data tersebut?</i>
                        </small>
                    </div>
                </div>
            </div>

        '
    ];
    echo json_encode($response);
    
?>

