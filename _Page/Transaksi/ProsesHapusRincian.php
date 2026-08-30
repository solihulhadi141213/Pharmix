<?php
    // =========================================================
    // KONEKSI, HELPER DAN SESSION
    // =========================================================
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/SettingGeneral.php";
    include "../../_Config/Session.php";
    include "../../_Config/FungsiAkses.php";

    // Default Json
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
    // AMBIL ID
    // =========================================================
    $id_transaksi_rincian = trim($_POST['id_transaksi_rincian'] ?? '');
    if ($id_transaksi_rincian === '' || !ctype_digit($id_transaksi_rincian)) {
        responseError('ID rincian transaksi tidak valid.');
    }
    $id_transaksi_rincian = (int)$id_transaksi_rincian;
    if ($id_transaksi_rincian <= 0) {
        responseError('ID rincian transaksi tidak valid.');
    }

    // =========================================================
    // MULAI TRANSACTION
    // =========================================================
    mysqli_begin_transaction($Conn);

    try {
        // =====================================================
        // 1. AMBIL RINCIAN
        // =====================================================
        $sql = "SELECT id_transaksi_rincian, id_transaksi FROM transaksi_rincian WHERE id_transaksi_rincian = ? LIMIT 1";
        $stmt = mysqli_prepare($Conn, $sql);
        if (!$stmt) {
            throw new Exception('Gagal menyiapkan query rincian.');
        }
        mysqli_stmt_bind_param($stmt, 'i', $id_transaksi_rincian);
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            throw new Exception('Gagal mengambil data rincian.');
        }
        $result = mysqli_stmt_get_result($stmt);
        if (!$result || mysqli_num_rows($result) === 0) {
            mysqli_stmt_close($stmt);
            throw new Exception('Data rincian transaksi tidak ditemukan.');
        }
        $rincian = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        $id_transaksi = $rincian['id_transaksi'];
        if ($id_transaksi <= 0) {
            throw new Exception('Transaksi induk tidak valid.');
        }

        // =====================================================
        // 2. HAPUS RINCIAN
        // =====================================================
        $sqlDelete = "DELETE FROM transaksi_rincian WHERE id_transaksi_rincian = ? LIMIT 1";
        $stmtDelete = mysqli_prepare($Conn, $sqlDelete);
        if (!$stmtDelete) {
            throw new Exception('Gagal menyiapkan proses penghapusan.');
        }
        mysqli_stmt_bind_param($stmtDelete, 'i', $id_transaksi_rincian);
        if (!mysqli_stmt_execute($stmtDelete)) {
            mysqli_stmt_close($stmtDelete);
            throw new Exception('Gagal menghapus rincian transaksi.');
        }
        $affectedRows = mysqli_stmt_affected_rows($stmtDelete);
        mysqli_stmt_close($stmtDelete);
        if ($affectedRows <= 0) {
            throw new Exception('Data rincian gagal dihapus.');
        }

        // =====================================================
        // 3. HITUNG ULANG JUMLAH TRANSAKSI
        // =====================================================
        $sqlTotal = "SELECT COALESCE(SUM(jumlah), 0) AS total FROM transaksi_rincian WHERE id_transaksi = ?";
        $stmtTotal = mysqli_prepare($Conn, $sqlTotal);
        if (!$stmtTotal) {
            throw new Exception('Gagal menghitung total transaksi.');
        }
        mysqli_stmt_bind_param($stmtTotal, 's', $id_transaksi);
        if (!mysqli_stmt_execute($stmtTotal)) {
            mysqli_stmt_close($stmtTotal);
            throw new Exception('Gagal menghitung total rincian.');
        }
        $resultTotal = mysqli_stmt_get_result($stmtTotal);
        $dataTotal = mysqli_fetch_assoc($resultTotal);
        mysqli_stmt_close($stmtTotal);
        $jumlah = (int)($dataTotal['total'] ?? 0);

        // =====================================================
        // 4. AMBIL DATA TRANSAKSI
        // =====================================================
        $sqlTransaksi = "SELECT t.pembayaran, tj.kategori FROM transaksi AS t LEFT JOIN transaksi_jenis AS tj ON tj.id_transaksi_jenis = t.id_transaksi_jenis WHERE t.id_transaksi = ? LIMIT 1";
        $stmtTransaksi = mysqli_prepare($Conn, $sqlTransaksi);
        if (!$stmtTransaksi) {
            throw new Exception('Gagal mengambil data transaksi.');
        }
        mysqli_stmt_bind_param($stmtTransaksi, 's', $id_transaksi);
        if (!mysqli_stmt_execute($stmtTransaksi)) {
            mysqli_stmt_close($stmtTransaksi);
            throw new Exception('Gagal mengambil informasi transaksi.');
        }
        $resultTransaksi = mysqli_stmt_get_result($stmtTransaksi);
        if (!$resultTransaksi || mysqli_num_rows($resultTransaksi) === 0) {
            mysqli_stmt_close($stmtTransaksi);
            throw new Exception('Transaksi tidak ditemukan.');
        }
        $dataTransaksi = mysqli_fetch_assoc($resultTransaksi);
        mysqli_stmt_close($stmtTransaksi);
        $pembayaran = (int)($dataTransaksi['pembayaran'] ?? 0);
        $kategori = $dataTransaksi['kategori'] ?? '';

        // =====================================================
        // 5. TENTUKAN STATUS
        // =====================================================
        $status = 'Lunas';
        if ($kategori === 'Pengeluaran') {
            if ($jumlah > $pembayaran) {
                $status = 'Utang';
            } elseif ($jumlah < $pembayaran) {
                $status = 'Piutang';
            } else {
                $status = 'Lunas';
            }
        } elseif ($kategori === 'Pemasukan') {
            if ($jumlah > $pembayaran) {
                $status = 'Piutang';
            } elseif ($jumlah < $pembayaran) {
                $status = 'Utang';
            } else {
                $status = 'Lunas';
            }
        }

        // =====================================================
        // 6. UPDATE TRANSAKSI
        // =====================================================
        $sqlUpdate = "UPDATE transaksi SET jumlah = ?, status = ?, update_at = ?, update_by_id = ?, update_by_name = ? WHERE id_transaksi = ? LIMIT 1";
        $stmtUpdate = mysqli_prepare($Conn, $sqlUpdate);
        if (!$stmtUpdate) {
            throw new Exception('Gagal menyiapkan update transaksi.');
        }
        mysqli_stmt_bind_param($stmtUpdate, 'ississ', $jumlah, $status, $now, $SessionIdAkses, $SessionNama, $id_transaksi);
        if (!mysqli_stmt_execute($stmtUpdate)) {
            mysqli_stmt_close($stmtUpdate);
            throw new Exception('Gagal memperbarui transaksi.');
        }
        mysqli_stmt_close($stmtUpdate);

        // =====================================================
        // 7. COMMIT
        // =====================================================
        mysqli_commit($Conn);

        // =====================================================
        // RESPONSE
        // =====================================================
        echo json_encode([
            'status'  => 'success',
            'message' => 'Rincian transaksi berhasil dihapus.',
            'data'    => [
                'id_transaksi' => $id_transaksi,
                'jumlah'       => $jumlah,
                'pembayaran'   => $pembayaran,
                'status'       => $status
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;

    } catch (Throwable $e) {
        // =====================================================
        // ROLLBACK
        // =====================================================
        mysqli_rollback($Conn);
        error_log('ProsesHapusRincian.php: ' . $e->getMessage());
        responseError($e->getMessage());
    }
?>