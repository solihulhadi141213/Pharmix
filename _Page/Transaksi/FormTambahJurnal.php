<?php
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/SettingGeneral.php";
    include "../../_Config/Session.php";
    header('Content-Type: application/json; charset=utf-8');

    function responseError($message) {
        echo json_encode([
            'status'  => 'error',
            'message' => $message,
            'html'    => '<div class="alert alert-danger mb-0"><small>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</small></div>'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (empty($SessionIdAkses)) {
        responseError('Sesi akses sudah berakhir. Silakan login ulang.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseError('Metode request tidak valid.');
    }

    if(empty($_POST['id_transaksi'])){
        responseError('ID Transaksi Tidak Boleh Kosong');
    }

    $id_transaksi = $_POST['id_transaksi'] ;
   

    $sql = "SELECT id_transaksi, tanggal FROM transaksi WHERE id_transaksi = ? LIMIT 1";
    $stmt = mysqli_prepare($Conn, $sql);
    if (!$stmt) {
        responseError('Gagal mempersiapkan query transaksi.');
    }
    mysqli_stmt_bind_param($stmt, 's', $id_transaksi);
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        responseError('Gagal mengambil data transaksi.');
    }
    $result = mysqli_stmt_get_result($stmt);
    if (!$result || mysqli_num_rows($result) === 0) {
        mysqli_stmt_close($stmt);
        responseError('Data transaksi tidak ditemukan.');
    }
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    $tanggal = $data['tanggal'] ?? '';
    $tanggal_format = '';
    if (!empty($tanggal)) {
        $strtotime = strtotime($tanggal);
        if ($strtotime !== false) {
            $tanggal_format = date('Y-m-d', $strtotime);
        }
    }

    $sql_akun = "SELECT kode, nama, level, saldo_normal FROM akun_perkiraan ORDER BY nama ASC";
    $result_akun = mysqli_query($Conn, $sql_akun);
    if (!$result_akun) {
        responseError('Gagal mengambil data akun perkiraan.');
    }

    $options_akun = '<option value="">Pilih</option>';
    while ($akun = mysqli_fetch_assoc($result_akun)) {
        $kode         = $akun['kode'];
        $nama         = $akun['nama'];
        $level        = (int) $akun['level'];
        $saldo_normal = $akun['saldo_normal'];
        $kolom_level  = 'kd' . $level;

        if (!preg_match('/^kd[0-9]+$/', $kolom_level)) {
            continue;
        }
    }

    $html = '
        <input type="hidden" name="id_transaksi" value="' . $id_transaksi . '">
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="kode_perkiraan">Akun Perkiraan</label>
                <select name="kode_perkiraan" id="kode_perkiraan" class="form-select" required>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="d_k">Posisi (D/K)</label>
                <select name="d_k" id="d_k" class="form-select" required>
                    <option value="">Pilih</option>
                    <option value="D">Debet</option>
                    <option value="K">Kredit</option>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="nilai">Nilai</label>
                <input type="text" class="form-control" id="nilai" name="nilai" inputmode="numeric" autocomplete="off" required>
            </div>
        </div>
    ';

    echo json_encode([
        'status'  => 'success',
        'message' => 'Form jurnal berhasil dimuat.',
        'html'    => $html
    ], JSON_UNESCAPED_UNICODE);
    exit;
?>