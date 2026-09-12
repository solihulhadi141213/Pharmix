<?php
// Run: php -d xdebug.mode=off tests/ConditionSatuSehatTest.php
// All database/token/HTTP operations below use test doubles; no patient data is sent.
require_once __DIR__.'/../_Page/Condition/SatuSehatCondition.php';

function checkCondition($value, $message) {
    if (!$value) throw new RuntimeException($message);
}
function generateTokenSatuSehat($Conn) {
    $Conn->tokenCalls++;
    return $Conn->tokenResult;
}
class ConditionTestResult {
    private $row;
    public $num_rows;
    public function __construct($row, $count = null) { $this->row = $row; $this->num_rows = $count ?? ($row ? 1 : 0); }
    public function fetch_assoc() { return $this->row; }
}
class ConditionTestStatement {
    private $conn;
    private $sql;
    private $params = [];
    public $affected_rows = 1;
    public function __construct($conn, $sql) { $this->conn = $conn; $this->sql = $sql; }
    public function bind_param($types, &...$params) {
        checkCondition(strlen($types) === count($params), 'Bind type mismatch');
        checkCondition(substr_count($this->sql, '?') === count($params), 'Placeholder mismatch');
        $this->params = $params;
    }
    public function execute() {
        if (strpos($this->sql, 'RELEASE_LOCK') !== false) $this->conn->releases++;
        if (strpos($this->sql, 'UPDATE diagnosis') === 0) {
            $this->conn->updates[] = $this->params;
            if ($this->conn->failUpdate) throw new RuntimeException('Simulated database failure');
        }
        return true;
    }
    public function get_result() {
        if (strpos($this->sql, 'GET_LOCK') !== false) return new ConditionTestResult(['acquired' => $this->conn->lockAvailable ? 1 : 0]);
        if (strpos($this->sql, 'WHERE diagnosis_code') !== false) {
            checkCondition($this->params === ['local-uuid'], 'Wrong diagnosis code lookup');
            return new ConditionTestResult(['id_diagnosis' => 12, 'id_kunjungan' => 42], $this->conn->matchingCodes);
        }
        if (strpos($this->sql, 'FROM diagnosis') !== false) {
            checkCondition($this->params === [12], 'Wrong diagnosis queried');
            return new ConditionTestResult($this->conn->data);
        }
        return new ConditionTestResult($this->conn->setting);
    }
    public function close() {}
}
class ConditionTestConnection {
    public $data = [
        'id_diagnosis' => 12, 'id_kunjungan' => 42, 'category' => 'Primary', 'medicalPersonelName' => 'Test Doctor',
        'diagnosis_code' => 'local-uuid', 'id_condition' => null,
        'icd_version' => 'ICD10', 'icd_code' => 'K35.8',
        'icd_description' => 'Acute appendicitis, other and unspecified',
        'diagnosis_text' => 'Test note', 'certainty_status' => 'Final',
        'id_encounter' => 'encounter-test', 'id_ihs' => '100000030009', 'nama_pasien' => 'Test Patient'
    ];
    public $setting = ['url_connection_satu_sehat' => 'https://example.invalid', 'organization_id' => 'org-test'];
    public $tokenResult = ['status' => 'success', 'token' => 'test-token'];
    public $tokenCalls = 0;
    public $updates = [];
    public $failUpdate = false;
    public $lockAvailable = true;
    public $releases = 0;
    public $matchingCodes = 1;
    public function prepare($sql) { return new ConditionTestStatement($this, $sql); }
}
$calls = 0;
$transport = function ($url, $token, $payload) use (&$calls) {
    $calls++;
    checkCondition($url === 'https://example.invalid/fhir-r4/v1/Condition', 'Wrong endpoint');
    checkCondition($token === 'test-token', 'Wrong token');
    checkCondition($payload['resourceType'] === 'Condition', 'Wrong resource');
    checkCondition($payload['subject']['reference'] === 'Patient/100000030009', 'Wrong patient');
    checkCondition($payload['encounter']['reference'] === 'Encounter/encounter-test', 'Wrong encounter');
    checkCondition($payload['category'][0]['coding'][0]['code'] === 'encounter-diagnosis', 'Wrong FHIR category');
    checkCondition($payload['code']['coding'][0]['system'] === 'http://hl7.org/fhir/sid/icd-10', 'Wrong ICD system');
    checkCondition($payload['code']['coding'][0]['code'] === 'K35.8', 'Wrong ICD code');
    checkCondition($payload['identifier'][0]['value'] === 'local-uuid', 'Local UUID lost');
    return ['http_code' => 201, 'curl_error' => 0, 'body' => '{"resourceType":"Condition","id":"remote-condition-id"}'];
};
$conn = new ConditionTestConnection();
$result = syncConditionSatuSehat($conn, 12, 9, 'Session User', $transport);
checkCondition($result['status'] === 'success' && $result['id_condition'] === 'remote-condition-id', 'Successful sync failed');
checkCondition($conn->updates[0][0] === 'remote-condition-id' && $conn->updates[0][2] === 9 && $conn->updates[0][3] === 'Session User' && $conn->updates[0][4] === 12, 'Wrong ID/audit persisted');
$payload = conditionSatuSehatPayload($conn->data, '');
checkCondition($payload['verificationStatus']['coding'][0]['code'] === 'confirmed', 'Final status mismatch');
$conn->data['certainty_status'] = 'Provisional';
$payload = conditionSatuSehatPayload($conn->data, '');
checkCondition($payload['verificationStatus']['coding'][0]['code'] === 'provisional', 'Provisional status mismatch');
foreach (['id_ihs', 'id_encounter', 'nama_pasien', 'icd_code', 'icd_description', 'icd_version'] as $field) {
    $conn = new ConditionTestConnection();
    $conn->data[$field] = '';
    $before = $calls;
    $result = syncConditionSatuSehat($conn, 12, 9, 'Session User', $transport);
    checkCondition($result['status'] === 'skipped' && $conn->tokenCalls === 0 && $calls === $before, 'Incomplete data was sent: '.$field);
}
$conn = new ConditionTestConnection();
$conn->data['id_condition'] = 'existing-id';
$before = $calls;
$result = syncConditionSatuSehat($conn, 12, 9, 'Session User', $transport);
checkCondition($result['id_condition'] === 'existing-id' && $calls === $before && $conn->tokenCalls === 0, 'Already sent condition was sent again');
$conn = new ConditionTestConnection();
$conn->setting = null;
checkCondition(syncConditionSatuSehat($conn, 12, 9, 'Session User', $transport)['status'] === 'skipped', 'Missing settings not skipped');
$conn = new ConditionTestConnection();
$conn->tokenResult = ['status' => 'error'];
$before = $calls;
checkCondition(syncConditionSatuSehat($conn, 12, 9, 'Session User', $transport)['status'] === 'error' && $calls === $before, 'Token failure sent request');
foreach ([
    ['http_code' => 400, 'curl_error' => 0, 'body' => '{"resourceType":"OperationOutcome","issue":[{"details":{"text":"Invalid code"}}]}'],
    ['http_code' => 200, 'curl_error' => 0, 'body' => '{"resourceType":"OperationOutcome"}'],
    ['http_code' => 201, 'curl_error' => 0, 'body' => 'invalid JSON'],
    ['http_code' => 201, 'curl_error' => 0, 'body' => '{"resourceType":"Patient","id":"wrong"}'],
    ['http_code' => 201, 'curl_error' => 0, 'body' => '{"resourceType":"Condition"}'],
    ['http_code' => 0, 'curl_error' => 28, 'body' => false]
] as $response) {
    $conn = new ConditionTestConnection();
    $result = syncConditionSatuSehat($conn, 12, 9, 'Session User', function () use ($response) { return $response; });
    checkCondition($result['status'] === 'error' && !$conn->updates, 'Failed HTTP response persisted');
}
$conn = new ConditionTestConnection();
$conn->failUpdate = true;
$result = syncConditionSatuSehat($conn, 12, 9, 'Session User', $transport);
checkCondition($result['status'] === 'warning' && $result['id_condition'] === 'remote-condition-id', 'Remote ID lost after persistence failure');
checkCondition($conn->releases === 1, 'Failed persistence did not release lock');
$conn = new ConditionTestConnection();
$preview = conditionSatuSehatPreview($conn, 12);
checkCondition($preview['eligible'] && $conn->tokenCalls === 0 && !$conn->updates, 'Preview performed side effects');
checkCondition($preview['payload'] === conditionSatuSehatPayload($conn->data, 'org-test'), 'Preview payload differs from send payload');
$conn->data['id_encounter'] = null;
checkCondition(!conditionSatuSehatPreview($conn, 12)['eligible'], 'Incomplete preview enabled sending');
$conn = new ConditionTestConnection();
$conn->data['id_condition'] = 'already-sent';
checkCondition(!conditionSatuSehatPreview($conn, 12)['eligible'], 'Already sent preview enabled sending');
$conn = new ConditionTestConnection();
checkCondition(conditionSatuSehatDiagnosis($conn, 'local-uuid')['id_diagnosis'] === 12, 'Code lookup failed');
$conn->matchingCodes = 2;
checkCondition(conditionSatuSehatDiagnosis($conn, 'local-uuid') === null, 'Ambiguous diagnosis code accepted');
checkCondition(conditionSatuSehatDiagnosis($conn, '') === null, 'Empty diagnosis code accepted');
$conn = new ConditionTestConnection();
$conn->lockAvailable = false;
$before = $calls;
$result = syncConditionSatuSehat($conn, 12, 9, 'Session User', $transport);
checkCondition($result['status'] === 'error' && $calls === $before && $conn->tokenCalls === 0 && $conn->releases === 0, 'Concurrent send not blocked');
echo "PASS: preview consistency, no preview side effects, eligibility, code lookup and concurrent-send lock.\n";
$tlsError = conditionSatuSehatResult(['http_code' => 0, 'curl_error' => 60, 'body' => false]);
checkCondition($tlsError['curl_error'] === 60 && strpos($tlsError['message'], 'sertifikat') !== false && strpos($tlsError['message'], 'belum dapat dipastikan') === false, 'TLS failure reported as ambiguous timeout');
$timeoutError = conditionSatuSehatResult(['http_code' => 0, 'curl_error' => 28, 'body' => false]);
checkCondition(strpos($timeoutError['message'], 'belum dapat dipastikan') !== false, 'Timeout lost delivery uncertainty');
echo "PASS: certificate error and timeout have distinct messages.\n";
echo "PASS: payload, eligibility, token failure, existing ID, HTTP/FHIR validation, timeout, persistence and audit.\n";
