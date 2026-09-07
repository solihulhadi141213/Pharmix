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
    $id_index_medication = filter_var($_POST['id_index_medication'] ?? null, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);
    if ($id_barang === false || $id_index_medication === false) {
        $respond('error', 'ID Barang dan ID Index harus berupa bilangan bulat positif.');
    }

    $Conn->begin_transaction();
    $transactionStarted = true;

    // Kunci barang agar permintaan bersamaan tidak menimpa index yang telah dipasang.
    $stmt = $Conn->prepare('SELECT id_index_medication FROM barang WHERE id_barang = ? FOR UPDATE');
    $stmt->bind_param('i', $id_barang);
    $stmt->execute();
    $barang = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$barang) {
        throw new RuntimeException('Data barang tidak ditemukan.');
    }
    if (!empty($barang['id_index_medication'])) {
        throw new RuntimeException('Barang sudah memiliki index medication.');
    }

    $stmt = $Conn->prepare('SELECT id_index_medication FROM medication WHERE id_index_medication = ? FOR UPDATE');
    $stmt->bind_param('i', $id_index_medication);
    $stmt->execute();
    $medication = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$medication) {
        throw new RuntimeException('Data medication tidak ditemukan.');
    }

    $stmt = $Conn->prepare('UPDATE barang SET id_index_medication = ? WHERE id_barang = ? AND (id_index_medication IS NULL OR id_index_medication = 0)');
    $stmt->bind_param('ii', $id_index_medication, $id_barang);
    $stmt->execute();
    $updated = $stmt->affected_rows;
    $stmt->close();
    if ($updated !== 1) {
        throw new RuntimeException('Index medication gagal disimpan. Muat ulang daftar barang.');
    }

    $now = date('Y-m-d H:i:s');
    $kategori_log = 'Barang';
    $deskripsi_log = 'Menghubungkan barang ' . $id_barang . ' dengan index medication ' . $id_index_medication;
    $stmt = $Conn->prepare('INSERT INTO log (id_akses, datetime_log, kategori_log, deskripsi_log) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $SessionIdAkses, $now, $kategori_log, $deskripsi_log);
    $stmt->execute();
    $stmt->close();

    $Conn->commit();
    $transactionStarted = false;
    $respond('success', 'Index medication berhasil disimpan.');
} catch (mysqli_sql_exception $e) {
    if ($transactionStarted) {
        $Conn->rollback();
    }
    $respond('error', 'Terjadi kesalahan database saat menyimpan index medication. Silakan coba lagi.');
} catch (RuntimeException $e) {
    if ($transactionStarted) {
        $Conn->rollback();
    }
    $respond('error', $e->getMessage());
}
