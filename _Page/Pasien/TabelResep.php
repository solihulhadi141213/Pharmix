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

// Template daftar resep. Seluruh data dinamis di-escape sebelum ditampilkan.
function renderRiwayatResep(array $daftarResep)
{
    $statusMap = [
        'Draft'     => ['label' => 'DRF', 'class' => 'bg-secondary'],
        'Verified'  => ['label' => 'VRF', 'class' => 'bg-primary'],
        'Partially' => ['label' => 'PRT', 'class' => 'bg-warning text-dark'],
        'Completed' => ['label' => 'CMP', 'class' => 'bg-success'],
        'Cancelled' => ['label' => 'CNL', 'class' => 'bg-danger'],
    ];

    ob_start();
    ?>
    <ul class="list-group list-group-flush">
        <?php foreach ($daftarResep as $resep): ?>
            <?php
            $timestamp = empty($resep['datetime_creat']) ? false : strtotime($resep['datetime_creat']);
            $tanggal = $timestamp === false ? '-' : date('d/m/Y H:i', $timestamp);
            $status = $resep['status_resep'] ?: '-';
            $badge = $statusMap[$status] ?? ['label' => '-', 'class' => 'bg-secondary'];
            ?>
            <li class="list-group-item px-0">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <small>
                        <b>
                            <i class="bi bi-calendar-event"></i>
                            <a href="javascript:void(0);"
                               class="detail_resep"
                               data-id="<?= teksRiwayat($resep['id_medication_request_group']) ?>">
                                <?= teksRiwayat($tanggal) ?>
                            </a>
                        </b>
                    </small>
                    <span class="badge <?= teksRiwayat($badge['class']) ?>" title="<?= teksRiwayat($status) ?>">
                        <?= teksRiwayat($badge['label']) ?>
                    </span>
                </div>
                <div class="small" style="display: grid; grid-template-columns: max-content auto minmax(0, 1fr); column-gap: 0.5rem; row-gap: 0.25rem;">
                    <?= barisRiwayat('Dokter', $resep['dokter_nama']) ?>
                    <?= barisRiwayat('No. Resep', $resep['no_resep_nasional'], true) ?>
                    <?= barisRiwayat('Sumber Resep', $resep['sumber_resep']) ?>
                    <?= barisRiwayat('Prioritas', strtoupper($resep['priority'] ?? '-')) ?>
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

$page = filter_var($_POST['page_resep'] ?? 1, FILTER_VALIDATE_INT, $opsiBilanganPositif) ?: 1;
$batas = 5;

try {
    // Hitung jumlah resep untuk menentukan batas pagination.
    $stmt = mysqli_prepare($Conn, 'SELECT COUNT(*) AS jumlah FROM medication_request_group WHERE id_anggota = ?');
    if (!$stmt) {
        throw new RuntimeException('Gagal menyiapkan jumlah resep.');
    }

    mysqli_stmt_bind_param($stmt, 'i', $id_anggota);
    if (!mysqli_stmt_execute($stmt)) {
        throw new RuntimeException('Gagal menghitung resep.');
    }

    $result = mysqli_stmt_get_result($stmt);
    $total_data = (int) mysqli_fetch_assoc($result)['jumlah'];
    mysqli_stmt_close($stmt);

    if ($total_data === 0) {
        responRiwayat('success', '<div class="alert alert-warning text-center"><h1 class="bi bi-inboxes"></h1><small>Belum Ada Riwayat Resep</small></div>');
    }

    $total_page = (int) ceil($total_data / $batas);
    $page = min($page, $total_page);
    $posisi = ($page - 1) * $batas;

    // Ambil hanya resep pada halaman yang diminta.
    $stmt = mysqli_prepare($Conn, '
        SELECT id_medication_request_group, datetime_creat, dokter_nama,
               no_resep_nasional, sumber_resep, priority, status_resep
        FROM medication_request_group
        WHERE id_anggota = ?
        ORDER BY datetime_creat DESC, id_medication_request_group DESC
        LIMIT ?, ?
    ');
    if (!$stmt) {
        throw new RuntimeException('Gagal menyiapkan daftar resep.');
    }

    mysqli_stmt_bind_param($stmt, 'iii', $id_anggota, $posisi, $batas);
    if (!mysqli_stmt_execute($stmt)) {
        throw new RuntimeException('Gagal membaca resep.');
    }

    $result = mysqli_stmt_get_result($stmt);
    $daftarResep = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    // Render HTML setelah pengambilan data selesai, lalu kirim respons AJAX.
    $html = renderRiwayatResep($daftarResep);
    responRiwayat('success', $html, $page, $total_page, $total_data);
} catch (Exception $e) {
    error_log('Riwayat resep pasien: ' . $e->getMessage());
    responRiwayat('error', '<div class="alert alert-danger"><small>Gagal memuat riwayat resep. Silakan coba lagi.</small></div>');
}
