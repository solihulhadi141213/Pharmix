<?php
    // Koneksi, Function Dan Session
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Format Response & Default Response
    header('Content-Type: application/json; charset=utf-8');

    $response = [
        'status'      => 'error',
        'message'     => 'Terjadi kesalahan.',
        'html'        => '',
        'jumlah_data' => 0
    ];

    // Response
    function responseExport(string $status, string $message, string $html = '', int $jumlahData = 0): void {
        echo json_encode([
            'status'      => $status,
            'message'     => $message,
            'html'        => $html,
            'jumlah_data' => $jumlahData
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // Validasi Session
    if (empty($SessionIdAkses)) {
        responseExport('error', 'Sesi akses telah berakhir. Silakan login ulang.');
    }

    // Validasi Method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responseExport('error', 'Metode request tidak valid.');
    }

    // Hitung Medication
    $sql = "SELECT COUNT(*) AS jumlah_data FROM medication";
    $result = $Conn->query($sql);

    if (!$result) {
        responseExport('error', 'Gagal menghitung data medication.');
    }

    $data = $result->fetch_assoc();
    $jumlah_data = (int)($data['jumlah_data'] ?? 0);

    // Format Jumlah
    $jumlah_display = number_format($jumlah_data, 0, ',', '.');

    // Form
    $html = '
        <div class="row mb-3">
            <div class="col-md-5">
                <small>Jumlah Data Medication</small>
            </div>
            <div class="col-md-1">
                <small>:</small>
            </div>
            <div class="col-md-6">
                <small><b>'.$jumlah_display.' Data</b></small>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-5">
                <label for="type_file_export">
                    <small>* Format File</small>
                </label>
            </div>
            <div class="col-md-7">
                <select name="type_file" id="type_file_export" class="form-control" required>
                    <option value="">Pilih</option>
                    <option value="XLSX" selected>Microsoft Excel (.xlsx)</option>
                    <option value="CSV">CSV (.csv)</option>
                </select>
            </div>
        </div>

        <div class="alert alert-warning mb-0">
            <small>
                <i class="bi bi-exclamation-triangle"></i>
                Semakin besar jumlah data medication, semakin lama waktu yang dibutuhkan sistem untuk membuat file export.
                Jangan menutup halaman atau melakukan proses export berulang kali sebelum file selesai dibuat.
            </small>
        </div>
    ';

    if ($jumlah_data < 1) {
        $html .= '
            <div class="alert alert-secondary text-center mt-3 mb-0">
                <small>Belum ada data medication yang dapat diexport.</small>
            </div>
        ';
    }

    responseExport(
        'success',
        'Informasi export berhasil dimuat.',
        $html,
        $jumlah_data
    );
?>