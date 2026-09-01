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
            <div class="col-md-12">
                <label for="nama_route_edit">Nama Route</label>
                <input type="text" name="nama_route" id="nama_route_edit" class="form-control" placeholder="" value="'.$nama_route.'">
                <small><small>Cara Obat / Alkes Digunakan (Cara konsumsi atau cara dimasukan ke dalam tubuh pasien)</small></small>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="display_route_edit"><i>Display</i></label>
                <input type="text" name="display_route" id="display_route_edit" class="form-control" placeholder="" value="'.$display_route.'">
                <small><small>Nama Route Berdasarkan FHIR</small></small>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="code_route_edit"><i>Code</i></label>
                <input type="text" name="code_route" id="code_route_edit" class="form-control" placeholder="" value="'.$code_route.'">
                <small><small>Kode Route sesuai FHIR</small></small>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="system_route_edit"><i>System</i></label>
                <input type="url" name="system_route" id="system_route_edit" class="form-control" list="list_system" placeholder="https://" value="'.$system_route.'">
                <small><small>Sistem yang digunakan sesuai FHIR</small></small>
                <datalist id="list_system"></datalist>
            </div>
        </div>
    ';
?>
