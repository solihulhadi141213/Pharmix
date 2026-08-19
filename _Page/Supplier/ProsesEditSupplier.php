<?php
    // JSON Header
    header('Content-Type: application/json; charset=utf-8');

    // Koneksi dan Session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Default Timezone
    date_default_timezone_set('Asia/Jakarta');

    // Function
    function sendResponse($status, $message, $httpCode = 200) {
        http_response_code($httpCode);
        echo json_encode([
            'status' => $status,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Validasi Metode Pengiriman Data
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse('error', 'Metode request tidak valid!');
    }

    // Validasi Sesi Akses
    if (empty($SessionIdAkses)) {
        sendResponse('error', 'Sesi Akses Sudah Berakhir, Silahkan Login Ulang');
    }

    // Tangkap Data
    $id_supplier     = trim($_POST['id_supplier'] ?? '');
    $nama_supplier   = trim($_POST['nama_supplier'] ?? '');
    $email_supplier  = trim($_POST['email_supplier'] ?? '');
    $kontak_supplier = trim($_POST['kontak_supplier'] ?? '');
    $alamat_supplier = trim($_POST['alamat_supplier'] ?? '');
    $pic             = trim($_POST['pic'] ?? '');
    $npwp            = trim($_POST['npwp'] ?? '');

    if ($id_supplier === '') {
        sendResponse('error', 'ID Supplier Tidak Boleh Kosong!');
    }

    if ($nama_supplier === '') {
        sendResponse('error', 'Nama Supplier Tidak Boleh Kosong!');
    }

    if ($kontak_supplier !== '' &&
        (strlen($kontak_supplier) < 6 || strlen($kontak_supplier) > 20 || !preg_match('/^[0-9]+$/', $kontak_supplier))) {
        sendResponse('error', 'Kontak hanya boleh terdiri dari 6-20 karakter numerik');
    }

    $stmt = mysqli_prepare(
        $Conn,
        'UPDATE supplier
        SET nama_supplier = ?, email_supplier = ?, kontak_supplier = ?, alamat_supplier = ?, pic = ?, npwp = ?
        WHERE id_supplier = ?'
    );

    if (!$stmt) {
        sendResponse('error', 'Gagal menyiapkan update data supplier.');
    }

    mysqli_stmt_bind_param(
        $stmt,
        'sssssss',
        $nama_supplier,
        $email_supplier,
        $kontak_supplier,
        $alamat_supplier,
        $pic,
        $npwp,
        $id_supplier
    );

    if (!mysqli_stmt_execute($stmt)) {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        sendResponse('error', 'Terjadi kesalahan pada saat menyimpan data: ' . $error);
    }

    mysqli_stmt_close($stmt);

    $kategori_log  = 'Supplier';
    $deskripsi_log = 'Edit Supplier ' . $nama_supplier;
    $input_log     = addLog($Conn, $SessionIdAkses, date('Y-m-d H:i:s'), $kategori_log, $deskripsi_log);

    if ($input_log !== 'Success') {
        sendResponse('error', 'Terjadi kesalahan pada saat menyimpan log.');
    }

    sendResponse('success', 'Data supplier berhasil disimpan.');
?>