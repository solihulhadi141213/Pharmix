<?php
require __DIR__ . '/../../_Config/Connection.php';
require __DIR__ . '/../../_Config/GlobalFunction.php';
require __DIR__ . '/../../_Config/Session.php';

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

function ihsDetailText($value){
    return htmlspecialchars(is_scalar($value) ? (string) $value : '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function ihsDetailError($message)
{
    echo '<div class="alert alert-danger" role="alert"><small>' . ihsDetailText($message) . '</small></div>';
    exit;
}

if (empty($SessionIdAkses)) {
    ihsDetailError('Sesi akses sudah berakhir. Silakan login ulang.');
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    ihsDetailError('Metode request tidak valid.');
}
$idIhs = $_POST['id_ihs'] ?? '';
if (!is_string($idIhs) || trim($idIhs) === '') {
    ihsDetailError('ID IHS tidak boleh kosong.');
}
$idIhs = trim($idIhs);
if (!preg_match('/^[A-Za-z0-9\-\.]{1,64}$/D', $idIhs) || strpos($idIhs, '..') !== false) {
    ihsDetailError('Format ID IHS tidak valid. Gunakan ID IHS lengkap.');
}

try {
    $stmt = $Conn->prepare('SELECT url_connection_satu_sehat FROM connection_satu_sehat WHERE status_connection_satu_sehat = 1 LIMIT 1');
    if (!$stmt || !$stmt->execute()) {
        ihsDetailError('Gagal membuka pengaturan koneksi SATUSEHAT.');
    }
    $setting = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $baseUrl = rtrim(trim($setting['url_connection_satu_sehat'] ?? ''), '/');
    if ($baseUrl === '') {
        ihsDetailError('Pengaturan koneksi SATUSEHAT aktif belum tersedia.');
    }
    $tokenResult = generateTokenSatuSehat($Conn);
    $token = $tokenResult['token'] ?? '';
    if (($tokenResult['status'] ?? '') !== 'success' || !is_string($token) || $token === '') {
        ihsDetailError('Gagal memperoleh token SATUSEHAT. Silakan periksa pengaturan koneksi.');
    }

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $baseUrl . '/fhir-r4/v1/Patient/' . rawurlencode($idIhs),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token, 'Accept: application/fhir+json'],
    ]);
    $response = curl_exec($curl);
    $curlError = curl_errno($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ($response === false || $curlError) {
        ihsDetailError('Tidak dapat terhubung ke SATUSEHAT. Silakan coba lagi atau periksa koneksi server.');
    }
    if ($httpCode === 404) {
        ihsDetailError('Data pasien dengan ID IHS tersebut tidak ditemukan di SATUSEHAT.');
    }
    if ($httpCode === 401 || $httpCode === 403) {
        ihsDetailError('Akses ke data SATUSEHAT ditolak. Silakan periksa token dan izin koneksi.');
    }
    $patient = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($patient)) {
        ihsDetailError('Respons SATUSEHAT tidak valid. Silakan coba lagi.');
    }
    if (($patient['resourceType'] ?? '') === 'OperationOutcome') {
        ihsDetailError('SATUSEHAT tidak dapat memproses permintaan detail pasien (HTTP ' . $httpCode . ').');
    }
    if ($httpCode < 200 || $httpCode >= 300) {
        ihsDetailError('Gagal mengambil data SATUSEHAT (HTTP ' . $httpCode . '). Silakan coba lagi.');
    }
    if (($patient['resourceType'] ?? '') !== 'Patient' || ($patient['id'] ?? '') !== $idIhs) {
        ihsDetailError('Data pasien dalam respons SATUSEHAT tidak sesuai dengan ID IHS yang diminta.');
    }
} catch (Throwable $exception) {
    ihsDetailError('Terjadi kesalahan saat mengambil detail IHS. Silakan periksa pengaturan koneksi SATUSEHAT.');
}

// Tampilkan data Patient; elemen opsional yang kosong ditampilkan sebagai tanda strip.
$name = [];
foreach (($patient['name'] ?? []) as $item) {
    if (empty($name) || ($item['use'] ?? '') === 'official') {
        $name = $item;
    }
    if (($item['use'] ?? '') === 'official') {
        break;
    }
}
$fullName = $name['text'] ?? trim(implode(' ', $name['given'] ?? []) . ' ' . ($name['family'] ?? ''));
$nik = '';
foreach (($patient['identifier'] ?? []) as $identifier) {
    if (($identifier['system'] ?? '') === 'https://fhir.kemkes.go.id/id/nik') {
        $nik = $identifier['value'] ?? '';
        break;
    }
}
$telecom = ['email' => [], 'phone' => []];
foreach (($patient['telecom'] ?? []) as $item) {
    $system = $item['system'] ?? '';
    if (isset($telecom[$system]) && !empty($item['value'])) {
        $telecom[$system][] = $item['value'];
    }
}
$formatAddress = static function ($address) {
    if (!empty($address['text'])) {
        return $address['text'];
    }
    $parts = $address['line'] ?? [];
    foreach (['city', 'district', 'state', 'postalCode', 'country'] as $field) {
        if (!empty($address[$field])) {
            $parts[] = $address[$field];
        }
    }
    return implode(', ', $parts);
};
$addresses = [];
foreach (($patient['address'] ?? []) as $address) {
    $addresses[] = $formatAddress($address);
}
$birthPlace = '';
foreach (($patient['extension'] ?? []) as $extension) {
    if (in_array($extension['url'] ?? '', [
        'https://fhir.kemkes.go.id/r4/StructureDefinition/birthPlace',
        'http://hl7.org/fhir/StructureDefinition/patient-birthPlace',
    ], true)) {
        $birthPlace = $formatAddress($extension['valueAddress'] ?? []);
        break;
    }
}
$genders = ['male' => 'Laki-laki', 'female' => 'Perempuan', 'other' => 'Lainnya', 'unknown' => 'Tidak diketahui'];
$details = [
    'ID IHS' => $patient['id'],
    'NIK' => $nik,
    'Nama' => $fullName,
    'Email' => implode(', ', $telecom['email']),
    'Kontak' => implode(', ', $telecom['phone']),
    'Alamat' => implode("\n", array_filter($addresses)),
    'Gender' => $genders[$patient['gender'] ?? ''] ?? ($patient['gender'] ?? ''),
    'Tempat Lahir' => $birthPlace,
    'Tanggal Lahir' => $patient['birthDate'] ?? '',
    'Status Aktif' => isset($patient['active']) ? ($patient['active'] ? 'Aktif' : 'Tidak aktif') : '',
];
echo '<p class="text-muted"><small>Data pasien dari SATUSEHAT.</small></p>';
echo '<dl class="mb-0">';
foreach ($details as $label => $value) {
    echo '<div class="row mb-3">'
        . '<dt class="col-12 col-md-4 text-muted fw-normal">' . ihsDetailText($label) . '</dt>'
        . '<dd class="col-12 col-md-8 mb-0 text-break">'
        . nl2br(ihsDetailText($value === '' || $value === null ? '-' : $value)) . '</dd>'
        . '</div>';
}
echo '</dl>';
