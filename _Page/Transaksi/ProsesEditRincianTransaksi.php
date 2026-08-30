<?php
    // =========================================================
    // KONFIGURASI
    // =========================================================
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/SettingGeneral.php";
    include "../../_Config/Session.php";
    include "../../_Config/FungsiAkses.php";

    // Default JSON Format
    header('Content-Type: application/json; charset=utf-8');

    // Time Zone
    date_default_timezone_set('Asia/Jakarta');

    // Time Now Tmp
    $now = date('Y-m-d H:i:s');

    // =========================================================
    // RESPONSE ERROR
    // =========================================================
    function responseError($message) {
        echo json_encode([
            'status'  => 'error',
            'message' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // =========================================================
    // VALIDASI SESSION
    // =========================================================
    if (empty($SessionIdAkses)) {
        responseError('Sesi akses sudah berakhir. Silakan login ulang.');
    }

    // =========================================================
    // VALIDASI METHOD
    // =========================================================
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseError('Metode request tidak valid.');
    }

    // =========================================================
    // AMBIL DATA
    // =========================================================
    $id_transaksi_rincian = trim($_POST['id_transaksi_rincian'] ?? '');
    $uraian               = trim($_POST['uraian_rincian'] ?? '');
    $harga_input          = trim($_POST['uraian_harga'] ?? '');
    $qty_input            = trim($_POST['uraian_qty'] ?? '');
    $satuan               = trim($_POST['uraian_satuan'] ?? '');

    // =========================================================
    // VALIDASI ID
    // =========================================================
    if ($id_transaksi_rincian === '' || !ctype_digit($id_transaksi_rincian)) {
        responseError('ID rincian transaksi tidak valid.');
    }
    $id_transaksi_rincian = (int) $id_transaksi_rincian;
    if ($id_transaksi_rincian <= 0) {
        responseError('ID rincian transaksi tidak valid.');
    }

    // =========================================================
    // VALIDASI URAIAN
    // =========================================================
    if ($uraian === '') {
        responseError('Uraian rincian wajib diisi.');
    }

    // =========================================================
    // BERSIHKAN HARGA
    // =========================================================
    $harga_input = preg_replace('/[^0-9]/', '', $harga_input);
    $harga = ($harga_input === '') ? 0 : (int) $harga_input;

    // =========================================================
    // BERSIHKAN QTY
    // =========================================================
    $qty_input = preg_replace('/[^0-9]/', '', $qty_input);
    if ($qty_input === '') {
        responseError('QTY wajib diisi.');
    }
    $qty = (int) $qty_input;
    if ($qty <= 0) {
        responseError('QTY harus lebih besar dari 0.');
    }

    // =========================================================
    // HITUNG JUMLAH
    // =========================================================
    $jumlah = $harga * $qty;

    // =========================================================
    // MULAI DATABASE TRANSACTION
    // =========================================================
    mysqli_begin_transaction($Conn);

    try {
        // =====================================================
        // AMBIL RINCIAN LAMA
        // =====================================================
        $sql_old = "SELECT id_transaksi_rincian, id_transaksi FROM transaksi_rincian WHERE id_transaksi_rincian = ? LIMIT 1";
        $stmt_old = mysqli_prepare($Conn, $sql_old);
        if (!$stmt_old) {
            throw new Exception('Gagal mempersiapkan query rincian.');
        }
        mysqli_stmt_bind_param($stmt_old, 'i', $id_transaksi_rincian);
        if (!mysqli_stmt_execute($stmt_old)) {
            mysqli_stmt_close($stmt_old);
            throw new Exception('Gagal mengambil rincian transaksi.');
        }
        $result_old = mysqli_stmt_get_result($stmt_old);
        if (!$result_old || mysqli_num_rows($result_old) === 0) {
            mysqli_stmt_close($stmt_old);
            throw new Exception('Rincian transaksi tidak ditemukan.');
        }
        $data_old = mysqli_fetch_assoc($result_old);
        mysqli_stmt_close($stmt_old);
        $id_transaksi = $data_old['id_transaksi'];

        // =====================================================
        // UPDATE RINCIAN
        // =====================================================
        $sql_update_rincian = "UPDATE transaksi_rincian SET rincian_transaksi = ?, harga = ?, qty = ?, satuan = ?, jumlah = ? WHERE id_transaksi_rincian = ? LIMIT 1";
        $stmt_update_rincian = mysqli_prepare($Conn, $sql_update_rincian);
        if (!$stmt_update_rincian) {
            throw new Exception('Gagal mempersiapkan update rincian.');
        }
        mysqli_stmt_bind_param($stmt_update_rincian, 'siisii', $uraian, $harga, $qty, $satuan, $jumlah, $id_transaksi_rincian);
        if (!mysqli_stmt_execute($stmt_update_rincian)) {
            mysqli_stmt_close($stmt_update_rincian);
            throw new Exception('Gagal memperbarui rincian transaksi.');
        }
        mysqli_stmt_close($stmt_update_rincian);

        // =====================================================
        // HITUNG ULANG TOTAL SELURUH RINCIAN
        // =====================================================
        $sql_total = "SELECT COALESCE(SUM(jumlah), 0) AS total_jumlah FROM transaksi_rincian WHERE id_transaksi = ?";
        $stmt_total = mysqli_prepare($Conn, $sql_total);
        if (!$stmt_total) {
            throw new Exception('Gagal mempersiapkan perhitungan total.');
        }
        mysqli_stmt_bind_param($stmt_total, 'i', $id_transaksi);
        if (!mysqli_stmt_execute($stmt_total)) {
            mysqli_stmt_close($stmt_total);
            throw new Exception('Gagal menghitung total transaksi.');
        }
        $result_total = mysqli_stmt_get_result($stmt_total);
        $data_total = mysqli_fetch_assoc($result_total);
        mysqli_stmt_close($stmt_total);
        $total_jumlah = (int) ($data_total['total_jumlah'] ?? 0);

        // =====================================================
        // AMBIL KATEGORI + PEMBAYARAN
        // =====================================================
        $sql_transaksi = "SELECT t.pembayaran, tj.kategori FROM transaksi AS t LEFT JOIN transaksi_jenis AS tj ON tj.id_transaksi_jenis = t.id_transaksi_jenis WHERE t.id_transaksi = ? LIMIT 1";
        $stmt_transaksi = mysqli_prepare($Conn, $sql_transaksi);
        if (!$stmt_transaksi) {
            throw new Exception('Gagal mempersiapkan query transaksi.');
        }
        mysqli_stmt_bind_param($stmt_transaksi, 's', $id_transaksi);
        if (!mysqli_stmt_execute($stmt_transaksi)) {
            mysqli_stmt_close($stmt_transaksi);
            throw new Exception('Gagal mengambil data transaksi.');
        }
        $result_transaksi = mysqli_stmt_get_result($stmt_transaksi);
        $data_transaksi = mysqli_fetch_assoc($result_transaksi);
        mysqli_stmt_close($stmt_transaksi);
        if (!$data_transaksi) {
            throw new Exception('Data transaksi tidak ditemukan.');
        }
        $kategori   = $data_transaksi['kategori'] ?? '';
        $pembayaran = (int) ($data_transaksi['pembayaran'] ?? 0);

        // =====================================================
        // HITUNG STATUS
        // =====================================================
        $status = 'Lunas';
        if ($kategori === 'Pengeluaran') {
            if ($total_jumlah > $pembayaran) {
                $status = 'Utang';
            } elseif ($total_jumlah < $pembayaran) {
                $status = 'Piutang';
            } else {
                $status = 'Lunas';
            }
        } elseif ($kategori === 'Pemasukan') {
            if ($total_jumlah > $pembayaran) {
                $status = 'Piutang';
            } elseif ($total_jumlah < $pembayaran) {
                $status = 'Utang';
            } else {
                $status = 'Lunas';
            }
        }

        // =====================================================
        // UPDATE TRANSAKSI
        // =====================================================
        $sql_update_transaksi = "UPDATE transaksi SET jumlah = ?, status = ?, update_at = ?, update_by_id = ?, update_by_name = ? WHERE id_transaksi = ? LIMIT 1";
        $stmt_update_transaksi = mysqli_prepare($Conn, $sql_update_transaksi);
        if (!$stmt_update_transaksi) {
            throw new Exception('Gagal mempersiapkan update transaksi.');
        }
        mysqli_stmt_bind_param($stmt_update_transaksi, 'ississ', $total_jumlah, $status, $now, $SessionIdAkses, $SessionNama, $id_transaksi);
        if (!mysqli_stmt_execute($stmt_update_transaksi)) {
            mysqli_stmt_close($stmt_update_transaksi);
            throw new Exception('Gagal memperbarui total transaksi.');
        }
        mysqli_stmt_close($stmt_update_transaksi);

        // =====================================================
        // COMMIT
        // =====================================================
        mysqli_commit($Conn);

        // =====================================================
        // RESPONSE SUCCESS
        // =====================================================
        echo json_encode([
            'status'           => 'success',
            'message'          => 'Rincian transaksi berhasil diperbarui.',
            'id_transaksi'     => $id_transaksi,
            'id_rincian'       => $id_transaksi_rincian,
            'harga'            => $harga,
            'qty'              => $qty,
            'jumlah'           => $jumlah,
            'total_jumlah'     => $total_jumlah,
            'pembayaran'       => $pembayaran,
            'kategori'         => $kategori,
            'status_transaksi' => $status
        ], JSON_UNESCAPED_UNICODE);
        exit;

    } catch (Exception $e) {
        // =====================================================
        // ROLLBACK
        // =====================================================
        mysqli_rollback($Conn);
        echo json_encode([
            'status'  => 'error',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
?>