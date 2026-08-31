<?php
    date_default_timezone_set('Asia/Jakarta');

    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    header('Content-Type: application/json; charset=utf-8');

    $response_error = static function (string $message): void {
        echo json_encode([
            'status' => 'error',
            'message' => $message,
            'html' => '',
            'title' => '',
            'data_count' => 0
        ], JSON_UNESCAPED_UNICODE);
        exit;
    };

    if (empty($SessionIdAkses)) {
        $response_error('Sesi akses sudah berakhir. Silakan login ulang.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response_error('Metode request tidak valid.');
    }

    $akun_pemasukan = trim($_POST['akun_pemasukan'] ?? '');
    $akun_pengeluaran = trim($_POST['akun_pengeluaran'] ?? '');
    $periode1 = trim($_POST['periode1'] ?? '');
    $periode2 = trim($_POST['periode2'] ?? '');

    if ($akun_pemasukan === '' || $akun_pengeluaran === '' || $periode1 === '' || $periode2 === '') {
        $response_error('Lengkapi akun pemasukan, akun pengeluaran, dan periode laporan.');
    }

    if (!ctype_digit($akun_pemasukan) || !ctype_digit($akun_pengeluaran)) {
        $response_error('ID akun perkiraan tidak valid.');
    }

    $valid_date = static function (string $date): bool {
        $date_object = DateTime::createFromFormat('Y-m-d', $date);
        return $date_object !== false && $date_object->format('Y-m-d') === $date;
    };

    if (!$valid_date($periode1) || !$valid_date($periode2)) {
        $response_error('Format periode tidak valid.');
    }
    if ($periode1 > $periode2) {
        $response_error('Periode awal tidak boleh lebih besar dari periode akhir.');
    }

    $periode2_exclusive = (new DateTime($periode2))->modify('+1 day')->format('Y-m-d');

    $stmt_akun = $Conn->prepare(" 
        SELECT id_perkiraan, kode, nama, level
        FROM akun_perkiraan
        WHERE id_perkiraan = ? OR id_perkiraan = ?
    ");
    if (!$stmt_akun) {
        $response_error('Gagal mempersiapkan query akun perkiraan.');
    }
    $id_pemasukan = (int) $akun_pemasukan;
    $id_pengeluaran = (int) $akun_pengeluaran;
    $stmt_akun->bind_param('ii', $id_pemasukan, $id_pengeluaran);
    if (!$stmt_akun->execute()) {
        $stmt_akun->close();
        $response_error('Gagal mengambil data akun perkiraan.');
    }

    $result_akun = $stmt_akun->get_result();
    $akun_by_id = [];
    while ($akun = $result_akun->fetch_assoc()) {
        $akun_by_id[(string) $akun['id_perkiraan']] = $akun;
    }
    $stmt_akun->close();

    if (!isset($akun_by_id[$akun_pemasukan]) || !isset($akun_by_id[$akun_pengeluaran])) {
        $response_error('Akun perkiraan yang dipilih tidak ditemukan.');
    }

    $akun_pilihan = [$akun_by_id[$akun_pemasukan], $akun_by_id[$akun_pengeluaran]];
    $kode_conditions = [];
    $kode_params = [];
    foreach ($akun_pilihan as $akun) {
        $kode_conditions[] = ((int) $akun['level'] === 1)
            ? 'j.kode_perkiraan LIKE ?'
            : 'j.kode_perkiraan = ?';
        $kode_params[] = ((int) $akun['level'] === 1)
            ? $akun['kode'] . '%'
            : $akun['kode'];
    }

    $stmt_count = $Conn->prepare(" 
        SELECT COUNT(*) AS total
        FROM jurnal AS j
        WHERE j.tanggal >= ? AND j.tanggal < ?
          AND (" . implode(' OR ', $kode_conditions) . ")
    ");
    if (!$stmt_count) {
        $response_error('Gagal mempersiapkan query data jurnal.');
    }
    $bind_values = array_merge([$periode1, $periode2_exclusive], $kode_params);
    $stmt_count->bind_param(str_repeat('s', count($bind_values)), ...$bind_values);
    if (!$stmt_count->execute()) {
        $stmt_count->close();
        $response_error('Gagal menghitung data jurnal.');
    }
    $count_data = $stmt_count->get_result()->fetch_assoc();
    $stmt_count->close();
    $data_count = (int) ($count_data['total'] ?? 0);

    $esc = static function ($value): string {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };
    $nama_pemasukan = $esc($akun_by_id[$akun_pemasukan]['kode'] . ' - ' . $akun_by_id[$akun_pemasukan]['nama']);
    $nama_pengeluaran = $esc($akun_by_id[$akun_pengeluaran]['kode'] . ' - ' . $akun_by_id[$akun_pengeluaran]['nama']);

    echo json_encode([
        'status' => 'success',
        'message' => 'Data siap di-export.',
        'title' => 'Export Laba Rugi',
        'data_count' => $data_count,
        'html' => '
            <input type="hidden" name="akun_pemasukan" value="' . $esc($akun_pemasukan) . '">
            <input type="hidden" name="akun_pengeluaran" value="' . $esc($akun_pengeluaran) . '">
            <input type="hidden" name="periode1" value="' . $esc($periode1) . '">
            <input type="hidden" name="periode2" value="' . $esc($periode2) . '">
            <div class="alert alert-info text-center">
                <div><small>Akun Pemasukan</small><br><b>' . $nama_pemasukan . '</b></div>
                <div class="mt-2"><small>Akun Pengeluaran</small><br><b>' . $nama_pengeluaran . '</b></div>
                <hr>
                <small>Jumlah Record</small>
                <h1>' . $data_count . '</h1>
                <i class="bi bi-check-circle"></i> Data siap di-export.
            </div>
        '
    ], JSON_UNESCAPED_UNICODE);
    exit;
?>
