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
// HEADER RESPONSE
// ============================================================
header('Content-Type: application/json; charset=utf-8');


// ============================================================
// RESPONSE ERROR
// ============================================================
function responseError(string $message): void
{
    echo json_encode([
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
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ============================================================
// BERSIHKAN NOMINAL
// ============================================================
function cleanMoney($value): int
{
    if ($value === null) {
        return 0;
    }

    $value = trim((string)$value);

    if ($value === '') {
        return 0;
    }

    // Menghapus Rp, titik, koma, spasi dan karakter lain
    $value = preg_replace('/[^0-9]/', '', $value);

    if ($value === '') {
        return 0;
    }

    return (int)$value;
}


// ============================================================
// GENERATE UUID V4
// ============================================================
function generateUuidV4(): string
{
    $data = random_bytes(16);

    // UUID Version 4
    $data[6] = chr(
        (ord($data[6]) & 0x0f) | 0x40
    );

    // RFC 4122 Variant
    $data[8] = chr(
        (ord($data[8]) & 0x3f) | 0x80
    );

    return vsprintf(
        '%s%s-%s-%s-%s-%s%s%s',
        str_split(bin2hex($data), 4)
    );
}


// ============================================================
// AMBIL DATA AKUN PERKIRAAN
// ============================================================
function getAkun(
    mysqli $Conn,
    int $id_perkiraan
): ?array {

    $sql = "
        SELECT
            id_perkiraan,
            kode,
            nama,
            saldo_normal
        FROM akun_perkiraan
        WHERE id_perkiraan = ?
        LIMIT 1
    ";

    $stmt = mysqli_prepare(
        $Conn,
        $sql
    );

    if (!$stmt) {
        return null;
    }

    mysqli_stmt_bind_param(
        $stmt,
        'i',
        $id_perkiraan
    );

    if (!mysqli_stmt_execute($stmt)) {

        mysqli_stmt_close($stmt);

        return null;
    }

    $result = mysqli_stmt_get_result(
        $stmt
    );

    $data = mysqli_fetch_assoc(
        $result
    );

    mysqli_stmt_close(
        $stmt
    );

    return $data ?: null;
}


// ============================================================
// INSERT JURNAL
// ============================================================
function insertJurnal(
    mysqli $Conn,
    string $uuid,
    int $id_transaksi,
    string $tanggal,
    array $akun,
    string $dk,
    int $nilai
): void {

    // Tidak menyimpan jurnal dengan nominal 0
    if ($nilai <= 0) {
        return;
    }

    // Validasi D/K
    if (
        !in_array(
            $dk,
            ['D', 'K'],
            true
        )
    ) {
        throw new Exception(
            'Jenis jurnal Debet/Kredit tidak valid.'
        );
    }

    // Validasi data akun
    if (
        empty($akun['kode']) ||
        empty($akun['nama'])
    ) {
        throw new Exception(
            'Data akun jurnal tidak lengkap.'
        );
    }


    $sql = "
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
            ?,
            ?
        )
    ";


    $stmt = mysqli_prepare(
        $Conn,
        $sql
    );


    if (!$stmt) {
        throw new Exception(
            'Gagal menyiapkan query jurnal: ' .
            mysqli_error($Conn)
        );
    }


    $kode_perkiraan =
        $akun['kode'];

    $nama_perkiraan =
        $akun['nama'];


    mysqli_stmt_bind_param(
        $stmt,
        'sissssi',
        $uuid,
        $id_transaksi,
        $tanggal,
        $kode_perkiraan,
        $nama_perkiraan,
        $dk,
        $nilai
    );


    if (
        !mysqli_stmt_execute(
            $stmt
        )
    ) {

        $error =
            mysqli_stmt_error(
                $stmt
            );

        mysqli_stmt_close(
            $stmt
        );

        throw new Exception(
            'Gagal menyimpan jurnal: ' .
            $error
        );
    }


    mysqli_stmt_close(
        $stmt
    );
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
// AMBIL INPUT
// ============================================================
$tanggal = trim(
    $_POST['tanggal'] ?? ''
);

$jam = trim(
    $_POST['jam'] ?? ''
);

$id_transaksi_jenis = trim(
    $_POST['id_transaksi_jenis'] ?? ''
);

$JumlahTotal =
    $_POST['JumlahTotal'] ?? 0;

$JumlahPembayaran =
    $_POST['JumlahPembayaran'] ?? 0;

$keterangan = trim(
    $_POST['keterangan'] ?? ''
);


// ============================================================
// VALIDASI TANGGAL
// ============================================================
if ($tanggal === '') {

    responseError(
        'Tanggal transaksi tidak boleh kosong.'
    );
}


// ============================================================
// VALIDASI JAM
// ============================================================
if ($jam === '') {

    responseError(
        'Jam transaksi tidak boleh kosong.'
    );
}


// ============================================================
// VALIDASI ID JENIS TRANSAKSI
// ============================================================
if (
    $id_transaksi_jenis === '' ||
    !ctype_digit($id_transaksi_jenis)
) {

    responseError(
        'Jenis transaksi tidak valid.'
    );
}


$id_transaksi_jenis =
    (int)$id_transaksi_jenis;


if ($id_transaksi_jenis <= 0) {

    responseError(
        'Jenis transaksi tidak valid.'
    );
}


// ============================================================
// VALIDASI DATETIME
// ============================================================
$tanggal_transaksi =
    $tanggal . ' ' . $jam;


// Format H:i
$dateObject =
    DateTime::createFromFormat(
        'Y-m-d H:i',
        $tanggal_transaksi
    );


// Jika gagal coba H:i:s
if (!$dateObject) {

    $dateObject =
        DateTime::createFromFormat(
            'Y-m-d H:i:s',
            $tanggal_transaksi
        );
}


if (!$dateObject) {

    responseError(
        'Format tanggal atau jam tidak valid.'
    );
}


$tanggal_transaksi =
    $dateObject->format(
        'Y-m-d H:i:s'
    );


$tanggal_jurnal =
    $dateObject->format(
        'Y-m-d'
    );


// ============================================================
// AMBIL DATA JENIS TRANSAKSI
// ============================================================
$sqlJenis = "
    SELECT
        id_transaksi_jenis,
        nama,
        kategori,
        id_akun_debet,
        id_akun_kredit,
        id_utang_piutang
    FROM transaksi_jenis
    WHERE id_transaksi_jenis = ?
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


if (
    !mysqli_stmt_execute(
        $stmtJenis
    )
) {

    mysqli_stmt_close(
        $stmtJenis
    );

    responseError(
        'Gagal mengambil data jenis transaksi.'
    );
}


$resultJenis =
    mysqli_stmt_get_result(
        $stmtJenis
    );


$dataJenis =
    mysqli_fetch_assoc(
        $resultJenis
    );


mysqli_stmt_close(
    $stmtJenis
);


if (!$dataJenis) {

    responseError(
        'Jenis transaksi tidak ditemukan.'
    );
}


// ============================================================
// DATA JENIS TRANSAKSI
// ============================================================
$kategori =
    $dataJenis['kategori'] ?? '';


$id_akun_debet =
    !empty($dataJenis['id_akun_debet'])
        ? (int)$dataJenis['id_akun_debet']
        : 0;


$id_akun_kredit =
    !empty($dataJenis['id_akun_kredit'])
        ? (int)$dataJenis['id_akun_kredit']
        : 0;


$id_utang_piutang =
    !empty($dataJenis['id_utang_piutang'])
        ? (int)$dataJenis['id_utang_piutang']
        : 0;


// ============================================================
// VALIDASI KATEGORI
// ============================================================
if (
    !in_array(
        $kategori,
        [
            'Pengeluaran',
            'Pemasukan'
        ],
        true
    )
) {

    responseError(
        'Kategori jenis transaksi tidak valid.'
    );
}


// ============================================================
// VALIDASI AKUN DEBET
// ============================================================
if ($id_akun_debet <= 0) {

    responseError(
        'Akun Debet belum diatur pada jenis transaksi.'
    );
}


// ============================================================
// VALIDASI AKUN KREDIT
// ============================================================
if ($id_akun_kredit <= 0) {

    responseError(
        'Akun Kredit belum diatur pada jenis transaksi.'
    );
}


// ============================================================
// AMBIL DATA AKUN DEBET
// ============================================================
$akunDebet =
    getAkun(
        $Conn,
        $id_akun_debet
    );


if (!$akunDebet) {

    responseError(
        'Data akun Debet tidak ditemukan.'
    );
}


// ============================================================
// AMBIL DATA AKUN KREDIT
// ============================================================
$akunKredit =
    getAkun(
        $Conn,
        $id_akun_kredit
    );


if (!$akunKredit) {

    responseError(
        'Data akun Kredit tidak ditemukan.'
    );
}


// ============================================================
// NOMINAL INPUT
// ============================================================
$jumlahInput =
    cleanMoney(
        $JumlahTotal
    );


$pembayaran =
    cleanMoney(
        $JumlahPembayaran
    );


// ============================================================
// VALIDASI ARRAY RINCIAN
// ============================================================
$uraianArray =
    isset($_POST['uraian']) &&
    is_array($_POST['uraian'])
        ? $_POST['uraian']
        : [];


$hargaArray =
    isset($_POST['harga']) &&
    is_array($_POST['harga'])
        ? $_POST['harga']
        : [];


$qtyArray =
    isset($_POST['qty']) &&
    is_array($_POST['qty'])
        ? $_POST['qty']
        : [];


$satuanArray =
    isset($_POST['satuan']) &&
    is_array($_POST['satuan'])
        ? $_POST['satuan']
        : [];


// ============================================================
// JUMLAH BARIS
// ============================================================
$jumlahBaris =
    max(
        count($uraianArray),
        count($hargaArray),
        count($qtyArray),
        count($satuanArray)
    );


// ============================================================
// PROSES RINCIAN
// ============================================================
$dataRincian = [];

$totalRincian = 0;


for (
    $i = 0;
    $i < $jumlahBaris;
    $i++
) {

    $uraian =
        trim(
            $uraianArray[$i] ?? ''
        );


    $harga =
        cleanMoney(
            $hargaArray[$i] ?? 0
        );


    $qty =
        cleanMoney(
            $qtyArray[$i] ?? 0
        );


    $satuan =
        trim(
            $satuanArray[$i] ?? ''
        );


    // --------------------------------------------------------
    // LEWATI BARIS KOSONG
    // --------------------------------------------------------
    if (
        $uraian === '' &&
        $harga === 0 &&
        $qty === 0 &&
        $satuan === ''
    ) {
        continue;
    }


    // --------------------------------------------------------
    // VALIDASI URAIAN
    // --------------------------------------------------------
    if ($uraian === '') {

        responseError(
            'Uraian transaksi pada rincian tidak boleh kosong.'
        );
    }


    // --------------------------------------------------------
    // VALIDASI HARGA
    // --------------------------------------------------------
    if ($harga <= 0) {

        responseError(
            'Harga rincian transaksi harus lebih dari 0.'
        );
    }


    // --------------------------------------------------------
    // VALIDASI QTY
    // --------------------------------------------------------
    if ($qty <= 0) {

        responseError(
            'Qty rincian transaksi harus lebih dari 0.'
        );
    }


    // --------------------------------------------------------
    // HITUNG JUMLAH RINCIAN
    // --------------------------------------------------------
    $jumlah_rincian =
        $harga * $qty;


    $totalRincian +=
        $jumlah_rincian;


    $dataRincian[] = [
        'uraian' => $uraian,
        'harga' => $harga,
        'qty' => $qty,
        'satuan' => $satuan,
        'jumlah' => $jumlah_rincian
    ];
}


// ============================================================
// TENTUKAN TOTAL TRANSAKSI
// ============================================================
if (
    count($dataRincian) > 0
) {

    $jumlah =
        $totalRincian;

} else {

    $jumlah =
        $jumlahInput;
}


// ============================================================
// VALIDASI TOTAL TRANSAKSI
// ============================================================
if ($jumlah <= 0) {

    responseError(
        'Jumlah transaksi harus lebih dari 0.'
    );
}


// ============================================================
// VALIDASI PEMBAYARAN
// ============================================================
if ($pembayaran < 0) {

    responseError(
        'Jumlah pembayaran tidak valid.'
    );
}


if ($pembayaran > $jumlah) {

    responseError(
        'Jumlah pembayaran tidak boleh melebihi total transaksi.'
    );
}


// ============================================================
// HITUNG SISA
// ============================================================
$sisa =
    $jumlah - $pembayaran;


// ============================================================
// TENTUKAN STATUS OTOMATIS
// ============================================================
if ($sisa <= 0) {

    $status = 'Lunas';

} else {

    if ($kategori === 'Pengeluaran') {

        $status = 'Utang';

    } elseif ($kategori === 'Pemasukan') {

        $status = 'Piutang';

    } else {

        responseError(
            'Kategori transaksi tidak valid.'
        );
    }
}


// ============================================================
// VALIDASI DAN AMBIL AKUN UTANG / PIUTANG
// ============================================================
$akunUtangPiutang = null;


if ($sisa > 0) {

    if ($id_utang_piutang <= 0) {

        responseError(
            'Akun Utang/Piutang belum diatur pada jenis transaksi.'
        );
    }


    $akunUtangPiutang =
        getAkun(
            $Conn,
            $id_utang_piutang
        );


    if (!$akunUtangPiutang) {

        responseError(
            'Data akun Utang/Piutang tidak ditemukan.'
        );
    }
}


// ============================================================
// MULAI DATABASE TRANSACTION
// ============================================================
mysqli_begin_transaction(
    $Conn
);


try {

    // ========================================================
    // WAKTU SEKARANG
    // ========================================================
    $now =
        date(
            'Y-m-d H:i:s'
        );


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
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
    ";


    $stmtTransaksi =
        mysqli_prepare(
            $Conn,
            $sqlTransaksi
        );


    if (!$stmtTransaksi) {

        throw new Exception(
            'Gagal menyiapkan query transaksi: ' .
            mysqli_error($Conn)
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


    if (
        !mysqli_stmt_execute(
            $stmtTransaksi
        )
    ) {

        $error =
            mysqli_stmt_error(
                $stmtTransaksi
            );

        mysqli_stmt_close(
            $stmtTransaksi
        );

        throw new Exception(
            'Gagal menyimpan transaksi: ' .
            $error
        );
    }


    $id_transaksi =
        mysqli_insert_id(
            $Conn
        );


    mysqli_stmt_close(
        $stmtTransaksi
    );


    if ($id_transaksi <= 0) {

        throw new Exception(
            'ID transaksi gagal diperoleh.'
        );
    }


    // ========================================================
    // INSERT RINCIAN TRANSAKSI
    // ========================================================
    if (
        count($dataRincian) > 0
    ) {

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
                ?, ?, ?, ?, ?, ?
            )
        ";


        $stmtRincian =
            mysqli_prepare(
                $Conn,
                $sqlRincian
            );


        if (!$stmtRincian) {

            throw new Exception(
                'Gagal menyiapkan query rincian transaksi: ' .
                mysqli_error($Conn)
            );
        }


        foreach (
            $dataRincian as $rincian
        ) {

            $uraian =
                $rincian['uraian'];

            $harga =
                $rincian['harga'];

            $qty =
                $rincian['qty'];

            $satuan =
                $rincian['satuan'];

            $jumlah_rincian =
                $rincian['jumlah'];


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
        }


        mysqli_stmt_close(
            $stmtRincian
        );
    }


    // ========================================================
    // GENERATE UUID JURNAL
    // ========================================================
    $uuid_jurnal =
        generateUuidV4();


    // ========================================================
    // JURNAL PEMASUKAN
    // ========================================================
    if ($kategori === 'Pemasukan') {

        /*
        ========================================================
        CONTOH
        ========================================================

        Total       : 1.000
        Pembayaran  :   200
        Piutang     :   800

        JURNAL:

        Debet Kas       : 200
        Debet Piutang   : 800
        Kredit Pendapatan : 1.000
        ========================================================
        */


        // ----------------------------------------------------
        // DEBET AKUN KAS / BANK
        // Sebesar uang yang benar-benar diterima
        // ----------------------------------------------------
        if ($pembayaran > 0) {

            insertJurnal(
                $Conn,
                $uuid_jurnal,
                $id_transaksi,
                $tanggal_jurnal,
                $akunDebet,
                'D',
                $pembayaran
            );
        }


        // ----------------------------------------------------
        // DEBET PIUTANG
        // Sebesar sisa transaksi
        // ----------------------------------------------------
        if ($sisa > 0) {

            insertJurnal(
                $Conn,
                $uuid_jurnal,
                $id_transaksi,
                $tanggal_jurnal,
                $akunUtangPiutang,
                'D',
                $sisa
            );
        }


        // ----------------------------------------------------
        // KREDIT PENDAPATAN
        // Sebesar total transaksi
        // ----------------------------------------------------
        insertJurnal(
            $Conn,
            $uuid_jurnal,
            $id_transaksi,
            $tanggal_jurnal,
            $akunKredit,
            'K',
            $jumlah
        );
    }


    // ========================================================
    // JURNAL PENGELUARAN
    // ========================================================
    elseif ($kategori === 'Pengeluaran') {

        /*
        ========================================================
        CONTOH
        ========================================================

        Total       : 2.000
        Pembayaran  :     0
        Utang       : 2.000

        JURNAL:

        Debet Beban     : 2.000
        Kredit Kas      :     0
        Kredit Utang    : 2.000
        ========================================================
        */


        // ----------------------------------------------------
        // DEBET BEBAN
        // Selalu sebesar total transaksi
        // ----------------------------------------------------
        insertJurnal(
            $Conn,
            $uuid_jurnal,
            $id_transaksi,
            $tanggal_jurnal,
            $akunDebet,
            'D',
            $jumlah
        );


        // ----------------------------------------------------
        // KREDIT KAS / BANK
        // Sebesar uang yang benar-benar dibayarkan
        // ----------------------------------------------------
        if ($pembayaran > 0) {

            insertJurnal(
                $Conn,
                $uuid_jurnal,
                $id_transaksi,
                $tanggal_jurnal,
                $akunKredit,
                'K',
                $pembayaran
            );
        }


        // ----------------------------------------------------
        // KREDIT UTANG
        // Sebesar sisa pembayaran
        // ----------------------------------------------------
        if ($sisa > 0) {

            insertJurnal(
                $Conn,
                $uuid_jurnal,
                $id_transaksi,
                $tanggal_jurnal,
                $akunUtangPiutang,
                'K',
                $sisa
            );
        }
    }


    // ========================================================
    // VALIDASI BALANCE JURNAL
    // ========================================================
    $totalDebet = 0;

    $totalKredit = 0;


    if ($kategori === 'Pemasukan') {

        $totalDebet =
            $pembayaran +
            $sisa;

        $totalKredit =
            $jumlah;

    } elseif ($kategori === 'Pengeluaran') {

        $totalDebet =
            $jumlah;

        $totalKredit =
            $pembayaran +
            $sisa;
    }


    if ($totalDebet !== $totalKredit) {

        throw new Exception(
            'Jurnal tidak balance. Debet: ' .
            $totalDebet .
            ', Kredit: ' .
            $totalKredit
        );
    }


    // ========================================================
    // COMMIT DATABASE TRANSACTION
    // ========================================================
    mysqli_commit(
        $Conn
    );


    // ========================================================
    // SESSION NOTIFIKASI
    // ========================================================
    $_SESSION['NotifikasiSwal'] =
        'Tambah Transaksi Berhasil';


    // ========================================================
    // RESPONSE SUCCESS
    // ========================================================
    echo json_encode([
        'status'       => 'success',
        'message'      => 'Transaksi berhasil disimpan.',
        'id_transaksi' => $id_transaksi,
        'kategori'     => $kategori,
        'jumlah'       => $jumlah,
        'pembayaran'   => $pembayaran,
        'sisa'         => $sisa,
        'status_transaksi' => $status,
        'html'         => '
            <small
                class="text-success"
                id="NotifikasiTambahTransaksiBerhasil"
            >
                Success
            </small>
        '
    ], JSON_UNESCAPED_UNICODE);

    exit;


} catch (Throwable $e) {

    // ========================================================
    // ROLLBACK DATABASE TRANSACTION
    // ========================================================
    mysqli_rollback(
        $Conn
    );


    responseError(
        $e->getMessage()
    );
}

?>
