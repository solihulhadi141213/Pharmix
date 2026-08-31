<?php
    //koneksi dan session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    
    //Zona Waktu
    date_default_timezone_set("Asia/Jakarta");

    //Session Akses
    if(empty($SessionIdAkses)){
        echo '
            <div class="row mb-3">
                <div class="col-12 text-center">
                    <div class="alert alert-danger"><small>Sesi Akses Sudah Berakhir! Silahkan Login Ulang.</small></div>
                </div>
            </div>
        ';
        exit;
    }

    //id_connection_satu_sehat wajib terisi
    if(empty($_POST['id_connection_satu_sehat'])){
        echo '
            <div class="row mb-3">
                <div class="col-12 text-center">
                    <div class="alert alert-danger"><small>Koneksi Satu Sehat Tiidak Boleh Kosong!</small></div>
                </div>
            </div>
        ';
        exit;
    }

    //Buat variabel 'id_connection_satu_sehat' dan sanitasi
    $id_connection_satu_sehat      = validateAndSanitizeInput($_POST['id_connection_satu_sehat']);

    //Buka Detail Koneksi Dengan Prepared Statment
    $Qry = $Conn->prepare("SELECT * FROM connection_satu_sehat WHERE id_connection_satu_sehat = ?");
    $Qry->bind_param("i", $id_connection_satu_sehat);
    if (!$Qry->execute()) {
        $error=$Conn->error;
        echo '
            <div class="alert alert-danger">
                <small>Terjadi kesalahan pada saat membuka data dari database!<br>Keterangan : '.$error.'</small>
            </div>
        ';
    }else{
        $Result = $Qry->get_result();
        $Data = $Result->fetch_assoc();
        $Qry->close();

        //Buat Variabel
        $id_connection_satu_sehat     = $Data['id_connection_satu_sehat'];
        $name_connection_satu_sehat   = $Data['name_connection_satu_sehat'];
        $url_connection_satu_sehat    = $Data['url_connection_satu_sehat'];
        $organization_id              = $Data['organization_id'];
        $client_key                   = $Data['client_key'];
        $secret_key                   = $Data['secret_key'];
        $status_connection_satu_sehat = $Data['status_connection_satu_sehat'];

        //Routing Status
        if(empty($status_connection_satu_sehat)){
            $label_status1 = 'selected';
            $label_status2 = '';
        }else{
            $label_status1 = '';
            $label_status2 = 'selected';
        }

        // Tampilkan Form Edit
        if(empty($Data['id_connection_satu_sehat'])){
            echo '
                <div class="alert alert-danger">
                    <small>Data Tidak Ditemukan</small>
                </div>
            '; 
        }else{
            echo '
                <input type="hidden" name="id_connection_satu_sehat" value="'.$id_connection_satu_sehat.'">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="name_connection_satu_sehat_edit">
                            <small>Nama Koneksi</small>
                        </label>
                        <input type="text" class="form-control" name="name_connection_satu_sehat" id="name_connection_satu_sehat_edit" value="'.$name_connection_satu_sehat.'" required>
                        <small>
                            <small class="text text-muted">
                                Example : Development, Staging, Production  dll.
                            </small>
                        </small>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="url_connection_satu_sehat_edit">
                            <small>URL Satu Sehat</small>
                        </label>
                        <input type="url" class="form-control" name="url_connection_satu_sehat" id="url_connection_satu_sehat_edit" placeholder="https://" value="'.$url_connection_satu_sehat.'" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="organization_id_edit">
                            <small>Organization ID</small>
                        </label>
                        <input type="text" class="form-control" name="organization_id" id="organization_id_edit" value="'.$organization_id.'" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="client_key_edit">
                            <small>Client Key</small>
                        </label>
                        <input type="text" class="form-control" name="client_key" id="client_key_edit" value="'.$client_key.'" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="secret_key_edit">
                            <small>Secret Key</small>
                        </label>
                        <input type="text" class="form-control" name="secret_key" id="secret_key_edit" value="'.$secret_key.'" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="status_connection_satu_sehat_edit">
                            <small>Status Connection</small>
                        </label>
                        <select name="status_connection_satu_sehat" id="status_connection_satu_sehat_edit" class="form-control">
                            <option '.$label_status1.' value="0">Inactive</option>
                            <option '.$label_status2.' value="1">Active</option>
                        </select>
                    </div>
                </div>
            ';

        }
    }
?>