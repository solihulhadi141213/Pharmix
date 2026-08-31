<?php
    // Koneksi dan Function
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Time Zone
    date_default_timezone_set('Asia/Jakarta');
    $now = date('Y-m-d H:i:s');

    // Validasi Session Akses
    if (empty($SessionIdAkses)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Sesi Akses Sudah Berakhir, Silahkan Login Ulang!'
        ]);
        exit;
    }

    // ==========================
    // AMBIL & SANITASI INPUT
    // ==========================
    $name_connection_satu_sehat  = isset($_POST['name_connection_satu_sehat']) 
        ? trim(htmlspecialchars($_POST['name_connection_satu_sehat'])) 
        : '';

    $url_connection_satu_sehat   = isset($_POST['url_connection_satu_sehat']) 
        ? trim(htmlspecialchars($_POST['url_connection_satu_sehat'])) 
        : '';

    $organization_id              = isset($_POST['organization_id']) 
        ? trim(htmlspecialchars($_POST['organization_id'])) 
        : '';

    $client_key              = isset($_POST['client_key']) 
        ? trim(htmlspecialchars($_POST['client_key'])) 
        : '';
    
    $secret_key              = isset($_POST['secret_key']) 
        ? trim(htmlspecialchars($_POST['secret_key'])) 
        : '';

    $status_connection_satu_sehat = isset($_POST['status_connection_satu_sehat']) 
        ? (int) $_POST['status_connection_satu_sehat'] 
        : 0;

    // ==========================
    // VALIDASI INPUT
    // ==========================

    // 1. Nama koneksi
    if ($name_connection_satu_sehat == '') {
        echo json_encode([
            'status' => 'error',
            'message' => 'Nama koneksi tidak boleh kosong!'
        ]);
        exit;
    }

    if (strlen($name_connection_satu_sehat) > 200) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Nama koneksi maksimal 200 karakter!'
        ]);
        exit;
    }

    // 2. URL Satu Sehat
    if ($url_connection_satu_sehat == '') {
        echo json_encode([
            'status' => 'error',
            'message' => 'URL Satu Sehat tidak boleh kosong!'
        ]);
        exit;
    }

    if (!filter_var($url_connection_satu_sehat, FILTER_VALIDATE_URL)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Format URL Satu Sehat tidak valid!'
        ]);
        exit;
    }

    // 3. Client ID & Client Key
    if ($organization_id == '' || $client_key == '' || $secret_key == '') {
        echo json_encode([
            'status' => 'error',
            'message' => 'Organization ID, Client ID dan Client Key tidak boleh kosong!'
        ]);
        exit;
    }

    // Mulai transaksi untuk memastikan konsistensi data
    $Conn->begin_transaction();

    try {
        // ==========================
        // NONAKTIFKAN SEMUA KONEKSI LAIN JIKA STATUS = 1
        // ==========================
        if ($status_connection_satu_sehat == 1) {
            $sql_deactivate = "UPDATE connection_satu_sehat SET status_connection_satu_sehat = 0";
            $stmt_deactivate = $Conn->prepare($sql_deactivate);
            
            if (!$stmt_deactivate) {
                throw new Exception('Gagal menyiapkan query untuk menonaktifkan koneksi lain!');
            }
            
            if (!$stmt_deactivate->execute()) {
                throw new Exception('Gagal menonaktifkan koneksi lain!');
            }
            
            $stmt_deactivate->close();
        }
        
        // ==========================
        // SIMPAN DATA BARU KE DATABASE
        // ==========================
        $sql_insert = "
            INSERT INTO connection_satu_sehat 
            (
                name_connection_satu_sehat,
                url_connection_satu_sehat,
                organization_id,
                client_key,
                secret_key,
                status_connection_satu_sehat
            ) 
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        
        $stmt_insert = $Conn->prepare($sql_insert);
        
        if (!$stmt_insert) {
            throw new Exception('Gagal menyiapkan query untuk menyimpan data!');
        }
        
        $stmt_insert->bind_param(
            "sssssi",
            $name_connection_satu_sehat,
            $url_connection_satu_sehat,
            $organization_id,
            $client_key,
            $secret_key,
            $status_connection_satu_sehat
        );
        
        if (!$stmt_insert->execute()) {
            throw new Exception('Gagal menyimpan data ke database!');
        }
        
        // Commit transaksi jika semua berhasil
        $Conn->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Koneksi Satu Sehat berhasil disimpan.'
        ]);
        
        $stmt_insert->close();
        
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi error
        $Conn->rollback();
        
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
        exit;
    }
?>