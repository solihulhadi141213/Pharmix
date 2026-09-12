<?php

    // Menangkap Dokumen dan ID
    if(empty($_POST['document'])){
        echo '
            <div class="alert alert-danger text-center">
                <small>
                    <b>Opss!</b> <br>
                    Tipe Dokumen Tidak Boleh Kosong!
                </small>
            </div>
        ';
        exit;
    }

    if(empty($_POST['id'])){
        echo '
            <div class="alert alert-danger text-center">
                <small>
                    <b>Opss!</b> <br>
                    Parameter ID Tidak Boleh Kosong!
                </small>
            </div>
        ';
        exit;
    }

    // Buat Variabel
    $document = $_POST['document'];
    $id       = $_POST['id'];

    //Index Halaman
    $page_arry=[
        "Condition"         => __DIR__."/Condition.php",
        "MedicationRequest" => __DIR__."/MedicationRequest.php",
        "Transaksi"         => __DIR__."/RiwayatTransaksi.php",
        "Error"             => __DIR__."/../Error/Error.php"
    ];

    //Kondisi Pada masing-masing Page
    if (array_key_exists($document, $page_arry)) { 
        include $page_arry[$document]; 
    } else { 
        echo '
            <div class="alert alert-danger text-center">
                <small>
                    <b>Opss!</b> <br>
                    Dokumen/Lampiran Yang Anda Pilih Tidak Valid!
                </small>
            </div>
        ';
        exit;
    }
?>
