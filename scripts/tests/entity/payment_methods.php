<?php
require_once __DIR__ . '/../helpers.php';

section('List payment methods');

runTest('payment_method', 'list-unauthenticated', 'GET', "$V1/payment-methods", ['Accept' => 'application/json'], null);
runTest('payment_method', 'list-invalid-token', 'GET', "$V1/payment-methods", authHeaders('invalid-token'), null);
if ($ownerToken) {
    runTest('payment_method', 'list-success', 'GET', "$V1/payment-methods", authHeaders($ownerToken), null);
}

section('Store payment method');

runTest('payment_method', 'store-unauthenticated', 'POST', "$V1/payment-methods", ['Accept' => 'application/json'],
    ['ar_name' => 'طريقة دفع تجريبية', 'en_name' => 'Test Payment Method', 'type' => 'Cash']);
runTest('payment_method', 'store-invalid-token', 'POST', "$V1/payment-methods", authHeaders('invalid-token'),
    ['ar_name' => 'طريقة دفع تجريبية', 'en_name' => 'Test Payment Method', 'type' => 'Cash']);
if ($patientToken) {
    runTest('payment_method', 'store-unauthorized-patient', 'POST', "$V1/payment-methods", authHeaders($patientToken),
        ['ar_name' => 'طريقة دفع تجريبية', 'en_name' => 'Test Payment Method', 'type' => 'Cash']);
}
if ($doctorToken) {
    runTest('payment_method', 'store-unauthorized-doctor', 'POST', "$V1/payment-methods", authHeaders($doctorToken),
        ['ar_name' => 'طريقة دفع تجريبية', 'en_name' => 'Test Payment Method', 'type' => 'Cash']);
}
if ($secretaryToken) {
    runTest('payment_method', 'store-unauthorized-secretary', 'POST', "$V1/payment-methods", authHeaders($secretaryToken),
        ['ar_name' => 'طريقة دفع تجريبية', 'en_name' => 'Test Payment Method', 'type' => 'Cash']);
}
if ($ownerToken) {
    runTest('payment_method', 'store-validation-empty', 'POST', "$V1/payment-methods", authHeaders($ownerToken), []);
}
if ($ownerToken) {
    runTest('payment_method', 'store-validation-invalid-type', 'POST', "$V1/payment-methods", authHeaders($ownerToken),
        ['ar_name' => 'طريقة دفع', 'en_name' => 'Payment Method', 'type' => 'InvalidType']);
}
if ($ownerToken) {
    $storeResult = runTest('payment_method', 'store-success', 'POST', "$V1/payment-methods", authHeaders($ownerToken),
        ['ar_name' => 'طريقة دفع تجريبية ' . uniqid(), 'en_name' => 'Test Method ' . uniqid(), 'type' => 'Cash']);
    $createdPaymentMethodId = $storeResult['body']['data']['id'] ?? null;
}

section('Stop payment method');

runTest('payment_method', 'stop-unauthenticated', 'PATCH', "$V1/payment-methods/1/stop", ['Accept' => 'application/json'], null);
runTest('payment_method', 'stop-invalid-token', 'PATCH', "$V1/payment-methods/1/stop", authHeaders('invalid-token'), null);
if ($patientToken) {
    runTest('payment_method', 'stop-unauthorized-patient', 'PATCH', "$V1/payment-methods/1/stop", authHeaders($patientToken), null);
}
if ($doctorToken) {
    runTest('payment_method', 'stop-unauthorized-doctor', 'PATCH', "$V1/payment-methods/1/stop", authHeaders($doctorToken), null);
}
if ($secretaryToken) {
    runTest('payment_method', 'stop-unauthorized-secretary', 'PATCH', "$V1/payment-methods/1/stop", authHeaders($secretaryToken), null);
}
if ($ownerToken) {
    runTest('payment_method', 'stop-not-found', 'PATCH', "$V1/payment-methods/999/stop", authHeaders($ownerToken), null);
}
if ($ownerToken && isset($createdPaymentMethodId)) {
    runTest('payment_method', 'stop-success', 'PATCH', "$V1/payment-methods/$createdPaymentMethodId/stop",
        authHeaders($ownerToken), null);
}

section('Delete payment method');

runTest('payment_method', 'delete-unauthenticated', 'DELETE', "$V1/payment-methods/1", ['Accept' => 'application/json'], null);
runTest('payment_method', 'delete-invalid-token', 'DELETE', "$V1/payment-methods/1", authHeaders('invalid-token'), null);
if ($patientToken) {
    runTest('payment_method', 'delete-unauthorized-patient', 'DELETE', "$V1/payment-methods/1", authHeaders($patientToken), null);
}
if ($doctorToken) {
    runTest('payment_method', 'delete-unauthorized-doctor', 'DELETE', "$V1/payment-methods/1", authHeaders($doctorToken), null);
}
if ($secretaryToken) {
    runTest('payment_method', 'delete-unauthorized-secretary', 'DELETE', "$V1/payment-methods/1", authHeaders($secretaryToken), null);
}
if ($ownerToken) {
    runTest('payment_method', 'delete-not-found', 'DELETE', "$V1/payment-methods/999", authHeaders($ownerToken), null);
}
if ($ownerToken && isset($createdPaymentMethodId)) {
    runTest('payment_method', 'delete-success', 'DELETE', "$V1/payment-methods/$createdPaymentMethodId",
        authHeaders($ownerToken), null);
}

summary('payment_method');
return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
