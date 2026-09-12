<?php
require_once __DIR__.'/ConditionHelper.php';

function conditionSatuSehatPayload($data, $organizationId) {
    $payload = [
        'resourceType' => 'Condition',
        'clinicalStatus' => ['coding' => [[
            'system' => 'http://terminology.hl7.org/CodeSystem/condition-clinical',
            'code' => 'active', 'display' => 'Active'
        ]]],
        'verificationStatus' => ['coding' => [[
            'system' => 'http://terminology.hl7.org/CodeSystem/condition-ver-status',
            'code' => $data['certainty_status'] === 'Final' ? 'confirmed' : 'provisional',
            'display' => $data['certainty_status'] === 'Final' ? 'Confirmed' : 'Provisional'
        ]]],
        // Local categories (Admission, Primary, etc.) remain in diagnosis.category.
        'category' => [['coding' => [[
            'system' => 'http://terminology.hl7.org/CodeSystem/condition-category',
            'code' => 'encounter-diagnosis', 'display' => 'Encounter Diagnosis'
        ]]]],
        'code' => ['coding' => [[
            'system' => 'http://hl7.org/fhir/sid/icd-10',
            'code' => $data['icd_code'], 'display' => $data['icd_description']
        ]]],
        'subject' => [
            'reference' => 'Patient/'.trim((string) $data['id_ihs']),
            'display' => $data['nama_pasien']
        ],
        'encounter' => ['reference' => 'Encounter/'.trim((string) $data['id_encounter'])]
    ];
    if ($organizationId !== '') {
        $payload['identifier'] = [[
            'system' => 'http://sys-ids.kemkes.go.id/condition/'.$organizationId,
            'value' => $data['diagnosis_code']
        ]];
    }
    if (trim((string) $data['diagnosis_text']) !== '') {
        $payload['code']['text'] = $data['diagnosis_text'];
    }
    return $payload;
}

function conditionSatuSehatRequest($url, $token, $payload, $method = 'POST') {
    if (!in_array($method, ['GET', 'POST', 'PUT'], true)) throw new InvalidArgumentException('Metode Condition tidak valid.');
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 60,
        // Localhost: aktifkan kembali VERIFYPEER=true dan VERIFYHOST=2 di production.
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer '.$token,
            'Content-Type: application/fhir+json',
            'Accept: application/fhir+json'
        ]
    ]);
    if ($method !== 'GET') {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }
    $body = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_errno($curl);
    $curlErrorMessage = curl_error($curl);
    curl_close($curl);
    if ($curlError) error_log('Condition SATUSEHAT cURL '.$curlError.': '.$curlErrorMessage);
    return ['body' => $body, 'http_code' => $httpCode, 'curl_error' => $curlError, 'curl_error_message' => $curlErrorMessage];
}

function conditionSatuSehatResult($response) {
    $httpCode = (int) ($response['http_code'] ?? 0);
    if (!empty($response['curl_error']) || $response['body'] === false) {
        $curlCode = (int) ($response['curl_error'] ?? 0);
        $messages = [
            6 => 'Nama host SATUSEHAT tidak dapat ditemukan (cURL 6). Periksa DNS dan URL koneksi.',
            7 => 'Koneksi ke server SATUSEHAT gagal (cURL 7). Periksa jaringan, firewall, dan URL koneksi.',
            35 => 'Negosiasi TLS dengan SATUSEHAT gagal (cURL 35). Periksa konfigurasi HTTPS server.',
            60 => 'Verifikasi sertifikat HTTPS SATUSEHAT gagal (cURL 60). Periksa sertifikat CA yang dipercaya oleh PHP.',
            77 => 'Berkas sertifikat CA tidak dapat dibaca (cURL 77). Periksa lokasi dan izin baca berkas CA.'
        ];
        $message = $messages[$curlCode] ?? ('Koneksi SATUSEHAT terputus atau melewati batas waktu (cURL '.$curlCode.'). Status penerimaan Condition belum dapat dipastikan; periksa SATUSEHAT sebelum mengirim ulang.');
        return ['status' => 'error', 'message' => $message, 'http_code' => $httpCode, 'curl_error' => $curlCode];
    }
    $data = json_decode($response['body'], true);
    if (!is_array($data)) {
        return ['status' => 'error', 'message' => 'Respons SATUSEHAT bukan JSON yang valid. Status penerimaan Condition belum dapat dipastikan.', 'http_code' => $httpCode];
    }
    if ($httpCode < 200 || $httpCode >= 300 || ($data['resourceType'] ?? '') === 'OperationOutcome') {
        $messages = [];
        foreach (($data['issue'] ?? []) as $issue) {
            $message = $issue['details']['text'] ?? $issue['diagnostics'] ?? $issue['code'] ?? '';
            if (is_string($message) && $message !== '') $messages[] = $message;
        }
        return ['status' => 'error', 'message' => $messages ? implode(' ', array_unique($messages)) : 'SATUSEHAT menolak pengiriman Condition (HTTP '.$httpCode.').', 'http_code' => $httpCode];
    }
    $id = $data['id'] ?? '';
    if (($data['resourceType'] ?? '') !== 'Condition' || !is_string($id) || !preg_match('/^[A-Za-z0-9.-]{1,64}$/D', $id)) {
        return ['status' => 'error', 'message' => 'Respons SATUSEHAT tidak memuat resource Condition dengan ID yang valid. Periksa SATUSEHAT sebelum mengirim ulang.', 'http_code' => $httpCode];
    }
    return ['status' => 'success', 'message' => 'Condition berhasil dikirim ke SATUSEHAT.', 'id_condition' => $id, 'http_code' => $httpCode];
}

