<?php
    // =========================================================
    // KONEKSI, HELPER, SETTING & SESSION
    // =========================================================
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/SettingGeneral.php";
    include "../../_Config/Session.php";
    include "../../_Config/FungsiAkses.php";

    // JSON Format
    header('Content-Type: application/json; charset=utf-8');

    // Time Zone
    date_default_timezone_set('Asia/Jakarta');

    // Time Now Tmp
    $now = date('Y-m-d H:i:s');

    // =========================================================
    // RESPONSE DEFAULT
    // =========================================================
    $response = [
        'status'  => 'error',
        'message' => 'Terjadi kesalahan.',
    ];

    // =========================================================
    // FUNGSI RESPONSE ERROR
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
    // AMBIL DATA POST
    // =========================================================
    $id_transaksi = trim($_POST['id_transaksi'] ?? '');
    $uraian       = trim($_POST['uraian_rincian'] ?? '');
    $harga_input  = trim($_POST['uraian_harga'] ?? '');
    $qty_input    = trim($_POST['uraian_qty'] ?? '');
    $satuan       = trim($_POST['uraian_satuan'] ?? '');

    // =========================================================
    // VALIDASI ID TRANSAKSI
    // =========================================================
    if ($id_transaksi === '' || !ctype_digit($id_transaksi)) {
        responseError('ID transaksi tidak valid.');
    }
    $id_transaksi = (int) $id_transaksi;
    if ($id_transaksi <= 0) {
        responseError('ID transaksi tidak valid.');
    }

    // =========================================================
    // VALIDASI URAIAN
    // =========================================================
    if ($uraian === '') {
        responseError('Uraian rincian wajib diisi.');
    }

    // =========================================================
    // VALIDASI HARGA
    // =========================================================
    $harga_input = preg_replace('/[^0-9]/', '', $harga_input);
    $harga = ($harga_input === '') ? 0 : (int) $harga_input;

    // =========================================================
    // VALIDASI QTY
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
    // VALIDASI TRANSAKSI
    // =========================================================
    $sql_transaksi = "SELECT id_transaksi FROM transaksi WHERE id_transaksi = ? LIMIT 1";
    $stmt_transaksi = mysqli_prepare($Conn, $sql_transaksi);
    if (!$stmt_transaksi) {
        responseError('Gagal mempersiapkan validasi transaksi.');
    }
    mysqli_stmt_bind_param($stmt_transaksi, 'i', $id_transaksi);
    if (!mysqli_stmt_execute($stmt_transaksi)) {
        mysqli_stmt_close($stmt_transaksi);
        responseError('Gagal memvalidasi transaksi.');
    }
    $result_transaksi = mysqli_stmt_get_result($stmt_transaksi);
    if (!$result_transaksi || mysqli_num_rows($result_transaksi) === 0) {
        mysqli_stmt_close($stmt_transaksi);
        responseError('Data transaksi tidak ditemukan.');
    }
    mysqli_stmt_close($stmt_transaksi);

    // =========================================================
    // TRANSACTION DATABASE
    // =========================================================
    mysqli_begin_transaction($Conn);

    try {
        // =====================================================
        // INSERT RINCIAN
        // =====================================================
        $sql_insert = "INSERT INTO transaksi_rincian (id_transaksi, rincian_transaksi, harga, qty, satuan, jumlah) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert = mysqli_prepare($Conn, $sql_insert);
        if (!$stmt_insert) {
            throw new Exception('Gagal mempersiapkan penyimpanan rincian.');
        }
        mysqli_stmt_bind_param($stmt_insert, 'isiisi', $id_transaksi, $uraian, $harga, $qty, $satuan, $jumlah);
        if (!mysqli_stmt_execute($stmt_insert)) {
            mysqli_stmt_close($stmt_insert);
            throw new Exception('Gagal menyimpan rincian transaksi.');
        }
        mysqli_stmt_close($stmt_insert);

        // =====================================================
        // HITUNG ULANG TOTAL TRANSAKSI
        // =====================================================
        $sql_total = "SELECT COALESCE(SUM(jumlah), 0) AS total_jumlah FROM transaksi_rincian WHERE id_transaksi = ?";
        $stmt_total = mysqli_prepare($Conn, $sql_total);
        if (!$stmt_total) {
            throw new Exception('Gagal menghitung total transaksi.');
        }
        mysqli_stmt_bind_param($stmt_total, 'i', $id_transaksi);
        if (!mysqli_stmt_execute($stmt_total)) {
            mysqli_stmt_close($stmt_total);
            throw new Exception('Gagal mengambil total transaksi.');
        }
        $result_total = mysqli_stmt_get_result($stmt_total);
        $data_total   = mysqli_fetch_assoc($result_total);
        mysqli_stmt_close($stmt_total);
        $total_jumlah = (int) ($data_total['total_jumlah'] ?? 0);

        // =====================================================
        // AMBIL KATEGORI TRANSAKSI
        // =====================================================
        $sql_kategori = "SELECT tj.kategori, t.pembayaran FROM transaksi AS t LEFT JOIN transaksi_jenis AS tj ON tj.id_transaksi_jenis = t.id_transaksi_jenis WHERE t.id_transaksi = ? LIMIT 1";
        $stmt_kategori = mysqli_prepare($Conn, $sql_kategori);
        if (!$stmt_kategori) {
            throw new Exception('Gagal mengambil kategori transaksi.');
        }
        mysqli_stmt_bind_param($stmt_kategori, 'i', $id_transaksi);
        if (!mysqli_stmt_execute($stmt_kategori)) {
            mysqli_stmt_close($stmt_kategori);
            throw new Exception('Gagal membaca kategori transaksi.');
        }
        $result_kategori = mysqli_stmt_get_result($stmt_kategori);
        $data_kategori   = mysqli_fetch_assoc($result_kategori);
        mysqli_stmt_close($stmt_kategori);
        if (!$data_kategori) {
            throw new Exception('Data transaksi tidak ditemukan.');
        }
        $kategori   = $data_kategori['kategori'] ?? '';
        $pembayaran = (int) ($data_kategori['pembayaran'] ?? 0);

        // =====================================================
        // TENTUKAN STATUS TRANSAKSI
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
        // UPDATE TOTAL & STATUS TRANSAKSI
        // =====================================================
        $sql_update = "UPDATE transaksi SET jumlah = ?, status = ?, update_at = ?, update_by_id = ?, update_by_name = ? WHERE id_transaksi = ? LIMIT 1";
        $stmt_update = mysqli_prepare($Conn, $sql_update);
        if (!$stmt_update) {
            throw new Exception('Gagal mempersiapkan pembaruan transaksi.');
        }
        mysqli_stmt_bind_param($stmt_update, 'issisi', $total_jumlah, $status, $now, $SessionIdAkses, $SessionNama, $id_transaksi);
        if (!mysqli_stmt_execute($stmt_update)) {
            mysqli_stmt_close($stmt_update);
            throw new Exception('Gagal memperbarui transaksi.');
        }
        mysqli_stmt_close($stmt_update);

        // =====================================================
        // COMMIT
        // =====================================================
        mysqli_commit($Conn);

        // =====================================================
        // RESPONSE SUCCESS
        // =====================================================
        echo json_encode([
            'status'             => 'success',
            'message'            => 'Rincian transaksi berhasil ditambahkan.',
            'id_transaksi'       => $id_transaksi,
            'harga'              => $harga,
            'qty'                => $qty,
            'jumlah'             => $jumlah,
            'total_jumlah'       => $total_jumlah,
            'pembayaran'         => $pembayaran,
            'kategori'           => $kategori,
            'status_transaksi'   => $status
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