<?php
require_once __DIR__ . '/../helpers.php';

runTest('secretary', 'info-unauthenticated', 'GET', "$V1/secretaries/$secretaryId", ['Accept' => 'application/json'], null);
runTest('secretary', 'info-invalid-token', 'GET', "$V1/secretaries/$secretaryId", authHeaders('invalid-token'), null);
if ($ownerToken) {
    runTest('secretary', 'info-not-found', 'GET', "$V1/secretaries/999", authHeaders($ownerToken), null);
}
if ($ownerToken) {
    runTest('secretary', 'info-owner-success', 'GET', "$V1/secretaries/$secretaryId", authHeaders($ownerToken), null);
}
if ($doctorToken) {
    runTest('secretary', 'info-doctor-success', 'GET', "$V1/secretaries/$secretaryId", authHeaders($doctorToken), null);
}
// if ($secretaryToken) {
//     runTest('secretary', 'info-secretary-self-success', 'GET', "$V1/secretaries/$secretaryId", authHeaders($secretaryToken), null);
// }

if ($doctorToken) {
    runTest('secretary', 'update-unauthorized-doctor', 'POST', "$V1/secretaries/update",
        authHeaders($doctorToken), ['fname' => 'Updated']);
}
if ($secretaryToken) {
    runTest('secretary', 'update-secretary-success', 'POST', "$V1/secretaries/update",
        authHeaders($secretaryToken), ['fname' => 'UpdatedSec ' . uniqid()]);
}

summary('secretary');
return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
