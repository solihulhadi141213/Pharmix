<?php

    // Koneksi dan session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    date_default_timezone_set("Asia/Jakarta");
    // Helper
     function tampil($value){
        return ($value === null || trim($value) === '')
            ? '-'
            : htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    // Validasi session
    if (empty($SessionIdAkses)) {
        echo '
            <div class="alert alert-danger">
                <small>Sesi akses sudah berakhir. Silahkan login ulang.</small>
            </div>
        ';
        exit;
    }

    // Validasi ID
    if (empty($_POST['id_anggota'])) {
        echo '
            <div class="alert alert-danger">
                <small>ID pasien tidak ditemukan.</small>
            </div>
        ';
        exit;
    }

    $id_anggota = (int) $_POST['id_anggota'];

    // Ambil data anggota
    $stmt = mysqli_prepare(
        $Conn,
        "SELECT * FROM anggota WHERE id_anggota = ?"
    );
    mysqli_stmt_bind_param($stmt, "i", $id_anggota);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // Jika data tidak ditemukan
    if (!$data) {
        echo '
            <div class="alert alert-danger">
                <small>Data pasien tidak ditemukan.</small>
            </div>
        ';
        exit;
    }

    /* ============================================================
    * MAPPING DATA
    * ============================================================ */
    $id_pasien     = tampil($data['id_pasien'] ?? null);
    $id_ihs        = tampil($data['id_ihs'] ?? null);
    $nik           = tampil($data['nik'] ?? null);
    $nama          = tampil($data['nama'] ?? null);
    $email         = tampil($data['email'] ?? null);
    $kontak        = tampil($data['kontak'] ?? null);
    $alamat        = tampil($data['alamat'] ?? null);
    $gender        = tampil($data['gender'] ?? null);
    $tempat_lahir  = tampil($data['tempat_lahir'] ?? null);
    $tanggal_lahir = tampil($data['tanggal_lahir'] ?? null);

    // Format tanggl lahir
    if(!empty($tanggal_lahir)){
        $tanggal_lahir = date('d/m/Y', strtotime($tanggal_lahir));
    }

    // Metadata
    $creat_at       = tampil($data['creat_at'] ?? null);
    $creat_by_id    = tampil($data['creat_by_id'] ?? null);
    $creat_by_name  = tampil($data['creat_by_name'] ?? null);
    $update_at      = tampil($data['update_at'] ?? null);
    $update_by_id   = tampil($data['update_by_id'] ?? null);
    $update_by_name = tampil($data['update_by_name'] ?? null);

    if(empty($creat_by_id)){
        $creator = GetDetailData($Conn, 'akses', 'id_akses', $creat_by_id, 'nama_akses');
    }else{
        $creator = $creat_by_name;
    }

    if(empty($update_by_id)){
        $updater = GetDetailData($Conn, 'akses', 'id_akses', $update_by_id, 'nama_akses');
    }else{
        $updater = $creat_by_name;
    }
    echo '
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-4"><small>No.RM</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-muted">' . $id_pasien . '</small></div>
            </div>

            <div class="row mb-2">
                <div class="col-4"><small>Nama Lengkap</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-muted">' . $nama . '</small></div>
            </div>

            <div class="row mb-2">
                <div class="col-4"><small>Gender</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-muted">' . $gender . '</small></div>
            </div>

            <div class="row mb-2">
                <div class="col-4"><small>NIK/KTP</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-muted">' . $nik . '</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-4"><small>ID IHS</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-muted">' . $id_ihs . '</small></div>
            </div>

            <div class="row mb-2">
                <div class="col-4"><small>Email</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-muted">' . $email . '</small></div>
            </div>

            <div class="row mb-2">
                <div class="col-4"><small>Kontak</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-muted">' . $kontak . '</small></div>
            </div>

            <div class="row mb-2">
                <div class="col-4"><small>Alamat</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-muted">' . $alamat . '</small></div>
            </div>

            <div class="row mb-2">
                <div class="col-4"><small>Tempat Lahir</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-muted">' . $tempat_lahir . '</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-4"><small>Tanggal Lahir</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-muted">' . $tanggal_lahir . '</small></div>
            </div>
            <div class="row mb-2 mt-4">
                <div class="col-12">
                    <div class="alert alert-danger text-center">
                        <small>
                            <b>PENTING!</b><br>
                            Data Yang Sudah Dihapus Tidak Dapat Dikembalikan Lagi. <br>
                            <i>Apakah Anda Yakin Akan Menghapus Data Tersebut?</i>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    ';
?>

<input type="hidden" name="id_anggota" value="<?php echo $id_anggota; ?>">
