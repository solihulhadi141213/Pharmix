<?php
require_once __DIR__.'/ConditionSatuSehatTest.php';
require_once __DIR__.'/../_Page/Condition/ConditionEditHelper.php';

class EditTestStatement extends ConditionTestStatement {
    private $editConn;
    private $editSql;
    private $editParams;
    public function __construct($conn, $sql) {
        parent::__construct($conn, $sql);
        $this->editConn = $conn;
        $this->editSql = $sql;
    }
    public function bind_param($types, &...$params) {
        parent::bind_param($types, ...$params);
        $this->editParams = $params;
    }
    public function get_result() {
        if (strpos($this->editSql, 'FROM medical_personel') !== false) return new ConditionTestResult(['medicalPersonelName' => 'Master Doctor', 'medicalPersonelStatus' => 'Active']);
        if (strpos($this->editSql, 'FROM icd') !== false) return new ConditionTestResult($this->editConn->validIcd ? ['kode' => 'A00', 'long_des' => 'Master description'] : null);
        return parent::get_result();
    }
    public function execute() {
        $result = parent::execute();
        if (strpos($this->editSql, 'UPDATE diagnosis SET medicalPersonelId') === 0) {
            checkCondition(strpos($this->editSql, 'creat_by') === false && strpos($this->editSql, 'id_condition =') === false && strpos($this->editSql, 'id_pasien =') === false && strpos($this->editSql, 'category =') === false, 'Immutable fields updated');
            $keys = ['medicalPersonelId', 'medicalPersonelName', 'icd_code', 'icd_description', 'diagnosis_text', 'case_status', 'certainty_status', 'update_at', 'update_by_id', 'update_by_name', 'id_diagnosis'];
            foreach ($keys as $index => $key) $this->editConn->data[$key] = $this->editParams[$index];
            $this->editConn->data['icd_version'] = 'ICD10';
        }
        return $result;
    }
}
class EditTestConnection extends ConditionTestConnection {
    public $validIcd = true;
    public function prepare($sql) { return new EditTestStatement($this, $sql); }
}
$input = [
    'id_diagnosis' => '12', 'medicalPersonelId' => '7', 'icd_code' => 'A00',
    'diagnosis_text' => '', 'case_status' => 'Kronis', 'certainty_status' => 'Final',
    'id_condition' => 'forged-remote-id', 'id_pasien' => 'forged-patient',
    'medicalPersonelName' => 'forged-doctor', 'icd_description' => 'forged-description'
];
$httpCalls = [];
$remote = [
    'resourceType' => 'Condition', 'id' => 'remote-id',
    'subject' => ['reference' => 'Patient/100000030009'],
    'encounter' => ['reference' => 'Encounter/encounter-test'],
    'code' => ['text' => 'old note'], 'clinicalStatus' => ['text' => 'Existing clinical status'],
    'note' => [['text' => 'Keep existing note']], 'text' => ['status' => 'generated', 'div' => 'Old narrative']
];
$transport = function ($url, $token, $payload, $method) use (&$httpCalls, $remote) {
    $httpCalls[] = $method;
    checkCondition($url === 'https://example.invalid/fhir-r4/v1/Condition/remote-id', 'Wrong update target');
    checkCondition($method !== 'POST', 'Edit created a new remote condition');
    if ($method === 'GET') return ['http_code' => 200, 'curl_error' => 0, 'body' => json_encode($remote)];
    checkCondition($payload['id'] === 'remote-id', 'PUT resource ID mismatch');
    checkCondition($payload['code']['coding'][0]['code'] === 'A00' && $payload['code']['coding'][0]['display'] === 'Master description', 'PUT used stale or forged ICD');
    checkCondition(!isset($payload['code']['text']) && !isset($payload['text']), 'Cleared diagnosis text or generated narrative retained');
    checkCondition($payload['note'] === $remote['note'] && $payload['clinicalStatus'] === $remote['clinicalStatus'], 'Unedited remote fields lost');
    return ['http_code' => 200, 'curl_error' => 0, 'body' => json_encode($payload)];
};
$conn = new EditTestConnection();
$result = editConditionDiagnosis($conn, $input, 9, 'Editor', $transport);
checkCondition($result['status'] === 'success' && $result['satusehat']['status'] === 'skipped' && !$httpCalls && $conn->tokenCalls === 0, 'Local-only edit called SATUSEHAT');
checkCondition($conn->data['medicalPersonelName'] === 'Master Doctor' && $conn->data['icd_description'] === 'Master description' && $conn->data['update_by_name'] === 'Editor', 'Master or audit fields incorrect');
checkCondition($conn->data['diagnosis_code'] === 'local-uuid' && $conn->data['id_condition'] === null && $conn->releases === 1, 'Local IDs or lock incorrect');
$conn = new EditTestConnection();
$conn->data['id_condition'] = 'remote-id';
$result = editConditionDiagnosis($conn, $input, 9, 'Editor', $transport);
checkCondition($result['status'] === 'success' && $result['satusehat']['status'] === 'success' && $httpCalls === ['GET', 'PUT'], 'Remote update failed');
checkCondition($result['id_condition'] === 'remote-id' && $conn->releases === 1, 'Remote ID or lock not preserved');
$conn = new EditTestConnection();
$conn->data['id_condition'] = 'remote-id';
$result = editConditionDiagnosis($conn, $input, 9, 'Editor', function () { return ['http_code' => 404, 'curl_error' => 0, 'body' => '{"resourceType":"OperationOutcome"}']; });
checkCondition($result['status'] === 'success' && $result['satusehat']['status'] === 'error' && count($conn->updates) === 1 && $conn->releases === 1, 'Remote error lost local save');
$conn = new EditTestConnection();
$conn->validIcd = false;
checkCondition(editConditionDiagnosis($conn, $input, 9, 'Editor', $transport)['status'] === 'error' && !$conn->updates, 'Invalid ICD saved');
$conn = new EditTestConnection();
$conn->lockAvailable = false;
checkCondition(editConditionDiagnosis($conn, $input, 9, 'Editor', $transport)['status'] === 'error' && !$conn->updates, 'Locked record edited');
$conn = new EditTestConnection();
$conn->failUpdate = true;
$before = count($httpCalls);
checkCondition(editConditionDiagnosis($conn, $input, 9, 'Editor', $transport)['status'] === 'error' && count($httpCalls) === $before, 'Local DB failure still called remote');
echo "PASS: local-only edit, GET/PUT, preserved resource data and IDs, master values, audit, failed sync, validation and locks.\n";