function conditionSatuSehatDiagnosis($Conn, $code) {
    if ($code === '' || strlen($code) > 255) return null;
    $stmt = conditionQuery($Conn, 'SELECT id_diagnosis, id_kunjungan FROM diagnosis WHERE diagnosis_code = ? LIMIT 2', 's', [$code]);
    $result = $stmt->get_result();
    $data = $result->num_rows === 1 ? $result->fetch_assoc() : null;
    $stmt->close();
    return $data;
}

function conditionSatuSehatPreview($Conn, $idDiagnosis, $forUpdate = false) {
    $data = conditionRow($Conn, 'SELECT d.id_diagnosis, d.id_kunjungan, d.category, d.medicalPersonelName, d.diagnosis_code, d.id_condition, d.icd_version, d.icd_code, d.icd_description, d.diagnosis_text, d.certainty_status, k.id_encounter, a.id_ihs, a.nama AS nama_pasien FROM diagnosis d LEFT JOIN kunjungan k ON k.id_kunjungan = d.id_kunjungan LEFT JOIN anggota a ON a.id_anggota = k.id_anggota WHERE d.id_diagnosis = ? LIMIT 1', 'i', [$idDiagnosis]);
    if (!$data) throw new RuntimeException('Diagnosis tidak ditemukan.');
    $setting = conditionRow($Conn, 'SELECT url_connection_satu_sehat, organization_id FROM connection_satu_sehat WHERE status_connection_satu_sehat = 1 LIMIT 1', '', []);
    $baseUrl = rtrim(trim($setting['url_connection_satu_sehat'] ?? ''), '/');
    $checks = [
        ['label' => $forUpdate ? 'ID Condition SATUSEHAT tersedia dan valid' : 'Belum pernah dikirim ke SATUSEHAT', 'valid' => $forUpdate ? (bool) preg_match('/^[A-Za-z0-9.-]{1,64}$/D', trim((string) $data['id_condition'])) : trim((string) $data['id_condition']) === ''],
        ['label' => 'Kode diagnosis lokal tersedia', 'valid' => trim((string) $data['diagnosis_code']) !== ''],
        ['label' => 'ID IHS pasien tersedia dan valid', 'valid' => (bool) preg_match('/^[A-Za-z0-9.-]{1,64}$/D', trim((string) $data['id_ihs']))],
        ['label' => 'ID Encounter SATUSEHAT tersedia dan valid', 'valid' => (bool) preg_match('/^[A-Za-z0-9.-]{1,64}$/D', trim((string) $data['id_encounter']))],
        ['label' => 'Nama pasien tersedia', 'valid' => trim((string) $data['nama_pasien']) !== ''],
        ['label' => 'Kode dan deskripsi ICD10 lengkap', 'valid' => $data['icd_version'] === 'ICD10' && trim((string) $data['icd_code']) !== '' && trim((string) $data['icd_description']) !== ''],
        ['label' => 'Kepastian diagnosis valid', 'valid' => in_array($data['certainty_status'], ['Provisional', 'Final'], true)],
        ['label' => 'Konfigurasi SATUSEHAT aktif dan URL HTTPS valid', 'valid' => $setting && filter_var($baseUrl, FILTER_VALIDATE_URL) && parse_url($baseUrl, PHP_URL_SCHEME) === 'https']
    ];
    $errors = [];
    foreach ($checks as $check) {
        if (!$check['valid']) $errors[] = $check['label'];
    }
    $payload = conditionSatuSehatPayload($data, trim($setting['organization_id'] ?? ''));
    // Ensure the same payload can be encoded before offering the send action.
    json_encode($payload, JSON_THROW_ON_ERROR);
    return ['data' => $data, 'checks' => $checks, 'errors' => $errors, 'eligible' => !$errors, 'payload' => $payload, 'base_url' => $baseUrl];
}
// Local insertion has completed before this function runs. A remote failure must
// never turn the successful local insert into an error that invites duplicate input.
function syncConditionSatuSehat($Conn, $idDiagnosis, $accessId, $accessName, $transport = null) {
    $lockName = 'pharmix-condition-'.(int) $idDiagnosis;
    $locked = false;
    try {
        $lock = conditionRow($Conn, 'SELECT GET_LOCK(?, 0) AS acquired', 's', [$lockName]);
        $locked = (int) ($lock['acquired'] ?? 0) === 1;
        if (!$locked) return ['status' => 'error', 'message' => 'Diagnosis ini sedang dikirim. Tunggu proses selesai lalu muat ulang daftar diagnosis.'];
        $preview = conditionSatuSehatPreview($Conn, $idDiagnosis);
        $data = $preview['data'];
        if (trim((string) $data['id_condition']) !== '') {
            return ['status' => 'success', 'message' => 'Condition sudah pernah dikirim ke SATUSEHAT.', 'id_condition' => $data['id_condition']];
        }
        if (!$preview['eligible']) {
            return ['status' => 'skipped', 'message' => 'Syarat belum terpenuhi: '.implode('; ', $preview['errors']).'.'];
        }
        $baseUrl = $preview['base_url'];
        $tokenResult = generateTokenSatuSehat($Conn);
        $token = trim($tokenResult['token'] ?? '');
        if (($tokenResult['status'] ?? '') !== 'success' || $token === '') {
            return ['status' => 'error', 'message' => 'Gagal memperoleh token SATUSEHAT. Periksa pengaturan koneksi.'];
        }
        $payload = $preview['payload'];
        $transport = $transport ?? 'conditionSatuSehatRequest';
        $result = conditionSatuSehatResult($transport($baseUrl.'/fhir-r4/v1/Condition', $token, $payload));
        if ($result['status'] !== 'success') return $result;

        try {
            $stmt = conditionQuery($Conn, "UPDATE diagnosis SET id_condition = ?, update_at = ?, update_by_id = ?, update_by_name = ? WHERE id_diagnosis = ? AND (id_condition IS NULL OR id_condition = '')", 'ssisi', [$result['id_condition'], date('Y-m-d H:i:s'), $accessId, $accessName, $idDiagnosis]);
            $affected = $stmt->affected_rows;
            $stmt->close();
            if ($affected !== 1) throw new RuntimeException('Condition ID was not updated.');
        } catch (Throwable $error) {
            error_log('Condition SATUSEHAT: gagal menyimpan ID untuk diagnosis '.(int) $idDiagnosis);
            $result['status'] = 'warning';
            $result['message'] = 'Condition telah diterima SATUSEHAT, tetapi ID '.$result['id_condition'].' gagal disimpan lokal. Jangan membuat ulang diagnosis; simpan ID tersebut pada data diagnosis ini.';
        }
        return $result;
    } catch (Throwable $error) {
        error_log('Condition SATUSEHAT: proses gagal untuk diagnosis '.(int) $idDiagnosis);
        return ['status' => 'error', 'message' => 'Pengiriman Condition belum dapat diselesaikan. Periksa koneksi dan struktur database; jangan membuat ulang diagnosis yang sudah tersimpan.'];
    } finally {
        if ($locked) {
            try {
                $stmt = conditionQuery($Conn, 'SELECT RELEASE_LOCK(?)', 's', [$lockName]);
                $stmt->close();
            } catch (Throwable $error) {
                error_log('Condition SATUSEHAT: gagal melepas lock diagnosis '.(int) $idDiagnosis);
            }
        }
    }
}
