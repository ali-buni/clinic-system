<?php
require_once __DIR__ . '/../helpers.php';

runTest('verification', 'verify-code-missing', 'POST', "$V1/verify-code", ['Accept' => 'application/json'], []);
runTest(
    'verification',
    'verify-code-validation',
    'POST',
    "$V1/verify-code",
    ['Accept' => 'application/json'],
    ['email' => 'not-an-email', 'code' => '']
);
runTest('verification', 'resend-code-missing', 'POST', "$V1/resend-code", ['Accept' => 'application/json'], []);
runTest(
    'verification',
    'resend-code-validation',
    'POST',
    "$V1/resend-code",
    ['Accept' => 'application/json'],
    ['email' => 'not-an-email']
);
runTest(
    'verification',
    'resend-code-not-found',
    'POST',
    "$V1/resend-code",
    ['Accept' => 'application/json'],
    ['email' => 'nonexistent@test.com']
);
runTest(
    'verification',
    'resend-code-success',
    'POST',
    "$V1/resend-code",
    ['Accept' => 'application/json'],
    ['login' => $patientUser2->email, 'password' => 'password']
);

\App\Models\Verification_code::create([
    'user_id'    => $ownerUser->id,
    'type'       => 'email',
    'sent_to'    => $ownerUser->email,
    'code_hash'  => \Illuminate\Support\Facades\Hash::make('123456'),
    'expires_at' => now()->addMinutes(15),
]);
runTest(
    'verification',
    'verify-code-success',
    'POST',
    "$V1/verify-code",
    ['Accept' => 'application/json'],
    ['login' => $ownerUser->email, 'code' => '123456', 'type' => 'email']
);

summary('verification');
return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
