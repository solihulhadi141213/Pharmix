<?php
    date_default_timezone_set('Asia/Jakarta');

    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/SettingGeneral.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');

    function responseError($message) {
        echo json_encode([
            'status' => 'error',
            'message' => $message,
            'html' => '
                <div class="alert alert-danger mb-0">
                    <small>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</small>
                </div>
            '
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    if (empty($SessionIdAkses)) {
        responseError('Sesi akses sudah berakhir. Silakan login kembali.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseError('Metode request tidak valid.');
    }

    $id_jurnal = trim($_POST['id_jurnal'] ?? '');

    if ($id_jurnal === '' || !ctype_digit($id_jurnal)) {
        responseError('ID jurnal tidak valid.');
    }

    $id_jurnal = (int)$id_jurnal;

    if ($id_jurnal <= 0) {
        responseError('ID jurnal tidak valid.');
    }

    $sql = "
        SELECT
            id_jurnal,
            kategori,
            uuid,
            id_transaksi,
            tanggal,
            kode_perkiraan,
            nama_perkiraan,
            d_k,
            nilai
        FROM jurnal
        WHERE id_jurnal = ?
        LIMIT 1
    ";

    $stmt = mysqli_prepare($Conn, $sql);

    if (!$stmt) {
        responseError('Gagal mempersiapkan query jurnal.');
    }

    mysqli_stmt_bind_param($stmt, 'i', $id_jurnal);

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        responseError('Gagal mengambil data jurnal.');
    }

    $result = mysqli_stmt_get_result($stmt);

    if (!$result || mysqli_num_rows($result) === 0) {
        mysqli_stmt_close($stmt);
        responseError('Data jurnal tidak ditemukan.');
    }

    $data = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    $kode_perkiraan = $data['kode_perkiraan'] ?? '';
    $nama_perkiraan = $data['nama_perkiraan'] ?? '';
    $d_k            = $data['d_k'] ?? '';
    $nilai          = (int)($data['nilai'] ?? 0);
    $tanggal        = $data['tanggal'] ?? '';
    $kategori       = $data['kategori'] ?? '';

    $nilai_format = number_format($nilai, 0, ',', '.');

    if ($d_k === 'D') {
        $posisi = 'Debet';
    } else {
        $posisi = 'Kredit';
    }

    $tanggal_format = '-';

    if (!empty($tanggal)) {
        $strtotime = strtotime($tanggal);

        if ($strtotime !== false) {
            $tanggal_format = date('d-m-Y', $strtotime);
        }
    }

    $html = '
        <input type="hidden" name="id_jurnal" value="' . $id_jurnal . '">

        <div class="alert alert-warning">
            <div class="d-flex">
                <div class="me-2">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div>
                    <strong>Perhatian!</strong>
                    <br>
                    <small>
                        Data jurnal yang dihapus tidak dapat dikembalikan lagi.
                        Pastikan data yang akan dihapus sudah benar.
                    </small>
                </div>
            </div>
        </div>

        <div class="card border">
            <div class="card-body">

                <div class="row mb-2">
                    <div class="col-5">
                        <small class="text-muted">Akun Perkiraan</small>
                    </div>
                    <div class="col-7 text-end">
                        <strong>' . htmlspecialchars($nama_perkiraan, ENT_QUOTES, 'UTF-8') . '</strong>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-5">
                        <small class="text-muted">Kode Perkiraan</small>
                    </div>
                    <div class="col-7 text-end">
                        ' . htmlspecialchars($kode_perkiraan, ENT_QUOTES, 'UTF-8') . '
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-5">
                        <small class="text-muted">Tanggal</small>
                    </div>
                    <div class="col-7 text-end">
                        ' . htmlspecialchars($tanggal_format, ENT_QUOTES, 'UTF-8') . '
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-5">
                        <small class="text-muted">Posisi</small>
                    </div>
                    <div class="col-7 text-end">
                        <strong>' . htmlspecialchars($posisi, ENT_QUOTES, 'UTF-8') . '</strong>
                    </div>
                </div>

                <div class="row">
                    <div class="col-5">
                        <small class="text-muted">Nilai</small>
                    </div>
                    <div class="col-7 text-end">
                        <strong>Rp ' . $nilai_format . '</strong>
                    </div>
                </div>

            </div>
        </div>

        <div class="text-center mt-3">
            <strong>Apakah Anda yakin ingin menghapus jurnal ini?</strong>
        </div>
    ';

    echo json_encode([
        'status' => 'success',
        'message' => 'Data jurnal berhasil ditemukan.',
        'html' => $html
    ], JSON_UNESCAPED_UNICODE);

    exit;
?>