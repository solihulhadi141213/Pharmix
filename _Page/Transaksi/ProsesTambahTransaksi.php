<?php

    // ============================================================
    // KONFIGURASI
    // ============================================================
    date_default_timezone_set('Asia/Jakarta');

    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    include "../../_Config/FungsiAkses.php";


    // ============================================================
    // HEADER JSON
    // ============================================================
    header('Content-Type: application/json; charset=utf-8');

    // Time Zone
    date_default_timezone_set('Asia/Jakarta');

    // Time Now Tmp
    $now = date('Y-m-d H:i:s');


    // ============================================================
    // RESPONSE DEFAULT
    // ============================================================
    $response = [
        'status'  => 'error',
        'message' => 'Terjadi kesalahan.',
        'html'    => ''
    ];


    // ============================================================
    // FUNGSI RESPONSE
    // ============================================================
    function responseError($message){
        global $response;

        $response = [
            'status'  => 'error',
            'message' => $message,
            'html'    => '
                <div class="alert alert-danger">
                    <small>
                        <b>Oops!</b> ' .
                        htmlspecialchars(
                            $message,
                            ENT_QUOTES,
                            'UTF-8'
                        ) . '
                    </small>
                </div>
            '
        ];

        echo json_encode(
            $response,
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }


    // ============================================================
    // VALIDASI SESSION
    // ============================================================
    if (empty($SessionIdAkses)) {

        responseError(
            'Sesi akses sudah berakhir. Silakan login ulang.'
        );
    }


    // ============================================================
    // VALIDASI METHOD
    // ============================================================
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        responseError(
            'Metode request tidak valid.'
        );
    }


    // ============================================================
    // AMBIL DATA UTAMA
    // ============================================================
    $tanggal =
        trim($_POST['tanggal'] ?? '');

    $jam =
        trim($_POST['jam'] ?? '');

    $id_transaksi_jenis =
        trim($_POST['id_transaksi_jenis'] ?? '');

    $status =
        trim($_POST['status'] ?? 'Lunas');

    $JumlahTotal =
        trim($_POST['JumlahTotal'] ?? '0');

    $JumlahPembayaran =
        trim($_POST['JumlahPembayaran'] ?? '0');

    $keterangan =
        trim($_POST['keterangan'] ?? '');


    // ============================================================
    // VALIDASI DATA UTAMA
    // ============================================================
    if ($tanggal === '') {

        responseError(
            'Tanggal transaksi tidak boleh kosong.'
        );
    }


    if ($jam === '') {

        responseError(
            'Jam transaksi tidak boleh kosong.'
        );
    }


    if ($id_transaksi_jenis === '') {

        responseError(
            'Jenis transaksi tidak boleh kosong.'
        );
    }


    // ============================================================
    // VALIDASI ID TRANSAKSI JENIS
    // ============================================================
    if (!ctype_digit($id_transaksi_jenis)) {

        responseError(
            'ID jenis transaksi tidak valid.'
        );
    }

    $id_transaksi_jenis =
        (int) $id_transaksi_jenis;


    if ($id_transaksi_jenis <= 0) {

        responseError(
            'Jenis transaksi tidak valid.'
        );
    }


    // ============================================================
    // VALIDASI STATUS
    // ============================================================
    $allowedStatus = [
        'Lunas',
        'Utang',
        'Piutang'
    ];


    if (!in_array($status, $allowedStatus, true)) {

        responseError(
            'Status transaksi tidak valid.'
        );
    }


    // ============================================================
    // BERSIHKAN FORMAT UANG
    //
    // Contoh:
    // 1.500.000 -> 1500000
    // 018.000   -> 18000
    // Rp 1.500.000 -> 1500000
    // ============================================================
    function cleanMoney($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }

        // Hilangkan semua karakter selain angka
        $value = preg_replace(
            '/[^0-9]/',
            '',
            (string) $value
        );

        if ($value === '' || $value === null) {
            return 0;
        }

        return (int) $value;
    }


    $jumlah =
        cleanMoney($JumlahTotal);


    $pembayaran =
        cleanMoney($JumlahPembayaran);


    // ============================================================
    // VALIDASI NILAI
    // ============================================================
    if ($jumlah < 0) {

        responseError(
            'Jumlah transaksi tidak valid.'
        );
    }


    if ($pembayaran < 0) {

        responseError(
            'Jumlah pembayaran tidak valid.'
        );
    }


    // ============================================================
    // TANGGAL TRANSAKSI
    // ============================================================
    $tanggal_transaksi =
        $tanggal . ' ' . $jam;


    // Validasi tanggal
    $timestamp =
        strtotime($tanggal_transaksi);


    if ($timestamp === false) {

        responseError(
            'Format tanggal atau jam transaksi tidak valid.'
        );
    }


    // Normalisasi tanggal
    $tanggal_transaksi =
        date(
            'Y-m-d H:i:s',
            $timestamp
        );


    // Tanggal untuk jurnal
    $tanggal_jurnal =
        date(
            'Y-m-d',
            $timestamp
        );


    // ============================================================
    // AMBIL DATA JENIS TRANSAKSI + AKUN
    // ============================================================
    $sqlJenis = "

        SELECT

            tj.id_transaksi_jenis,
            tj.nama,
            tj.kategori,
            tj.id_akun_debet,
            tj.id_akun_kredit,

            ad.kode AS kode_akun_debet,
            ad.nama AS nama_akun_debet,

            ak.kode AS kode_akun_kredit,
            ak.nama AS nama_akun_kredit

        FROM transaksi_jenis AS tj

        LEFT JOIN akun_perkiraan AS ad
            ON ad.id_perkiraan = tj.id_akun_debet

        LEFT JOIN akun_perkiraan AS ak
            ON ak.id_perkiraan = tj.id_akun_kredit

        WHERE tj.id_transaksi_jenis = ?

        LIMIT 1

    ";


    $stmtJenis =
        mysqli_prepare(
            $Conn,
            $sqlJenis
        );


    if (!$stmtJenis) {

        responseError(
            'Gagal menyiapkan query jenis transaksi.'
        );
    }


    mysqli_stmt_bind_param(
        $stmtJenis,
        'i',
        $id_transaksi_jenis
    );


    if (!mysqli_stmt_execute($stmtJenis)) {

        mysqli_stmt_close($stmtJenis);

        responseError(
            'Gagal mengambil data jenis transaksi.'
        );
    }


    $resultJenis =
        mysqli_stmt_get_result(
            $stmtJenis
        );


    if (
        !$resultJenis ||
        mysqli_num_rows($resultJenis) === 0
    ) {

        mysqli_stmt_close($stmtJenis);

        responseError(
            'Jenis transaksi tidak ditemukan.'
        );
    }


    $dataJenis =
        mysqli_fetch_assoc(
            $resultJenis
        );


    mysqli_stmt_close($stmtJenis);


    // ============================================================
    // DATA JENIS TRANSAKSI
    // ============================================================
    $nama =
        $dataJenis['nama'] ?? '';

    $kategori =
        $dataJenis['kategori'] ?? '';

    $id_akun_debet =
        $dataJenis['id_akun_debet'] ?? null;

    $id_akun_kredit =
        $dataJenis['id_akun_kredit'] ?? null;

    $KodeAkunDebet =
        $dataJenis['kode_akun_debet'] ?? '';

    $AkunDebet =
        $dataJenis['nama_akun_debet'] ?? '';

    $KodeAkunKredit =
        $dataJenis['kode_akun_kredit'] ?? '';

    $AkunKredit =
        $dataJenis['nama_akun_kredit'] ?? '';


    // ============================================================
    // VALIDASI KATEGORI
    // ============================================================
    if (
        $kategori !== 'Pengeluaran' &&
        $kategori !== 'Pemasukan'
    ) {

        responseError(
            'Kategori transaksi tidak valid.'
        );
    }


    // ============================================================
    // VALIDASI AKUN DEBET
    // ============================================================
    if (
        empty($id_akun_debet) ||
        empty($KodeAkunDebet) ||
        empty($AkunDebet)
    ) {

        responseError(
            'Akun Debet pada pengaturan jenis transaksi belum valid.'
        );
    }


    // ============================================================
    // VALIDASI AKUN KREDIT
    // ============================================================
    if (
        empty($id_akun_kredit) ||
        empty($KodeAkunKredit) ||
        empty($AkunKredit)
    ) {

        responseError(
            'Akun Kredit pada pengaturan jenis transaksi belum valid.'
        );
    }


    // ============================================================
    // MULAI DATABASE TRANSACTION
    // ============================================================
    mysqli_begin_transaction($Conn);


    try {

        // ========================================================
        // INSERT TRANSAKSI
        // ========================================================
        $sqlTransaksi = "

            INSERT INTO transaksi (
                id_transaksi_jenis,
                tanggal,
                jumlah,
                pembayaran,
                keterangan,
                status,
                creat_at,
                creat_by_id,
                creat_by_name,
                update_at,
                update_by_id,
                update_by_name
            )

            VALUES (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            )

        ";


        $stmtTransaksi =
            mysqli_prepare(
                $Conn,
                $sqlTransaksi
            );


        if (!$stmtTransaksi) {

            throw new Exception(
                'Gagal menyiapkan penyimpanan transaksi.'
            );
        }


        mysqli_stmt_bind_param(
            $stmtTransaksi,
            'isiisssissis',
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

            $error =
                mysqli_stmt_error($stmtTransaksi);

            mysqli_stmt_close($stmtTransaksi);

            throw new Exception(
                'Gagal menyimpan transaksi: ' . $error
            );
        }


        // ========================================================
        // AMBIL ID TRANSAKSI
        // ========================================================
        $id_transaksi =
            mysqli_insert_id($Conn);


        mysqli_stmt_close($stmtTransaksi);


        if ($id_transaksi <= 0) {

            throw new Exception(
                'ID transaksi gagal diperoleh.'
            );
        }


        // ========================================================
        // INSERT RINCIAN
        // ========================================================
        $jumlahUraian =
            isset($_POST['uraian']) &&
            is_array($_POST['uraian'])
                ? count($_POST['uraian'])
                : 0;


        $jumlahRincianBerhasil = 0;


        if ($jumlahUraian > 0) {

            // ----------------------------------------------------
            // Siapkan query rincian
            // ----------------------------------------------------
            $sqlRincian = "

                INSERT INTO transaksi_rincian (
                    id_transaksi,
                    rincian_transaksi,
                    harga,
                    qty,
                    satuan,
                    jumlah
                )

                VALUES (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                )

            ";


            $stmtRincian =
                mysqli_prepare(
                    $Conn,
                    $sqlRincian
                );


            if (!$stmtRincian) {

                throw new Exception(
                    'Gagal menyiapkan penyimpanan rincian transaksi.'
                );
            }


            for (
                $i = 0;
                $i < $jumlahUraian;
                $i++
            ) {

                // ------------------------------------------------
                // Uraian
                // ------------------------------------------------
                $uraian =
                    trim(
                        $_POST['uraian'][$i] ?? ''
                    );


                // ------------------------------------------------
                // Harga
                // ------------------------------------------------
                $harga =
                    cleanMoney(
                        $_POST['harga'][$i] ?? 0
                    );


                // ------------------------------------------------
                // Qty
                // ------------------------------------------------
                $qty =
                    cleanMoney(
                        $_POST['qty'][$i] ?? 0
                    );


                // ------------------------------------------------
                // Satuan
                // ------------------------------------------------
                $satuan =
                    trim(
                        $_POST['satuan'][$i] ?? ''
                    );


                // ------------------------------------------------
                // Jumlah
                // ------------------------------------------------
                $jumlah_rincian =
                    cleanMoney(
                        $_POST['jumlah'][$i] ?? 0
                    );


                // ------------------------------------------------
                // Abaikan baris yang benar-benar kosong
                // ------------------------------------------------
                if (
                    $uraian === '' &&
                    $harga === 0 &&
                    $qty === 0 &&
                    $satuan === '' &&
                    $jumlah_rincian === 0
                ) {

                    continue;
                }


                // ------------------------------------------------
                // Hitung ulang jumlah di SERVER
                //
                // Jangan mempercayai nilai jumlah[] dari browser.
                // ------------------------------------------------
                $jumlah_rincian =
                    $harga * $qty;


                // ------------------------------------------------
                // Bind
                // ------------------------------------------------
                mysqli_stmt_bind_param(
                    $stmtRincian,
                    'isiisi',
                    $id_transaksi,
                    $uraian,
                    $harga,
                    $qty,
                    $satuan,
                    $jumlah_rincian
                );


                // ------------------------------------------------
                // Execute
                // ------------------------------------------------
                if (
                    !mysqli_stmt_execute(
                        $stmtRincian
                    )
                ) {

                    $error =
                        mysqli_stmt_error(
                            $stmtRincian
                        );

                    mysqli_stmt_close(
                        $stmtRincian
                    );

                    throw new Exception(
                        'Gagal menyimpan rincian transaksi: ' .
                        $error
                    );
                }


                $jumlahRincianBerhasil++;
            }


            mysqli_stmt_close(
                $stmtRincian
            );
        }


        // ========================================================
        // VALIDASI TOTAL RINCIAN
        //
        // Jika ada baris yang dikirim tetapi gagal diproses,
        // transaksi dibatalkan.
        // ========================================================
        if (
            $jumlahUraian > 0 &&
            $jumlahRincianBerhasil === 0
        ) {

            throw new Exception(
                'Tidak ada rincian transaksi yang valid.'
            );
        }


        // ========================================================
        // UUID UNTUK GROUP JURNAL
        //
        // Tabel transaksi tidak membutuhkan UUID.
        // UUID ini hanya digunakan sebagai identifier kelompok
        // jurnal Debet + Kredit.
        // ========================================================
        $uuid_jurnal =
            sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff)
            );


        // ========================================================
        // INSERT JURNAL DEBET
        // ========================================================
        $sqlJurnalDebet = "

            INSERT INTO jurnal (
                kategori,
                uuid,
                id_transaksi,
                tanggal,
                kode_perkiraan,
                nama_perkiraan,
                d_k,
                nilai
            )

            VALUES (
                'Transaksi',
                ?,
                ?,
                ?,
                ?,
                ?,
                'D',
                ?
            )

        ";


        $stmtJurnalDebet =
            mysqli_prepare(
                $Conn,
                $sqlJurnalDebet
            );


        if (!$stmtJurnalDebet) {

            throw new Exception(
                'Gagal menyiapkan jurnal Debet.'
            );
        }


        mysqli_stmt_bind_param(
            $stmtJurnalDebet,
            'sisssi',
            $uuid_jurnal,
            $id_transaksi,
            $tanggal_jurnal,
            $KodeAkunDebet,
            $AkunDebet,
            $jumlah
        );


        if (
            !mysqli_stmt_execute(
                $stmtJurnalDebet
            )
        ) {

            $error =
                mysqli_stmt_error(
                    $stmtJurnalDebet
                );

            mysqli_stmt_close(
                $stmtJurnalDebet
            );

            throw new Exception(
                'Gagal menyimpan jurnal Debet: ' .
                $error
            );
        }


        mysqli_stmt_close(
            $stmtJurnalDebet
        );


        // ========================================================
        // INSERT JURNAL KREDIT
        // ========================================================
        $sqlJurnalKredit = "

            INSERT INTO jurnal (
                kategori,
                uuid,
                id_transaksi,
                tanggal,
                kode_perkiraan,
                nama_perkiraan,
                d_k,
                nilai
            )

            VALUES (
                'Transaksi',
                ?,
                ?,
                ?,
                ?,
                ?,
                'K',
                ?
            )

        ";


        $stmtJurnalKredit =
            mysqli_prepare(
                $Conn,
                $sqlJurnalKredit
            );


        if (!$stmtJurnalKredit) {

            throw new Exception(
                'Gagal menyiapkan jurnal Kredit.'
            );
        }


        mysqli_stmt_bind_param(
            $stmtJurnalKredit,
            'sisssi',
            $uuid_jurnal,
            $id_transaksi,
            $tanggal_jurnal,
            $KodeAkunKredit,
            $AkunKredit,
            $jumlah
        );


        if (
            !mysqli_stmt_execute(
                $stmtJurnalKredit
            )
        ) {

            $error =
                mysqli_stmt_error(
                    $stmtJurnalKredit
                );

            mysqli_stmt_close(
                $stmtJurnalKredit
            );

            throw new Exception(
                'Gagal menyimpan jurnal Kredit: ' .
                $error
            );
        }


        mysqli_stmt_close(
            $stmtJurnalKredit
        );


        // ========================================================
        // COMMIT
        // ========================================================
        mysqli_commit($Conn);


        // ========================================================
        // NOTIFIKASI
        // ========================================================
        $_SESSION['NotifikasiSwal'] =
            'Tambah Transaksi Berhasil';


        // ========================================================
        // RESPONSE SUCCESS
        // ========================================================
        $response = [

            'status'  => 'success',

            'message' =>
                'Transaksi berhasil disimpan.',

            'html' => '
                <small
                    class="text-success"
                    id="NotifikasiTambahTransaksiBerhasil"
                >
                    Success
                </small>
            ',

            'id_transaksi' =>
                $id_transaksi

        ];


        echo json_encode(
            $response,
            JSON_UNESCAPED_UNICODE
        );

        exit;


    } catch (Throwable $e) {

        // ========================================================
        // ROLLBACK
        // ========================================================
        mysqli_rollback($Conn);


        // ========================================================
        // RESPONSE ERROR
        // ========================================================
        responseError(
            $e->getMessage()
        );
    }

?>