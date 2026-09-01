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

    echo '
        <input type="hidden" name="id_referensi_sediaan" value="'.$id_referensi_sediaan.'">
        <div class="row mb-3">
            <div class="col-4"><small><i>Code</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small class="text text-grayish">'.$code.'</small></div>
        </div>
        <div class="row mb-3">
            <div class="col-4"><small><i>Display</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small class="text text-grayish">'.$display.'</small></div>
        </div>
        <div class="row mb-3">
            <div class="col-4"><small><i>System</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small class="text text-grayish">'.$system_referensi.'</small></div>
        </div>
        <div class="row mb-3">
            <div class="col-4"><small><i>Category</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small class="text text-grayish">'.$category.'</small></div>
        </div>
        <div class="row mb-3">
            <div class="col-4"><small><i>Group</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small class="text text-grayish">'.$group_name.'</small></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12 text-center">
               <div class="alert alert-warning">
                    Apakah Anda Yakin Akan Menghapus Data Referensi Sediaan Tersebut?
               </div>
            </div>
        </div>
    ';
?>
