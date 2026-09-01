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

    // Hitung Jumlah Data
    $jml_data        = mysqli_num_rows(mysqli_query($Conn, "SELECT id_referensi_route  FROM referensi_route "));
    $jml_data_format = "" . number_format($jml_data,0,',','.');

    echo '
        <div class="row mb-2">
            <div class="col-md-12 text-center">
                <small>Jumlah Data</small>
                <h2>'.$jml_data_format.'</h2>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-12 text-center">
                <small class="text text-grayish">
                    Semakin besar data, maka sistem membutuhkan waktu lebih lama untuk melakukan export.
                </small>
            </div>
        </div>
    ';
?>