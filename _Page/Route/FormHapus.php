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

    // Validasi id_referensi_route 
    if (empty($_POST['id_referensi_route'])) {
        echo '
            <div class="alert alert-danger">
                <small>ID Sediaan Tidak Boleh Kosong!</small>
            </div>
        ';
        exit;
    }

    // Buat Variabel 'id_referensi_route' dan sanitazi
    $id_referensi_route = validateAndSanitizeInput($_POST['id_referensi_route']);

    // Query Database 'referensi_route'
    $Qry = $Conn->prepare("SELECT * FROM referensi_route WHERE id_referensi_route = ?");
    $Qry->bind_param("i", $id_referensi_route);

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

    $id_referensi_route = $Data['id_referensi_route'];
    $nama_route         = $Data['nama_route'];
    $display_route      = $Data['display_route'];
    $code_route         = $Data['code_route'];
    $system_route       = $Data['system_route'];

   

    echo '
        <input type="hidden" name="id_referensi_route" value="'.$id_referensi_route.'">
        <div class="row mb-3">
            <div class="col-4"><small><i>Nama Route</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small class="text text-grayish">'.$nama_route.'</small></div>
        </div>
        <div class="row mb-3">
            <div class="col-4"><small><i>Display</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small class="text text-grayish">'.$display_route.'</small></div>
        </div>
        <div class="row mb-3">
            <div class="col-4"><small><i>Code</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small class="text text-grayish">'.$code_route.'</small></div>
        </div>
        <div class="row mb-3">
            <div class="col-4"><small><i>System</i></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small class="text text-grayish">'.$system_route.'</small></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12 text-center">
            <div class="alert alert-warning">
                    Apakah Anda Yakin Akan Menghapus Data Referensi Route Tersebut?
            </div>
            </div>
        </div>
    ';
?>
