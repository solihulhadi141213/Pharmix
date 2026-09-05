<?php
    // Menangkap Data
    if(empty($_POST['ingridient_kfa'])){
        echo json_encode([
            'status'  => 'error',
            'message' => 'Jenis/nama kandungan zat tidak boleh kosong!'
        ]);
        exit;
    }

    // Numerator
    if(empty($_POST['jumlah_numerator'])){
        $jumlah_numerator = "";
    }else{
        $jumlah_numerator = $_POST['jumlah_numerator'];
    }
    if(empty($_POST['satuan_numerator'])){
        $satuan_numerator = "";
        $kode_numerator   = "";
        $nama_numerator  = "";
    }else{
        $satuan_numerator = $_POST['satuan_numerator'];
        list($kode_numerator, $nama_numerator) = explode('|', $satuan_numerator);
    }

    //Denominator
    if(empty($_POST['jumlah_denominator'])){
        $jumlah_denominator = "";
    }else{
        $jumlah_denominator = $_POST['jumlah_denominator'];
    }
    if(empty($_POST['satuan_denominator'])){
        $satuan_denominator = "";
        $kode_denominator = "";
        $nama_denominator = "";
    }else{
        $satuan_denominator = $_POST['satuan_denominator'];
        list($kode_denominator, $nama_denominator) = explode('|', $satuan_denominator);
    }

    // KFA
    $ingridient_kfa = $_POST['ingridient_kfa'];
    list($kode_kfa, $nama_kfa) = explode('|', $ingridient_kfa);

    // Buat Payload
    $payload = [
        'kode_kfa'           => $kode_kfa,
        'nama_kfa'           => $nama_kfa,
        'jumlah_numerator'   => $jumlah_numerator,
        'kode_numerator'     => $kode_numerator,
        'nama_numerator'     => $nama_numerator,
        'jumlah_denominator' => $jumlah_denominator,
        'kode_denominator'   => $kode_denominator,
        'nama_denominator'   => $nama_denominator
    ];

    echo json_encode([
        'status'  => 'success',
        'payload'  => $payload,
        'message' => 'Ingridient Berhasil Ditambahkan'
    ]);
    exit;
?>