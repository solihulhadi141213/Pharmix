<?php
    // KONEKSI, SESSION, GLOBAL FUNCTION
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    include "../../_Config/FungsiAkses.php";

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
    if(empty($_POST['id_pasien'])){
        echo json_encode([
            'status'  => 'error',
            'message' => 'ID Pasien Tidak Boleh Kosong.'
        ]);
        exit;
    }
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
    $id_pasien     = validateAndSanitizeInput($_POST['id_pasien']);
    $nama          = validateAndSanitizeInput($_POST['nama']);
    $gender        = validateAndSanitizeInput($_POST['gender']);
    $nik           = validateAndSanitizeInput($_POST['nik']);
    $id_ihs        = validateAndSanitizeInput($_POST['id_ihs'] ?? "");
    $kontak        = validateAndSanitizeInput($_POST['kontak']);
    $alamat        = validateAndSanitizeInput($_POST['alamat']);
    $tempat_lahir  = validateAndSanitizeInput($_POST['tempat_lahir']);
    $tanggal_lahir = validateAndSanitizeInput($_POST['tanggal_lahir']);
    $email         = validateAndSanitizeInput($_POST['email']);
   
    // Validasi Duplikat
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

    if(!empty($id_ihs)){
        $validasi_duplikat = GetDetailData($Conn, 'anggota', 'id_ihs', $id_ihs, 'id_anggota');
        if(!empty($validasi_duplikat)){
            echo json_encode([
                'status'  => 'error',
                'message' => 'IHS Tersebut Sudah Terdaftar'
            ]);
            exit;
        }
    }

    // Menentukan Tanggal
    $now = date('Y-m-d H:i:s');

    // Simpan Ke Database
    $stmt = $Conn->prepare("INSERT INTO anggota (
        id_pasien, 
        id_ihs, 
        nik, 
        nama, 
        email,
        kontak,
        alamat,
        gender,
        tempat_lahir,
        tanggal_lahir,
        creat_at,
        creat_by_id,
        creat_by_name,
        update_at,
        update_by_id,
        update_by_name
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )");
    $stmt->bind_param("sssssssssssissis", 
        $id_pasien, 
        $id_ihs, 
        $nik, 
        $nama, 
        $email, 
        $kontak,
        $alamat,
        $gender,
        $tempat_lahir,
        $tanggal_lahir,
        $now,
        $SessionIdAkses,
        $SessionNama,
        $now,
        $SessionIdAkses,
        $SessionNama
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