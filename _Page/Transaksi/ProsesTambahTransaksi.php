<?php
    // ============================================================
    // KONFIGURASI & KONEKSI
    // ============================================================
    date_default_timezone_set('Asia/Jakarta');

    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    include "../../_Config/FungsiAkses.php";

    header('Content-Type: application/json; charset=utf-8');

    // ============================================================
    // FUNGSI BANTUAN (HELPER)
    // ============================================================
    function responseError(string $message): void
    {
        echo json_encode([
            'status'  => 'error',
            'message' => $message,
            'html'    => '
                <div class="alert alert-danger">
                    <small><b>Oops!</b> ' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</small>
                </div>
            '
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    function cleanMoney($value): int
    {
        if ($value === null) {
            return 0;
        }
        $value = trim((string)$value);
        if ($value === '') {
            return 0;
        }
        $value = preg_replace('/[^0-9]/', '', $value);
        return ($value === '') ? 0 : (int)$value;
    }

    function generateUuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    function getAkun(mysqli $Conn, int $id_perkiraan): ?array
    {
        $sql = "SELECT id_perkiraan, kode, nama, saldo_normal FROM akun_perkiraan WHERE id_perkiraan = ? LIMIT 1";
        $stmt = mysqli_prepare($Conn, $sql);
        if (!$stmt) {
            return null;
        }
        mysqli_stmt_bind_param($stmt, 'i', $id_perkiraan);
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return null;
        }
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $data ?: null;
    }

    function insertJurnal(
        mysqli $Conn,
        string $id_transaksi,
        string $tanggal,
        array $akun,
        string $dk,
        int $nilai,
        string $kategori_jurnal
    ): void {
        if ($nilai <= 0) {
            return;
        }
        if (!in_array($dk, ['D', 'K'], true)) {
            throw new Exception('Jenis jurnal Debet/Kredit tidak valid.');
        }
        if (empty($akun['kode']) || empty($akun['nama'])) {
            throw new Exception('Data akun jurnal tidak lengkap.');
        }

        $uuid_jurnal = generateUuidV4();
        
        // 8 tanda tanya (?) -> mapping: kategori(s), uuid(s), id_transaksi(s), tanggal(s), kode_perkiraan(s), nama_perkiraan(s), d_k(s), nilai(i)
        $sql = "
            INSERT INTO jurnal (
                kategori, uuid, id_transaksi, id_transaksi_jual_beli, id_transaksi_pembayaran,
                tanggal, kode_perkiraan, nama_perkiraan, d_k, nilai
            ) VALUES (?, ?, ?, NULL, NULL, ?, ?, ?, ?, ?)
        ";

        $stmt = mysqli_prepare($Conn, $sql);
        if (!$stmt) {
            throw new Exception('Gagal menyiapkan query jurnal: ' . mysqli_error($Conn));
        }

        $kode_perkiraan = $akun['kode'];
        $nama_perkiraan = $akun['nama'];

        // Tipe bind: 7 string ('sssssss') dan 1 integer ('i') = 'sssssssi' (pas 8 parameter)
        mysqli_stmt_bind_param(
            $stmt,
            'sssssssi',
            $kategori_jurnal,
            $uuid_jurnal,
            $id_transaksi,
            $tanggal,
            $kode_perkiraan,
            $nama_perkiraan,
            $dk,
            $nilai
        );

        if (!mysqli_stmt_execute($stmt)) {
            $error = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            throw new Exception('Gagal menyimpan jurnal: ' . $error);
        }
        mysqli_stmt_close($stmt);
    }

    // ============================================================
    // VALIDASI SESI & METHOD
    // ============================================================
    if (empty($SessionIdAkses)) {
        responseError('Sesi akses sudah berakhir. Silakan login ulang.');
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseError('Metode request tidak valid.');
    }

    // ============================================================
    // AMBIL & VALIDASI INPUT
    // ============================================================
    $tanggal = trim($_POST['tanggal'] ?? '');
    $jam = trim($_POST['jam'] ?? '');
    $id_transaksi_jenis = trim($_POST['id_transaksi_jenis'] ?? '');
    $JumlahTotal = $_POST['JumlahTotal'] ?? 0;
    $JumlahPembayaran = $_POST['JumlahPembayaran'] ?? 0;
    
    $keteranganInput = trim($_POST['keterangan'] ?? '');
    $keterangan = ($keteranganInput === '') ? null : $keteranganInput;

    if ($tanggal === '') responseError('Tanggal transaksi tidak boleh kosong.');
    if ($jam === '') responseError('Jam transaksi tidak boleh kosong.');
    if ($id_transaksi_jenis === '' || !ctype_digit($id_transaksi_jenis)) responseError('Jenis transaksi tidak valid.');

    $id_transaksi_jenis = (int)$id_transaksi_jenis;
    if ($id_transaksi_jenis <= 0) responseError('Jenis transaksi tidak valid.');

    $tanggal_transaksi = $tanggal . ' ' . $jam;
    $dateObject = DateTime::createFromFormat('Y-m-d H:i', $tanggal_transaksi) 
               ?: DateTime::createFromFormat('Y-m-d H:i:s', $tanggal_transaksi);

    if (!$dateObject) {
        responseError('Format tanggal atau jam tidak valid.');
    }

    $tanggal_transaksi = $dateObject->format('Y-m-d H:i:s');
    $tanggal_jurnal = $dateObject->format('Y-m-d');

    // ============================================================
    // AMBIL DATA JENIS TRANSAKSI & AKUN
    // ============================================================
    $sqlJenis = "SELECT id_transaksi_jenis, nama, kategori, id_akun_debet, id_akun_kredit, id_utang_piutang FROM transaksi_jenis WHERE id_transaksi_jenis = ? LIMIT 1";
    $stmtJenis = mysqli_prepare($Conn, $sqlJenis);
    if (!$stmtJenis) responseError('Gagal menyiapkan query jenis transaksi.');

    mysqli_stmt_bind_param($stmtJenis, 'i', $id_transaksi_jenis);
    if (!mysqli_stmt_execute($stmtJenis)) {
        mysqli_stmt_close($stmtJenis);
        responseError('Gagal mengambil data jenis transaksi.');
    }

    $resultJenis = mysqli_stmt_get_result($stmtJenis);
    $dataJenis = mysqli_fetch_assoc($resultJenis);
    mysqli_stmt_close($stmtJenis);

    if (!$dataJenis) responseError('Jenis transaksi tidak ditemukan.');

    $kategori = $dataJenis['kategori'] ?? '';
    $id_akun_debet = (int)($dataJenis['id_akun_debet'] ?? 0);
    $id_akun_kredit = (int)($dataJenis['id_akun_kredit'] ?? 0);
    $id_utang_piutang = (int)($dataJenis['id_utang_piutang'] ?? 0);

    $kode_awal = ($kategori === "Pengeluaran") ? "PNG" : "PMS";
    $id_transaksi = $kode_awal . "-" . GenerateKodeBarang(6);

    if (!in_array($kategori, ['Pengeluaran', 'Pemasukan'], true)) {
        responseError('Kategori jenis transaksi tidak valid.');
    }

    if ($id_akun_debet <= 0) responseError('Akun Debet belum diatur pada jenis transaksi.');
    if ($id_akun_kredit <= 0) responseError('Akun Kredit belum diatur pada jenis transaksi.');

    $akunDebet = getAkun($Conn, $id_akun_debet);
    if (!$akunDebet) responseError('Data akun Debet tidak ditemukan.');

    $akunKredit = getAkun($Conn, $id_akun_kredit);
    if (!$akunKredit) responseError('Data akun Kredit tidak ditemukan.');

    // ============================================================
    // PROSES RINCIAN & TOTAL
    // ============================================================
    $jumlahInput = cleanMoney($JumlahTotal);
    $pembayaran = cleanMoney($JumlahPembayaran);

    $uraianArray = $_POST['uraian'] ?? [];
    $hargaArray = $_POST['harga'] ?? [];
    $qtyArray = $_POST['qty'] ?? [];
    $satuanArray = $_POST['satuan'] ?? [];

    $jumlahBaris = max(count($uraianArray), count($hargaArray), count($qtyArray), count($satuanArray));
    $dataRincian = [];
    $totalRincian = 0;

    for ($i = 0; $i < $jumlahBaris; $i++) {
        $uraian = trim($uraianArray[$i] ?? '');
        $harga = cleanMoney($hargaArray[$i] ?? 0);
        $qty = cleanMoney($qtyArray[$i] ?? 0);
        $satuan = trim($satuanArray[$i] ?? '');

        if ($uraian === '' && $harga === 0 && $qty === 0 && $satuan === '') {
            continue;
        }
        if ($uraian === '') responseError('Uraian transaksi pada rincian tidak boleh kosong.');
        if ($harga <= 0) responseError('Harga rincian transaksi harus lebih dari 0.');
        if ($qty <= 0) responseError('Qty rincian transaksi harus lebih dari 0.');

        $sub_jumlah = $harga * $qty;
        $totalRincian += $sub_jumlah;

        $dataRincian[] = [
            'uraian' => $uraian,
            'harga' => $harga,
            'qty' => $qty,
            'satuan' => $satuan,
            'jumlah' => $sub_jumlah
        ];
    }

    $jumlah = (count($dataRincian) > 0) ? $totalRincian : $jumlahInput;
    if ($jumlah <= 0) responseError('Jumlah transaksi harus lebih dari 0.');
    if ($pembayaran < 0) responseError('Jumlah pembayaran tidak valid.');
    if ($pembayaran > $jumlah) responseError('Jumlah pembayaran tidak boleh melebihi total transaksi.');

    $sisa = $jumlah - $pembayaran;

    if ($sisa <= 0) {
        $status = 'Lunas';
    } else {
        $status = ($kategori === 'Pengeluaran') ? 'Utang' : 'Piutang';
    }

    $akunUtangPiutang = null;
    if ($sisa > 0) {
        if ($id_utang_piutang <= 0) responseError('Akun Utang/Piutang belum diatur pada jenis transaksi.');
        $akunUtangPiutang = getAkun($Conn, $id_utang_piutang);
        if (!$akunUtangPiutang) responseError('Data akun Utang/Piutang tidak ditemukan.');
    }

    // ============================================================
    // EKSEKUSI DATABASE TRANSACTION
    // ============================================================
    mysqli_begin_transaction($Conn);

    try {
        $now = date('Y-m-d H:i:s');

        // Insert Transaksi (13 parameter: sisiisssissis)
        $sqlTransaksi = "
            INSERT INTO transaksi (
                id_transaksi, id_transaksi_jenis, tanggal, jumlah, pembayaran, 
                keterangan, status, creat_at, creat_by_id, creat_by_name, 
                update_at, update_by_id, update_by_name
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmtTransaksi = mysqli_prepare($Conn, $sqlTransaksi);
        if (!$stmtTransaksi) {
            throw new Exception('Gagal menyiapkan query transaksi: ' . mysqli_error($Conn));
        }

        mysqli_stmt_bind_param(
            $stmtTransaksi,
            'sisiisssissis',
            $id_transaksi,
            $id_transaksi_jenis,
            $tanggal_transaksi,
            $jumlah,
            $pembayaran,
            $keterangan,
            $status,
            $now,
            $SessionIdAkses,
            $SessionNama,
            $now,
            $SessionIdAkses,
            $SessionNama
        );

        if (!mysqli_stmt_execute($stmtTransaksi)) {
            $error = mysqli_stmt_error($stmtTransaksi);
            mysqli_stmt_close($stmtTransaksi);
            throw new Exception('Gagal menyimpan transaksi: ' . $error);
        }
        mysqli_stmt_close($stmtTransaksi);

        // Insert Rincian Transaksi
        if (count($dataRincian) > 0) {
            $sqlRincian = "INSERT INTO transaksi_rincian (id_transaksi, rincian_transaksi, harga, qty, satuan, jumlah) VALUES (?, ?, ?, ?, ?, ?)";
            $stmtRincian = mysqli_prepare($Conn, $sqlRincian);
            if (!$stmtRincian) {
                throw new Exception('Gagal menyiapkan query rincian transaksi: ' . mysqli_error($Conn));
            }

            foreach ($dataRincian as $rincian) {
                mysqli_stmt_bind_param(
                    $stmtRincian,
                    'ssiisi',
                    $id_transaksi,
                    $rincian['uraian'],
                    $rincian['harga'],
                    $rincian['qty'],
                    $rincian['satuan'],
                    $rincian['jumlah']
                );
                if (!mysqli_stmt_execute($stmtRincian)) {
                    $error = mysqli_stmt_error($stmtRincian);
                    mysqli_stmt_close($stmtRincian);
                    throw new Exception('Gagal menyimpan rincian transaksi: ' . $error);
                }
            }
            mysqli_stmt_close($stmtRincian);
        }

        // Insert Jurnal Berdasarkan Kategori
        if ($kategori === 'Pemasukan') {
            if ($pembayaran > 0) {
                insertJurnal($Conn, $id_transaksi, $tanggal_jurnal, $akunDebet, 'D', $pembayaran, $kategori);
            }
            if ($sisa > 0) {
                insertJurnal($Conn, $id_transaksi, $tanggal_jurnal, $akunUtangPiutang, 'D', $sisa, $kategori);
            }
            insertJurnal($Conn, $id_transaksi, $tanggal_jurnal, $akunKredit, 'K', $jumlah, $kategori);
        } elseif ($kategori === 'Pengeluaran') {
            insertJurnal($Conn, $id_transaksi, $tanggal_jurnal, $akunDebet, 'D', $jumlah, $kategori);
            if ($pembayaran > 0) {
                insertJurnal($Conn, $id_transaksi, $tanggal_jurnal, $akunKredit, 'K', $pembayaran, $kategori);
            }
            if ($sisa > 0) {
                insertJurnal($Conn, $id_transaksi, $tanggal_jurnal, $akunUtangPiutang, 'K', $sisa, $kategori);
            }
        }

        // Validasi Balance Jurnal
        $totalDebet = ($kategori === 'Pemasukan') ? ($pembayaran + $sisa) : $jumlah;
        $totalKredit = ($kategori === 'Pemasukan') ? $jumlah : ($pembayaran + $sisa);

        if ($totalDebet !== $totalKredit) {
            throw new Exception("Jurnal tidak balance. Debet: {$totalDebet}, Kredit: {$totalKredit}");
        }

        mysqli_commit($Conn);
        $_SESSION['NotifikasiSwal'] = 'Tambah Transaksi Berhasil';

        echo json_encode([
            'status'             => 'success',
            'message'            => 'Transaksi berhasil disimpan.',
            'id_transaksi'       => $id_transaksi,
            'kategori'           => $kategori,
            'jumlah'             => $jumlah,
            'pembayaran'         => $pembayaran,
            'sisa'               => $sisa,
            'status_transaksi'   => $status,
            'html'               => '<small class="text-success" id="NotifikasiTambahTransaksiBerhasil">Success</small>'
        ], JSON_UNESCAPED_UNICODE);
        exit;

    } catch (Throwable $e) {
        mysqli_rollback($Conn);
        responseError($e->getMessage());
    }
?>