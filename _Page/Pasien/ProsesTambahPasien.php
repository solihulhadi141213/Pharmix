<?php
    // KONEKSI, SESSION, GLOBAL FUNCTION
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    date_default_timezone_set("Asia/Jakarta");

    // RESPONSE HEADER
    header('Content-Type: application/json');

    // inisiasi Function
    function getPost($key, $default = ''){
        if (!isset($_POST[$key])) {
            return $default;
        }

        $value = $_POST[$key];

        if (is_array($value)) {
            return array_map('trim', $value);
        }

        $value = trim($value);
        $value = str_replace(["\r", "\n"], ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return $value;
    }

    // VALIDASI SESSION
    if (empty($SessionIdAkses)) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Sesi akses telah berakhir. Silakan login ulang.'
        ]);
        exit;
    }

    // DATA WAJIB
    if(empty($_POST['nama'])){
        echo json_encode([
            'status'  => 'error',
            'message' => 'Nama Pasien Tidak Boleh Kosong.'
        ]);
        exit;
    }

    if(empty($_POST['gender'])){
        echo json_encode([
            'status'  => 'error',
            'message' => 'Gender Pasien Tidak Boleh Kosong.'
        ]);
        exit;
    }

    // BUAT VARIABELNYA
    $nama   = validateAndSanitizeInput($_POST['nama']);
    $gender = validateAndSanitizeInput($_POST['gender']);
    $nik    = validateAndSanitizeInput($_POST['nik']);
    $kontak = validateAndSanitizeInput($_POST['kontak']);
    $email  = validateAndSanitizeInput($_POST['email']);
   
    // Jika NIK Diisi Validasi Duplikat
    if(!empty($nik)){
        $validasi_duplikat = GetDetailData($Conn, 'anggota', 'nik', $nik, 'id_anggota');
        if(!empty($validasi_duplikat)){
            echo json_encode([
                'status'  => 'error',
                'message' => 'NIK Tersebut Sudah Terdaftar'
            ]);
            exit;
        }
    }

    // Menentukan Tanggal
    $tanggal_masuk = date('Y-m-d');

    // Simpan Ke Database
    $stmt = $Conn->prepare("INSERT INTO anggota (
        tanggal_masuk, 
        nik, 
        nama, 
        email,
        kontak,
        alamat,
        gender
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?
    )");
    $stmt->bind_param("sssssss", 
        $tanggal_masuk, 
        $nik, 
        $nama, 
        $email, 
        $kontak,
        $alamat,
        $gender
    );
    $Input = $stmt->execute();
    $stmt->close();

    // ======================================================
    // RESPONSE SUKSES
    // ======================================================
    echo json_encode([
        'status'         => 'success',
        'message'        => 'Berhasil menambah pasien baru'
    ]);
    exit;
?>