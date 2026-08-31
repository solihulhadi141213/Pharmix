<?php
    date_default_timezone_set('Asia/Jakarta');

    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');

    function responseError(string $message): void
    {
        echo json_encode([
            'status'  => 'error',
            'message' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (empty($SessionIdAkses)) {
        responseError('Sesi akses sudah berakhir. Silakan login ulang.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseError('Metode request tidak valid.');
    }

    $id_jurnal = trim($_POST['id_jurnal'] ?? '');
    if ($id_jurnal === '') {
        responseError('ID jurnal tidak boleh kosong.');
    }

    if (!ctype_digit($id_jurnal) || (int) $id_jurnal < 1) {
        responseError('ID jurnal tidak valid.');
    }
    $id_jurnal = (int) $id_jurnal;

    // Ambil referensi pembayaran dan pastikan jurnal berasal dari kategori Pembayaran.
    $stmt_jurnal = $Conn->prepare(
        'SELECT id_transaksi_pembayaran, kategori FROM jurnal
         WHERE id_jurnal = ? LIMIT 1'
    );
    if (!$stmt_jurnal) {
        responseError('Gagal mempersiapkan validasi jurnal.');
    }
    $stmt_jurnal->bind_param('i', $id_jurnal);
    if (!$stmt_jurnal->execute()) {
        $stmt_jurnal->close();
        responseError('Gagal mengambil data jurnal.');
    }
    $result_jurnal = $stmt_jurnal->get_result();
    $data_jurnal = $result_jurnal ? $result_jurnal->fetch_assoc() : null;
    $stmt_jurnal->close();

    if (!$data_jurnal) {
        responseError('Data jurnal tidak ditemukan.');
    }

    $id_transaksi_pembayaran = trim($data_jurnal['id_transaksi_pembayaran'] ?? '');
    if ($data_jurnal['kategori'] !== 'Pembayaran' || $id_transaksi_pembayaran === '') {
        responseError('Jurnal yang dipilih bukan jurnal pembayaran.');
    }

    $Conn->begin_transaction();

    try {
        $stmt_delete = $Conn->prepare(
            "DELETE FROM jurnal
             WHERE id_jurnal = ?
               AND id_transaksi_pembayaran = ?
               AND kategori = 'Pembayaran'"
        );
        if (!$stmt_delete) {
            throw new Exception('Gagal mempersiapkan penghapusan jurnal.');
        }

        $stmt_delete->bind_param('is', $id_jurnal, $id_transaksi_pembayaran);
        if (!$stmt_delete->execute()) {
            $error = $stmt_delete->error;
            $stmt_delete->close();
            throw new Exception('Gagal menghapus jurnal pembayaran: ' . $error);
        }

        $affected_rows = $stmt_delete->affected_rows;
        $stmt_delete->close();

        if ($affected_rows !== 1) {
            throw new Exception('Data jurnal tidak berhasil dihapus.');
        }

        $Conn->commit();
    } catch (Throwable $error) {
        $Conn->rollback();
        responseError($error->getMessage());
    }

    echo json_encode([
        'status'                  => 'success',
        'message'                 => 'Jurnal pembayaran berhasil dihapus.',
        'id_transaksi_pembayaran' => $id_transaksi_pembayaran
    ], JSON_UNESCAPED_UNICODE);
?>
