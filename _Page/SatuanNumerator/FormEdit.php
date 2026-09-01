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

    // Query Database 'referensi_sediaan'
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
                <small>Data Sediaan tidak ditemukan.</small>
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
            <div class="col-md-12">
                <label for="unit_edit"><i>Unit</i></label>
                <input type="text" name="unit" id="unit_edit" class="form-control" placeholder="Ex: gram, liter, dll" value="'.$unit.'">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="code_numerator_edit"><i>Code</i></label>
                <input type="text" name="code_numerator" id="code_numerator_edit" class="form-control" placeholder="Ex: g, ml" value="'.$code_numerator.'">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="system_numerator_edit"><i>System</i></label>
                <input type="url" name="system_numerator" id="system_numerator_edit" class="form-control" list="list_system_edit" placeholder="https://" value="'.$system_numerator.'">
                <datalist id="list_system_edit"></datalist>
            </div>
        </div>
    ';
?>
