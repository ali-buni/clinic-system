<?php
require_once __DIR__ . '/../helpers.php';

section('Google Auth');

runTest('other', 'google-redirect', 'GET', "$API/auth/google", ['Accept' => 'application/json'], null);
runTest(
    'other',
    'google-callback-invalid',
    'GET',
    "$API/auth/google/callback",
    ['Accept' => 'application/json'],
    null,
    ['code' => 'invalid_code']
);

summary('other');
return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
