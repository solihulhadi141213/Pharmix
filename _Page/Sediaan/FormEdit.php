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

    // Validasi id_referensi_sediaan
    if (empty($_POST['id_referensi_sediaan'])) {
        echo '
            <div class="alert alert-danger">
                <small>ID Sediaan Tidak Boleh Kosong!</small>
            </div>
        ';
        exit;
    }

    // Buat Variabel 'id_referensi_sediaan' dan sanitazi
    $id_referensi_sediaan = validateAndSanitizeInput($_POST['id_referensi_sediaan']);

    // Query Database 'referensi_sediaan'
    $Qry = $Conn->prepare("SELECT * FROM referensi_sediaan WHERE id_referensi_sediaan = ?");
    $Qry->bind_param("i", $id_referensi_sediaan);

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

    $id_referensi_sediaan = $Data['id_referensi_sediaan'];
    $code                 = $Data['code'];
    $display              = $Data['display'];
    $system_referensi     = $Data['system_referensi'];
    $category             = $Data['category'];
    $group_name           = $Data['group_name'];

    // Routing $group_name
    if($group_name=="Obat"){
        $select_obat = "selected";
        $select_alkes = "";
    }else{
        if($group_name=="Alkes"){
            $select_obat = "";
            $select_alkes = "selected";
        }else{
            $select_obat = "";
            $select_alkes = "";
        }
    }

    echo '
        <input type="hidden" name="id_referensi_sediaan" value="'.$id_referensi_sediaan.'">
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="code_edit"><i>Code</i></label>
                <input type="text" name="code" id="code_edit" class="form-control" value="'.$code.'">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="display"><i>Display</i></label>
                <input type="text" name="display" id="display" class="form-control" value="'.$display.'">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="system_edit"><i>System</i></label>
                <input type="url" name="system" id="system_edit" class="form-control" list="list_system_edit" placeholder="https://" value="'.$system_referensi.'">
                <datalist id="list_system_edit"></datalist>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="category_edit"><i>Category</i></label>
                <input type="text" name="category" id="category_edit" list="list_category_edit" class="form-control" placeholder="Ex: Aerosol" value="'.$category.'">
                <datalist id="list_category_edit"></datalist>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="group_edit"><i>Group</i></label>
                <select name="group" id="group_edit" class="form-control">
                    <option '.$select_obat.' value="Obat">Obat</option>
                    <option '.$select_alkes.' value="Alkes">Alkes</option>
                </select>
            </div>
        </div>
    ';
?>
