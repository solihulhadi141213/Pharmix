<?php

    // --------------------------------------------------------------
    // CONNECTION, HELPER & SESSION
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    date_default_timezone_set('Asia/Jakarta');

    // --------------------------------------------------------------
    // VALIDASI AKSES
    if (empty($SessionIdAkses)) {
        echo '
            <div class="alert alert-danger">
                <small>Sesi Akses Sudah Berakhir. Silahkan Login Ulang!</small>
            </div>
        ';
        exit;
    }

    if (empty($_POST['id'])) {
        echo '
            <div class="alert alert-danger">
                <small>ID Medication Tidak Boleh Kosong!</small>
            </div>
        ';
        exit;
    }

    // --------------------------------------------------------------
    // HELPER UNTUK MENAMPILKAN DATA
    function tampil($value){
        return ($value === null || trim($value) === '')? '-': htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /* ============================================================
    * AMBIL ID
    * ============================================================ */
    $id_index_medication = validateAndSanitizeInput($_POST['id']);

    /* ============================================================
    * QUERY DATABASE
    * ============================================================ */
    $Qry = $Conn->prepare("SELECT * FROM medication WHERE id_index_medication = ?");
    $Qry->bind_param("i", $id_index_medication);

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
                <small>Data medication tidak ditemukan.</small>
            </div>
        ';
        exit;
    }

    /* ============================================================
    * MAPPING DATA
    * ============================================================ */
    $id_medication       = tampil($Data['id_medication'] ?? null);
    $medication_code     = tampil($Data['medication_code'] ?? null);
    $medication_name     = tampil($Data['medication_name'] ?? null);
    $medication_category = tampil($Data['medication_category'] ?? null);
    $kfa_code            = tampil($Data['kfa_code'] ?? null);
    $kfa_display         = tampil($Data['kfa_display'] ?? null);
    $sediaan_code        = tampil($Data['sediaan_code'] ?? null);
    $sediaan_display     = tampil($Data['sediaan_display'] ?? null);
    $racikan_code        = tampil($Data['racikan_code'] ?? null);
    $racikan_display     = tampil($Data['racikan_display'] ?? null);
    $manufacturer_id     = tampil($Data['manufacturer_id'] ?? null);
    $manufacturer_name   = tampil($Data['manufacturer_name'] ?? null);
    $ingredient          = tampil($Data['ingredient'] ?? null);

    /* ============================================================
    * FORMAT RACIKAN
    * ============================================================ */
    $racikan = ($racikan_code === '-' && $racikan_display === '-')
        ? '-'
        : $racikan_code . ' - ' . $racikan_display;

    /* ============================================================
    * OUTPUT HTML
    * ============================================================ */
    echo '
        <div class="container-fluid">

            <div class="row mb-2">
                <div class="col-12">
                    <small><b>A. Informasi Umum</b></small>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-4"><small><i>ID Medication</i></small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-muted">' . $id_medication . '</small></div>
            </div>

            <div class="row mb-2">
                <div class="col-4"><small>Kode Lokal</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-muted">' . $medication_code . '</small></div>
            </div>

            <div class="row mb-2">
                <div class="col-4"><small>Nama Obat / Alkes</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-muted">' . $medication_name . '</small></div>
            </div>

            <div class="row mb-2">
                <div class="col-4"><small>Kategori</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-muted">' . $medication_category . '</small></div>
            </div>

            <div class="row mb-2">
                <div class="col-4"><small>Kategori Racikan</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-muted">' . $racikan . '</small></div>
            </div>

            <div class="row mb-2 mt-3">
                <div class="col-12">
                    <small><b>B. Kamus Farmasi & Alat Kesehatan (KFA)</b></small>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-4"><small>Kode KFA</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-muted">' . $kfa_code . '</small></div>
            </div>

            <div class="row mb-2">
                <div class="col-4"><small>Nama KFA</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-muted">' . $kfa_display . '</small></div>
            </div>

            <div class="row mb-2 mt-3">
                <div class="col-12">
                    <small><b>C. Informasi Sediaan</b></small>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-4"><small>Kode Sediaan</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-muted">' . $sediaan_code . '</small></div>
            </div>

            <div class="row mb-2">
                <div class="col-4"><small>Nama Sediaan</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-muted">' . $sediaan_display . '</small></div>
            </div>

            <div class="row mb-2 mt-3">
                <div class="col-12">
                    <small><b>D. Manufaktur</b></small>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-4"><small>ID Manufaktur</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-muted">' . $manufacturer_id . '</small></div>
            </div>

            <div class="row mb-2">
                <div class="col-4"><small>Nama Manufaktur</small></div>
                <div class="col-1"><small>:</small></div>
                <div class="col-7"><small class="text-muted">' . $manufacturer_name . '</small></div>
            </div>

        </div>
    ';
?>
