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

    $id_jurnal           = trim($_POST['id_jurnal'] ?? '');
    $tanggal             = trim($_POST['tanggal'] ?? '');
    $nominal_input       = trim($_POST['nominal'] ?? '');
    $debet_kredit        = trim($_POST['debet_kredit'] ?? '');
    $id_akun_perkiraan   = trim($_POST['id_akun_perkiraan'] ?? '');

    if (
        $id_jurnal === '' ||
        $tanggal === '' ||
        $nominal_input === '' ||
        $debet_kredit === '' ||
        $id_akun_perkiraan === ''
    ) {
        responseError('Semua data mandatory wajib diisi.');
    }

    if (!ctype_digit($id_jurnal) || (int) $id_jurnal < 1) {
        responseError('ID jurnal tidak valid.');
    }
    $id_jurnal = (int) $id_jurnal;

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

    // Ambil jurnal sekaligus memastikan jurnal tersebut adalah jurnal pembayaran.
    $stmt_jurnal = $Conn->prepare(
        'SELECT id_transaksi_pembayaran, kategori FROM jurnal
         WHERE id_jurnal = ? LIMIT 1'
    );
    if (!$stmt_jurnal) {
        responseError('Gagal mempersiapkan validasi jurnal.');
    }
    $stmt_jurnal->bind_param('i', $id_jurnal);
    if (!$stmt_jurnal->execute()) {
        $stmt_jurnal->close();
        responseError('Gagal mengambil data jurnal.');
    }
    $result_jurnal = $stmt_jurnal->get_result();
    $data_jurnal = $result_jurnal ? $result_jurnal->fetch_assoc() : null;
    $stmt_jurnal->close();

    if (!$data_jurnal) {
        responseError('Data jurnal tidak ditemukan.');
    }

    $id_transaksi_pembayaran = trim($data_jurnal['id_transaksi_pembayaran'] ?? '');
    if ($data_jurnal['kategori'] !== 'Pembayaran' || $id_transaksi_pembayaran === '') {
        responseError('Jurnal yang dipilih bukan jurnal pembayaran.');
    }

    // Ambil kode dan nama akun dari master akun berdasarkan ID akun.
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

    $kode_perkiraan = $data_akun['kode'];
    $nama_perkiraan = $data_akun['nama'];

    $stmt_update = $Conn->prepare(
        'UPDATE jurnal
         SET tanggal = ?, kode_perkiraan = ?, nama_perkiraan = ?, d_k = ?, nilai = ?
         WHERE id_jurnal = ? AND id_transaksi_pembayaran = ? AND kategori = \'Pembayaran\''
    );
    if (!$stmt_update) {
        responseError('Gagal mempersiapkan perubahan jurnal.');
    }

    $stmt_update->bind_param(
        'ssssiis',
        $tanggal,
        $kode_perkiraan,
        $nama_perkiraan,
        $d_k,
        $nominal,
        $id_jurnal,
        $id_transaksi_pembayaran
    );

    if (!$stmt_update->execute()) {
        $stmt_update->close();
        responseError('Gagal memperbarui jurnal pembayaran.');
    }
    $stmt_update->close();

    echo json_encode([
        'status'                  => 'success',
        'message'                 => 'Jurnal pembayaran berhasil diperbarui.',
        'id_transaksi_pembayaran' => $id_transaksi_pembayaran
    ], JSON_UNESCAPED_UNICODE);
?>
