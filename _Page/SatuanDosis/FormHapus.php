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

    // Validasi id_referensi_satuan_dosis
    if (empty($_POST['id_referensi_satuan_dosis'])) {
        echo '
            <div class="alert alert-danger">
                <small>ID satuan Tidak Boleh Kosong!</small>
            </div>
        ';
        exit;
    }

    // Buat Variabel 'id_referensi_satuan_dosis' dan sanitazi
    $id_referensi_satuan_dosis = validateAndSanitizeInput($_POST['id_referensi_satuan_dosis']);

    // Query Database 'referensi_satuan_dosis'
    $Qry = $Conn->prepare("SELECT * FROM referensi_satuan_dosis WHERE id_referensi_satuan_dosis = ?");
    $Qry->bind_param("i", $id_referensi_satuan_dosis);

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

    $nama_satuan_dosis         = $Data['nama_satuan_dosis'];
    $unit_satuan_dosis         = $Data['unit_satuan_dosis'];
    $code_satuan_dosis         = $Data['code_satuan_dosis'];
    $system_satuan_dosis       = $Data['system_satuan_dosis'];

    echo '
        <input type="hidden" name="id_referensi_satuan_dosis" value="'.$id_referensi_satuan_dosis.'">
        <div class="row mb-3">
            <div class="col-4"><small><i>Nama Satuan</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small class="text text-grayish">'.$nama_satuan_dosis.'</small></div>
        </div>
        <div class="row mb-3">
            <div class="col-4"><small><i>Unit Satuan</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small class="text text-grayish">'.$unit_satuan_dosis.'</small></div>
        </div>
        <div class="row mb-3">
            <div class="col-4"><small><i>Code Satuan</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small class="text text-grayish">'.$code_satuan_dosis.'</small></div>
        </div>
        <div class="row mb-3">
            <div class="col-4"><small><i>System Satuan</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small class="text text-grayish">'.$system_satuan_dosis.'</small></div>
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
