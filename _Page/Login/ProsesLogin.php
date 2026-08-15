<?php
    session_start();
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    
    // Set header agar selalu mengembalikan JSON
    header('Content-Type: application/json');

    // Tambahkan beberapa header keamanan
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');

    // Tetapkan zona waktu
    date_default_timezone_set('Asia/Jakarta');

    // Timestamp sekarang
    $timestamp_now = date('Y-m-d H:i:s');

    // Atur waktu login
    $expired_seconds = 60 * 60; // 1 hour
    $date_expired = date('Y-m-d H:i:s', strtotime($timestamp_now) + $expired_seconds);

    // Fungsi untuk membuat token
    function generateTokenNew($length = 36) {
        return bin2hex(random_bytes($length / 2));
    }

    // Fungsi untuk memvalidasi input
    function validateAndSanitizeInputNew($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    // Inisialisasi respon default
    $response = [
        'status' => 'error',
        'message' => 'Terjadi kesalahan.'
    ];

    // Validasi input Tidak Boleh Kosong
    $email    = isset($_POST["email"]) ? filter_var(validateAndSanitizeInputNew($_POST["email"]), FILTER_VALIDATE_EMAIL) : null;
    $password = isset($_POST["password"]) ? validateAndSanitizeInputNew($_POST["password"]) : null;
    $captcha  = isset($_POST["captcha"]) ? validateAndSanitizeInputNew($_POST["captcha"]) : null;

    if (!$email) {
        $response['message'] = 'Email tidak valid atau kosong.';
    } elseif (empty($password)) {
        $response['message'] = 'Password tidak boleh kosong.';
    } elseif (empty($captcha)) {
        $response['message'] = 'Captcha tidak boleh kosong.';
    } else {
        
        // Validasi Captcha
        $QryCaptcha = $Conn->prepare("SELECT * FROM captcha  WHERE unique_code  = ?");
        $QryCaptcha->bind_param("s", $captcha);
        $QryCaptcha->execute();
        $DataCaptcha = $QryCaptcha->get_result()->fetch_assoc();

        if (!$DataCaptcha) {
            $response['message'] = 'Captcha tidak valid.';
        } elseif ($DataCaptcha['timestamp_expired'] < $timestamp_now) {
            $response['message'] = 'Captcha expired.';
        } else {

            // Validasi Email
            $status_akses = 1;
            $stmt = $Conn->prepare("SELECT * FROM akses  WHERE email_akses = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $DataAkses = $stmt->get_result()->fetch_assoc();

            //Validasi Password
            if ($DataAkses && password_verify($password, $DataAkses['password'])) {
                $id_akses = $DataAkses["id_akses"];

                // Hapus token lama
                $deleteTokenStmt = $Conn->prepare("DELETE FROM akses_login WHERE id_akses = ?");
                $deleteTokenStmt->bind_param("i", $id_akses);
                $deleteTokenStmt->execute();

                // Buat token baru
                $token = GenerateToken(36);
                $insertTokenStmt = $Conn->prepare("INSERT INTO akses_login (id_akses, token, date_creat, date_expired) VALUES (?, ?, ?, ?)");
                $insertTokenStmt->bind_param("isss", $id_akses, $token, $timestamp_now, $date_expired);

                if ($insertTokenStmt->execute()) {

                    //Simpan Log
                    date_default_timezone_set('Asia/Jakarta');
                    $now           = date('Y-m-d H:i:s');
                    $kategori_log  = "Login";
                    $deskripsi_log = "Login Berhasil";
                    $InputLog      = addLog($Conn,$id_akses,$now,$kategori_log,$deskripsi_log);
                    if($InputLog=="Success"){
                        $_SESSION["id_akses"]       = $id_akses;
                        $_SESSION["login_token"]    = $token;
                        $_SESSION["NotifikasiSwal"] = "Login Berhasil";
                        $response['status']         = 'success';
                        $response['message']        = 'Login berhasil.';

                        //Hapus Captcha Lama
                        $deleteExpiredCaptchas = $Conn->prepare("DELETE FROM captcha WHERE unique_code = ?");
                        $deleteExpiredCaptchas->bind_param("s",$captcha);
                        $deleteExpiredCaptchas->execute();
                    }else{
                        $response['message'] = 'Terjadi kesalahan pada saat menyimpan log.';
                    }
                } else {
                    $response['message'] = 'Gagal membuat sesi login.';
                }
            } else {
                $response['message'] = 'Kombinasi email dan password tidak valid.';
            }
        }
    }

    // Output respon sebagai JSON
    echo json_encode($response);
?>
