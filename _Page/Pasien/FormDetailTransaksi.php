<?php
header('Content-Type: application/json; charset=utf-8');

include __DIR__ . '/../../_Config/Connection.php';
include __DIR__ . '/../../_Config/GlobalFunction.php';
include __DIR__ . '/../../_Config/Session.php';
include __DIR__ . '/../../_Config/FungsiAkses.php';

// Kontrak respons untuk modal detail transaksi pada Pasien.js.
function responDetailTransaksi($status, $message, $html = '')
{
    echo json_encode(compact('status', 'message', 'html'), JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function teksDetailTransaksi($value)
{
    $value = trim((string) ($value ?? ''));
    return htmlspecialchars($value === '' ? '-' : $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function rupiahDetailTransaksi($value)
{
    return 'Rp ' . number_format((float) ($value ?? 0), 2, ',', '.');
}

// Kedua query menggunakan ID teks dan selalu menutup prepared statement.
function ambilDataDetailTransaksi($conn, $query, $idTransaksi)
{
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new RuntimeException('Gagal menyiapkan query detail transaksi.');
    }

    try {
        mysqli_stmt_bind_param($stmt, 's', $idTransaksi);
        if (!mysqli_stmt_execute($stmt)) {
            throw new RuntimeException('Gagal membaca detail transaksi.');
        }
        return mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    } finally {
        mysqli_stmt_close($stmt);
    }
}

// Template HTML responsif dengan escaping seluruh nilai dari database.
function renderDetailTransaksi(array $transaksi, array $daftarRincian)
{
    $timestamp = empty($transaksi['tanggal']) ? false : strtotime($transaksi['tanggal']);
    $informasi = [
        'ID Transaksi' => $transaksi['id_transaksi_jual_beli'],
        'Tanggal' => $timestamp === false ? '-' : date('d/m/Y H:i', $timestamp),
        'Kategori' => $transaksi['kategori'],
        'Status Pembayaran' => $transaksi['status'],
    ];
    $ringkasan = [
        'Subtotal' => $transaksi['subtotal'],
        'PPN' => $transaksi['ppn'],
        'Diskon' => $transaksi['diskon'],
        'Total Transaksi' => $transaksi['total'],
        'Tunai (Cash)' => $transaksi['cash'],
        'Kembalian' => $transaksi['kembalian'],
    ];

    ob_start();
    ?>
    <section aria-labelledby="judul-informasi-transaksi-pasien">
        <h6 id="judul-informasi-transaksi-pasien" class="fw-bold mb-3">A. Informasi Transaksi</h6>
        <dl class="row g-3 small mb-0">
            <?php foreach ($informasi as $label => $nilai): ?>
                <div class="col-12 col-md-6">
                    <dt class="text-muted fw-normal"><?= teksDetailTransaksi($label) ?></dt>
                    <dd class="text-break mb-0"><?= teksDetailTransaksi($nilai) ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
    </section>

    <section class="mt-4" aria-labelledby="judul-ringkasan-transaksi-pasien">
        <h6 id="judul-ringkasan-transaksi-pasien" class="fw-bold mb-3">B. Ringkasan Nominal</h6>
        <dl class="row g-3 small mb-0">
            <?php foreach ($ringkasan as $label => $nilai): ?>
                <div class="col-12 col-md-6">
                    <dt class="text-muted fw-normal"><?= teksDetailTransaksi($label) ?></dt>
                    <dd class="text-break mb-0<?= $label === 'Total Transaksi' ? ' fw-bold' : '' ?>">
                        <?= teksDetailTransaksi(rupiahDetailTransaksi($nilai)) ?>
                    </dd>
                </div>
            <?php endforeach; ?>
        </dl>
    </section>

    <section class="mt-4" aria-labelledby="judul-rincian-transaksi-pasien">
        <h6 id="judul-rincian-transaksi-pasien" class="fw-bold mb-3">
            C. Rincian Transaksi <span class="badge bg-secondary"><?= count($daftarRincian) ?></span>
        </h6>
        <?php if (!$daftarRincian): ?>
            <div class="alert alert-warning mb-0" role="status">
                <small>Belum ada rincian transaksi.</small>
            </div>
        <?php else: ?>
            <ul class="list-group">
                <?php foreach ($daftarRincian as $index => $item): ?>
                    <?php
                    $rincian = [
                        'Jumlah' => trim(($item['qty'] ?? '-') . ' ' . ($item['satuan'] ?? '')),
                        'Harga Satuan' => rupiahDetailTransaksi($item['harga']),
                        'PPN' => rupiahDetailTransaksi($item['ppn']),
                        'Diskon' => rupiahDetailTransaksi($item['diskon']),
                        'Subtotal Item' => rupiahDetailTransaksi($item['subtotal']),
                    ];
                    ?>
                    <li class="list-group-item p-3">
                        <div class="fw-bold text-break mb-3">
                            <?= $index + 1 ?>. <?= teksDetailTransaksi($item['nama_barang']) ?>
                        </div>
                        <dl class="row g-3 small mb-0">
                            <?php foreach ($rincian as $label => $nilai): ?>
                                <div class="col-12 col-md-6">
                                    <dt class="text-muted fw-normal"><?= teksDetailTransaksi($label) ?></dt>
                                    <dd class="text-break mb-0"><?= teksDetailTransaksi($nilai) ?></dd>
                                </div>
                            <?php endforeach; ?>
                        </dl>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
}

// Validasi sesi dan ID sebelum mengakses data transaksi.
if (empty($SessionIdAkses)) {
    responDetailTransaksi('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
}

$idTransaksi = $_POST['id_transaksi_jual_beli'] ?? '';
if (!is_string($idTransaksi) || trim($idTransaksi) === '') {
    responDetailTransaksi('error', 'ID transaksi tidak valid atau belum diisi.');
}
$idTransaksi = trim($idTransaksi);

try {
    // Gunakan izin yang sama dengan halaman Pasien.
    if (IjinAksesSaya($Conn, $SessionIdAkses, 'oWpF1xPn8dLgRi8hRJx') !== 'Ada') {
        responDetailTransaksi('error', 'Anda tidak memiliki izin untuk mengakses detail transaksi pasien.');
    }

    $dataTransaksi = ambilDataDetailTransaksi($Conn, '
        SELECT id_transaksi_jual_beli, tanggal, kategori, status,
               subtotal, ppn, diskon, total, cash, kembalian
        FROM transaksi_jual_beli
        WHERE id_transaksi_jual_beli = ?
    ', $idTransaksi);

    if (!$dataTransaksi) {
        responDetailTransaksi('error', 'Data transaksi tidak ditemukan.');
    }

    $daftarRincian = ambilDataDetailTransaksi($Conn, '
        SELECT nama_barang, satuan, qty, harga, ppn, diskon, subtotal
        FROM transaksi_jual_beli_rincian
        WHERE id_transaksi_jual_beli = ?
        ORDER BY id_transaksi_jual_beli_rincian ASC
    ', $idTransaksi);

    $html = renderDetailTransaksi($dataTransaksi[0], $daftarRincian);
    responDetailTransaksi('success', 'Detail transaksi berhasil dimuat.', $html);
} catch (Exception $e) {
    error_log('Detail transaksi pasien: ' . $e->getMessage());
    responDetailTransaksi('error', 'Gagal memuat detail transaksi. Silakan coba lagi.');
}
