<?php
    date_default_timezone_set('Asia/Jakarta');

    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');

    function responseError(string $message): void
    {
        echo json_encode([
            'status'  => 'error',
            'message' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (empty($SessionIdAkses)) {
        responseError('Sesi akses sudah berakhir. Silakan login ulang.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseError('Metode request tidak valid.');
    }

    // Data mandatory dari form tambah jurnal.
    $id_transaksi_pembayaran = trim($_POST['id_transaksi_pembayaran'] ?? '');
    $tanggal                 = trim($_POST['tanggal'] ?? '');
    $nominal_input           = trim($_POST['nominal'] ?? '');
    $debet_kredit            = trim($_POST['debet_kredit'] ?? '');
    $id_akun_perkiraan       = trim($_POST['id_akun_perkiraan'] ?? '');

    if (
        $id_transaksi_pembayaran === '' ||
        $tanggal === '' ||
        $nominal_input === '' ||
        $debet_kredit === '' ||
        $id_akun_perkiraan === ''
    ) {
        responseError('Semua data mandatory wajib diisi.');
    }

    $tanggal_object = DateTime::createFromFormat('Y-m-d', $tanggal);
    if (!$tanggal_object || $tanggal_object->format('Y-m-d') !== $tanggal) {
        responseError('Format tanggal tidak valid.');
    }

    // Nominal pada form menggunakan titik sebagai pemisah ribuan.
    $nominal = str_replace('.', '', $nominal_input);
    $nominal = preg_replace('/[^0-9]/', '', $nominal);
    if ($nominal === '' || (int) $nominal <= 0) {
        responseError('Nominal jurnal harus berupa angka lebih besar dari 0.');
    }
    $nominal = (int) $nominal;

    $debet_kredit = strtolower($debet_kredit);
    if ($debet_kredit === 'debet') {
        $d_k = 'D';
    } elseif ($debet_kredit === 'kredit') {
        $d_k = 'K';
    } else {
        responseError('Posisi jurnal hanya boleh Debet atau Kredit.');
    }

    // Pastikan pembayaran yang menjadi referensi benar-benar ada.
    $stmt_pembayaran = $Conn->prepare(
        'SELECT id_transaksi_pembayaran FROM transaksi_pembayaran
         WHERE id_transaksi_pembayaran = ? LIMIT 1'
    );
    if (!$stmt_pembayaran) {
        responseError('Gagal mempersiapkan validasi pembayaran.');
    }
    $stmt_pembayaran->bind_param('s', $id_transaksi_pembayaran);
    if (!$stmt_pembayaran->execute()) {
        $stmt_pembayaran->close();
        responseError('Gagal memvalidasi data pembayaran.');
    }
    $result_pembayaran = $stmt_pembayaran->get_result();
    $pembayaran_ada = $result_pembayaran && $result_pembayaran->num_rows > 0;
    $stmt_pembayaran->close();

    if (!$pembayaran_ada) {
        responseError('ID pembayaran tidak ditemukan.');
    }

    // Ambil kode dan nama akun berdasarkan ID yang dikirim form.
    $stmt_akun = $Conn->prepare(
        'SELECT kode, nama FROM akun_perkiraan WHERE id_perkiraan = ? LIMIT 1'
    );
    if (!$stmt_akun) {
        responseError('Gagal mempersiapkan validasi akun perkiraan.');
    }
    $stmt_akun->bind_param('s', $id_akun_perkiraan);
    if (!$stmt_akun->execute()) {
        $stmt_akun->close();
        responseError('Gagal mengambil data akun perkiraan.');
    }
    $result_akun = $stmt_akun->get_result();
    $data_akun = $result_akun ? $result_akun->fetch_assoc() : null;
    $stmt_akun->close();

    if (!$data_akun) {
        responseError('Akun perkiraan tidak ditemukan.');
    }

    $kategori = 'Pembayaran';
    $uuid = $id_transaksi_pembayaran;

    $sql_insert = 'INSERT INTO jurnal (
        kategori,
        uuid,
        id_transaksi_pembayaran,
        tanggal,
        kode_perkiraan,
        nama_perkiraan,
        d_k,
        nilai
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';

    $stmt_insert = $Conn->prepare($sql_insert);
    if (!$stmt_insert) {
        responseError('Gagal mempersiapkan penyimpanan jurnal.');
    }

    $kode_perkiraan = $data_akun['kode'];
    $nama_perkiraan = $data_akun['nama'];
    $stmt_insert->bind_param(
        'sssssssi',
        $kategori,
        $uuid,
        $id_transaksi_pembayaran,
        $tanggal,
        $kode_perkiraan,
        $nama_perkiraan,
        $d_k,
        $nominal
    );

    if (!$stmt_insert->execute()) {
        $stmt_insert->close();
        responseError('Gagal menyimpan jurnal pembayaran.');
    }

    $stmt_insert->close();

    echo json_encode([
        'status'                  => 'success',
        'message'                 => 'Jurnal pembayaran berhasil ditambahkan.',
        'id_transaksi_pembayaran' => $id_transaksi_pembayaran
    ], JSON_UNESCAPED_UNICODE);
?>
