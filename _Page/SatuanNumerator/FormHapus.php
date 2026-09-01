<?php
    // Koneksi Session Dan Function
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Zona Waktu
    date_default_timezone_set('Asia/Jakarta');

    // Validasi Sesi Akses
    if (empty($SessionIdAkses)) {
        echo '
            <div class="alert alert-danger">
                <small>Sesi Akses Sudah Berakhir. Silahkan Login Ulang!</small>
            </div>
        ';
        exit;
    }

    // Validasi id_referensi_numerator
    if (empty($_POST['id_referensi_numerator'])) {
        echo '
            <div class="alert alert-danger">
                <small>ID Satuan Tidak Boleh Kosong!</small>
            </div>
        ';
        exit;
    }

    // Buat Variabel 'id_referensi_numerator' dan sanitazi
    $id_referensi_numerator = validateAndSanitizeInput($_POST['id_referensi_numerator']);

    // Query Database 'referensi_numerator'
    $Qry = $Conn->prepare("SELECT * FROM referensi_numerator WHERE id_referensi_numerator = ?");
    $Qry->bind_param("i", $id_referensi_numerator);

    if (!$Qry->execute()) {
        echo '
            <div class="alert alert-danger">
                <small>Terjadi kesalahan saat membuka data!<br>
                Keterangan : ' . htmlspecialchars($Conn->error) . '</small>
            </div>
        ';
        exit;
    }

    $Result = $Qry->get_result();
    $Data   = $Result->fetch_assoc();
    $Qry->close();

    if (!$Data) {
        echo '
            <div class="alert alert-warning">
                <small>Data Satuan tidak ditemukan.</small>
            </div>
        ';
        exit;
    }

    $id_referensi_numerator = $Data['id_referensi_numerator'];
    $unit                   = $Data['unit'];
    $code_numerator         = $Data['code_numerator'];
    $system_numerator       = $Data['system_numerator'];

    echo '
        <input type="hidden" name="id_referensi_numerator" value="'.$id_referensi_numerator.'">
        <div class="row mb-3">
            <div class="col-4"><small><i>Unit</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small class="text text-grayish">'.$unit.'</small></div>
        </div>
        <div class="row mb-3">
            <div class="col-4"><small><i>Code</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small class="text text-grayish">'.$code_numerator.'</small></div>
        </div>
        <div class="row mb-3">
            <div class="col-4"><small><i>System</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small class="text text-grayish">'.$system_numerator.'</small></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12 text-center">
               <div class="alert alert-warning">
                    Apakah Anda Yakin Akan Menghapus Data Referensi Satuan Tersebut?
               </div>
            </div>
        </div>
    ';
?>
