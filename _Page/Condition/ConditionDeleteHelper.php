<?php
require_once __DIR__.'/SatuSehatCondition.php';

// Called while the diagnosis lock is held, before deleting the local record.
function invalidateConditionSatuSehat($Conn, $idCondition, $transport = null) {
    if (!preg_match('/^[A-Za-z0-9.-]{1,64}$/D', $idCondition)) {
        return ['status' => 'error', 'message' => 'ID Condition SATUSEHAT tidak valid.'];
    }
    try {
        $setting = conditionRow($Conn, 'SELECT url_connection_satu_sehat FROM connection_satu_sehat WHERE status_connection_satu_sehat = 1 LIMIT 1', '', []);
        $base = rtrim(trim($setting['url_connection_satu_sehat'] ?? ''), '/');
        if (!filter_var($base, FILTER_VALIDATE_URL) || parse_url($base, PHP_URL_SCHEME) !== 'https') {
            return ['status' => 'error', 'message' => 'Konfigurasi SATUSEHAT aktif belum tersedia atau URL tidak valid.'];
        }
        $tokenResult = generateTokenSatuSehat($Conn);
        if (($tokenResult['status'] ?? '') !== 'success' || empty($tokenResult['token'])) {
            return ['status' => 'error', 'message' => 'Gagal memperoleh token SATUSEHAT.'];
        }
        $url = (preg_match('~/fhir-r4/v1$~', $base) ? $base : $base.'/fhir-r4/v1').'/Condition/'.rawurlencode($idCondition);
        $transport = $transport ?? 'conditionSatuSehatRequest';
        $response = $transport($url, $tokenResult['token'], null, 'GET');
        $result = conditionSatuSehatResult($response);
        if ($result['status'] !== 'success') return $result;
        if ($result['id_condition'] !== $idCondition) return ['status' => 'error', 'message' => 'ID Condition balasan SATUSEHAT tidak sesuai.'];
        $remote = json_decode($response['body'], true);
        $isInvalid = static function ($resource) {
            foreach (($resource['verificationStatus']['coding'] ?? []) as $coding) {
                if (($coding['system'] ?? '') === 'http://terminology.hl7.org/CodeSystem/condition-ver-status' && ($coding['code'] ?? '') === 'entered-in-error') return true;
            }
            return false;
        };
        // Supports retry after remote invalidation succeeded but local deletion failed.
        if ($isInvalid($remote) && !isset($remote['clinicalStatus'])) {
            return ['status' => 'success', 'message' => 'Condition SATUSEHAT sudah berstatus entered-in-error.', 'id_condition' => $idCondition];
        }
        $remote['verificationStatus'] = ['coding' => [[
            'system' => 'http://terminology.hl7.org/CodeSystem/condition-ver-status',
            'code' => 'entered-in-error',
            'display' => 'Entered in Error'
        ]]];
        // FHIR R4 con-5 forbids clinicalStatus with entered-in-error.
        unset($remote['clinicalStatus']);
        if (($remote['text']['status'] ?? '') === 'generated') unset($remote['text']);
        $response = $transport($url, $tokenResult['token'], $remote, 'PUT');
        $result = conditionSatuSehatResult($response);
        if ($result['status'] !== 'success') return $result;
        $updated = json_decode($response['body'], true);
        if ($result['id_condition'] !== $idCondition || !$isInvalid($updated) || isset($updated['clinicalStatus'])) {
            return ['status' => 'error', 'message' => 'SATUSEHAT belum mengonfirmasi status entered-in-error pada Condition yang dimaksud.'];
        }
        $result['message'] = 'Condition SATUSEHAT berhasil ditandai entered-in-error.';
        return $result;
    } catch (Throwable $error) {
        error_log('Hapus Condition: gagal memperbarui status SATUSEHAT.');
        return ['status' => 'error', 'message' => 'Status Condition SATUSEHAT gagal diperbarui.'];
    }
}
