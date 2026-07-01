<?php
require_once __DIR__ . '/../helpers.php';

runTest('phone', 'update-phone-unauthenticated', 'POST', "$V1/phone/update", ['Accept' => 'application/json'],
    ['phone' => '0912345678']);
runTest('phone', 'update-phone-invalid-token', 'POST', "$V1/phone/update",
    authHeaders('invalid-token'), ['phone' => '0912345678']);
if ($ownerToken) {
    runTest('phone', 'update-phone-validation', 'POST', "$V1/phone/update", authHeaders($ownerToken), []);
}

if ($patientToken) {
    runTest('phone', 'update-phone-success', 'POST', "$V1/phone/update",
        authHeaders($patientToken), ['phone' => '0944444445']);
}

if ($patientToken) {
    \Illuminate\Support\Facades\Cache::put('phone_update:' . $patientUser->id, [
        'code' => \Illuminate\Support\Facades\Hash::make('123456'),
        'new_phone' => '0944444445',
        'attempts' => 0,
    ], now()->addMinutes(15));
    runTest('phone', 'verify-phone-update-success', 'POST', "$V1/phone/verify-update",
        authHeaders($patientToken), ['code' => '123456']);
}

if ($ownerToken) {
    runTest('phone', 'verify-phone-update-missing', 'POST', "$V1/phone/verify-update",
        authHeaders($ownerToken), []);
}
if ($ownerToken) {
    runTest('phone', 'verify-phone-update-validation', 'POST', "$V1/phone/verify-update",
        authHeaders($ownerToken), ['code' => '']);
}
runTest('phone', 'verify-phone-update-unauthenticated', 'POST', "$V1/phone/verify-update",
    ['Accept' => 'application/json'], ['code' => '123456']);

summary('phone');
return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
