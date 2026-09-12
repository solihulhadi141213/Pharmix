<?php
require_once __DIR__.'/SatuSehatCondition.php';

// Caller holds the same diagnosis lock used by create/send.
function updateConditionSatuSehat($Conn, $idDiagnosis, $transport = null) {
    try {
        $preview = conditionSatuSehatPreview($Conn, $idDiagnosis, true);
        if (!$preview['eligible']) return ['status' => 'error', 'message' => implode('; ', $preview['errors'])];
        $idCondition = trim($preview['data']['id_condition']);
        $tokenResult = generateTokenSatuSehat($Conn);
        if (($tokenResult['status'] ?? '') !== 'success' || empty($tokenResult['token'])) {
            return ['status' => 'error', 'message' => 'Gagal memperoleh token SATUSEHAT.'];
        }
        $base = $preview['base_url'];
        $url = (preg_match('~/fhir-r4/v1$~', $base) ? $base : $base.'/fhir-r4/v1').'/Condition/'.rawurlencode($idCondition);
        $transport = $transport ?? 'conditionSatuSehatRequest';
        $response = $transport($url, $tokenResult['token'], null, 'GET');
        $result = conditionSatuSehatResult($response);
        if ($result['status'] !== 'success') return $result;
        if ($result['id_condition'] !== $idCondition) return ['status' => 'error', 'message' => 'ID Condition SATUSEHAT tidak sesuai.'];
        $remote = json_decode($response['body'], true);
        if (($remote['subject']['reference'] ?? '') !== $preview['payload']['subject']['reference'] || ($remote['encounter']['reference'] ?? '') !== $preview['payload']['encounter']['reference']) {
            return ['status' => 'error', 'message' => 'Referensi pasien atau kunjungan pada Condition SATUSEHAT tidak sesuai data lokal.'];
        }
        // Only replace the FHIR fields managed by this edit form. Preserve other
        // clinical information, identifiers, and references already on SATUSEHAT.
        $remote['code'] = $preview['payload']['code'];
        $remote['verificationStatus'] = $preview['payload']['verificationStatus'];
        if (($remote['text']['status'] ?? '') === 'generated') unset($remote['text']);
        $result = conditionSatuSehatResult($transport($url, $tokenResult['token'], $remote, 'PUT'));
        if ($result['status'] === 'success') {
            if ($result['id_condition'] !== $idCondition) return ['status' => 'error', 'message' => 'ID balasan pembaruan SATUSEHAT tidak sesuai.'];
            $result['message'] = 'Condition SATUSEHAT berhasil diperbarui.';
        }
        return $result;
    } catch (Throwable $error) {
        error_log('Edit Condition SATUSEHAT gagal untuk diagnosis '.(int) $idDiagnosis);
        return ['status' => 'error', 'message' => 'Pembaruan SATUSEHAT belum dapat diselesaikan.'];
    }
}

function editConditionDiagnosis($Conn, $input, $accessId, $accessName, $transport = null) {
    $id = filter_var($input['id_diagnosis'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $personelId = filter_var($input['medicalPersonelId'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$id || !$personelId || $input['icd_code'] === '') return ['status' => 'error', 'message' => 'ID diagnosis, tenaga medis, dan kode ICD10 wajib diisi dengan benar.'];
    if (!in_array($input['case_status'], ['Baru', 'Lama', 'Kambuh', 'Kronis'], true) || !in_array($input['certainty_status'], ['Provisional', 'Final'], true)) {
        return ['status' => 'error', 'message' => 'Status kasus atau kepastian diagnosis tidak valid.'];
    }
    if (strlen($input['diagnosis_text']) > 65535) return ['status' => 'error', 'message' => 'Catatan diagnosis terlalu panjang.'];
    $locked = false;
    $lockName = 'pharmix-condition-'.$id;
    try {
        $lock = conditionRow($Conn, 'SELECT GET_LOCK(?, 0) AS acquired', 's', [$lockName]);
        $locked = (int) ($lock['acquired'] ?? 0) === 1;
        if (!$locked) return ['status' => 'error', 'message' => 'Diagnosis sedang diproses. Silakan coba kembali setelah proses selesai.'];
        $existing = conditionRow($Conn, 'SELECT id_diagnosis, id_kunjungan, id_condition, medicalPersonelId, medicalPersonelName FROM diagnosis WHERE id_diagnosis = ? LIMIT 1', 'i', [$id]);
        if (!$existing) return ['status' => 'error', 'message' => 'Data diagnosis tidak ditemukan.'];
        $personel = conditionRow($Conn, 'SELECT medicalPersonelName, medicalPersonelStatus FROM medical_personel WHERE medicalPersonelId = ? LIMIT 1', 'i', [$personelId]);
        // Existing inactive personnel may remain selected; new selections must be active.
        if (!$personel || ($personel['medicalPersonelStatus'] !== 'Active' && $personelId !== (int) $existing['medicalPersonelId'])) {
            return ['status' => 'error', 'message' => 'Tenaga medis tidak ditemukan atau tidak aktif. Silakan pilih ulang.'];
        }
        $icd = conditionRow($Conn, "SELECT kode, long_des FROM icd WHERE kode = ? AND icd = 'ICD10' LIMIT 1", 's', [$input['icd_code']]);
        if (!$icd) return ['status' => 'error', 'message' => 'Kode ICD10 tidak ditemukan. Silakan pilih ulang.'];
        // IDs, patient, category, local UUID, SATUSEHAT ID and creator are retained
        // from the stored record, regardless of hidden fields sent by the browser.
        $stmt = conditionQuery($Conn, "UPDATE diagnosis SET medicalPersonelId = ?, medicalPersonelName = ?, icd_version = 'ICD10', icd_code = ?, icd_description = ?, diagnosis_text = ?, case_status = ?, certainty_status = ?, update_at = ?, update_by_id = ?, update_by_name = ? WHERE id_diagnosis = ?", 'isssssssisi', [$personelId, $personel['medicalPersonelName'], $icd['kode'], $icd['long_des'], $input['diagnosis_text'], $input['case_status'], $input['certainty_status'], date('Y-m-d H:i:s'), $accessId, $accessName, $id]);
        $stmt->close();
        $idCondition = trim((string) $existing['id_condition']);
        $satusehat = $idCondition === ''
            ? ['status' => 'skipped', 'message' => 'Diagnosis belum memiliki ID Condition; perubahan hanya disimpan lokal.']
            : updateConditionSatuSehat($Conn, $id, $transport);
        return [
            'status' => 'success',
            'message' => 'Diagnosis berhasil diperbarui di database. '.($satusehat['status'] === 'error' ? 'Pembaruan SATUSEHAT gagal: ' : '').$satusehat['message'],
            'id_diagnosis' => $id, 'id_kunjungan' => (int) $existing['id_kunjungan'],
            'id_condition' => $idCondition ?: null, 'satusehat' => $satusehat
        ];
    } catch (Throwable $error) {
        error_log('ProsesEditCondition: gagal memperbarui diagnosis '.(int) $id);
        return ['status' => 'error', 'message' => 'Diagnosis gagal diperbarui. Silakan coba kembali.'];
    } finally {
        if ($locked) {
            try {
                $stmt = conditionQuery($Conn, 'SELECT RELEASE_LOCK(?)', 's', [$lockName]);
                $stmt->close();
            } catch (Throwable $error) {
                error_log('ProsesEditCondition: gagal melepas lock.');
            }
        }
    }
}
