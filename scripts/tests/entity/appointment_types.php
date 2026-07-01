<?php
require_once __DIR__ . '/../helpers.php';

runTest('appointment-type', 'index-success', 'GET', "$V1/appointment-types", ['Accept' => 'application/json'], null);
runTest(
    'appointment-type',
    'add-unauthenticated',
    'POST',
    "$V1/appointment-types",
    ['Accept' => 'application/json'],
    ['ar_name' => 'Test Type']
);

if ($patientToken) {
    runTest(
        'appointment-type',
        'add-unauthorized-patient',
        'POST',
        "$V1/appointment-types",
        authHeaders($patientToken),
        ['ar_name' => 'Test Type', 'en_name' => 'Test Type', 'types' => 2]
    );
}
if ($ownerToken) {
    runTest('appointment-type', 'add-validation', 'POST', "$V1/appointment-types", authHeaders($ownerToken), []);
}
if ($ownerToken) {
    runTest(
        'appointment-type',
        'add-success',
        'POST',
        "$V1/appointment-types",
        authHeaders($ownerToken),
        ['en_name' => 'New Type ' . uniqid(), 'ar_name' => 'New Type ' . uniqid(), 'types' => 1]
    );
}
runTest('appointment-type', 'delete-unauthenticated', 'DELETE', "$V1/appointment-types/999", ['Accept' => 'application/json'], null);
if ($ownerToken) {
    runTest('appointment-type', 'delete-not-found', 'DELETE', "$V1/appointment-types/999", authHeaders($ownerToken), null);
}
if ($ownerToken) {
    $allTypes = request('GET', "$V1/appointment-types", ['Accept' => 'application/json'], null)['body']['data'] ?? '[]';
    $typeIds = array_column(is_array($allTypes) ? $allTypes : [], 'id');
    $targetId = !empty($typeIds) ? end($typeIds) : 1;
    runTest(
        'appointment-type',
        'delete-success',
        'DELETE',
        "$V1/appointment-types/$targetId",
        authHeaders($ownerToken),
        null
    );
}

summary('appointment-type');
return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
