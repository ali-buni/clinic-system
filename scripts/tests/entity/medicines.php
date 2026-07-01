<?php
require_once __DIR__ . '/../helpers.php';

runTest('medicine', 'search-public-success', 'GET', "$V1/medicines/search", ['Accept' => 'application/json'], null, ['query' => 'test']);
runTest('medicine', 'search-no-query', 'GET', "$V1/medicines/search", ['Accept' => 'application/json'], null);
runTest('medicine', 'store-unauthenticated', 'POST', "$V1/medicines", ['Accept' => 'application/json'],
    ['name' => 'Test Med', 'dosage' => '500mg']);
runTest('medicine', 'store-invalid-token', 'POST', "$V1/medicines", authHeaders('invalid-token'),
    ['name' => 'Test Med', 'dosage' => '500mg']);
if ($patientToken) {
    runTest('medicine', 'store-unauthorized-patient', 'POST', "$V1/medicines", authHeaders($patientToken),
        ['name' => 'Test Med', 'dosage' => '500mg']);
}
if ($doctorToken) {
    runTest('medicine', 'store-validation', 'POST', "$V1/medicines", authHeaders($doctorToken), []);
}
if ($doctorToken) {
    runTest('medicine', 'store-success', 'POST', "$V1/medicines", authHeaders($doctorToken),
        ['en_name' => 'Test Med ' . uniqid()]);
}

summary('medicine');
return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
