<?php
header('Content-Type: application/json; charset=utf-8');
include __DIR__ . '/../../_Config/Connection.php';
include __DIR__ . '/../../_Config/GlobalFunction.php';
include __DIR__ . '/../../_Config/Session.php';

// Helper respons JSON dan escaping HTML.
function responRiwayat($status, $html, $page = 1, $total_page = 1, $total_data = 0)
{
    echo json_encode(compact('status', 'html', 'page', 'total_page', 'total_data'), JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function teksRiwayat($value)
{
    return htmlspecialchars((string) ($value ?? '-'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function barisRiwayat($label, $value, $ringkas = false)
{
    $value = trim((string) $value);
    if ($value === '') {
        $value = '-';
    }

    $tampil = $ringkas && mb_strlen($value, 'UTF-8') > 18
        ? mb_substr($value, 0, 8, 'UTF-8') . '...' . mb_substr($value, -6, null, 'UTF-8')
        : $value;

    ob_start();
    ?>
    <span><?= teksRiwayat($label) ?></span>
    <span>:</span>
    <span class="text-break" title="<?= teksRiwayat($value) ?>"><?= teksRiwayat($tampil) ?></span>
    <?php
    return ob_get_clean();
}

// Template daftar transaksi, terpisah dari proses pengambilan data.
function renderRiwayatTransaksi(array $daftarTransaksi)
{
    $statusMap = [
        'Lunas'   => ['label' => 'LNS', 'class' => 'bg-success'],
        'Utang'   => ['label' => 'UTG', 'class' => 'bg-danger'],
        'Piutang' => ['label' => 'PTG', 'class' => 'bg-warning text-dark'],
    ];

    ob_start();
    ?>
    <ul class="list-group list-group-flush">
        <?php foreach ($daftarTransaksi as $transaksi): ?>
            <?php
                $timestamp = empty($transaksi['tanggal']) ? false : strtotime($transaksi['tanggal']);
                $tanggal = $timestamp === false ? '-' : date('d/m/Y H:i', $timestamp);
                $status = $transaksi['status'] ?: '-';
                $badge = $statusMap[$status] ?? ['label' => '-', 'class' => 'bg-secondary'];
                $total = 'Rp ' . number_format((float) ($transaksi['total'] ?? 0), 2, ',', '.');
            ?>
            <li class="list-group-item px-0">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <small>
                        <b>
                            <a href="javascript:void(0);"  class="detail_transaksi" data-id="<?php echo $transaksi['id_transaksi_jual_beli'] ?>">
                                <i class="bi bi-calendar-event"></i>
                                <?= teksRiwayat($tanggal) ?>
                            </a>
                        </b>
                    </small>
                    <span class="badge <?= teksRiwayat($badge['class']) ?>" title="<?= teksRiwayat($status) ?>">
                        <?= teksRiwayat($badge['label']) ?>
                    </span>
                </div>
                <div class="small" style="display: grid; grid-template-columns: max-content auto minmax(0, 1fr); column-gap: 0.5rem; row-gap: 0.25rem;">
                    <?= barisRiwayat('No. Transaksi', $transaksi['id_transaksi_jual_beli'], true) ?>
                    <?= barisRiwayat('Kategori', $transaksi['kategori']) ?>
                    <?= barisRiwayat('Total', $total) ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
    return ob_get_clean();
}

// Validasi sesi dan parameter permintaan.
if (empty($SessionIdAkses)) {
    responRiwayat('error', '<div class="alert alert-danger"><small>Sesi akses sudah berakhir. Silakan login ulang.</small></div>');
}

$opsiBilanganPositif = ['options' => ['min_range' => 1]];
$id_anggota = filter_var($_POST['id_anggota'] ?? null, FILTER_VALIDATE_INT, $opsiBilanganPositif);
if (!$id_anggota) {
    responRiwayat('error', '<div class="alert alert-danger"><small>ID pasien tidak valid.</small></div>');
}

$page = filter_var($_POST['page_transaksi'] ?? 1, FILTER_VALIDATE_INT, $opsiBilanganPositif) ?: 1;
$batas = 5;

try {
    // Hitung jumlah transaksi untuk menentukan batas pagination.
    $stmt = mysqli_prepare($Conn, 'SELECT COUNT(*) AS jumlah FROM transaksi_jual_beli WHERE id_anggota = ?');
    if (!$stmt) {
        throw new RuntimeException('Gagal menyiapkan jumlah transaksi.');
    }

    mysqli_stmt_bind_param($stmt, 'i', $id_anggota);
    if (!mysqli_stmt_execute($stmt)) {
        throw new RuntimeException('Gagal menghitung transaksi.');
    }

    $result = mysqli_stmt_get_result($stmt);
    $total_data = (int) mysqli_fetch_assoc($result)['jumlah'];
    mysqli_stmt_close($stmt);

    if ($total_data === 0) {
        responRiwayat('success', '<div class="alert alert-warning text-center"><h1 class="bi bi-inboxes"></h1><small>Belum Ada Riwayat Transaksi</small></div>');
    }

    $total_page = (int) ceil($total_data / $batas);
    $page = min($page, $total_page);
    $posisi = ($page - 1) * $batas;

    // Ambil transaksi pada halaman yang diminta, dimulai dari yang terbaru.
    $stmt = mysqli_prepare($Conn, '
        SELECT id_transaksi_jual_beli, tanggal, kategori, total, status
        FROM transaksi_jual_beli
        WHERE id_anggota = ?
        ORDER BY tanggal DESC, id_transaksi_jual_beli DESC
        LIMIT ?, ?
    ');
    if (!$stmt) {
        throw new RuntimeException('Gagal menyiapkan daftar transaksi.');
    }

    mysqli_stmt_bind_param($stmt, 'iii', $id_anggota, $posisi, $batas);
    if (!mysqli_stmt_execute($stmt)) {
        throw new RuntimeException('Gagal membaca transaksi.');
    }

    $result = mysqli_stmt_get_result($stmt);
    $daftarTransaksi = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    // Render HTML dan kirim respons AJAX beserta informasi pagination.
    $html = renderRiwayatTransaksi($daftarTransaksi);
    responRiwayat('success', $html, $page, $total_page, $total_data);
} catch (Exception $e) {
    error_log('Riwayat transaksi pasien: ' . $e->getMessage());
    responRiwayat('error', '<div class="alert alert-danger"><small>Gagal memuat riwayat transaksi. Silakan coba lagi.</small></div>');
}
