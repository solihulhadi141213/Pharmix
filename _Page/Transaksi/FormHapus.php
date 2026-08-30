<?php
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/SettingGeneral.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');

    $response = ['status' => 'error', 'message' => 'Terjadi kesalahan.', 'html' => ''];

    // [PETUNJUK PENGEMBANGAN] Fungsi untuk menangani respons error terpusat
    function responseError($message) {
        $response = [
            'status' => 'error',
            'message' => $message,
            'html' => '<div class="row"><div class="col-md-12 mb-2 text-center"><small class="text-danger">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</small></div></div>'
        ];
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (empty($SessionIdAkses)) {
        responseError('Sesi akses sudah berakhir. Silakan login ulang.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseError('Metode request tidak valid.');
    }
    if(empty($_POST['id_transaksi'])){
        responseError('ID Transaksi tidak valid.');
    }
    $id_transaksi = $_POST['id_transaksi'];
   

    // [PETUNJUK PENGEMBANGAN] Sesuaikan query utama di sini jika ada penambahan kolom dari tabel relasi
    $sql = "SELECT t.id_transaksi, t.id_transaksi_jenis, t.tanggal, t.jumlah, t.pembayaran, t.keterangan, t.status, tj.nama AS nama_transaksi, tj.kategori AS kategori FROM transaksi AS t LEFT JOIN transaksi_jenis AS tj ON tj.id_transaksi_jenis = t.id_transaksi_jenis WHERE t.id_transaksi = ? LIMIT 1";

    $stmt = mysqli_prepare($Conn, $sql);
    if (!$stmt) {
        responseError('Gagal menyiapkan query transaksi.');
    }

    mysqli_stmt_bind_param($stmt, 's', $id_transaksi);
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        responseError('Gagal menjalankan query transaksi.');
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result || mysqli_num_rows($result) === 0) {
        mysqli_stmt_close($stmt);
        responseError('Data transaksi tidak ditemukan.');
    }

    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    $id_transaksi_jenis = (int) ($data['id_transaksi_jenis'] ?? 0);
    $nama_transaksi     = $data['nama_transaksi'] ?? '-';
    $kategori           = $data['kategori'] ?? '-';
    $tanggal            = $data['tanggal'] ?? '';
    $jumlah             = (int) ($data['jumlah'] ?? 0);
    $pembayaran         = (int) ($data['pembayaran'] ?? 0);
    $status             = $data['status'] ?? '-';

    // [PETUNJUK PENGEMBANGAN] Mengatur agar keterangan otomatis menjadi '-' jika kosong atau null
    $keterangan_mentah  = trim($data['keterangan'] ?? '');
    $keterangan         = ($keterangan_mentah === '') ? '-' : $keterangan_mentah;

    // [PETUNJUK PENGEMBANGAN] Blok query tambahan untuk menghitung rincian transaksi
    $sqlRincian = "SELECT COUNT(*) AS jumlah FROM transaksi_rincian WHERE id_transaksi = ?";
    $stmtRincian = mysqli_prepare($Conn, $sqlRincian);
    $JumlahRincian = 0;
    if ($stmtRincian) {
        mysqli_stmt_bind_param($stmtRincian, 's', $id_transaksi);
        if (mysqli_stmt_execute($stmtRincian)) {
            $resultRincian = mysqli_stmt_get_result($stmtRincian);
            if ($resultRincian) {
                $dataRincian = mysqli_fetch_assoc($resultRincian);
                $JumlahRincian = (int) ($dataRincian['jumlah'] ?? 0);
            }
        }
        mysqli_stmt_close($stmtRincian);
    }

    // [PETUNJUK PENGEMBANGAN] Blok query tambahan untuk menghitung jumlah jurnal terkait
    $sqlJurnal = "SELECT COUNT(*) AS jumlah FROM jurnal WHERE id_transaksi = ?";
    $stmtJurnal = mysqli_prepare($Conn, $sqlJurnal);
    $JumlahJurnal = 0;
    if ($stmtJurnal) {
        mysqli_stmt_bind_param($stmtJurnal, 's', $id_transaksi);
        if (mysqli_stmt_execute($stmtJurnal)) {
            $resultJurnal = mysqli_stmt_get_result($stmtJurnal);
            if ($resultJurnal) {
                $dataJurnal = mysqli_fetch_assoc($resultJurnal);
                $JumlahJurnal = (int) ($dataJurnal['jumlah'] ?? 0);
            }
        }
        mysqli_stmt_close($stmtJurnal);
    }

    $JumlahFormat = 'Rp ' . number_format($jumlah, 0, ',', '.');
    $PembayaranFormat = 'Rp ' . number_format($pembayaran, 0, ',', '.');

    $TanggalFormat = '-';
    if (!empty($tanggal)) {
        $strtotime = strtotime($tanggal);
        if ($strtotime !== false) {
            $TanggalFormat = date('d/m/Y H:i:s', $strtotime);
        }
    }

    // [PETUNJUK PENGEMBANGAN] Tambahkan kondisi status baru pada switch-case ini jika diperlukan
    $status_label = '';
    switch ($status) {
        case 'Lunas':
            $status_label = '<span class="badge bg-success">Lunas</span>';
            break;
        case 'Utang':
            $status_label = '<span class="badge bg-danger">Utang</span>';
            break;
        case 'Piutang':
            $status_label = '<span class="badge bg-warning text-dark">Piutang</span>';
            break;
        default:
            $status_label = '<span class="badge bg-secondary">' . htmlspecialchars($status, ENT_QUOTES, 'UTF-8') . '</span>';
            break;
    }

    $id_transaksi_html   = htmlspecialchars((string) $id_transaksi, ENT_QUOTES, 'UTF-8');
    $nama_transaksi_html = htmlspecialchars($nama_transaksi, ENT_QUOTES, 'UTF-8');
    $kategori_html       = htmlspecialchars($kategori, ENT_QUOTES, 'UTF-8');
    $keterangan_html     = htmlspecialchars($keterangan, ENT_QUOTES, 'UTF-8');

    // [PETUNJUK PENGEMBANGAN] Template HTML tampilan modal/konten konfirmasi hapus
    $html = '
        <input type="hidden" name="id_transaksi" value="' . $id_transaksi_html . '">
        <div class="col-md-12 mb-4">
            <div class="row mb-2">
                <div class="col-6"><small>Tanggal Transaksi</small></div>
                <div class="col-6"><small class="text-grayish">' . $TanggalFormat . '</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-6"><small>Nama Transaksi</small></div>
                <div class="col-6"><small class="text-grayish">' . $nama_transaksi_html . '</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-6"><small>Kategori</small></div>
                <div class="col-6"><small class="text-grayish">' . $kategori_html . '</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-6"><small>Jumlah (Rp)</small></div>
                <div class="col-6"><small class="text-grayish">' . $JumlahFormat . '</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-6"><small>Pembayaran (Rp)</small></div>
                <div class="col-6"><small class="text-grayish">' . $PembayaranFormat . '</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-6"><small>Status</small></div>
                <div class="col-6">' . $status_label . '</div>
            </div>
            <div class="row mb-2">
                <div class="col-6"><small>Keterangan</small></div>
                <div class="col-6"><small class="text-grayish">' . $keterangan_html . '</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-6"><small>Rincian</small></div>
                <div class="col-6"><small class="text-grayish">' . $JumlahRincian . ' Record</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-6"><small>Jurnal</small></div>
                <div class="col-6"><small class="text-grayish">' . $JumlahJurnal . ' Record</small></div>
            </div>
            <div class="row mb-2">
                <div class="col-md-12">
                    <div class="alert alert-danger">
                        <small>
                            <b>Penting!</b> Data yang sudah dihapus tidak akan bisa dikembalikan lagi.
                            Pastikan juga data yang akan dihapus sudah sesuai.<br><br>
                            <i>Apakah anda yakin akan menghapus data ini?</i>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    ';

    $response = [
        'status' => 'success',
        'message' => 'Data transaksi berhasil ditemukan.',
        'html' => $html
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
?>