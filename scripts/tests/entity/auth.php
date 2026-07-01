<?php
require_once __DIR__ . '/../helpers.php';

section('Login');

runTest('auth', 'login-missing-fields', 'POST', "$V1/login", ['Accept' => 'application/json'], []);
runTest('auth', 'login-validation', 'POST', "$V1/login", ['Accept' => 'application/json'],
    ['login' => 'not-an-email', 'password' => 'short']);
runTest('auth', 'login-success', 'POST', "$V1/login", ['Accept' => 'application/json'],
    ['login' => $ownerUser->email, 'password' => 'password']);

section('Register');

runTest('auth', 'register-missing-fields', 'POST', "$V1/register", ['Accept' => 'application/json'], []);
runTest('auth', 'register-validation', 'POST', "$V1/register", ['Accept' => 'application/json'],
    ['fname' => '', 'lname' => '', 'email' => 'not-an-email', 'password' => 'short', 'password_confirmation' => 'short', 'clinic_id' => 999]);
runTest('auth', 'register-password-mismatch', 'POST', "$V1/register", ['Accept' => 'application/json'],
    ['fname' => 'New', 'lname' => 'User', 'email' => 'newuser_' . uniqid() . '@test.com', 'password' => 'password123', 'password_confirmation' => 'different']);

runTest('auth', 'register-duplicate-email', 'POST', "$V1/register", ['Accept' => 'application/json'],
    ['fname' => 'Another', 'lname' => 'User', 'email' => $ownerUser->email, 'password' => 'password123', 'password_confirmation' => 'password123']);

runTest('auth', 'register-success', 'POST', "$V1/register", ['Accept' => 'application/json'],
    ['fname' => 'New', 'lname' => 'User', 'email' => 'newuser_' . uniqid() . '@test.com', 'password' => 'password123', 'password_confirmation' => 'password123']);

section('Forgot Password');

runTest('auth', 'forgot-password-validation', 'POST', "$V1/forgot-password", ['Accept' => 'application/json'],
    ['email' => 'not-an-email']);
runTest('auth', 'forgot-password-not-found', 'POST', "$V1/forgot-password", ['Accept' => 'application/json'],
    ['email' => 'nonexistent@test.com']);
runTest('auth', 'forgot-password-success', 'POST', "$V1/forgot-password", ['Accept' => 'application/json'],
    ['email' => $ownerUser->email]);

section('Reset Password with Code');

runTest('auth', 'reset-with-code-missing', 'POST', "$V1/reset-password-with-code", ['Accept' => 'application/json'], []);
runTest('auth', 'reset-with-code-validation', 'POST', "$V1/reset-password-with-code", ['Accept' => 'application/json'],
    ['email' => 'not-an-email', 'code' => 'abc', 'password' => 'short', 'password_confirmation' => 'short']);
runTest('auth', 'reset-with-code-not-found', 'POST', "$V1/reset-password-with-code", ['Accept' => 'application/json'],
    ['email' => 'nonexistent@test.com', 'code' => '123456', 'password' => 'newpass123', 'password_confirmation' => 'newpass123']);

$resetUser = \App\Models\User::create([
    'fname' => 'Reset', 'lname' => 'Test',
    'email' => 'reset_test_' . uniqid() . '@test.com',
    'password' => \Illuminate\Support\Facades\Hash::make('password123'),
]);
$resetUser->assignRole('patient');
\App\Models\Verification_code::create([
    'user_id'   => $resetUser->id,
    'type'      => 'email_reset',
    'sent_to'   => $resetUser->email,
    'code_hash' => \Illuminate\Support\Facades\Hash::make('123456'),
    'expires_at' => now()->addMinutes(15),
]);
runTest('auth', 'reset-with-code-success', 'POST', "$V1/reset-password-with-code", ['Accept' => 'application/json'],
    ['email' => $resetUser->email, 'code' => '123456', 'password' => 'newpass123', 'password_confirmation' => 'newpass123']);

section('Sign Out');

runTest('auth', 'signout-unauthenticated', 'POST', "$V1/signout", ['Accept' => 'application/json'], []);
runTest('auth', 'signout-invalid-token', 'POST', "$V1/signout", authHeaders('invalid-token-value'), []);
$ownerTempToken = $ownerUser->createToken('api-test-signout')->plainTextToken;
if ($ownerTempToken) {
    runTest('auth', 'signout-success', 'POST', "$V1/signout", authHeaders($ownerTempToken), []);
}

section('Reset Password (authenticated)');

if ($ownerToken) {
    runTest('auth', 'reset-password-success', 'POST', "$V1/reset-password", authHeaders($ownerToken),
    ['email' => $ownerUser->email, 'password' => 'password', 'password_confirmation' => 'password', 'new_password' => 'newpass123', 'new_password_confirmation' => 'newpass123']);
}
if ($ownerToken) {
    runTest('auth', 'reset-password-validation', 'POST', "$V1/reset-password", authHeaders($ownerToken),
        ['password' => 'short', 'password_confirmation' => 'short']);
}
if ($ownerToken) {
    runTest('auth', 'reset-password-wrong-current', 'POST', "$V1/reset-password", authHeaders($ownerToken),
        ['password' => 'wrongpassword', 'password_confirmation' => 'wrongpassword', 'new_password' => 'newpass123', 'new_password_confirmation' => 'newpass123']);
}

section('Refresh Token');

runTest('auth', 'refresh-token-unauthenticated', 'POST', "$V1/refresh-token", ['Accept' => 'application/json'], []);
runTest('auth', 'refresh-token-invalid-token', 'POST', "$V1/refresh-token", authHeaders('invalid-token-value'), []);
if ($ownerToken) {
    runTest('auth', 'refresh-token-success', 'POST', "$V1/refresh-token", authHeaders($ownerToken), []);
}

summary('auth');
return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
