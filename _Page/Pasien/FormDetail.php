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
    * HELPER TAMPIL DATA
    * ============================================================ */
    function tampil($value){
        return ($value === null || trim($value) === '')
            ? '-'
            : htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
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
    $tanggal_masuk = tampil($Data['tanggal_masuk'] ?? null);
    $nik           = tampil($Data['nik'] ?? null);
    $nama          = tampil($Data['nama'] ?? null);
    $email         = tampil($Data['email'] ?? null);
    $kontak        = tampil($Data['kontak'] ?? null);
    $alamat        = tampil($Data['alamat'] ?? null);
    $gender        = tampil($Data['gender'] ?? null);

    /* ============================================================
    * OUTPUT HTML
    * ============================================================ */
    echo '
    <div class="container-fluid">

        <div class="row mb-2">
            <div class="col-4"><small><i>Nama Lengkap</i></small></div>
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
            <div class="col-4"><small>Tanggal Daftar</small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-7"><small class="text-muted">' . $tanggal_masuk . '</small></div>
        </div>

    </div>
    ';
?>
