<?php
    // ============================================================
    // KONFIGURASI — ditempatkan di _Page/Condition/
    // ============================================================
    date_default_timezone_set('Asia/Jakarta');
    require_once __DIR__.'/../../_Config/Connection.php';
    require_once __DIR__.'/../../_Config/GlobalFunction.php';
    require_once __DIR__.'/../../_Config/Session.php';
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');

    // Helper lokal agar tidak bentrok dengan GlobalFunction.php.
    $escape = static function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    };
    $fail = static function ($message) use ($escape) {
        echo '<div class="alert alert-danger mb-0" role="alert">'.nl2br($escape($message)).'</div>';
        exit;
    };
    $dateText = static function ($value) {
        if (!$value) return '-';
        // Pertahankan presisi tanggal FHIR; jangan menambahkan jam yang tidak tersedia.
        if (preg_match('/^\d{4}(-\d{2})?(-\d{2})?$/', $value)) return $value;
        try {
            return (new DateTimeImmutable($value))->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('d/m/Y H:i:s').' WIB';
        } catch (Throwable $error) {
            return $value;
        }
    };
    $concept = static function ($value) {
        $parts = [];
        if (!empty($value['text'])) $parts[] = $value['text'];
        foreach (($value['coding'] ?? []) as $coding) {
            $label = trim(($coding['display'] ?? '').' ['.($coding['code'] ?? '-').']');
            if (!empty($coding['system'])) $label .= "\n".$coding['system'];
            $parts[] = $label;
        }
        return $parts ? implode("\n", $parts) : '-';
    };
    $reference = static function ($value) {
        $parts = array_filter([$value['display'] ?? '', $value['reference'] ?? ''], 'strlen');
        return $parts ? implode("\n", $parts) : '-';
    };
    $row = static function ($label, $value) use ($escape) {
        return '<div class="row mb-2"><div class="col-sm-4 text-muted"><small>'.$escape($label).'</small></div>'
            .'<div class="col-sm-8 text-break"><small>'.nl2br($escape($value === '' ? '-' : $value)).'</small></div></div>';
    };
    $section = static function ($title) use ($escape) {
        return '<div class="border-bottom mt-3 mb-3 pb-2 fw-bold">'.$escape($title).'</div>';
    };

    // ============================================================
    // VALIDASI SESSION DAN INPUT
    // ============================================================
    if (empty($SessionIdAkses)) $fail('Sesi akses sudah berakhir. Silakan login ulang.');
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') $fail('Metode request tidak valid.');
    $id_condition = $_POST['id_condition'] ?? '';
    if (!is_string($id_condition)) $fail('ID Condition tidak valid.');
    $id_condition = trim($id_condition);
    if ($id_condition === '') $fail('ID Condition tidak boleh kosong.');
    if (!preg_match('/^[A-Za-z0-9\-.]{1,64}$/D', $id_condition)) $fail('Format ID Condition tidak valid.');

    // ============================================================
    // KONFIGURASI SATUSEHAT DAN TOKEN
    // ============================================================
    try {
        $stmt = $Conn->prepare('SELECT url_connection_satu_sehat FROM connection_satu_sehat WHERE status_connection_satu_sehat = 1 LIMIT 1');
        if (!$stmt || !$stmt->execute()) $fail('Gagal membuka pengaturan SATUSEHAT.');
        $stmt->bind_result($configuredUrl);
        $found = $stmt->fetch();
        $stmt->close();
    } catch (Throwable $error) {
        $fail('Gagal membuka pengaturan SATUSEHAT.');
    }
    $baseUrl = $found ? rtrim(trim((string) $configuredUrl), '/') : '';
    if ($baseUrl === '') $fail('URL koneksi SATUSEHAT belum dikonfigurasi.');
    $urlParts = parse_url($baseUrl);
    if (!$urlParts || strtolower($urlParts['scheme'] ?? '') !== 'https' || empty($urlParts['host'])
        || isset($urlParts['user']) || isset($urlParts['pass']) || isset($urlParts['query']) || isset($urlParts['fragment'])) {
        $fail('URL SATUSEHAT harus berupa URL HTTPS tanpa kredensial, query, atau fragment.');
    }
    if (!function_exists('curl_init')) $fail('Ekstensi cURL PHP belum aktif.');
    try {
        $tokenResult = generateTokenSatuSehat($Conn);
    } catch (Throwable $error) {
        $fail('Gagal memperoleh token SATUSEHAT. Periksa pengaturan koneksi.');
    }
    if (($tokenResult['status'] ?? '') !== 'success') $fail('Gagal memperoleh token SATUSEHAT. Periksa pengaturan koneksi.');
    $token = trim($tokenResult['token'] ?? '');
    if ($token === '' || preg_match('/[\r\n]/', $token)) $fail('Token SATUSEHAT tidak valid.');

    // Mendukung konfigurasi berupa host maupun base URL FHIR lengkap.
    $fhirBase = preg_match('~/fhir-r4/v1$~', $baseUrl) ? $baseUrl : $baseUrl.'/fhir-r4/v1';
    $url = $fhirBase.'/Condition/'.rawurlencode($id_condition);
    $curl = curl_init($url);
    if ($curl === false) $fail('Gagal menginisialisasi koneksi SATUSEHAT.');
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_FOLLOWLOCATION => false,
        // Localhost: aktifkan kembali VERIFYPEER=true dan VERIFYHOST=2 di production.
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer '.$token, 'Accept: application/fhir+json']
    ]);
    $response = curl_exec($curl);
    $curlError = curl_errno($curl);
    $curlErrorMessage = curl_error($curl);
    $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ($response === false || $curlError !== 0) {
        error_log('FormDetailIdCondition cURL '.$curlError.': '.$curlErrorMessage);
        $fail($curlError === CURLE_OPERATION_TIMEDOUT
            ? 'Koneksi SATUSEHAT melewati batas waktu. Silakan coba kembali.'
            : 'Gagal mengambil detail Condition dari SATUSEHAT (cURL '.$curlError.'): '.$curlErrorMessage);
    }

    // ============================================================
    // VALIDASI RESPONS API
    // ============================================================
    $data = json_decode($response, true);
    if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
        $fail('Respons SATUSEHAT bukan JSON resource yang valid (HTTP '.$httpCode.').');
    }
    if ($httpCode < 200 || $httpCode >= 300 || ($data['resourceType'] ?? '') === 'OperationOutcome') {
        $messages = [];
        foreach (($data['issue'] ?? []) as $issue) {
            $messages[] = $issue['details']['text'] ?? $issue['diagnostics'] ?? $issue['code'] ?? 'Kesalahan tidak diketahui.';
        }
        $fallback = $httpCode === 404 ? 'Condition tidak ditemukan. Periksa ID dan lingkungan sandbox/production.' : 'Gagal mengambil data Condition.';
        $fail('SATUSEHAT (HTTP '.$httpCode."): ".($messages ? implode("\n", array_unique($messages)) : $fallback));
    }
    if (($data['resourceType'] ?? '') !== 'Condition') $fail('Resource yang diterima bukan Condition.');
    if (($data['id'] ?? '') !== $id_condition) $fail('ID Condition pada respons tidak sesuai permintaan.');

    // ============================================================
    // HTML DETAIL — langsung cocok dengan success: .html(response)
    // ============================================================
    $html = $section('A. Informasi Condition');
    $html .= $row('ID Condition', $data['id']);
    $html .= $row('Diagnosis', $concept($data['code'] ?? []));
    $html .= $row('Status Klinis', $concept($data['clinicalStatus'] ?? []));
    $html .= $row('Status Verifikasi', $concept($data['verificationStatus'] ?? []));
    foreach (($data['verificationStatus']['coding'] ?? []) as $coding) {
        if (($coding['code'] ?? '') === 'entered-in-error') {
            $html .= '<div class="alert alert-warning py-2"><small>Catatan ini ditandai salah input (entered-in-error).</small></div>';
            break;
        }
    }
    $categories = array_map($concept, $data['category'] ?? []);
    $html .= $row('Kategori', $categories ? implode("\n\n", $categories) : '-');
    $html .= $row('Tingkat Keparahan', $concept($data['severity'] ?? []));
    $sites = array_map($concept, $data['bodySite'] ?? []);
    $html .= $row('Lokasi Anatomis', $sites ? implode("\n\n", $sites) : '-');
    $html .= $section('B. Pasien, Kunjungan, dan Petugas');
    $html .= $row('Pasien', $reference($data['subject'] ?? []));
    $html .= $row('Encounter', $reference($data['encounter'] ?? []));
    $html .= $row('Penyata Diagnosis', $reference($data['asserter'] ?? []));
    $html .= $row('Pencatat', $reference($data['recorder'] ?? []));
    $html .= $section('C. Waktu');
    $html .= $row('Dicatat', $dateText($data['recordedDate'] ?? ''));
    foreach (['onset' => 'Mulai Kondisi', 'abatement' => 'Kondisi Teratasi / Remisi'] as $prefix => $label) {
        $value = '-';
        foreach (['DateTime', 'Age', 'Period', 'Range', 'String'] as $suffix) {
            if (!isset($data[$prefix.$suffix])) continue;
            $raw = $data[$prefix.$suffix];
            if ($suffix === 'DateTime') $value = $dateText($raw);
            elseif ($suffix === 'String') $value = $raw;
            elseif ($suffix === 'Age') $value = trim(($raw['comparator'] ?? '').' '.($raw['value'] ?? '-').' '.($raw['unit'] ?? $raw['code'] ?? ''));
            elseif ($suffix === 'Period') $value = $dateText($raw['start'] ?? '').' — '.$dateText($raw['end'] ?? '');
            else $value = json_encode($raw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            break;
        }
        $html .= $row($label, $value);
    }
    $html .= $row('Terakhir Diperbarui', $dateText($data['meta']['lastUpdated'] ?? ''));
    $html .= $section('D. Identifier dan Catatan');
    foreach (($data['identifier'] ?? []) as $index => $identifier) {
        $html .= $row('Identifier '.($index + 1), ($identifier['value'] ?? '-')."\n".($identifier['system'] ?? '-'));
    }
    if (empty($data['identifier'])) $html .= $row('Identifier', '-');
    foreach (($data['note'] ?? []) as $index => $note) {
        $text = $note['text'] ?? '-';
        if (!empty($note['authorString'])) $text .= "\n".$note['authorString'];
        elseif (!empty($note['authorReference'])) $text .= "\n".$reference($note['authorReference']);
        if (!empty($note['time'])) $text .= "\n".$dateText($note['time']);
        $html .= $row('Catatan '.($index + 1), $text);
    }
    if (empty($data['note'])) $html .= $row('Catatan', '-');
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $html .= '<details class="mt-3"><summary class="text-primary">Lihat JSON Condition</summary>'
        .'<pre class="bg-light border rounded p-3 mt-2 mb-0 small" style="max-height:350px;overflow:auto;white-space:pre-wrap;overflow-wrap:anywhere;">'
        .$escape($json).'</pre></details>';
    echo $html;
