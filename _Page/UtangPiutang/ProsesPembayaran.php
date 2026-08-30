<?php
include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";

date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json; charset=utf-8');

function paymentResponse($status, $message, $id = null, $kategori = null)
{
    $response = ['status' => $status, 'message' => $message];
    if ($id !== null) {
        $response['id'] = $id;
        $response['kategori'] = $kategori;
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

function cleanPaymentAmount($value)
{
    $value = preg_replace('/[^0-9]/', '', (string) $value);
    return $value === '' ? 0 : (int) $value;
}

function insertPaymentJournal($Conn, $kategori, $idJualBeli, $idTransaksi, $idPembayaran, $tanggal, $idAkun, $d_k, $nilai)
{
    if (empty($idAkun)) {
        throw new Exception('Pengaturan akun jurnal belum lengkap.');
    }

    $stmtAkun = $Conn->prepare('SELECT kode, nama FROM akun_perkiraan WHERE id_perkiraan = ? LIMIT 1');
    if (!$stmtAkun) {
        throw new Exception('Gagal mempersiapkan akun jurnal.');
    }
    $stmtAkun->bind_param('i', $idAkun);
    $stmtAkun->execute();
    $akun = $stmtAkun->get_result()->fetch_assoc();
    $stmtAkun->close();

    if (!$akun) {
        throw new Exception('Akun jurnal tidak ditemukan.');
    }

    $stmt = $Conn->prepare(
        'INSERT INTO jurnal
        (kategori, uuid, id_transaksi, id_transaksi_jual_beli, id_transaksi_pembayaran,
         tanggal, kode_perkiraan, nama_perkiraan, d_k, nilai)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    if (!$stmt) {
        throw new Exception('Gagal mempersiapkan penyimpanan jurnal.');
    }

    $uuid = $idPembayaran;
    $stmt->bind_param(
        'sssssssssi',
        $kategori,
        $uuid,
        $idTransaksi,
        $idJualBeli,
        $idPembayaran,
        $tanggal,
        $akun['kode'],
        $akun['nama'],
        $d_k,
        $nilai
    );

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new Exception('Gagal menyimpan jurnal: ' . $error);
    }
    $stmt->close();
}

if (empty($SessionIdAkses)) {
    paymentResponse('error', 'Sesi akses sudah berakhir! Silahkan login ulang.');
}

$id = trim((string) ($_POST['id'] ?? ''));
$kategori = strtolower(trim((string) ($_POST['kategori'] ?? '')));
$tanggal = trim((string) ($_POST['tanggal_pembayaran'] ?? ''));
$jam = trim((string) ($_POST['jam_pembayaran'] ?? ''));
$jumlah = cleanPaymentAmount($_POST['nominal_pembayaran'] ?? '');

if ($id === '' || $tanggal === '' || $jam === '') {
    paymentResponse('error', 'ID, tanggal, jam, dan nominal pembayaran wajib diisi.');
}
if (!in_array($kategori, ['jual_beli', 'operasional'], true)) {
    paymentResponse('error', 'Kategori transaksi tidak valid.');
}
if ($jumlah <= 0) {
    paymentResponse('error', 'Nominal pembayaran harus lebih besar dari 0.');
}
$date = DateTime::createFromFormat('Y-m-d H:i', "$tanggal $jam");
if (!$date || $date->format('Y-m-d H:i') !== "$tanggal $jam") {
    paymentResponse('error', 'Format tanggal atau jam pembayaran tidak valid.');
}

$tanggalJam = "$tanggal $jam:00";
$now = date('Y-m-d H:i:s');
$isJualBeli = ($kategori === 'jual_beli');
$idPembayaran = 'PBY-' . GenerateKodeTransaksi();

try {
    $Conn->begin_transaction();

    $idJualBeli = null;
    $idTransaksi = null;
    $kategoriTransaksi = '';
    $total = 0;
    $cash = 0;
    $pembayaranSebelumnya = 0;
    $akunDebet = null;
    $akunKredit = null;

    if ($isJualBeli) {
        $stmt = $Conn->prepare(
            "SELECT id_transaksi_jual_beli, kategori, total, cash, status
             FROM transaksi_jual_beli WHERE id_transaksi_jual_beli = ? LIMIT 1 FOR UPDATE"
        );
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $transaksi = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$transaksi) {
            throw new Exception('Transaksi jual/beli tidak ditemukan.');
        }
        if ($transaksi['status'] === 'Lunas') {
            throw new Exception('Transaksi tersebut sudah lunas.');
        }

        $idJualBeli = $transaksi['id_transaksi_jual_beli'];
        $kategoriTransaksi = $transaksi['kategori'];
        $total = (float) $transaksi['total'];
        $cash = (float) $transaksi['cash'];

        $stmt = $Conn->prepare('SELECT COALESCE(SUM(jumlah), 0) AS total FROM transaksi_pembayaran WHERE id_transaksi_jual_beli = ?');
        $stmt->bind_param('s', $idJualBeli);
        $stmt->execute();
        $pembayaranSebelumnya = (float) $stmt->get_result()->fetch_assoc()['total'];
        $stmt->close();

        $stmt = $Conn->prepare('SELECT debet, kredit, utang_piutang FROM setting_autojurnal_jual_beli WHERE kategori = ? LIMIT 1');
        $settingKategori = $kategoriTransaksi;
        $stmt->bind_param('s', $settingKategori);
        $stmt->execute();
        $setting = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$setting) {
            throw new Exception('Setting auto jurnal jual/beli belum tersedia.');
        }
        if ($kategoriTransaksi === 'Pembelian') {
            $akunDebet = $setting['utang_piutang'];
            $akunKredit = $setting['debet'];
        } elseif ($kategoriTransaksi === 'Penjualan') {
            $akunDebet = $setting['debet'];
            $akunKredit = $setting['utang_piutang'];
        } elseif ($kategoriTransaksi === 'Retur Pembelian') {
            $akunDebet = $setting['debet'];
            $akunKredit = $setting['utang_piutang'];
        } elseif ($kategoriTransaksi === 'Retur Penjualan') {
            $akunDebet = $setting['utang_piutang'];
            $akunKredit = $setting['debet'];
        } else {
            throw new Exception('Kategori transaksi jual/beli tidak valid.');
        }
    } else {
        $idTransaksi = $id;
        $stmt = $Conn->prepare(
            'SELECT t.id_transaksi, t.jumlah, t.pembayaran, t.status, tj.kategori,
                    tj.id_akun_debet, tj.id_akun_kredit, tj.id_utang_piutang
             FROM transaksi t INNER JOIN transaksi_jenis tj ON tj.id_transaksi_jenis = t.id_transaksi_jenis
             WHERE t.id_transaksi = ? LIMIT 1 FOR UPDATE'
        );
        $stmt->bind_param('s', $idTransaksi);
        $stmt->execute();
        $transaksi = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$transaksi) {
            throw new Exception('Transaksi operasional tidak ditemukan.');
        }
        if ($transaksi['status'] === 'Lunas') {
            throw new Exception('Transaksi tersebut sudah lunas.');
        }

        $kategoriTransaksi = $transaksi['kategori'];
        $total = (float) $transaksi['jumlah'];
        $cash = (float) $transaksi['pembayaran'];
        if ($kategoriTransaksi === 'Pemasukan') {
            $akunDebet = $transaksi['id_akun_debet'];
            $akunKredit = $transaksi['id_utang_piutang'];
        } elseif ($kategoriTransaksi === 'Pengeluaran') {
            $akunDebet = $transaksi['id_utang_piutang'];
            $akunKredit = $transaksi['id_akun_debet'];
        } else {
            throw new Exception('Kategori transaksi operasional tidak valid.');
        }

        $stmt = $Conn->prepare('SELECT COALESCE(SUM(jumlah), 0) AS total FROM transaksi_pembayaran WHERE id_transaksi = ?');
        $stmt->bind_param('s', $idTransaksi);
        $stmt->execute();
        $pembayaranSebelumnya = (float) $stmt->get_result()->fetch_assoc()['total'];
        $stmt->close();
    }

    $sisaTagihan = $total - $cash - $pembayaranSebelumnya;
    if ($jumlah > $sisaTagihan) {
        throw new Exception('Nominal yang anda masukan tidak boleh melebihi sisa tagihan.');
    }

    $kategoriPembayaran = $jumlah == $sisaTagihan ? 'Pelunasan' : 'Termin';
    $kategoriPembayaranTransaksi = $kategoriTransaksi;
    $namaAkses = GetDetailData($Conn, 'akses', 'id_akses', $SessionIdAkses, 'nama_akses');
    $stmt = $Conn->prepare(
        'INSERT INTO transaksi_pembayaran
        (id_transaksi_pembayaran, id_transaksi, id_transaksi_jual_beli, kategori_pembayaran, kategori_transaksi,
         tanggal, jumlah, creat_at, creat_by_id, creat_by_name, update_at, update_by_id, update_by_name)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param(
        'ssssssisissis',
        $idPembayaran,
        $idTransaksi,
        $idJualBeli,
        $kategoriPembayaran,
        $kategoriPembayaranTransaksi,
        $tanggalJam,
        $jumlah,
        $now,
        $SessionIdAkses,
        $namaAkses,
        $now,
        $SessionIdAkses,
        $namaAkses
    );
    if (!$stmt->execute()) {
        throw new Exception('Gagal menyimpan pembayaran: ' . $stmt->error);
    }
    $stmt->close();

    if ($isJualBeli) {
        insertPaymentJournal($Conn, 'Pembayaran', $idJualBeli, null, $idPembayaran, $tanggal, $akunDebet, 'D', $jumlah);
        insertPaymentJournal($Conn, 'Pembayaran', $idJualBeli, null, $idPembayaran, $tanggal, $akunKredit, 'K', $jumlah);
        if ($jumlah == $sisaTagihan) {
            $stmt = $Conn->prepare("UPDATE transaksi_jual_beli SET status = 'Lunas' WHERE id_transaksi_jual_beli = ?");
            $stmt->bind_param('s', $idJualBeli);
            if (!$stmt->execute()) {
                throw new Exception('Gagal memperbarui status transaksi jual/beli.');
            }
            $stmt->close();
        }
    } else {
        insertPaymentJournal($Conn, 'Pembayaran', null, $idTransaksi, $idPembayaran, $tanggal, $akunDebet, 'D', $jumlah);
        insertPaymentJournal($Conn, 'Pembayaran', null, $idTransaksi, $idPembayaran, $tanggal, $akunKredit, 'K', $jumlah);
        if ($jumlah == $sisaTagihan) {
            $stmt = $Conn->prepare("UPDATE transaksi SET status = 'Lunas' WHERE id_transaksi = ?");
            $stmt->bind_param('s', $idTransaksi);
            if (!$stmt->execute()) {
                throw new Exception('Gagal memperbarui status transaksi operasional.');
            }
            $stmt->close();
        }
    }

    if (addLog($Conn, $SessionIdAkses, $now, 'Utang Piutang', 'Pembayaran Utang Piutang') !== 'Success') {
        throw new Exception('Gagal menyimpan log pembayaran.');
    }
    $Conn->commit();
    paymentResponse('success', 'Pembayaran Utang Piutang Berhasil.', $id, $isJualBeli ? 'jual_beli' : 'operasional');
} catch (Throwable $e) {
    $Conn->rollback();
    paymentResponse('error', $e->getMessage());
}
?>
