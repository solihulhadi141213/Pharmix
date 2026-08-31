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
    $tanggal        = date('Y-m-d', strtotime($tanggal));
    $nilai          = pembulatan_nilai($Data['nilai']);

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
            <div class="row mb-3">
                <div class="col-12">
                    <label for="tanggal_jurnal">Tanggal</label>
                    <input type="date" name="tanggal" id="tanggal_jurnal" class="form-control" value="'.$tanggal.'" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-12">
                    <label for="nominal_jurnal_edit">Nominal</label>
                    <input type="text" name="nominal" id="nominal_jurnal_edit" class="form-control form-money" value="'.$nilai.'" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-12">
                    <label for="debet_kredit_edit">Debet/Kredit</label>
                    <select name="debet_kredit" id="debet_kredit_edit" class="form-control" required>
                        <option value="Debet"'.$debet_selected.'>Debet</option>
                        <option value="Kredit"'.$kredit_selected.'>Kredit</option>
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-12">
                    <label for="id_akun_perkiraan_edit">Akun Perkiraan</label>
                    <select name="id_akun_perkiraan" id="id_akun_perkiraan_edit" class="form-control" required>
                        '.$html_akun_perkiraan.'
                    </select>
                </div>
            </div>

        '
    ];
    echo json_encode($response);
    
?>

