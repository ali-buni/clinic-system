<?php
require_once __DIR__ . '/../helpers.php';

section('List payments');

runTest('payment', 'list-unauthenticated', 'GET', "$V1/payments", ['Accept' => 'application/json'], null, ['invoice_id' => 1]);
runTest('payment', 'list-invalid-token', 'GET', "$V1/payments", authHeaders('invalid-token'), null, ['invoice_id' => 1]);
runTest('payment', 'list-missing-invoice-id', 'GET', "$V1/payments", ['Accept' => 'application/json'], null);
if ($patientToken) {
    runTest('payment', 'list-unauthorized-patient', 'GET', "$V1/payments", authHeaders($patientToken), null, ['invoice_id' => 1]);
}
if ($ownerToken) {
    runTest('payment', 'list-success', 'GET', "$V1/payments", authHeaders($ownerToken), null, ['invoice_id' => 1]);
}

section('Show payment');

runTest('payment', 'show-unauthenticated', 'GET', "$V1/payments/1", ['Accept' => 'application/json'], null);
runTest('payment', 'show-invalid-token', 'GET', "$V1/payments/1", authHeaders('invalid-token'), null);
if ($patientToken) {
    runTest('payment', 'show-unauthorized-patient', 'GET', "$V1/payments/1", authHeaders($patientToken), null);
}
if ($ownerToken) {
    runTest('payment', 'show-not-found', 'GET', "$V1/payments/999", authHeaders($ownerToken), null);
}
if ($ownerToken) {
    runTest('payment', 'show-success', 'GET', "$V1/payments/1", authHeaders($ownerToken), null);
}


section('Store payment (process)');

runTest('payment', 'store-unauthenticated', 'POST', "$V1/payments", ['Accept' => 'application/json'],
    ['invoice_id' => 1, 'payment_method_id' => 1, 'amount' => 100]);
runTest('payment', 'store-invalid-token', 'POST', "$V1/payments", authHeaders('invalid-token'),
    ['invoice_id' => 1, 'payment_method_id' => 1, 'amount' => 100]);
if ($patientToken) {
    runTest('payment', 'store-unauthorized-patient', 'POST', "$V1/payments", authHeaders($patientToken),
        ['invoice_id' => 1, 'payment_method_id' => 1, 'amount' => 100]);
}
if ($ownerToken) {
    runTest('payment', 'store-validation-empty', 'POST', "$V1/payments", authHeaders($ownerToken), []);
}
if ($ownerToken) {
    runTest('payment', 'store-validation-missing-fields', 'POST', "$V1/payments", authHeaders($ownerToken),
        ['invoice_id' => 1]);
}
if ($ownerToken) {
    runTest('payment', 'store-not-found-invoice', 'POST', "$V1/payments", authHeaders($ownerToken),
        ['invoice_id' => 99999, 'payment_method_id' => 1, 'amount' => 100]);
}
if ($ownerToken) {
    runTest('payment', 'store-not-found-payment-method', 'POST', "$V1/payments", authHeaders($ownerToken),
        ['invoice_id' => 1, 'payment_method_id' => 99999, 'amount' => 100]);
}
if ($ownerToken) {
    runTest('payment', 'store-invalid-amount', 'POST', "$V1/payments", authHeaders($ownerToken),
        ['invoice_id' => 1, 'payment_method_id' => 1, 'amount' => -50]);
}


$createdPaymentId = null;
$paymentUrl = null;
if ($patientToken) {
    $r = runTest('payment', 'store-success', 'POST', "$V1/payments", authHeaders($patientToken),
        ['invoice_id' => $invoice->id, 'payment_method_id' => 4, 'amount' => $invoice->total_cost]);
    $paymentUrl = $r['body']['data']['payment_url'] ?? null;
    $lastPayment = \App\Models\Payment::latest('id')->first();
    $createdPaymentId = $lastPayment?->id;
    if ($paymentUrl) {
        echo "\n  [!] Stripe payment URL: $paymentUrl\n";
        echo "  [!] Open this URL in browser to complete payment.\n";
        echo "  [!] Press ENTER here when payment is done...\n";
        readline();
        echo "\n";
    }
}

section('Delete payment');

runTest('payment', 'delete-unauthenticated', 'DELETE', "$V1/payments/1", ['Accept' => 'application/json'], null);
runTest('payment', 'delete-invalid-token', 'DELETE', "$V1/payments/1", authHeaders('invalid-token'), null);
if ($patientToken) {
    runTest('payment', 'delete-unauthorized-patient', 'DELETE', "$V1/payments/1", authHeaders($patientToken), null);
}
if ($ownerToken) {
    runTest('payment', 'delete-not-found', 'DELETE', "$V1/payments/999", authHeaders($ownerToken), null);
}

if ($ownerToken && $createdPaymentId) {
    runTest('payment', 'delete-success', 'DELETE', "$V1/payments/$createdPaymentId", authHeaders($ownerToken), null);
}

summary('payment');
return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
