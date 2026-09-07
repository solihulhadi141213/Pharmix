<?php
header('Content-Type: application/json; charset=utf-8');

$respond = static function (string $status, string $message, string $html = ''): void {
    if ($status === 'error') {
        $html = '<div class="alert alert-warning text-center mb-0" role="alert">'
            . '<small>' . htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</small></div>';
    }
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'html' => $html
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
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
        $respond('error', 'Sesi akses sudah berakhir. Silakan login kembali.');
    }

    $selectedTag = isset($_POST['tags']) && is_string($_POST['tags']) ? trim($_POST['tags']) : '';

    // Hitung dokumentasi unik agar tag berulang tidak menggandakan jumlah.
    $result = $Conn->query("
        SELECT TRIM(dt.tags) AS tag, COUNT(DISTINCT d.id_dokumentasi) AS jumlah
        FROM dokumentasi_tags dt
        INNER JOIN dokumentasi d ON d.id_dokumentasi = dt.id_dokumentasi
        WHERE TRIM(dt.tags) <> ''
        GROUP BY TRIM(dt.tags)
        ORDER BY tag ASC
    ");

    $resultTotal = $Conn->query('SELECT COUNT(*) AS jumlah FROM dokumentasi');
    $totalDokumentasi = (int) $resultTotal->fetch_assoc()['jumlah'];
    $resultTotal->free();
    $allActive = $selectedTag === '';
    $html = '<button type="button" class="btn btn-sm rounded-pill pilih_tags '
        . ($allActive ? 'btn-primary active' : 'btn-outline-primary')
        . '" data-id="" aria-pressed="' . ($allActive ? 'true' : 'false') . '">All'
        . ' <span class="badge bg-light text-dark ms-1" aria-label="Jumlah dokumentasi">'
        . $totalDokumentasi . '</span></button>';
    while ($row = $result->fetch_assoc()) {
        $tag = trim($row['tag']);
        if ($tag === '') {
            continue;
        }
        $safeTag = htmlspecialchars($tag, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $active = $selectedTag === $tag;
        $class = $active ? 'btn-primary active' : 'btn-outline-primary';
        $html .= '<button type="button" class="btn btn-sm rounded-pill pilih_tags text-wrap text-break ' . $class . '"'
            . ' data-id="' . $safeTag . '" aria-pressed="' . ($active ? 'true' : 'false') . '">'
            . '<i class="bi bi-tag me-1" aria-hidden="true"></i>' . $safeTag
            . ' <span class="badge bg-light text-dark ms-1" aria-label="Jumlah dokumentasi">' . (int) $row['jumlah'] . '</span></button>';
    }
    $result->free();

    $html = '<div class="d-flex flex-wrap gap-2" aria-label="Tags dokumentasi">' . $html . '</div>';

    $respond('success', 'Label tags dokumentasi berhasil dimuat.', $html);
} catch (mysqli_sql_exception $e) {
    $respond('error', 'Terjadi kesalahan database saat mengambil tags dokumentasi.');
}
