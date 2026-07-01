<?php
require_once __DIR__ . '/../helpers.php';

runTest('disease', 'search-public-success', 'GET', "$V1/diseases/search", ['Accept' => 'application/json'], null, ['query' => 'Hypertension']);
runTest('disease', 'search-no-query', 'GET', "$V1/diseases/search", ['Accept' => 'application/json'], null);
runTest('disease', 'store-unauthenticated', 'POST', "$V1/diseases", ['Accept' => 'application/json'],
    ['name' => 'Test Disease', 'icd_code' => 'A00']);
runTest('disease', 'store-invalid-token', 'POST', "$V1/diseases", authHeaders('invalid-token'),
    ['name' => 'Test Disease', 'icd_code' => 'A00']);
if ($patientToken) {
    runTest('disease', 'store-unauthorized', 'POST', "$V1/diseases", authHeaders($patientToken),
        ['name' => 'Test Disease', 'icd_code' => 'A00']);
}
if ($doctorToken) {
    runTest('disease', 'store-validation', 'POST', "$V1/diseases", authHeaders($doctorToken), []);
}

if ($ownerToken) {
    runTest('disease', 'store-success', 'POST', "$V1/diseases", authHeaders($ownerToken),
        ['en_name' => 'Owner Disease ' . uniqid(), 'disease_nature' => 'other', 'ar_name' => 'owner']);
}

summary('disease');
return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
