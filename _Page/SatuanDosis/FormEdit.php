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
                <small>ID Sediaan Tidak Boleh Kosong!</small>
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
                <small>Data Sediaan tidak ditemukan.</small>
            </div>
        ';
        exit;
    }

    $id_referensi_satuan_dosis = $Data['id_referensi_satuan_dosis'];
    $nama_satuan_dosis         = $Data['nama_satuan_dosis'];
    $unit_satuan_dosis         = $Data['unit_satuan_dosis'];
    $code_satuan_dosis         = $Data['code_satuan_dosis'];
    $system_satuan_dosis       = $Data['system_satuan_dosis'];

   

    echo '
        <input type="hidden" name="id_referensi_satuan_dosis" value="'.$id_referensi_satuan_dosis.'">
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="nama_satuan_dosis_edit">Nama Satuan</label>
                <input type="text" name="nama_satuan_dosis" id="nama_satuan_dosis_edit" class="form-control" value="'.$nama_satuan_dosis.'">
                <small><small>Nama satuan dalam bahasa Indonesia (Ex: Sendok Makan)</small></small>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="unit_satuan_dosis_edit">Unit Satuan</label>
                <input type="text" name="unit_satuan_dosis" id="unit_satuan_dosis_edit" class="form-control" value="'.$unit_satuan_dosis.'">
                <small><small>Unit satuan sesuai FHIR (Ex: tablespoon - untuk Sendok Makan)</small></small>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="code_satuan_dosis_edit"><i>Code Unit</i></label>
                <input type="text" name="code_satuan_dosis" id="code_satuan_dosis_edit" class="form-control" value="'.$code_satuan_dosis.'">
                <small><small>Kode satuan sesuai FHIR (Ex: tbsp - Unit Untuk Sendok Makan)</small></small>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="system_satuan_dosis_edit"><i>System</i></label>
                <input type="url" name="system_satuan_dosis" id="system_satuan_dosis_edit" class="form-control" list="list_system_edit" placeholder="https://" value="'.$system_satuan_dosis.'">
                <small><small>Sistem yang digunakan sesuai FHIR</small></small>
                <datalist id="list_system_edit"></datalist>
            </div>
        </div>
    ';
?>
