<?php
require_once __DIR__ . '/../helpers.php';

runTest('device', 'register-token-unauthenticated', 'POST', "$V1/devices/register-token",
    ['Accept' => 'application/json'], ['fcm_token' => 'test-token-123']);
runTest('device', 'register-token-invalid-token', 'POST', "$V1/devices/register-token",
    authHeaders('invalid-token'), ['fcm_token' => 'test-token-123']);
if ($ownerToken) {
    runTest('device', 'register-token-validation', 'POST', "$V1/devices/register-token",
        authHeaders($ownerToken), []);
}
if ($ownerToken) {
    runTest('device', 'register-token-success', 'POST', "$V1/devices/register-token",
        authHeaders($ownerToken), ['fcm_token' => 'test-token-owner-' . uniqid()]);
}

summary('device');
return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
