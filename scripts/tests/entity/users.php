<?php

require_once __DIR__ . '/../helpers.php';

runTest('user', 'info-unauthenticated', 'GET', "$V1/users/info", ['Accept' => 'application/json'], null);
runTest('user', 'info-invalid-token', 'GET', "$V1/users/info", authHeaders('invalid-token'), null);
if ($ownerToken) {
    runTest('user', 'info-owner-success', 'GET', "$V1/users/info", authHeaders($ownerToken), null);
}
if ($doctorToken) {
    runTest('user', 'info-doctor-success', 'GET', "$V1/users/info", authHeaders($doctorToken), null);
}
if ($patientToken) {
    runTest('user', 'info-patient-success', 'GET', "$V1/users/info", authHeaders($patientToken), null);
}
if ($secretaryToken) {
    runTest('user', 'info-secretary-success', 'GET', "$V1/users/info", authHeaders($secretaryToken), null);
}
runTest('user', 'update-image-unauthenticated', 'POST', "$V1/users/update-image",
    ['Accept' => 'application/json'], []);
if ($ownerToken) {
    runTest('user', 'update-image-validation', 'POST', "$V1/users/update-image",
        authHeaders($ownerToken), []);
}
if ($patientToken) {
    $tmp = tempnam(sys_get_temp_dir(), 'test_img_') . '.png';
    file_put_contents($tmp, base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
    ));
    runTest('user', 'update-image-patient-success', 'POST', "$V1/users/update-image",
        authHeaders($patientToken), ['profile_image' => curl_file_create($tmp, 'image/png', 'test.png')]);
    unlink($tmp);
}
runTest('user', 'image-url-unauthenticated', 'GET', "$V1/users/image-url", ['Accept' => 'application/json'], null);
if ($ownerToken) {
    runTest('user', 'image-url-owner-success', 'GET', "$V1/users/image-url", authHeaders($ownerToken), null);
}

summary('user');
return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
