<?php
    /**
     * ============================================================
     * DETAIL MEDICATION
     * ============================================================
     */

    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    date_default_timezone_set('Asia/Jakarta');

    function tampilDetail($value)
    {
        if ($value === null || trim((string) $value) === '') {
            return '-';
        }

        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    function tampilTanggalDetail($value)
    {
        if ($value === null || trim((string) $value) === '') {
            return '-';
        }

        $timestamp = strtotime((string) $value);

        return $timestamp === false ? '-' : date('d/m/Y H:i', $timestamp);
    }

    /* ============================================================
    * VALIDASI AKSES
    * ============================================================ */
    if (empty($SessionIdAkses)) {
        echo '
            <div class="alert alert-danger">
                <small>Sesi Akses Sudah Berakhir. Silahkan Login Ulang!</small>
            </div>
        ';
        exit;
    }

    if (empty($_POST['id_anggota'])) {
        echo '
            <div class="alert alert-danger">
                <small>ID Medication Tidak Boleh Kosong!</small>
            </div>
        ';
        exit;
    }


    /* ============================================================
    * AMBIL ID
    * ============================================================ */
    $id_anggota = validateAndSanitizeInput($_POST['id_anggota']);

    /* ============================================================
    * QUERY DATABASE
    * ============================================================ */
    $Qry = $Conn->prepare("SELECT * FROM anggota WHERE id_anggota = ?");
    $Qry->bind_param("i", $id_anggota);

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
                <small>Data pasien tidak ditemukan.</small>
            </div>
        ';
        exit;
    }

    /* ============================================================
    * MAPPING DATA
    * ============================================================ */
    $id_pasien     = tampilDetail($Data['id_pasien'] ?? null);
    $id_ihs        = tampilDetail($Data['id_ihs'] ?? null);
    $nik           = tampilDetail($Data['nik'] ?? null);
    $nama          = tampilDetail($Data['nama'] ?? null);
    $email         = tampilDetail($Data['email'] ?? null);
    $kontak        = tampilDetail($Data['kontak'] ?? null);
    $alamat        = tampilDetail($Data['alamat'] ?? null);
    $gender        = tampilDetail($Data['gender'] ?? null);
    $tempat_lahir  = tampilDetail($Data['tempat_lahir'] ?? null);
    $tanggal_lahir = tampilDetail($Data['tanggal_lahir'] ?? null);

    // Format Tanggal Lahir
    if(!empty($Data['tanggal_lahir'])){
        $tanggal_lahir_timestamp = strtotime($Data['tanggal_lahir']);
        $tanggal_lahir = $tanggal_lahir_timestamp === false
            ? '-'
            : date('d/m/Y', $tanggal_lahir_timestamp);
    }else{
        $tanggal_lahir = "-";
    }

    // Metadata
    $creat_at       = $Data['creat_at'] ?? null;
    $creat_by_id    = $Data['creat_by_id'] ?? null;
    $creat_by_name  = $Data['creat_by_name'] ?? null;
    $update_at      = $Data['update_at'] ?? null;
    $update_by_id   = $Data['update_by_id'] ?? null;
    $update_by_name = $Data['update_by_name'] ?? null;

    $creator = !empty($creat_by_id)
        ? GetDetailData($Conn, 'akses', 'id_akses', $creat_by_id, 'nama_akses')
        : $creat_by_name;
    $updater = !empty($update_by_id)
        ? GetDetailData($Conn, 'akses', 'id_akses', $update_by_id, 'nama_akses')
        : $update_by_name;

    $creator = tampilDetail($creator);
    $updater = tampilDetail($updater);

    /* 
    * ============================================================
    * OUTPUT HTML
    * ============================================================ 
    */
    echo '
        <input type="hidden" name="id_anggota" id="get_id" value="' . $id_anggota . '">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12"><small><b># Informasi Pasien</b></small></div>
            </div>
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
                <div class="col-12"><small><b># Metadata</b></small></div>
            </div>
            <div class="row mb-2">
                <div class="col-4"><small>Creat At</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-muted">' . tampilTanggalDetail($creat_at) . '</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-4"><small>Update At</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-muted">' . tampilTanggalDetail($update_at) . '</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-4"><small>Creator</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-muted">' . $creator . '</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-4"><small>Updater</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-muted">' . $updater . '</small></div>
            </div>
        </div>
    ';
?>
