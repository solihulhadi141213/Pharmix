<?php
    // ============================================================
    // KONEKSI, HELPER, SETTING, DAN SESSION
    // ============================================================
    date_default_timezone_set('Asia/Jakarta');

    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/SettingGeneral.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');


    // ============================================================
    // RESPONSE ERROR
    // ============================================================
    function responseError($message)
    {
        echo json_encode([
            'status'  => 'error',
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


    // ============================================================
    // TANGKAP DATA FORM
    // ============================================================
    if(empty($_POST['id_transaksi'])){
        responseError('ID transaksi tidak boleh kosong.');
    }
    $id_transaksi   = $_POST['id_transaksi'];
    $kode_perkiraan = trim($_POST['kode_perkiraan'] ?? '');
    $d_k            = strtoupper(trim($_POST['d_k'] ?? ''));
    $nilai          = trim($_POST['nilai'] ?? '');


    // ============================================================
    // VALIDASI MANDATORI
    // ============================================================

    // Kode Perkiraan
    if ($kode_perkiraan === '') {
        responseError('Akun perkiraan wajib dipilih.');
    }


    // D / K
    if ($d_k === '') {
        responseError('Posisi D/K wajib dipilih.');
    }

    if (!in_array($d_k, ['D', 'K'], true)) {
        responseError('Posisi jurnal hanya boleh D atau K.');
    }


    // Nilai
    if ($nilai === '') {
        responseError('Nilai nominal wajib diisi.');
    }


    // ============================================================
    // BERSIHKAN NILAI NOMINAL
    // ============================================================
    // Contoh:
    // 1.000     -> 1000
    // 15.000    -> 15000
    // 1.500.000 -> 1500000

    $nilai = str_replace('.', '', $nilai);

    // Hilangkan karakter selain angka
    $nilai = preg_replace('/[^0-9]/', '', $nilai);

    if ($nilai === '') {
        responseError('Nilai nominal tidak valid.');
    }

    $nilai = (int) $nilai;

    if ($nilai <= 0) {
        responseError('Nilai nominal harus lebih besar dari 0.');
    }


    // ============================================================
    // VALIDASI TRANSAKSI
    // ============================================================
    // Hanya mengambil tanggal.
    // TIDAK mengambil kategori karena kolom kategori tidak ada
    // pada tabel transaksi.

    $sql_transaksi = "SELECT id_transaksi, tanggal FROM transaksi WHERE id_transaksi = ? LIMIT 1";
    $stmt_transaksi = mysqli_prepare($Conn, $sql_transaksi);
    if (!$stmt_transaksi) {
        responseError('Gagal mempersiapkan query transaksi.');
    }
    mysqli_stmt_bind_param($stmt_transaksi,'s',$id_transaksi);
    if (!mysqli_stmt_execute($stmt_transaksi)) {
        mysqli_stmt_close($stmt_transaksi);
        responseError('Gagal mengambil data transaksi.');
    }

    $result_transaksi = mysqli_stmt_get_result($stmt_transaksi);
    if (!$result_transaksi || mysqli_num_rows($result_transaksi) === 0) {
        mysqli_stmt_close($stmt_transaksi);
        responseError('Data transaksi tidak ditemukan.');
    }

    $data_transaksi = mysqli_fetch_assoc($result_transaksi);
    mysqli_stmt_close($stmt_transaksi);


    // ============================================================
    // TANGGAL TRANSAKSI
    // ============================================================
    $tanggal = $data_transaksi['tanggal'] ?? '';

    if (empty($tanggal)) {
        responseError('Tanggal transaksi tidak ditemukan.');
    }


    // ============================================================
    // VALIDASI AKUN PERKIRAAN
    // ============================================================
    // kode_perkiraan harus benar-benar berasal dari tabel
    // akun_perkiraan.

    $sql_akun = "
        SELECT
            kode,
            nama
        FROM akun_perkiraan
        WHERE kode = ?
        LIMIT 1
    ";

    $stmt_akun = mysqli_prepare($Conn, $sql_akun);

    if (!$stmt_akun) {
        responseError('Gagal mempersiapkan query akun perkiraan.');
    }

    mysqli_stmt_bind_param(
        $stmt_akun,
        's',
        $kode_perkiraan
    );

    if (!mysqli_stmt_execute($stmt_akun)) {
        mysqli_stmt_close($stmt_akun);
        responseError('Gagal mengambil data akun perkiraan.');
    }

    $result_akun = mysqli_stmt_get_result($stmt_akun);

    if (!$result_akun || mysqli_num_rows($result_akun) === 0) {
        mysqli_stmt_close($stmt_akun);
        responseError('Akun perkiraan tidak ditemukan.');
    }

    $data_akun = mysqli_fetch_assoc($result_akun);

    mysqli_stmt_close($stmt_akun);


    // ============================================================
    // DATA AKUN
    // ============================================================
    $kode_perkiraan = $data_akun['kode'];
    $nama_perkiraan = $data_akun['nama'];


    // ============================================================
    // GENERATE UUID 36 KARAKTER
    // ============================================================
    function generateUuid()
    {
        $data = random_bytes(16);

        // UUID v4
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf(
            '%s%s-%s-%s-%s-%s%s%s',
            str_split(bin2hex($data), 4)
        );
    }

    $uuid = generateUuid();


    // ============================================================
    // DATA JURNAL
    // ============================================================
    // kategori                 = Transaksi
    // uuid                     = UUID random 36 karakter
    // id_transaksi             = dari form
    // id_transaksi_jual_beli   = NULL
    // id_transaksi_pembayaran  = NULL
    // tanggal                  = tanggal transaksi
    // kode_perkiraan           = dari akun_perkiraan
    // nama_perkiraan            = dari akun_perkiraan
    // d_k                      = D / K
    // nilai                    = nominal

    $kategori = 'Transaksi';


    // ============================================================
    // INSERT JURNAL
    // ============================================================
    $sql_insert = "
        INSERT INTO jurnal (
            kategori,
            uuid,
            id_transaksi,
            id_transaksi_jual_beli,
            id_transaksi_pembayaran,
            tanggal,
            kode_perkiraan,
            nama_perkiraan,
            d_k,
            nilai
        ) VALUES (
            ?,
            ?,
            ?,
            NULL,
            NULL,
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ";

    $stmt_insert = mysqli_prepare($Conn, $sql_insert);

    if (!$stmt_insert) {
        responseError('Gagal mempersiapkan proses penyimpanan jurnal.');
    }


    // ============================================================
    // BIND PARAMETER
    // ============================================================
    // s = string
    // i = integer
    //
    // kategori             s
    // uuid                 s
    // id_transaksi         i
    // tanggal              s
    // kode_perkiraan       s
    // nama_perkiraan       s
    // d_k                  s
    // nilai                i

    mysqli_stmt_bind_param(
        $stmt_insert,
        'sssssssi',
        $kategori,
        $uuid,
        $id_transaksi,
        $tanggal,
        $kode_perkiraan,
        $nama_perkiraan,
        $d_k,
        $nilai
    );


    // ============================================================
    // EKSEKUSI INSERT
    // ============================================================
    if (!mysqli_stmt_execute($stmt_insert)) {

        $error = mysqli_stmt_error($stmt_insert);

        mysqli_stmt_close($stmt_insert);

        responseError(
            'Gagal menyimpan jurnal: ' . $error
        );
    }

    $id_jurnal = mysqli_insert_id($Conn);

    mysqli_stmt_close($stmt_insert);


    // ============================================================
    // RESPONSE BERHASIL
    // ============================================================
    echo json_encode([
        'status'  => 'success',
        'message' => 'Jurnal berhasil ditambahkan.',
        'id_jurnal' => $id_jurnal
    ], JSON_UNESCAPED_UNICODE);

    exit;
?>