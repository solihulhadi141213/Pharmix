<?php
    // ============================================================
    // KONFIGURASI
    // ============================================================
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/SettingGeneral.php";
    include "../../_Config/Session.php";

    // ============================================================
    // HEADER JSON
    // ============================================================
    header('Content-Type: application/json; charset=utf-8');

    // ============================================================
    // RESPONSE DEFAULT
    // ============================================================
    $response = [
        'status'  => 'error',
        'message' => 'Terjadi kesalahan.',
        'html'    => ''
    ];

    // ============================================================
    // FUNGSI RESPONSE ERROR
    // ============================================================
    function responseError($message) {
        global $response;
        $response['status']  = 'error';
        $response['message'] = $message;
        $response['html'] = '
            <div class="row">
                <div class="col-md-12 mb-3 text-center">
                    <small class="text-danger">
                        ' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '
                    </small>
                </div>
            </div>
        ';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ============================================================
    // VALIDASI SESSION
    // ============================================================
    if (empty($SessionIdAkses)) {
        responseError('Sesi akses sudah berakhir, silakan login ulang.');
    }

    // ============================================================
    // VALIDASI REQUEST
    // ============================================================
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseError('Metode request tidak valid.');
    }

    // ============================================================
    // AMBIL ID TRANSAKSI
    // ============================================================
    $id_transaksi = $_POST['id_transaksi'] ?? '';
    $id_transaksi = trim($id_transaksi);

    // ============================================================
    // VALIDASI ID TRANSAKSI
    // ============================================================
    if ($id_transaksi === '') {
        responseError('ID transaksi tidak boleh kosong.');
    }
    

    // ============================================================
    // QUERY TRANSAKSI
    // ============================================================
    $sql = "
        SELECT
            t.id_transaksi,
            t.id_transaksi_jenis,
            t.tanggal,
            t.jumlah,
            t.pembayaran,
            t.keterangan,
            t.status,
            t.creat_at,
            t.creat_by_id,
            t.creat_by_name,
            t.update_at,
            t.update_by_id,
            t.update_by_name,
            tj.nama AS nama_transaksi,
            tj.kategori AS kategori
        FROM transaksi AS t
        INNER JOIN transaksi_jenis AS tj
            ON tj.id_transaksi_jenis = t.id_transaksi_jenis
        WHERE t.id_transaksi = ?
        LIMIT 1
    ";

    // ============================================================
    // PREPARE
    // ============================================================
    $stmt = mysqli_prepare($Conn, $sql);
    if (!$stmt) {
        responseError('Gagal menyiapkan query transaksi.');
    }

    // ============================================================
    // BIND PARAMETER
    // ============================================================
    mysqli_stmt_bind_param($stmt, 's', $id_transaksi);

    // ============================================================
    // EXECUTE
    // ============================================================
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        responseError('Gagal menjalankan query transaksi.');
    }

    // ============================================================
    // GET RESULT
    // ============================================================
    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        mysqli_stmt_close($stmt);
        responseError('Gagal memperoleh hasil query transaksi.');
    }

    // ============================================================
    // CEK DATA
    // ============================================================
    if (mysqli_num_rows($result) === 0) {
        mysqli_stmt_close($stmt);
        responseError('Data transaksi tidak ditemukan.');
    }

    // ============================================================
    // AMBIL DATA
    // ============================================================
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // ============================================================
    // VARIABEL TRANSAKSI
    // ============================================================
    $id_transaksi_jenis = (int) ($data['id_transaksi_jenis'] ?? 0);
    $nama_transaksi     = $data['nama_transaksi'] ?? '';
    $kategori           = $data['kategori'] ?? '';
    $tanggal            = $data['tanggal'] ?? '';
    $jumlah             = (int) ($data['jumlah'] ?? 0);
    $pembayaran         = (int) ($data['pembayaran'] ?? 0);
    $keterangan         = $data['keterangan'] ?? '';
    $status             = $data['status'] ?? '';
    $creat_at           = $data['creat_at'] ?? '';
    $creat_by_id        = $data['creat_by_id'] ?? '';
    $creat_by_name      = $data['creat_by_name'] ?? '';
    $update_at          = $data['update_at'] ?? '';
    $update_by_id       = $data['update_by_id'] ?? '';
    $update_by_name     = $data['update_by_name'] ?? '';
    
    // Format Tanggal
    $creat_at_format = '-';
    if (!empty($creat_at)) {
        $timestamp = strtotime($creat_at);
        if ($timestamp !== false) {
            $creat_at_format = date('d/m/Y H:i:s', $timestamp);
        }
    }

    $update_at_format = '-';
    if (!empty($update_at)) {
        $timestamp = strtotime($update_at);
        if ($timestamp !== false) {
            $update_at_format = date('d/m/Y H:i:s', $timestamp);
        }
    }

    // Menentukan Creator Dan Updater (Berdasarkan tabel akses: id_akses & nama_akses)
    if (!empty($creat_by_id)) {
        $creator = GetDetailData($Conn, 'akses', 'id_akses', $creat_by_id, 'nama_akses');
    } else {
        $creator = !empty($creat_by_name) ? $creat_by_name : '-';
    }
    if (!empty($update_by_id)) {
        $updater = GetDetailData($Conn, 'akses', 'id_akses', $update_by_id, 'nama_akses');
    } else {
        $updater = !empty($update_by_name) ? $update_by_name : '-';
    }

    // ============================================================
    // NORMALISASI NILAI
    // ============================================================
    if ($jumlah < 0) { $jumlah = 0; }
    if ($pembayaran < 0) { $pembayaran = 0; }

    // ============================================================
    // HITUNG JUMLAH RINCIAN
    // ============================================================
    $sql_rincian = "
        SELECT COUNT(*) AS jumlah
        FROM transaksi_rincian
        WHERE id_transaksi = ?
    ";
    $stmt_rincian = mysqli_prepare($Conn, $sql_rincian);
    $JumlahRincian = 0;
    if ($stmt_rincian) {
        mysqli_stmt_bind_param($stmt_rincian, 's', $id_transaksi);
        if (mysqli_stmt_execute($stmt_rincian)) {
            $result_rincian = mysqli_stmt_get_result($stmt_rincian);
            if ($result_rincian) {
                $data_rincian = mysqli_fetch_assoc($result_rincian);
                $JumlahRincian = (int) ($data_rincian['jumlah'] ?? 0);
            }
        }
        mysqli_stmt_close($stmt_rincian);
    }

    // ============================================================
    // JUMLAH JURNAL
    // ============================================================
    $JumlahJurnal = 0;

    // ============================================================
    // FORMAT RUPIAH
    // ============================================================
    $JumlahFormat     = 'Rp ' . number_format($jumlah, 0, ',', '.');
    $PembayaranFormat = 'Rp ' . number_format($pembayaran, 0, ',', '.');

    // ============================================================
    // FORMAT TANGGAL
    // ============================================================
    $TanggalFormat = '-';
    if (!empty($tanggal)) {
        $strtotime = strtotime($tanggal);
        if ($strtotime !== false) {
            $TanggalFormat = date('d/m/Y H:i:s', $strtotime);
        }
    }

    // ============================================================
    // ESCAPE DATA
    // ============================================================
    $nama_transaksi_html     = htmlspecialchars($nama_transaksi, ENT_QUOTES, 'UTF-8');
    $kategori_html           = htmlspecialchars($kategori, ENT_QUOTES, 'UTF-8');
    $status_html             = htmlspecialchars($status, ENT_QUOTES, 'UTF-8');
    $keterangan_html         = htmlspecialchars($keterangan, ENT_QUOTES, 'UTF-8');
    $id_transaksi_html       = htmlspecialchars((string) $id_transaksi, ENT_QUOTES, 'UTF-8');
    $id_transaksi_jenis_html = htmlspecialchars((string) $id_transaksi_jenis, ENT_QUOTES, 'UTF-8');

    // ============================================================
    // BADGE KATEGORI
    // ============================================================
    if ($kategori === 'Pengeluaran') {
        $kategori_label = '<small class="text-danger"><i class="bi bi-arrow-down-circle me-1"></i>Pengeluaran</small>';
    } elseif ($kategori === 'Pemasukan') {
        $kategori_label = '<small class="text-success"><i class="bi bi-arrow-up-circle me-1"></i>Pemasukan</small>';
    } else {
        $kategori_label = '<small class="text-secondary">' . $kategori_html . '</small>';
    }

    // ============================================================
    // BADGE STATUS
    // ============================================================
    switch ($status) {
        case 'Lunas':
            $status_label = '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Lunas</span>';
            break;
        case 'Utang':
            $status_label = '<span class="badge bg-danger"><i class="bi bi-exclamation-circle me-1"></i>Utang</span>';
            break;
        case 'Piutang':
            $status_label = '<span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Piutang</span>';
            break;
        default:
            $status_label = '<span class="badge bg-secondary">' . $status_html . '</span>';
            break;
    }

    // ============================================================
    // HTML DETAIL
    // ============================================================
    $html = '
        <!-- Hidden ID -->
        <input type="hidden" id="DetailIdTransaksi" value="' . $id_transaksi_html . '">
        <input type="hidden" id="DetailIdTransaksiJenis" value="' . $id_transaksi_jenis_html . '">
        <div class="row mt-3 mb-3">
            <!-- KOLOM KIRI -->
            <div class="col-md-6">
                <!-- ID Transaksi -->
                <div class="row mb-3">
                    <div class="col-6"><small>ID Transaksi</small></div>
                    <div class="col-6"><small class="text-grayish">' . $id_transaksi_html . '</small></div>
                </div>
                <!-- Tanggal -->
                <div class="row mb-3">
                    <div class="col-6"><small>Tanggal & Jam</small></div>
                    <div class="col-6"><small class="text-grayish">' . $TanggalFormat . '</small></div>
                </div>
                <!-- Nama Transaksi -->
                <div class="row mb-3">
                    <div class="col-6"><small>Nama Transaksi</small></div>
                    <div class="col-6"><small class="text-grayish">' . $nama_transaksi_html . '</small></div>
                </div>
                <!-- Kategori -->
                <div class="row mb-3">
                    <div class="col-6"><small>Kategori</small></div>
                    <div class="col-6">' . $kategori_label . '</div>
                </div>
                <!-- Status -->
                <div class="row mb-3">
                    <div class="col-6"><small>Status</small></div>
                    <div class="col-6">' . $status_label . '</div>
                </div>
                <!-- Rincian -->
                <div class="row mb-3">
                    <div class="col-6"><small>Rincian</small></div>
                    <div class="col-6"><small class="text-grayish">' . $JumlahRincian . ' Record</small></div>
                </div>
                <!-- Jurnal -->
                <div class="row mb-3">
                    <div class="col-6"><small>Jurnal</small></div>
                    <div class="col-6"><small class="text-grayish">' . $JumlahJurnal . ' Record</small></div>
                </div>
            </div>
            <!-- KOLOM KANAN -->
            <div class="col-md-6">
                <!-- Creat At -->
                <div class="row mb-3">
                    <div class="col-6"><small>Creat At</small></div>
                    <div class="col-6"><small class="text-grayish">' . $creat_at_format . '</small></div>
                </div>
                <!-- Update At -->
                <div class="row mb-3">
                    <div class="col-6"><small>Update At</small></div>
                    <div class="col-6"><small class="text-grayish">' . $update_at_format . '</small></div>
                </div>
                <!-- Creator -->
                <div class="row mb-3">
                    <div class="col-6"><small>Creat By</small></div>
                    <div class="col-6"><small class="text-grayish">' . $creator . '</small></div>
                </div>
                <!-- Updater -->
                <div class="row mb-3">
                    <div class="col-6"><small>Update By</small></div>
                    <div class="col-6"><small class="text-grayish">' . $updater . '</small></div>
                </div>
                <!-- Keterangan -->
                <div class="row mb-3">
                    <div class="col-6"><small>Keterangan</small></div>
                    <div class="col-6"><small class="text-grayish">' . ($keterangan !== '' ? nl2br($keterangan_html) : '-') . '</small></div>
                </div>
            </div>
        </div>
    ';

    // ============================================================
    // RESPONSE SUCCESS
    // ============================================================
    $response = [
        'status'  => 'success',
        'message' => 'Data transaksi berhasil ditemukan.',
        'html'    => $html
    ];

    // ============================================================
    // OUTPUT JSON
    // ============================================================
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
?>