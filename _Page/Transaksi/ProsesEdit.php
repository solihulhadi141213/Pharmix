<?php
    // ============================================================
    // PROSES EDIT TRANSAKSI
    // ============================================================
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/SettingGeneral.php";
    include "../../_Config/Session.php";
    include "../../_Config/FungsiAkses.php";
    header('Content-Type: application/json; charset=utf-8');

    // ============================================================
    // RESPONSE ERROR
    // ============================================================
    function responseError($message) {
        echo json_encode([
            'status'  => 'error',
            'message' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ============================================================
    // RESPONSE SUCCESS
    // ============================================================
    function responseSuccess($message) {
        echo json_encode([
            'status'  => 'success',
            'message' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ============================================================
    // VALIDASI SESSION
    // ============================================================
    if (empty($SessionIdAkses)) {
        responseError('Sesi akses sudah berakhir. Silakan login ulang.');
    }

    // ============================================================
    // VALIDASI METHOD
    // ============================================================
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseError('Metode request tidak valid.');
    }

    // Time Zone
    date_default_timezone_set('Asia/Jakarta');

    // Time Now Tmp
    $now = date('Y-m-d H:i:s');

    // ============================================================
    // VALIDASI ID TRANSAKSI
    // ============================================================
    if(empty($_POST['id_transaksi'])){
        responseError('ID transaksi tidak valid.');
    }

    // ============================================================
    // AMBIL INPUT
    // ============================================================
    $id_transaksi       = $_POST['id_transaksi'];
    $id_transaksi_jenis = trim($_POST['id_transaksi_jenis'] ?? '');
    $tanggal            = trim($_POST['tanggal'] ?? '');
    $jam                = trim($_POST['jam'] ?? '');
    $pembayaran         = trim($_POST['JumlahPembayaran'] ?? '');
    $keterangan         = trim($_POST['keterangan'] ?? '');

    
    // ============================================================
    // VALIDASI JENIS TRANSAKSI
    // ============================================================
    if ($id_transaksi_jenis === '' || !ctype_digit($id_transaksi_jenis)) {
        responseError('Jenis transaksi harus dipilih.');
    }
    $id_transaksi_jenis = (int) $id_transaksi_jenis;
    if ($id_transaksi_jenis <= 0) {
        responseError('Jenis transaksi tidak valid.');
    }

    // ============================================================
    // VALIDASI TANGGAL
    // ============================================================
    $dateObject = DateTime::createFromFormat('Y-m-d', $tanggal);
    if (!$dateObject || $dateObject->format('Y-m-d') !== $tanggal) {
        responseError('Tanggal transaksi tidak valid.');
    }

    // ============================================================
    // VALIDASI JAM
    // ============================================================
    $timeObject = DateTime::createFromFormat('H:i:s', $jam);
    if (!$timeObject || $timeObject->format('H:i:s') !== $jam) {
        $timeObject = DateTime::createFromFormat('H:i', $jam);
        if (!$timeObject || $timeObject->format('H:i') !== $jam) {
            responseError('Jam transaksi tidak valid.');
        }
        $jam = $timeObject->format('H:i:s');
    }

    // ============================================================
    // GABUNG TANGGAL + JAM
    // ============================================================
    $tanggal_transaksi = $tanggal . ' ' . $jam;

    // ============================================================
    // NORMALISASI PEMBAYARAN
    // ============================================================
    $pembayaran = preg_replace('/[^0-9]/', '', $pembayaran);
    if ($pembayaran === '') {
        $pembayaran = 0;
    }
    $pembayaran = (int) $pembayaran;

    // ============================================================
    // VALIDASI PEMBAYARAN
    // ============================================================
    if ($pembayaran < 0) {
        responseError('Pembayaran tidak valid.');
    }

    // ============================================================
    // MULAI DATABASE TRANSACTION
    // ============================================================
    mysqli_begin_transaction($Conn);

    try {
        // ========================================================
        // 1. AMBIL DATA TRANSAKSI LAMA
        // ========================================================
        $sql = "SELECT id_transaksi, id_transaksi_jenis, tanggal, jumlah, pembayaran, keterangan, status FROM transaksi WHERE id_transaksi = ? LIMIT 1";
        $stmt = mysqli_prepare($Conn, $sql);
        if (!$stmt) {
            throw new Exception('Gagal mempersiapkan query transaksi.');
        }
        mysqli_stmt_bind_param($stmt, 's', $id_transaksi);
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            throw new Exception('Gagal mengambil data transaksi.');
        }
        $result = mysqli_stmt_get_result($stmt);
        $transaksiLama = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$transaksiLama) {
            throw new Exception('Data transaksi tidak ditemukan.');
        }

        // ========================================================
        // 2. AMBIL JENIS TRANSAKSI
        // ========================================================
        $sql = "SELECT id_transaksi_jenis, nama, kategori, id_akun_debet, id_akun_kredit FROM transaksi_jenis WHERE id_transaksi_jenis = ? LIMIT 1";
        $stmt = mysqli_prepare($Conn, $sql);
        if (!$stmt) {
            throw new Exception('Gagal mempersiapkan query jenis transaksi.');
        }
        mysqli_stmt_bind_param($stmt, 'i', $id_transaksi_jenis);
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            throw new Exception('Gagal mengambil jenis transaksi.');
        }
        $resultJenis = mysqli_stmt_get_result($stmt);
        $jenisTransaksi = mysqli_fetch_assoc($resultJenis);
        mysqli_stmt_close($stmt);

        if (!$jenisTransaksi) {
            throw new Exception('Jenis transaksi tidak ditemukan.');
        }

        $kategori = $jenisTransaksi['kategori'];
        $id_akun_debet  = $jenisTransaksi['id_akun_debet'];
        $id_akun_kredit = $jenisTransaksi['id_akun_kredit'];

        // ========================================================
        // 3. HITUNG ULANG JUMLAH DARI TRANSAKSI_RINCIAN
        // ========================================================
        $sql = "SELECT COALESCE(SUM(COALESCE(jumlah, 0)), 0) AS total FROM transaksi_rincian WHERE id_transaksi = ?";
        $stmt = mysqli_prepare($Conn, $sql);
        if (!$stmt) {
            throw new Exception('Gagal mempersiapkan query rincian transaksi.');
        }
        mysqli_stmt_bind_param($stmt, 's', $id_transaksi);
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            throw new Exception('Gagal menghitung rincian transaksi.');
        }
        $resultJumlah = mysqli_stmt_get_result($stmt);
        $dataJumlah = mysqli_fetch_assoc($resultJumlah);
        mysqli_stmt_close($stmt);
        $jumlah = (int) ($dataJumlah['total'] ?? 0);

        // ========================================================
        // 4. HITUNG STATUS
        // ========================================================
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
        } else {
            throw new Exception('Kategori transaksi tidak valid.');
        }

        // ========================================================
        // 5. UPDATE TRANSAKSI
        // ========================================================
        $sql = "UPDATE transaksi SET id_transaksi_jenis = ?, tanggal = ?, jumlah = ?, pembayaran = ?, keterangan = ?, status = ?, update_at = ?, update_by_id = ?, update_by_name = ? WHERE id_transaksi = ?";
        $stmt = mysqli_prepare($Conn, $sql);
        if (!$stmt) {
            throw new Exception('Gagal mempersiapkan proses update transaksi.');
        }
        mysqli_stmt_bind_param($stmt, 'isiisssisi', $id_transaksi_jenis, $tanggal_transaksi, $jumlah, $pembayaran, $keterangan, $status, $now, $SessionIdAkses, $SessionNama, $id_transaksi);
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            throw new Exception('Gagal memperbarui transaksi.');
        }
        mysqli_stmt_close($stmt);

        // ========================================================
        // 6. PERBARUI JURNAL
        // ========================================================
        $sql = "DELETE FROM jurnal WHERE id_transaksi = ?";
        $stmt = mysqli_prepare($Conn, $sql);
        if (!$stmt) {
            throw new Exception('Gagal mempersiapkan penghapusan jurnal lama.');
        }
        mysqli_stmt_bind_param($stmt, 's', $id_transaksi);
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            throw new Exception('Gagal menghapus jurnal lama.');
        }
        mysqli_stmt_close($stmt);

        // ========================================================
        // 7. BUAT UUID JURNAL
        // ========================================================
        $uuid = function_exists('RamdomUuid') ? RamdomUuid() : sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

        // ========================================================
        // 8. TANGGAL JURNAL
        // ========================================================
        $tanggal_jurnal = $tanggal;

        // ========================================================
        // 9. INSERT JURNAL DEBET
        // ========================================================
        if (!empty($id_akun_debet)) {
            $sql = "SELECT kode, nama FROM akun_perkiraan WHERE id_perkiraan = ? LIMIT 1";
            $stmt = mysqli_prepare($Conn, $sql);
            if (!$stmt) {
                throw new Exception('Gagal mengambil akun debet.');
            }
            mysqli_stmt_bind_param($stmt, 'i', $id_akun_debet);
            if (!mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                throw new Exception('Gagal mengambil akun debet.');
            }
            $resultAkun = mysqli_stmt_get_result($stmt);
            $akunDebet = mysqli_fetch_assoc($resultAkun);
            mysqli_stmt_close($stmt);

            if ($akunDebet) {
                $kode_perkiraan = $akunDebet['kode'];
                $nama_perkiraan = $akunDebet['nama'];
                $d_k = 'D';

                $sql = "INSERT INTO jurnal (kategori, uuid, id_transaksi, tanggal, kode_perkiraan, nama_perkiraan, d_k, nilai) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($Conn, $sql);
                if (!$stmt) {
                    throw new Exception('Gagal mempersiapkan jurnal debet.');
                }
                mysqli_stmt_bind_param($stmt, 'sssssssi', $kategori, $uuid, $id_transaksi, $tanggal_jurnal, $kode_perkiraan, $nama_perkiraan, $d_k, $jumlah);
                if (!mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    throw new Exception('Gagal mencatat jurnal debet.');
                }
                mysqli_stmt_close($stmt);
            }
        }

        // ========================================================
        // 10. INSERT JURNAL KREDIT
        // ========================================================
        if (!empty($id_akun_kredit)) {
            $sql = "SELECT kode, nama FROM akun_perkiraan WHERE id_perkiraan = ? LIMIT 1";
            $stmt = mysqli_prepare($Conn, $sql);
            if (!$stmt) {
                throw new Exception('Gagal mengambil akun kredit.');
            }
            mysqli_stmt_bind_param($stmt, 'i', $id_akun_kredit);
            if (!mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                throw new Exception('Gagal mengambil akun kredit.');
            }
            $resultAkun = mysqli_stmt_get_result($stmt);
            $akunKredit = mysqli_fetch_assoc($resultAkun);
            mysqli_stmt_close($stmt);

            if ($akunKredit) {
                $kode_perkiraan = $akunKredit['kode'];
                $nama_perkiraan = $akunKredit['nama'];
                $d_k = 'K';

                $sql = "INSERT INTO jurnal (kategori, uuid, id_transaksi, tanggal, kode_perkiraan, nama_perkiraan, d_k, nilai) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($Conn, $sql);
                if (!$stmt) {
                    throw new Exception('Gagal mempersiapkan jurnal kredit.');
                }
                mysqli_stmt_bind_param($stmt, 'sssssssi', $kategori, $uuid, $id_transaksi, $tanggal_jurnal, $kode_perkiraan, $nama_perkiraan, $d_k, $jumlah);
                if (!mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    throw new Exception('Gagal mencatat jurnal kredit.');
                }
                mysqli_stmt_close($stmt);
            }
        }

        // ========================================================
        // COMMIT
        // ========================================================
        mysqli_commit($Conn);
        responseSuccess('Data transaksi berhasil diperbarui.');

    } catch (Throwable $e) {
        // ========================================================
        // ROLLBACK
        // ========================================================
        mysqli_rollback($Conn);
        error_log('ProsesEditTransaksi Error: ' . $e->getMessage());
        responseError($e->getMessage());
    }
?>