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
    // RESPONSE DEFAULT
    // ============================================================
    $response = [
        'status'  => 'error',
        'message' => 'Terjadi kesalahan.',
        'html'    => ''
    ];

    // ============================================================
    // HEADER JSON
    // ============================================================
    header('Content-Type: application/json; charset=utf-8');

    // ============================================================
    // VALIDASI SESSION
    // ============================================================
    if (empty($SessionIdAkses)) {
        $response['message'] = 'Sesi akses sudah berakhir.';
        $response['html'] = '
            <div class="row">
                <div class="col-md-12 mb-2 text-center">
                    <small class="text-danger">
                        Sesi akses sudah berakhir.
                        Silakan login ulang.
                    </small>
                </div>
            </div>
        ';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ============================================================
    // VALIDASI METHOD
    // ============================================================
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response['message'] = 'Metode request tidak valid.';
        $response['html'] = '
            <div class="alert alert-danger">
                <small>
                    Metode request tidak valid.
                </small>
            </div>
        ';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ============================================================
    // VALIDASI ID TRANSAKSI
    // ============================================================
    if(empty($_POST['id_transaksi'])) {
        $response['message'] = 'ID transaksi tidak valid.';
        $response['html'] = '
            <div class="row">
                <div class="col-md-12 mb-2 text-center">
                    <small class="text-danger">
                        ID transaksi tidak valid.
                    </small>
                </div>
            </div>
        ';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $id_transaksi = $_POST['id_transaksi'] ?? '';

    // ============================================================
    // QUERY DATA TRANSAKSI
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
        LEFT JOIN transaksi_jenis AS tj
            ON tj.id_transaksi_jenis = t.id_transaksi_jenis
        WHERE t.id_transaksi = ?
        LIMIT 1
    ";

    $stmt = mysqli_prepare($Conn, $sql);

    // ============================================================
    // VALIDASI PREPARE
    // ============================================================
    if (!$stmt) {
        $response['message'] = 'Gagal menyiapkan query transaksi.';
        $response['html'] = '
            <div class="alert alert-danger">
                <small>
                    Gagal mengambil data transaksi.
                </small>
            </div>
        ';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ============================================================
    // BIND PARAMETER
    // ============================================================
    mysqli_stmt_bind_param($stmt, "s", $id_transaksi);

    // ============================================================
    // EXECUTE
    // ============================================================
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        $response['message'] = 'Gagal menjalankan query transaksi.';
        $response['html'] = '
            <div class="alert alert-danger">
                <small>
                    Gagal mengambil data transaksi.
                </small>
            </div>
        ';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ============================================================
    // AMBIL HASIL
    // ============================================================
    $result = mysqli_stmt_get_result($stmt);

    // ============================================================
    // CEK DATA
    // ============================================================
    if (!$result || mysqli_num_rows($result) === 0) {
        mysqli_stmt_close($stmt);
        $response['message'] = 'Data transaksi tidak ditemukan.';
        $response['html'] = '
            <div class="row">
                <div class="col-md-12 mb-2 text-center">
                    <small class="text-danger">
                        Data transaksi tidak ditemukan.
                    </small>
                </div>
            </div>
        ';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ============================================================
    // AMBIL DATA
    // ============================================================
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // ============================================================
    // VARIABEL DATA
    // ============================================================
    $id_transaksi_jenis = (int) ($data['id_transaksi_jenis'] ?? 0);
    $nama_transaksi     = $data['nama_transaksi'] ?? '-';
    $kategori           = $data['kategori'] ?? '-';
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
    // AMBIL DATA RINCIAN TRANSAKSI
    // ============================================================
    $sql_rincian = "
        SELECT
            id_transaksi_rincian,
            rincian_transaksi,
            harga,
            qty,
            satuan,
            jumlah
        FROM transaksi_rincian
        WHERE id_transaksi = ?
        ORDER BY id_transaksi_rincian ASC
    ";
    $stmt_rincian = mysqli_prepare($Conn, $sql_rincian);
    $JumlahRincian = 0;
    $HtmlRincian   = '';
    if ($stmt_rincian) {
        mysqli_stmt_bind_param($stmt_rincian, "s", $id_transaksi);
        if (mysqli_stmt_execute($stmt_rincian)) {
            $result_rincian = mysqli_stmt_get_result($stmt_rincian);
            if ($result_rincian) {
                $no_rincian = 1;
                while ($data_rincian = mysqli_fetch_assoc($result_rincian)) {
                    $uraian = htmlspecialchars($data_rincian['rincian_transaksi'] ?? '', ENT_QUOTES, 'UTF-8');
                    $harga = (float) ($data_rincian['harga'] ?? 0);
                    $qty = (float) ($data_rincian['qty'] ?? 0);
                    $satuan = htmlspecialchars($data_rincian['satuan'] ?? '', ENT_QUOTES, 'UTF-8');
                    $jumlah_rincian = (float) ($data_rincian['jumlah'] ?? 0);

                    // Format Rupiah
                    $harga_format = 'Rp ' . number_format($harga, 0, ',', '.');
                    $jumlah_rincian_format = 'Rp ' . number_format($jumlah_rincian, 0, ',', '.');

                    // Tambahkan HTML
                    $HtmlRincian .= '
                        <tr>
                            <td class="text-center">' . $no_rincian . '</td>
                            <td>' . $uraian . '</td>
                            <td>' . $harga_format . '</td>
                            <td>' . number_format($qty, 0, ',', '.') . '</td>
                            <td>' . $satuan . '</td>
                            <td>' . $jumlah_rincian_format . '</td>
                        </tr>
                    ';

                    $no_rincian++;
                    $JumlahRincian++;
                }
            }
        }
        mysqli_stmt_close($stmt_rincian);
    }

    if (empty($HtmlRincian)) {
        $HtmlRincian = '
            <tr>
                <td colspan="6" class="text-center text-muted">
                    <small>Tidak ada data rincian transaksi</small>
                </td>
            </tr>
        ';
    }

    // ============================================================
    // HITUNG JUMLAH JURNAL
    // ============================================================
    $JumlahJurnal = 0;

    // ============================================================
    // FORMAT DATA
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
    // ESCAPE OUTPUT
    // ============================================================
    $id_transaksi       = htmlspecialchars((string) $id_transaksi, ENT_QUOTES, 'UTF-8');
    $id_transaksi_jenis_html = htmlspecialchars((string) $id_transaksi_jenis, ENT_QUOTES, 'UTF-8');
    $nama_transaksi_html     = htmlspecialchars($nama_transaksi, ENT_QUOTES, 'UTF-8');
    $kategori_html           = htmlspecialchars($kategori, ENT_QUOTES, 'UTF-8');
    $status_html             = htmlspecialchars($status, ENT_QUOTES, 'UTF-8');
    $keterangan_html         = nl2br(htmlspecialchars($keterangan, ENT_QUOTES, 'UTF-8'));
    $creator_html            = htmlspecialchars($creator, ENT_QUOTES, 'UTF-8');
    $updater_html            = htmlspecialchars($updater, ENT_QUOTES, 'UTF-8');

    // ============================================================
    // BADGE KATEGORI
    // ============================================================
    if ($kategori === 'Pengeluaran') {
        $kategori_label = '<small class="text-danger"><i class="bi bi-arrow-down-circle me-1"></i> Pengeluaran</small>';
    } elseif ($kategori === 'Pemasukan') {
        $kategori_label = '<small class="text-success"><i class="bi bi-arrow-up-circle me-1"></i> Pemasukan</small>';
    } else {
        $kategori_label = '<small class="text-secondary">' . $kategori_html . '</small>';
    }

    // ============================================================
    // BADGE STATUS
    // ============================================================
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
            $status_label = '<span class="badge bg-secondary">' . $status_html . '</span>';
            break;
    }

    // ============================================================
    // HTML DETAIL
    // ============================================================
    $html = '
        <input type="hidden" name="id_transaksi" id="put_id_transaksi" value="' . $id_transaksi . '">
        <input type="hidden" name="id_transaksi_jenis" value="' . $id_transaksi_jenis_html . '">
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="row mb-2">
                    <div class="col-6"><small>ID Transaksi</small></div>
                    <div class="col-6"><small class="text-muted">' . $id_transaksi . '</small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-6"><small>Tanggal Transaksi</small></div>
                    <div class="col-6"><small class="text-muted">' . $TanggalFormat . '</small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-6"><small>Jenis Transaksi</small></div>
                    <div class="col-6"><small class="text-muted">' . $nama_transaksi_html . '</small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-6"><small>Kategori</small></div>
                    <div class="col-6">' . $kategori_label . '</div>
                </div>
                <div class="row mb-2">
                    <div class="col-6"><small>Status</small></div>
                    <div class="col-6">' . $status_label . '</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row mb-2">
                    <div class="col-6"><small>Keterangan</small></div>
                    <div class="col-6"><small class="text-muted">' . ($keterangan !== '' ? $keterangan_html : '-') . '</small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-6"><small>Creat At</small></div>
                    <div class="col-6"><small class="text-muted">' . $creat_at_format . '</small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-6"><small>Update At</small></div>
                    <div class="col-6"><small class="text-muted">' . $update_at_format . '</small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-6"><small>Creat By</small></div>
                    <div class="col-6"><small class="text-muted">' . $creator_html . '</small></div>
                </div>
                <div class="row mb-2">
                    <div class="col-6"><small>Update By</small></div>
                    <div class="col-6"><small class="text-muted">' . $updater_html . '</small></div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-12">
                <div class="table table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="bg-dark text-white"><b>No</b></th>
                                <th class="bg-dark text-white"><b>Uraian</b></th>
                                <th class="bg-dark text-white"><b>Harga</b></th>
                                <th class="bg-dark text-white"><b>QTY</b></th>
                                <th class="bg-dark text-white"><b>Satuan</b></th>
                                <th class="bg-dark text-white"><b>Jumlah</b></th>
                            </tr>
                        </thead>
                        <tbody>
                            ' . $HtmlRincian . '
                            <tr>
                                <td></td>
                                <td colspan="4"><b>Jumlah</b></td>
                                <td><b>' . $JumlahFormat . '</b></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td colspan="4"><b>Pembayaran</b></td>
                                <td><b>' . $PembayaranFormat . '</b></td>
                            </tr>
                        </tbody>
                    </table>
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

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
?>