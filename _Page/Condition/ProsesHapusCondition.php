<?php
require_once __DIR__.'/../../_Config/Connection.php';
require_once __DIR__.'/../../_Config/GlobalFunction.php';
require_once __DIR__.'/../../_Config/Session.php';
require_once __DIR__.'/ConditionHelper.php';
require_once __DIR__.'/ConditionDeleteHelper.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (empty($SessionIdAkses)) conditionResponse('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') conditionResponse('error', 'Metode request tidak valid.');
$id = filter_var(conditionInput('id_diagnosis'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) conditionResponse('error', 'ID diagnosis wajib diisi dan harus valid.');
$token = conditionInput('delete_token');
$expected = $_SESSION['hapus_condition'][$id] ?? '';
if ($expected === '' || $token === '' || !hash_equals($expected, $token)) {
    conditionResponse('error', 'Konfirmasi hapus tidak valid. Silakan buka kembali modal hapus.');
}

$deleteDiagnosis = static function () use ($Conn, $id) {
    $locked = false;
    $remoteUpdated = false;
    $lockName = 'pharmix-condition-'.$id;
    try {
        $lock = conditionRow($Conn, 'SELECT GET_LOCK(?, 0) AS acquired', 's', [$lockName]);
        $locked = (int) ($lock['acquired'] ?? 0) === 1;
        if (!$locked) return ['status' => 'error', 'message' => 'Diagnosis sedang diproses. Silakan coba kembali setelah proses selesai.'];
        $data = conditionRow($Conn, 'SELECT id_kunjungan, id_condition FROM diagnosis WHERE id_diagnosis = ? LIMIT 1', 'i', [$id]);
        if (!$data) return ['status' => 'error', 'message' => 'Diagnosis tidak ditemukan atau sudah dihapus.'];
        $idCondition = trim((string) $data['id_condition']);
        if ($idCondition !== '') {
            $satusehat = invalidateConditionSatuSehat($Conn, $idCondition);
            if ($satusehat['status'] !== 'success') {
                return ['status' => 'error', 'message' => 'Diagnosis lokal belum dihapus. '.$satusehat['message'], 'satusehat' => $satusehat];
            }
            $remoteUpdated = true;
        }
        $stmt = conditionQuery($Conn, 'DELETE FROM diagnosis WHERE id_diagnosis = ? LIMIT 1', 'i', [$id]);
        $deleted = $stmt->affected_rows;
        $stmt->close();
        if ($deleted !== 1) return ['status' => 'error', 'message' => 'Diagnosis tidak ditemukan atau sudah dihapus.'];
        unset($_SESSION['hapus_condition'][$id]);
        return ['status' => 'success', 'message' => ($remoteUpdated ? 'Condition SATUSEHAT ditandai entered-in-error. ' : '').'Diagnosis berhasil dihapus dari database aplikasi.', 'id_kunjungan' => (int) $data['id_kunjungan']];
    } catch (Throwable $error) {
        error_log('ProsesHapusCondition: gagal menghapus diagnosis '.(int) $id);
        return ['status' => 'error', 'message' => $remoteUpdated ? 'Condition SATUSEHAT sudah ditandai entered-in-error, tetapi diagnosis lokal gagal dihapus. Silakan coba hapus kembali.' : 'Diagnosis gagal dihapus. Silakan coba kembali.'];
    } finally {
        if ($locked) {
            try {
                $stmt = conditionQuery($Conn, 'SELECT RELEASE_LOCK(?)', 's', [$lockName]);
                $stmt->close();
            } catch (Throwable $error) {
                error_log('ProsesHapusCondition: gagal melepas lock.');
            }
        }
    }
};
echo json_encode($deleteDiagnosis(), JSON_UNESCAPED_UNICODE);
