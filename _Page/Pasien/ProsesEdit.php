<?php

    // Koneksi dan session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    date_default_timezone_set("Asia/Jakarta");

    // Response JSON
    header('Content-Type: application/json');


    // =========================================================
    // VALIDASI SESSION
    // =========================================================

    if (empty($SessionIdAkses)) {

        echo json_encode([
            'status'  => 'error',
            'message' => 'Sesi akses sudah berakhir. Silahkan login ulang.'
        ]);

        exit;
    }


    // =========================================================
    // AMBIL DATA POST
    // =========================================================

    $id_anggota    = isset($_POST['id_anggota']) ? (int) $_POST['id_anggota'] : 0;
    $nama          = isset($_POST['nama']) ? trim($_POST['nama']) : '';
    $nik           = isset($_POST['nik']) ? trim($_POST['nik']) : '';
    $gender        = isset($_POST['gender']) ? trim($_POST['gender']) : '';
    $kontak        = isset($_POST['kontak']) ? trim($_POST['kontak']) : '';
    $email         = isset($_POST['email']) ? trim($_POST['email']) : '';
    $alamat        = isset($_POST['alamat']) ? trim($_POST['alamat']) : '';
    $tanggal_masuk = isset($_POST['tanggal_masuk']) ? trim($_POST['tanggal_masuk']) : '';


    // =========================================================
    // VALIDASI DATA
    // =========================================================

    if ($id_anggota <= 0) {

        echo json_encode([
            'status'  => 'error',
            'message' => 'ID pasien tidak valid.'
        ]);

        exit;
    }


    if (empty($nama)) {

        echo json_encode([
            'status'  => 'error',
            'message' => 'Nama pasien wajib diisi.'
        ]);

        exit;
    }


    if (empty($gender)) {

        echo json_encode([
            'status'  => 'error',
            'message' => 'Jenis kelamin wajib dipilih.'
        ]);

        exit;
    }


    if ($gender != 'Male' && $gender != 'Female') {

        echo json_encode([
            'status'  => 'error',
            'message' => 'Jenis kelamin tidak valid.'
        ]);

        exit;
    }


    if (empty($tanggal_masuk)) {

        echo json_encode([
            'status'  => 'error',
            'message' => 'Tanggal masuk wajib diisi.'
        ]);

        exit;
    }


    // =========================================================
    // VALIDASI FORMAT TANGGAL
    // =========================================================

    $date = DateTime::createFromFormat('Y-m-d', $tanggal_masuk);

    if (!$date || $date->format('Y-m-d') !== $tanggal_masuk) {

        echo json_encode([
            'status'  => 'error',
            'message' => 'Format tanggal masuk tidak valid.'
        ]);

        exit;
    }


    // =========================================================
    // CEK DATA ANGGOTA
    // =========================================================

    $stmt = mysqli_prepare(
        $Conn,
        "SELECT id_anggota
        FROM anggota
        WHERE id_anggota = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id_anggota
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 0) {

        mysqli_stmt_close($stmt);

        echo json_encode([
            'status'  => 'error',
            'message' => 'Data pasien tidak ditemukan.'
        ]);

        exit;
    }

    mysqli_stmt_close($stmt);


    // =========================================================
    // VALIDASI DUPLIKAT NIK
    // =========================================================

    if (!empty($nik)) {

        $stmt = mysqli_prepare(
            $Conn,
            "SELECT id_anggota
            FROM anggota
            WHERE nik = ?
            AND id_anggota != ?
            LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "si",
            $nik,
            $id_anggota
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {

            mysqli_stmt_close($stmt);

            echo json_encode([
                'status'  => 'error',
                'message' => 'NIK tersebut sudah digunakan oleh pasien lain.'
            ]);

            exit;
        }

        mysqli_stmt_close($stmt);
    }


    // =========================================================
    // VALIDASI DUPLIKAT KONTAK
    // =========================================================

    if (!empty($kontak)) {

        $stmt = mysqli_prepare(
            $Conn,
            "SELECT id_anggota
            FROM anggota
            WHERE kontak = ?
            AND id_anggota != ?
            LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "si",
            $kontak,
            $id_anggota
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {

            mysqli_stmt_close($stmt);

            echo json_encode([
                'status'  => 'error',
                'message' => 'Nomor kontak tersebut sudah digunakan oleh pasien lain.'
            ]);

            exit;
        }

        mysqli_stmt_close($stmt);
    }


    // =========================================================
    // PROSES UPDATE
    // =========================================================

    $stmt = mysqli_prepare(
        $Conn,
        "UPDATE anggota SET
            tanggal_masuk = ?,
            nik = ?,
            nama = ?,
            email = ?,
            kontak = ?,
            alamat = ?,
            gender = ?
        WHERE id_anggota = ?"
    );

    if (!$stmt) {

        echo json_encode([
            'status'  => 'error',
            'message' => 'Gagal mempersiapkan proses update data.'
        ]);

        exit;
    }


    mysqli_stmt_bind_param(
        $stmt,
        "sssssssi",
        $tanggal_masuk,
        $nik,
        $nama,
        $email,
        $kontak,
        $alamat,
        $gender,
        $id_anggota
    );


    // =========================================================
    // EKSEKUSI UPDATE
    // =========================================================

    if (mysqli_stmt_execute($stmt)) {

        $affected_rows = mysqli_stmt_affected_rows($stmt);

        mysqli_stmt_close($stmt);

        if ($affected_rows > 0) {

            echo json_encode([
                'status'  => 'success',
                'message' => 'Data pasien berhasil diperbarui.'
            ]);

        } else {

            echo json_encode([
                'status'  => 'success',
                'message' => 'Data pasien tidak mengalami perubahan.'
            ]);
        }

        exit;

    } else {

        mysqli_stmt_close($stmt);

        echo json_encode([
            'status'  => 'error',
            'message' => 'Gagal memperbarui data pasien.'
        ]);

        exit;
    }
?>
