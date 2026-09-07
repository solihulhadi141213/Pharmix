<?php
header('Content-Type: application/json; charset=utf-8');
$respond = static function ($status, $message, $html = '') {
    echo json_encode(compact('status', 'message', 'html'), JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
};
$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    $respond('error', 'Permintaan harus menggunakan POST.');
}
try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    require_once __DIR__ . '/../../_Config/Connection.php';
    require_once __DIR__ . '/../../_Config/GlobalFunction.php';
    require_once __DIR__ . '/../../_Config/Session.php';
    if (empty($SessionIdAkses)) {
        $respond('error', 'Sesi akses sudah berakhir. Silakan login kembali.');
    }
    $id = filter_var($_POST['id_dokumentasi'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        $respond('error', 'ID dokumentasi tidak valid.');
    }
    $stmt = $Conn->prepare('SELECT judul, deskripsi, author_name, creat_at, status FROM dokumentasi WHERE id_dokumentasi = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$data) {
        $respond('error', 'Dokumentasi tidak ditemukan.');
    }

    $stmt = $Conn->prepare('SELECT DISTINCT tags FROM dokumentasi_tags WHERE id_dokumentasi = ? ORDER BY tags');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $tags = $stmt->get_result();
    $htmlTags = '';
    while ($tag = $tags->fetch_assoc()) {
        if (trim($tag['tags']) !== '') {
            $htmlTags .= '<span class="badge bg-light text-dark border me-1 mb-1">' . $escape($tag['tags']) . '</span>';
        }
    }
    $stmt->close();

    // Pertahankan format dasar editor tanpa menjalankan HTML atau atribut aktif.
    $renderText = static function ($value) use ($escape) {
        $dom = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><html><body>' . (string) $value . '</body></html>', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $render = null;
        $render = static function ($node) use (&$render, $escape) {
            if ($node->nodeType === XML_TEXT_NODE) return $escape($node->nodeValue);
            $tag = strtolower($node->nodeName);
            if (in_array($tag, ['script', 'style', 'iframe', 'object'], true)) return '';
            $content = '';
            foreach ($node->childNodes as $child) $content .= $render($child);
            if ($tag === 'br') return '<br>';
            if (in_array($tag, ['p', 'div', 'strong', 'b', 'em', 'i', 'u', 's', 'ul', 'ol', 'li', 'h2', 'h3', 'h4', 'blockquote', 'pre', 'code'], true)) {
                return '<' . $tag . '>' . $content . '</' . $tag . '>';
            }
            if ($tag === 'a') {
                $url = $node->getAttribute('href');
                if (preg_match('~^https?://~i', $url)) return '<a href="' . $escape($url) . '" rel="noopener noreferrer">' . $content . '</a>';
            }
            return $content;
        };
        return $render($dom->getElementsByTagName('body')->item(0));
    };

    $stmt = $Conn->prepare('SELECT * FROM dokumentasi_konten WHERE id_dokumentasi = ? ORDER BY sequence ASC, id_dokumentasi_konten ASC');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $content = '';
    while ($row = $result->fetch_assoc()) {
        $type = $row['tipe_konten'];
        if ($type === 'Text') {
            $content .= '<div class="mb-3 text-break">' . $renderText($row['text_konten']) . '</div>';
        } elseif ($type === 'List Numbering' || $type === 'List Bullet') {
            $items = json_decode($row['list_konten'] ?? '', true);
            if (is_array($items)) {
                $tag = $type === 'List Numbering' ? 'ol' : 'ul';
                $content .= '<' . $tag . ' class="mb-3">';
                foreach ($items as $item) {
                    if (is_scalar($item)) $content .= '<li class="mb-1">' . $escape($item) . '</li>';
                }
                $content .= '</' . $tag . '>';
            }
        } elseif ($type === 'Local Image' || $type === 'Url Image') {
            $url = '';
            if ($type === 'Local Image' && !empty($row['local_image_konten'])) {
                $url = 'assets/img/dokumentasi/' . rawurlencode(basename($row['local_image_konten']));
            } elseif (preg_match('~^https?://~i', $row['url_image_konten'] ?? '') && filter_var($row['url_image_konten'], FILTER_VALIDATE_URL)) {
                $url = $row['url_image_konten'];
            }
            if ($url !== '') $content .= '<figure class="mb-3"><img src="' . $escape($url) . '" class="img-fluid rounded" loading="lazy" alt="Ilustrasi ' . $escape($data['judul']) . '"></figure>';
        }
    }
    $stmt->close();
    $timestamp = strtotime($data['creat_at']);
    $tanggal = $timestamp !== false ? date('d/m/Y H:i', $timestamp) : '-';
    $html = '<article class="card bantuan-item"><div class="card-body p-3 p-md-4">'
        . '<h2 class="h5 fw-bold text-break">' . $escape($data['judul']) . '</h2>'
        . '<p class="small text-muted">' . $escape($data['author_name']) . ' &middot; '
        . ($data['status'] === 'Publish' ? 'Terbit: ' : 'Dibuat: ') . $tanggal . '</p>'
        . '<div class="mb-3">' . $htmlTags . '</div>'
        . '<p class="text-muted text-break">' . nl2br($escape($data['deskripsi'])) . '</p><hr>'
        . '<div class="bantuan-description text-break">' . ($content ?: '<p class="text-muted">Belum ada isi konten bantuan.</p>') . '</div></div></article>';
    $respond('success', 'Detail bantuan berhasil dimuat.', $html);
} catch (Throwable $e) {
    $respond('error', 'Terjadi kesalahan saat memuat detail bantuan.');
}
