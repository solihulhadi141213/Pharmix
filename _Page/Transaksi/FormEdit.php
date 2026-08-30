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
    $keterangan         = $data['keterangan'] ?? '';
    $jumlah             = (int) ($data['jumlah'] ?? 0);
    $pembayaran         = (int) ($data['pembayaran'] ?? 0);
    $status             = $data['status'] ?? '';

    // Format uang
    $JumlahFormat     = '' . number_format($jumlah, 0, ',', '.');
    $PembayaranFormat = '' . number_format($pembayaran, 0, ',', '.');

    $TanggalFormat = '-';
    if (!empty($tanggal)) {
        $TanggalFormat = date('Y-m-d', strtotime($tanggal));
        $JamFormat     = date('H:i:s', strtotime($tanggal));
    }else{
        $TanggalFormat = "";
        $JamFormat     = "";
    }

    // [PETUNJUK PENGEMBANGAN] Template HTML tampilan modal/konten konfirmasi hapus
    $html = '
        <input type="hidden" name="id_transaksi" value="' . $id_transaksi . '">
        
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="id_transaksi_jenis_edit">
                    * Kategori Operasional
                </label>
                <select
                    name="id_transaksi_jenis"
                    id="id_transaksi_jenis_edit"
                    class="form-select"
                    style="width: 100%;"
                    data-selected-id="' . $id_transaksi_jenis . '"
                    data-selected-text="' . htmlspecialchars($nama_transaksi . ' (' . $kategori . ')', ENT_QUOTES, 'UTF-8') . '"
                >
                    <option value="">Pilih</option>
                    <option
                        selected
                        value="' . $id_transaksi_jenis . '"
                        data-kategori="' . htmlspecialchars($kategori, ENT_QUOTES, 'UTF-8') . '"
                    >' . htmlspecialchars($nama_transaksi, ENT_QUOTES, 'UTF-8') . ' (' . htmlspecialchars($kategori, ENT_QUOTES, 'UTF-8') . ')</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="tanggal_edit">* Tanggal Transaksi</label>
                <input type="date" name="tanggal" id="tanggal_edit" class="form-control" value="'.$TanggalFormat.'">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="jam_edit">* Jam Transaksi</label>
                <input type="time" name="jam" id="jam_edit" class="form-control" value="'.$JamFormat.'">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="JumlahTotalEdit">Jumlah (Rp)</label>
                <input type="text" name="JumlahTotal" id="JumlahTotalEdit" class="form-control" readonly value="'.$JumlahFormat.'">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="JumlahPembayaranEdit">Pembayaran (Rp)</label>
                <input type="text" name="JumlahPembayaran" id="JumlahPembayaranEdit" class="form-control" inputmode="numeric" value="'.$PembayaranFormat.'">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="status_edit">Status</label>
                <input type="text" name="status" id="status_edit" class="form-control" readonly value="'.$status.'">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="keterangan_edit">Keterangan</label>
                <textarea name="keterangan" id="keterangan_edit" class="form-control">'.$keterangan.'</textarea>
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