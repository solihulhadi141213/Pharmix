<?php
header('Content-Type: application/json; charset=utf-8');

$respond = static function (string $status, string $message): void {
    echo json_encode([
        'status' => $status,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
};

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    $respond('error', 'Permintaan harus menggunakan metode POST.');
}

$transactionStarted = false;
try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    require_once __DIR__ . '/../../_Config/Connection.php';
    require_once __DIR__ . '/../../_Config/GlobalFunction.php';
    require_once __DIR__ . '/../../_Config/Session.php';

    if (empty($SessionIdAkses)) {
        $respond('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
    }

    $id_barang = filter_var($_POST['id_barang'] ?? null, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);
    if ($id_barang === false) {
        $respond('error', 'ID Barang harus berupa bilangan bulat positif.');
    }

    $Conn->begin_transaction();
    $transactionStarted = true;
    $stmt = $Conn->prepare('SELECT id_index_medication FROM barang WHERE id_barang = ? FOR UPDATE');
    $stmt->bind_param('i', $id_barang);
    $stmt->execute();
    $barang = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$barang) {
        throw new RuntimeException('Data barang tidak ditemukan.');
    }

    // NULL adalah nilai kosong untuk kolom index yang bertipe integer.
    if ($barang['id_index_medication'] !== null) {
        $stmt = $Conn->prepare('UPDATE barang SET id_index_medication = NULL WHERE id_barang = ?');
        $stmt->bind_param('i', $id_barang);
        $stmt->execute();
        $stmt->close();

        $now = date('Y-m-d H:i:s');
        $kategori_log = 'Barang';
        $deskripsi_log = 'Menghapus koneksi index medication ' . $barang['id_index_medication'] . ' dari barang ' . $id_barang;
        $stmt = $Conn->prepare('INSERT INTO log (id_akses, datetime_log, kategori_log, deskripsi_log) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $SessionIdAkses, $now, $kategori_log, $deskripsi_log);
        $stmt->execute();
        $stmt->close();
    }

    $Conn->commit();
    $transactionStarted = false;
    $respond('success', 'Koneksi index medication berhasil dihapus.');
} catch (mysqli_sql_exception $e) {
    if ($transactionStarted) {
        $Conn->rollback();
    }
    $respond('error', 'Terjadi kesalahan database saat menghapus koneksi index. Silakan coba lagi.');
} catch (RuntimeException $e) {
    if ($transactionStarted) {
        $Conn->rollback();
    }
    $respond('error', $e->getMessage());
}
