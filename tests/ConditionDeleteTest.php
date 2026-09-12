<?php
require_once __DIR__.'/ConditionSatuSehatTest.php';
require_once __DIR__.'/../_Page/Condition/ConditionDeleteHelper.php';
$conn = new ConditionTestConnection();
$calls = [];
$remote = ['resourceType' => 'Condition', 'id' => 'remote-id',
    'clinicalStatus' => ['coding' => [['code' => 'active']]],
    'code' => ['text' => 'Keep diagnosis'], 'subject' => ['reference' => 'Patient/test'],
    'encounter' => ['reference' => 'Encounter/test'], 'note' => [['text' => 'Keep note']]];
$transport = function ($url, $token, $payload, $method) use (&$calls, $remote) {
    $calls[] = $method;
    checkCondition($url === 'https://example.invalid/fhir-r4/v1/Condition/remote-id', 'Wrong invalidation target');
    if ($method === 'GET') return ['http_code' => 200, 'curl_error' => 0, 'body' => json_encode($remote)];
    checkCondition($method === 'PUT', 'Invalidation used wrong method');
    checkCondition($payload['verificationStatus'] === ['coding' => [[
        'system' => 'http://terminology.hl7.org/CodeSystem/condition-ver-status',
        'code' => 'entered-in-error', 'display' => 'Entered in Error'
    ]]], 'Wrong verification status');
    checkCondition(!isset($payload['clinicalStatus']), 'FHIR con-5 violated');
    foreach (['code', 'subject', 'encounter', 'note'] as $key) checkCondition($payload[$key] === $remote[$key], 'Remote field lost: '.$key);
    return ['http_code' => 200, 'curl_error' => 0, 'body' => json_encode($payload)];
};
$result = invalidateConditionSatuSehat($conn, 'remote-id', $transport);
checkCondition($result['status'] === 'success' && $calls === ['GET', 'PUT'], 'Invalidation failed');
$invalid = $remote;
unset($invalid['clinicalStatus']);
$invalid['verificationStatus'] = ['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/condition-ver-status', 'code' => 'entered-in-error']]];
$retryCalls = 0;
$result = invalidateConditionSatuSehat($conn, 'remote-id', function ($url, $token, $payload, $method) use ($invalid, &$retryCalls) {
    $retryCalls++;
    checkCondition($method === 'GET', 'Already invalid condition updated again');
    return ['http_code' => 200, 'curl_error' => 0, 'body' => json_encode($invalid)];
});
checkCondition($result['status'] === 'success' && $retryCalls === 1, 'Retry failed');
foreach ([
    ['http_code' => 404, 'curl_error' => 0, 'body' => '{"resourceType":"OperationOutcome"}'],
    ['http_code' => 0, 'curl_error' => 28, 'body' => false],
    ['http_code' => 200, 'curl_error' => 0, 'body' => '{"resourceType":"Condition","id":"wrong-id"}']
] as $response) {
    checkCondition(invalidateConditionSatuSehat($conn, 'remote-id', function () use ($response) { return $response; })['status'] === 'error', 'Remote failure accepted');
}
$result = invalidateConditionSatuSehat($conn, 'remote-id', function () use ($remote) {
    return ['http_code' => 200, 'curl_error' => 0, 'body' => json_encode($remote)];
});
checkCondition($result['status'] === 'error', 'Unchanged verification status accepted');
echo "PASS: invalidation payload, preserved resource fields, con-5, retry, timeout, rejection and response status verification.\n";
