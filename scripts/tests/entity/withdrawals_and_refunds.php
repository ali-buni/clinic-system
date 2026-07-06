<?php
require_once __DIR__ . '/../helpers.php';

section('Doctor withdrawals - Balance');

runTest('withdrawal', 'balance-unauthenticated', 'GET', "$V1/doctor-withdrawals/balance", ['Accept' => 'application/json'], null);
runTest('withdrawal', 'balance-invalid-token', 'GET', "$V1/doctor-withdrawals/balance", authHeaders('invalid-token'), null);
if ($ownerToken) {
    runTest('withdrawal', 'balance-unauthorized-owner', 'GET', "$V1/doctor-withdrawals/balance", authHeaders($ownerToken), null);
}
if ($patientToken) {
    runTest('withdrawal', 'balance-unauthorized-patient', 'GET', "$V1/doctor-withdrawals/balance", authHeaders($patientToken), null);
}
if ($secretaryToken) {
    runTest('withdrawal', 'balance-unauthorized-secretary', 'GET', "$V1/doctor-withdrawals/balance", authHeaders($secretaryToken), null);
}
if ($doctorToken) {
    runTest('withdrawal', 'balance-doctor-success', 'GET', "$V1/doctor-withdrawals/balance", authHeaders($doctorToken), null);
}

section('Doctor withdrawals - List');

runTest('withdrawal', 'list-unauthenticated', 'GET', "$V1/doctor-withdrawals", ['Accept' => 'application/json'], null);
runTest('withdrawal', 'list-invalid-token', 'GET', "$V1/doctor-withdrawals", authHeaders('invalid-token'), null);
if ($ownerToken) {
    runTest('withdrawal', 'list-unauthorized-owner', 'GET', "$V1/doctor-withdrawals", authHeaders($ownerToken), null);
}
if ($patientToken) {
    runTest('withdrawal', 'list-unauthorized-patient', 'GET', "$V1/doctor-withdrawals", authHeaders($patientToken), null);
}
if ($secretaryToken) {
    runTest('withdrawal', 'list-unauthorized-secretary', 'GET', "$V1/doctor-withdrawals", authHeaders($secretaryToken), null);
}
if ($doctorToken) {
    runTest('withdrawal', 'list-doctor-success', 'GET', "$V1/doctor-withdrawals", authHeaders($doctorToken), null);
}

section('Doctor withdrawals - Store');

runTest('withdrawal', 'store-unauthenticated', 'POST', "$V1/doctor-withdrawals", ['Accept' => 'application/json'],
    ['amount' => 100]);
runTest('withdrawal', 'store-invalid-token', 'POST', "$V1/doctor-withdrawals", authHeaders('invalid-token'),
    ['amount' => 100]);
if ($ownerToken) {
    runTest('withdrawal', 'store-unauthorized-owner', 'POST', "$V1/doctor-withdrawals", authHeaders($ownerToken),
        ['amount' => 100]);
}
if ($patientToken) {
    runTest('withdrawal', 'store-unauthorized-patient', 'POST', "$V1/doctor-withdrawals", authHeaders($patientToken),
        ['amount' => 100]);
}
if ($secretaryToken) {
    runTest('withdrawal', 'store-unauthorized-secretary', 'POST', "$V1/doctor-withdrawals", authHeaders($secretaryToken),
        ['amount' => 100]);
}
if ($doctorToken) {
    runTest('withdrawal', 'store-validation-empty', 'POST', "$V1/doctor-withdrawals", authHeaders($doctorToken), []);
}
if ($doctorToken) {
    runTest('withdrawal', 'store-validation-invalid-amount', 'POST', "$V1/doctor-withdrawals", authHeaders($doctorToken),
        ['amount' => -50]);
}
if ($doctorToken) {
    runTest('withdrawal', 'store-validation-zero-amount', 'POST', "$V1/doctor-withdrawals", authHeaders($doctorToken),
        ['amount' => 0]);
}

section('Doctor withdrawals - Setup Stripe');

runTest('withdrawal', 'setup-stripe-unauthenticated', 'POST', "$V1/doctor-withdrawals/setup-stripe",
    ['Accept' => 'application/json'], null);
runTest('withdrawal', 'setup-stripe-invalid-token', 'POST', "$V1/doctor-withdrawals/setup-stripe",
    authHeaders('invalid-token'), null);
if ($ownerToken) {
    runTest('withdrawal', 'setup-stripe-unauthorized-owner', 'POST', "$V1/doctor-withdrawals/setup-stripe",
        authHeaders($ownerToken), null);
}
if ($patientToken) {
    runTest('withdrawal', 'setup-stripe-unauthorized-patient', 'POST', "$V1/doctor-withdrawals/setup-stripe",
        authHeaders($patientToken), null);
}
if ($secretaryToken) {
    runTest('withdrawal', 'setup-stripe-unauthorized-secretary', 'POST', "$V1/doctor-withdrawals/setup-stripe",
        authHeaders($secretaryToken), null);
}
if ($doctorToken) {
    runTest('withdrawal', 'setup-stripe-doctor-success', 'POST', "$V1/doctor-withdrawals/setup-stripe",
        authHeaders($doctorToken), null);
}

section('Payment refunds');

runTest('refund', 'refund-unauthenticated', 'POST', "$V1/payments/refund", ['Accept' => 'application/json'],
    ['refunds' => [['payment_id' => 1, 'amount' => 10, 'reason' => 'Test refund']]]);
runTest('refund', 'refund-invalid-token', 'POST', "$V1/payments/refund", authHeaders('invalid-token'),
    ['refunds' => [['payment_id' => 1, 'amount' => 10, 'reason' => 'Test refund']]]);
if ($ownerToken) {
    runTest('refund', 'refund-unauthorized-owner', 'POST', "$V1/payments/refund", authHeaders($ownerToken),
        ['refunds' => [['payment_id' => 1, 'amount' => 10, 'reason' => 'Test refund']]]);
}
if ($patientToken) {
    runTest('refund', 'refund-unauthorized-patient', 'POST', "$V1/payments/refund", authHeaders($patientToken),
        ['refunds' => [['payment_id' => 1, 'amount' => 10, 'reason' => 'Test refund']]]);
}
if ($secretaryToken) {
    runTest('refund', 'refund-unauthorized-secretary', 'POST', "$V1/payments/refund", authHeaders($secretaryToken),
        ['refunds' => [['payment_id' => 1, 'amount' => 10, 'reason' => 'Test refund']]]);
}
if ($doctorToken) {
    runTest('refund', 'refund-validation-empty', 'POST', "$V1/payments/refund", authHeaders($doctorToken), []);
}
if ($doctorToken) {
    runTest('refund', 'refund-validation-empty-refunds', 'POST', "$V1/payments/refund", authHeaders($doctorToken),
        ['refunds' => []]);
}
if ($doctorToken) {
    runTest('refund', 'refund-not-found-payment', 'POST', "$V1/payments/refund", authHeaders($doctorToken),
        ['refunds' => [['payment_id' => 99999, 'amount' => 10]]]);
}
if ($doctorToken) {
    runTest('refund', 'refund-invalid-amount', 'POST', "$V1/payments/refund", authHeaders($doctorToken),
        ['refunds' => [['payment_id' => 1, 'amount' => -10]]]);
}

summary('withdrawal');
return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
