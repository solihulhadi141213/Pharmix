<?php
header('Content-Type: application/json; charset=utf-8');

$respond = static function (string $status, string $message, string $html = ''): void {
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'html' => $html
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
};

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    $respond('error', 'Permintaan harus menggunakan metode POST.');
}

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    require_once __DIR__ . '/../../_Config/Connection.php';
    require_once __DIR__ . '/../../_Config/GlobalFunction.php';
    require_once __DIR__ . '/../../_Config/Session.php';

    if (empty($SessionIdAkses)) {
        $respond('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
    }

    $id_barang = filter_var($_POST['id_barang'] ?? null, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);
    if ($id_barang === false) {
        $respond('error', 'ID Barang harus berupa bilangan bulat positif.');
    }

    // Ambil medication yang benar-benar terhubung dengan barang tersebut.
    $stmt = $Conn->prepare('
        SELECT b.id_barang, b.kode_barang, b.nama_barang, b.kategori_barang,
               b.satuan_barang, b.id_index_medication,
               m.id_index_medication AS medication_index,
               m.id_medication, m.medication_code, m.medication_name,
               m.medication_category, m.kfa_code, m.sediaan_display,
               m.racikan_display, m.manufacturer_name
        FROM barang b
        LEFT JOIN medication m ON m.id_index_medication = b.id_index_medication
        WHERE b.id_barang = ?
        LIMIT 1
    ');
    $stmt->bind_param('i', $id_barang);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$data) {
        $respond('error', 'Data barang tidak ditemukan.');
    }
    if (empty($data['id_index_medication'])) {
        $respond('error', 'Barang belum memiliki index medication.');
    }
    if (empty($data['medication_index'])) {
        $respond('error', 'Data medication yang terhubung dengan barang tidak ditemukan.');
    }
} catch (mysqli_sql_exception $e) {
    $respond('error', 'Terjadi kesalahan database saat mengambil detail index medication.');
}

$escape = static function ($value): string {
    $value = trim((string) ($value ?? ''));
    return htmlspecialchars($value === '' ? '-' : $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
$renderRows = static function (array $rows) use ($escape): string {
    $html = '<dl class="row small mb-0">';
    foreach ($rows as $label => $value) {
        $html .= '<dt class="col-5 fw-normal text-muted mb-2">' . $escape($label) . '</dt>'
            . '<dd class="col-7 text-break mb-2">' . $escape($value) . '</dd>';
    }
    return $html . '</dl>';
};

$badgeClass = [
    'Obat' => 'bg-primary',
    'Alkes' => 'bg-info text-dark',
    'Lainnya' => 'bg-secondary'
][$data['medication_category']] ?? 'bg-secondary';

$html = '<section class="border rounded p-3 mb-3">'
    . '<h6 class="small fw-bold mb-3"><i class="bi bi-box-seam me-1" aria-hidden="true"></i> Informasi Barang</h6>'
    . $renderRows([
        'ID Barang' => $data['id_barang'],
        'Kode' => $data['kode_barang'],
        'Nama' => $data['nama_barang'],
        'Kategori' => $data['kategori_barang'],
        'Satuan' => $data['satuan_barang']
    ])
    . '</section>'
    . '<section class="border rounded p-3">'
    . '<div class="d-flex justify-content-between align-items-start gap-2 mb-3">'
    . '<h6 class="small fw-bold mb-0"><i class="bi bi-link-45deg me-1" aria-hidden="true"></i> Index Medication</h6>'
    . '<span class="badge ' . $badgeClass . '">' . $escape($data['medication_category']) . '</span></div>'
    . $renderRows([
        'ID Index' => $data['id_index_medication'],
        'ID Medication' => $data['id_medication'],
        'Kode' => $data['medication_code'],
        'Nama' => $data['medication_name'],
        'Kode KFA' => $data['kfa_code'],
        'Sediaan' => $data['sediaan_display'],
        'Jenis Racikan' => $data['racikan_display'],
        'Produsen' => $data['manufacturer_name']
    ])
    . '</section>'
    . '<button type="button" class="btn btn-danger w-100 mt-3 hapus_koneksi_index" data-id="'.$id_barang.'"><i class="bi bi-plugin"></i> Hapus Koneksi Index</button>';

$respond('success', 'Detail index medication berhasil dimuat.', $html);
