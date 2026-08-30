<?php
include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";
include "../../_Config/FungsiAkses.php";

date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json; charset=utf-8');

function penjualanEditResponse($status, $message, $id = '', $mode = '')
{
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'id_transaksi_jual_beli' => $id,
        'mode' => $mode
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($SessionIdAkses)) {
    penjualanEditResponse('Error', 'Sesi Akses Sudah Berakhir, Silahkan Login Ulang');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    penjualanEditResponse('Error', 'Metode request tidak valid.');
}

$idTransaksi = trim((string) ($_POST['id_transaksi_jual_beli'] ?? ''));
$mode = trim((string) ($_POST['mode'] ?? ''));
$tanggal = trim((string) ($_POST['tanggal'] ?? ''));
$jam = trim((string) ($_POST['jam'] ?? ''));
$cashInput = preg_replace('/[^0-9]/', '', (string) ($_POST['cash'] ?? ''));
$cashInput = $cashInput === '' ? 0 : (int) $cashInput;

if ($idTransaksi === '') penjualanEditResponse('Error', 'ID Transaksi Tidak Boleh Kosong!');
if ($tanggal === '' || $jam === '') penjualanEditResponse('Error', 'Tanggal dan jam transaksi wajib diisi.');
$dateObject = DateTime::createFromFormat('Y-m-d H:i', "$tanggal $jam")
    ?: DateTime::createFromFormat('Y-m-d H:i:s', "$tanggal $jam");
if (!$dateObject) penjualanEditResponse('Error', 'Format tanggal atau jam tidak valid.');
$tanggalTransaksi = $dateObject->format('Y-m-d H:i:s');
$tanggalJurnal = $dateObject->format('Y-m-d');

mysqli_begin_transaction($Conn);
try {
    $stmt = mysqli_prepare($Conn, "SELECT kategori, total FROM transaksi_jual_beli WHERE id_transaksi_jual_beli = ? LIMIT 1");
    if (!$stmt) throw new Exception('Gagal menyiapkan query transaksi.');
    mysqli_stmt_bind_param($stmt, 's', $idTransaksi);
    mysqli_stmt_execute($stmt);
    $data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$data) throw new Exception('Transaksi tidak ditemukan.');
    $kategori = $data['kategori'];
    if (!in_array($kategori, ['Penjualan', 'Retur Penjualan'], true)) {
        throw new Exception('Kategori transaksi tidak valid.');
    }
    $total = (int) round((float) $data['total']);
    if ($total <= 0) throw new Exception('Total transaksi tidak valid.');

    // Pembayaran efektif dibatasi sebesar tagihan, sisanya menjadi kembalian.
    $pembayaran = min(max(0, $cashInput), $total);
    $kembalian = max(0, $cashInput - $total);
    if ($pembayaran === $total) {
        $status = 'Lunas';
    } elseif ($kategori === 'Penjualan') {
        $status = 'Piutang';
    } else {
        $status = 'Utang';
    }

    $now = date('Y-m-d H:i:s');
    $stmt = mysqli_prepare($Conn, "
        UPDATE transaksi_jual_beli
        SET tanggal = ?, cash = ?, kembalian = ?, status = ?,
            update_by_id = ?, update_by_name = ?, update_at = ?
        WHERE id_transaksi_jual_beli = ?
        LIMIT 1
    ");
    if (!$stmt) throw new Exception('Gagal menyiapkan update transaksi.');
    mysqli_stmt_bind_param($stmt, 'siisisss', $tanggalTransaksi, $pembayaran, $kembalian, $status, $SessionIdAkses, $SessionNama, $now, $idTransaksi);
    if (!mysqli_stmt_execute($stmt)) {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        throw new Exception('Gagal memperbarui transaksi: ' . $error);
    }
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($Conn, "DELETE FROM jurnal WHERE id_transaksi_jual_beli = ?");
    if (!$stmt) throw new Exception('Gagal menyiapkan penghapusan jurnal lama.');
    mysqli_stmt_bind_param($stmt, 's', $idTransaksi);
    if (!mysqli_stmt_execute($stmt)) {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        throw new Exception('Gagal menghapus jurnal lama: ' . $error);
    }
    mysqli_stmt_close($stmt);

    $autoJurnal = AutoJurnalJualBeli($Conn, $kategori, $tanggalJurnal, $idTransaksi, $total, $pembayaran, $status);
    if ($autoJurnal !== 'Success') throw new Exception($autoJurnal);

    if (addLog($Conn, $SessionIdAkses, $now, 'Transaksi Penjualan', 'Edit Transaksi Penjualan') !== 'Success') {
        throw new Exception('Terjadi kesalahan pada saat menyimpan log aktivitas.');
    }

    mysqli_commit($Conn);
    penjualanEditResponse('Success', 'Edit Transaksi Berhasil!', $idTransaksi, $mode);
} catch (Throwable $e) {
    mysqli_rollback($Conn);
    penjualanEditResponse('Error', $e->getMessage(), $idTransaksi, $mode);
}
