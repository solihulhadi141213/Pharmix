<?php
    header('Content-Type: application/json');

    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    try {

        // Validasi Session
        if (empty($SessionIdAkses)) {
            throw new Exception("Sesi akses tidak valid. Silahkan login kembali.");
        }

        // Ambil Data
        $url_service      = trim($_POST['url_service'] ?? '');
        $url_provider     = trim($_POST['url_provider'] ?? '');
        $email_gateway    = trim($_POST['email_gateway'] ?? '');
        $password_gateway = trim($_POST['password_gateway'] ?? '');
        $nama_pengirim    = trim($_POST['nama_pengirim'] ?? '');
        $port_gateway     = trim($_POST['port_gateway'] ?? '');

        // Validasi
        if (empty($url_service)) {
            throw new Exception("URL Service tidak boleh kosong.");
        }

        if (empty($url_provider)) {
            throw new Exception("Provider SMTP tidak boleh kosong.");
        }

        if (empty($email_gateway)) {
            throw new Exception("Akun Email tidak boleh kosong.");
        }

        if (!filter_var($email_gateway, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Format email gateway tidak valid.");
        }

        if (empty($password_gateway)) {
            throw new Exception("Password Email tidak boleh kosong.");
        }

        if (empty($nama_pengirim)) {
            throw new Exception("Nama Pengirim tidak boleh kosong.");
        }

        if (empty($port_gateway)) {
            throw new Exception("Port SMTP tidak boleh kosong.");
        }

        if (!is_numeric($port_gateway)) {
            throw new Exception("Port SMTP harus berupa angka.");
        }

        // Cek apakah setting sudah ada
        $sql = "SELECT id_setting_email_gateway 
                FROM setting_email_gateway 
                LIMIT 1";

        $stmt = mysqli_prepare($Conn, $sql);

        if (!$stmt) {
            throw new Exception(mysqli_error($Conn));
        }

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {

            // UPDATE
            $id_setting_email_gateway = $row['id_setting_email_gateway'];

            $sql_update = "UPDATE setting_email_gateway SET
                                email_gateway=?,
                                password_gateway=?,
                                url_provider=?,
                                port_gateway=?,
                                nama_pengirim=?,
                                url_service=?
                            WHERE id_setting_email_gateway=?";

            $stmt_update = mysqli_prepare($Conn, $sql_update);

            if (!$stmt_update) {
                throw new Exception(mysqli_error($Conn));
            }

            mysqli_stmt_bind_param(
                $stmt_update,
                "ssssssi",
                $email_gateway,
                $password_gateway,
                $url_provider,
                $port_gateway,
                $nama_pengirim,
                $url_service,
                $id_setting_email_gateway
            );

            if (!mysqli_stmt_execute($stmt_update)) {
                throw new Exception(mysqli_stmt_error($stmt_update));
            }

            mysqli_stmt_close($stmt_update);

        } else {

            // INSERT
            $sql_insert = "INSERT INTO setting_email_gateway (
                                email_gateway,
                                password_gateway,
                                url_provider,
                                port_gateway,
                                nama_pengirim,
                                url_service
                            ) VALUES (
                                ?, ?, ?, ?, ?, ?
                            )";

            $stmt_insert = mysqli_prepare($Conn, $sql_insert);

            if (!$stmt_insert) {
                throw new Exception(mysqli_error($Conn));
            }

            mysqli_stmt_bind_param(
                $stmt_insert,
                "ssssss",
                $email_gateway,
                $password_gateway,
                $url_provider,
                $port_gateway,
                $nama_pengirim,
                $url_service
            );

            if (!mysqli_stmt_execute($stmt_insert)) {
                throw new Exception(mysqli_stmt_error($stmt_insert));
            }

            mysqli_stmt_close($stmt_insert);
        }

        mysqli_stmt_close($stmt);

        echo json_encode([
            "status"  => "success",
            "message" => "Pengaturan Email Gateway berhasil disimpan."
        ]);

    } catch (Exception $e) {

        echo json_encode([
            "status"  => "error",
            "message" => $e->getMessage()
        ]);
    }
?>