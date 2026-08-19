<?php
    header('Content-Type: application/json; charset=utf-8');

    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    date_default_timezone_set('Asia/Jakarta');

    function sendResponse($status, $message) {
        echo json_encode([
            'status' => $status,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse('error', 'Metode request tidak valid!');
    }

    if (empty($SessionIdAkses)) {
        sendResponse('error', 'Sesi Akses Sudah Berakhir, Silahkan Login Ulang!');
    }

    $id_supplier = trim($_POST['id_supplier'] ?? '');
    if ($id_supplier === '') {
        sendResponse('error', 'ID Supplier Tidak Boleh Kosong!');
    }

    $select = mysqli_prepare($Conn, 'SELECT nama_supplier FROM supplier WHERE id_supplier = ? LIMIT 1');
    if (!$select) {
        sendResponse('error', 'Gagal menyiapkan data supplier.');
    }

    mysqli_stmt_bind_param($select, 's', $id_supplier);
    if (!mysqli_stmt_execute($select)) {
        mysqli_stmt_close($select);
        sendResponse('error', 'Gagal mengambil data supplier.');
    }

    $result = mysqli_stmt_get_result($select);
    $supplier = mysqli_fetch_assoc($result);
    mysqli_stmt_close($select);

    if (!$supplier) {
        sendResponse('error', 'Data supplier tidak ditemukan.');
    }

    $delete = mysqli_prepare($Conn, 'DELETE FROM supplier WHERE id_supplier = ?');
    if (!$delete) {
        sendResponse('error', 'Gagal menyiapkan proses hapus supplier.');
    }

    mysqli_stmt_bind_param($delete, 's', $id_supplier);
    if (!mysqli_stmt_execute($delete)) {
        $error = mysqli_stmt_error($delete);
        mysqli_stmt_close($delete);
        sendResponse('error', 'Hapus supplier gagal: ' . $error);
    }
    mysqli_stmt_close($delete);

    $kategori_log = 'Supplier';
    $deskripsi_log = 'Hapus Supplier ' . $supplier['nama_supplier'];
    $input_log = addLog($Conn, $SessionIdAkses, date('Y-m-d H:i:s'), $kategori_log, $deskripsi_log);

    if ($input_log !== 'Success') {
        sendResponse('error', 'Terjadi kesalahan pada saat menyimpan log.');
    }

    sendResponse('success', 'Data supplier berhasil dihapus.');
?>