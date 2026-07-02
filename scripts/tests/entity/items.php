<?php
require_once __DIR__ . '/../helpers.php';

section('List items');

runTest('item', 'list-unauthenticated', 'GET', "$V1/items", ['Accept' => 'application/json'], null);
runTest('item', 'list-invalid-token', 'GET', "$V1/items", authHeaders('invalid-token'), null);
if ($patientToken) {
    runTest('item', 'list-unauthorized-patient', 'GET', "$V1/items", authHeaders($patientToken), null);
}

if ($doctorToken) {
    runTest('item', 'list-doctor-success', 'GET', "$V1/items", authHeaders($doctorToken), null);
}

if ($ownerToken) {
    runTest('item', 'list-filter-by-name', 'GET', "$V1/items", authHeaders($ownerToken), null, ['item_name' => 'test']);
}
if ($ownerToken) {
    runTest('item', 'list-filter-by-clinic', 'GET', "$V1/items", authHeaders($ownerToken), null, ['clinic_id' => $clinicId]);
}
if ($ownerToken) {
    runTest('item', 'list-pagination', 'GET', "$V1/items", authHeaders($ownerToken), null, ['per_page' => 5, 'page' => 1]);
}

section('Create item');

runTest('item', 'store-unauthenticated', 'POST', "$V1/items", ['Accept' => 'application/json'],
    ['item_name' => 'Test Item']);
runTest('item', 'store-invalid-token', 'POST', "$V1/items", authHeaders('invalid-token'),
    ['item_name' => 'Test Item']);
if ($patientToken) {
    runTest('item', 'store-unauthorized-patient', 'POST', "$V1/items", authHeaders($patientToken),
        ['item_name' => 'Test Item', 'clinic_id' => $clinicId]);
}
if ($secretaryToken) {
    runTest('item', 'store-unauthorized-secretary', 'POST', "$V1/items", authHeaders($secretaryToken),
        ['item_name' => 'Test Item', 'clinic_id' => $clinicId]);
}
if ($doctorToken) {
    runTest('item', 'store-validation-empty', 'POST', "$V1/items", authHeaders($doctorToken), []);
}
if ($doctorToken) {
    runTest('item', 'store-validation-missing-name', 'POST', "$V1/items", authHeaders($doctorToken),
        ['clinic_id' => $clinicId]);
}
if ($doctorToken) {
    runTest('item', 'store-success', 'POST', "$V1/items", authHeaders($doctorToken),
        ['item_name' => 'Test Item ' . uniqid(), 'clinic_id' => $clinicId]);
}

section('Delete item');

$createdItemId = null;
if ($doctorToken) {
    $r = runTest('item', 'store-for-delete', 'POST', "$V1/items", authHeaders($doctorToken),
        ['item_name' => 'Delete Me ' . uniqid(), 'clinic_id' => $clinicId]);
    if (($r['status'] ?? 0) === 201) {
        $createdItemId = $r['body']['data']['id'] ?? null;
    }
}

runTest('item', 'delete-unauthenticated', 'DELETE', "$V1/items/1", ['Accept' => 'application/json'], null);
runTest('item', 'delete-invalid-token', 'DELETE', "$V1/items/1", authHeaders('invalid-token'), null);
if ($patientToken) {
    runTest('item', 'delete-unauthorized', 'DELETE', "$V1/items/1", authHeaders($patientToken), null);
}

runTest('item', 'delete-not-found', 'DELETE', "$V1/items/999", authHeaders($ownerToken ?? ''), null);
if ($ownerToken && $createdItemId) {
    runTest('item', 'delete-success', 'DELETE', "$V1/items/$createdItemId", authHeaders($ownerToken), null);
}

summary('item');
return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
