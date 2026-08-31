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

    // Ambil semua akun sekali untuk menghindari query berulang.
    $result_akun = mysqli_query($Conn, "
        SELECT id_perkiraan, kode, nama, saldo_normal, level
        FROM akun_perkiraan
        ORDER BY kode ASC
    ");
    if (!$result_akun) {
        $response_error('Gagal mengambil data akun perkiraan.');
    }

    $akun_by_id = [];
    $akun_list = [];
    while ($akun = mysqli_fetch_assoc($result_akun)) {
        $akun['id_perkiraan'] = (string) $akun['id_perkiraan'];
        $akun['kode'] = (string) $akun['kode'];
        $akun['level'] = (int) $akun['level'];
        $akun_by_id[$akun['id_perkiraan']] = $akun;
        $akun_list[] = $akun;
    }

    if (!isset($akun_by_id[$akun_pemasukan]) || !isset($akun_by_id[$akun_pengeluaran])) {
        $response_error('Akun perkiraan yang dipilih tidak ditemukan.');
    }

    $akun_pilihan = [
        'pemasukan' => $akun_by_id[$akun_pemasukan],
        'pengeluaran' => $akun_by_id[$akun_pengeluaran]
    ];

    $kode_conditions = [];
    $kode_params = [];
    foreach ($akun_pilihan as $akun_induk) {
        $kode_conditions[] = $akun_induk['level'] === 1
            ? 'j.kode_perkiraan LIKE ?'
            : 'j.kode_perkiraan = ?';
        $kode_params[] = $akun_induk['level'] === 1
            ? $akun_induk['kode'] . '%'
            : $akun_induk['kode'];
    }

    // Ambil seluruh jurnal pilihan dalam satu query.
    $stmt_jurnal = $Conn->prepare("
        SELECT j.kode_perkiraan, j.tanggal, j.kategori, j.d_k, j.nilai, j.nama_perkiraan
        FROM jurnal AS j
        WHERE j.tanggal >= ? AND j.tanggal < ?
          AND (" . implode(' OR ', $kode_conditions) . ")
        ORDER BY j.kode_perkiraan ASC, j.id_jurnal DESC
    ");
    if (!$stmt_jurnal) {
        $response_error('Gagal mempersiapkan query jurnal.');
    }

    $bind_values = array_merge([$periode1, $periode2_exclusive], $kode_params);
    $stmt_jurnal->bind_param(str_repeat('s', count($bind_values)), ...$bind_values);
    if (!$stmt_jurnal->execute()) {
        $stmt_jurnal->close();
        $response_error('Gagal mengambil data jurnal.');
    }

    $result_jurnal = $stmt_jurnal->get_result();
    $jurnal_by_kode = [];
    while ($jurnal = $result_jurnal->fetch_assoc()) {
        $jurnal_by_kode[$jurnal['kode_perkiraan']][] = $jurnal;
    }
    $stmt_jurnal->close();

    $esc = static function ($value): string {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };
    $rupiah = static function ($value): string {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    };

    $html = '';
    $total_record = 0;
    $saldo = ['pemasukan' => 0, 'pengeluaran' => 0];
    $nomor = ['pemasukan' => 1, 'pengeluaran' => 1];

    foreach (['pemasukan', 'pengeluaran'] as $jenis) {
        $akun_induk = $akun_pilihan[$jenis];
        $label = $jenis === 'pemasukan' ? 'A.' : 'B.';
        $judul = $jenis === 'pemasukan' ? 'Transaksi Pemasukan' : 'Transaksi Pengeluaran';

        $html .= '<tr class="bg-info"><td><b>' . $label . '</b></td>
            <td colspan="6"><b>' . $judul . '</b></td></tr>';

        foreach ($akun_list as $akun) {
            $kode = $akun['kode'];
            $termasuk = $akun_induk['level'] === 1
                ? $akun['level'] > 1 && strpos($kode, $akun_induk['kode']) === 0
                : $kode === $akun_induk['kode'];

            if (!$termasuk || empty($jurnal_by_kode[$kode])) {
                continue;
            }

            foreach ($jurnal_by_kode[$kode] as $jurnal) {
                $nilai = (float) ($jurnal['nilai'] ?? 0);
                $d_k = strtoupper(trim((string) ($jurnal['d_k'] ?? '')));
                $posisi = $d_k === 'D' ? 'Debet' : 'Kredit';
                $normal = strcasecmp($posisi, (string) $akun['saldo_normal']) === 0;
                $saldo[$jenis] += $normal ? $nilai : -$nilai;
                $warna = $normal ? 'text-success' : 'text-danger';
                $tanda = $normal ? $posisi : '(' . $posisi . ')';
                $nama_akun = $jurnal['nama_perkiraan'] ?: $akun['nama'];

                $html .= '<tr>
                    <td class="text-center">' . $label . $nomor[$jenis] . '</td>
                    <td>' . $esc($jurnal['tanggal']) . '</td>
                    <td>' . $esc($jurnal['kategori']) . '</td>
                    <td>' . $esc($kode . ' ' . $nama_akun) . '</td>
                    <td><span class="' . $warna . '">' . $esc($tanda) . '</span></td>
                    <td class="text-end">' . $rupiah($nilai) . '</td>
                    <td class="text-end">' . $rupiah($saldo[$jenis]) . '</td>
                </tr>';
                $nomor[$jenis]++;
                $total_record++;
            }
        }

        $html .= '<tr><td></td><td colspan="5"><b>JUMLAH SALDO ' .
            strtoupper($jenis) . '</b></td><td class="text-end"><b>' .
            $rupiah($saldo[$jenis]) . '</b></td></tr>';
    }

    $laba_rugi = $saldo['pemasukan'] - $saldo['pengeluaran'];
    $warna_laba = $laba_rugi < 0 ? 'text-danger' : 'text-success';
    $html .= '<tr><td></td><td colspan="5"><b>ESTIMASI LABA/RUGI</b></td>
        <td class="text-end"><b><span class="' . $warna_laba . '">' .
        $rupiah($laba_rugi) . '</span></b></td></tr>';

    $title = '<b>LAPORAN LABA / RUGI</b><br>
        <span>Periode: <b>' . $esc(date('d F Y', strtotime($periode1)) .
        ' s/d ' . date('d F Y', strtotime($periode2))) . '</b></span><br>
        <small>Pemasukan: <b>' . $esc($akun_pilihan['pemasukan']['nama']) .
        '</b> | Pengeluaran: <b>' .
        $esc($akun_pilihan['pengeluaran']['nama']) . '</b></small>';

    echo json_encode([
        'status' => 'success',
        'message' => $total_record > 0
            ? 'Data Laba Rugi berhasil ditampilkan.'
            : 'Tidak ada jurnal pada periode yang dipilih.',
        'html' => $html,
        'title' => $title,
        'data_count' => $total_record
    ], JSON_UNESCAPED_UNICODE);
    exit;
?>
