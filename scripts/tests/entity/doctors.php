<?php
require_once __DIR__ . '/../helpers.php';

runTest('doctor', 'info-unauthenticated', 'GET', "$V1/doctors/$doctorId/info", ['Accept' => 'application/json'], null);
runTest('doctor', 'info-invalid-token', 'GET', "$V1/doctors/$doctorId/info", authHeaders('invalid-token'), null);

if ($ownerToken) {
    runTest('doctor', 'info-not-found', 'GET', "$V1/doctors/999/info", authHeaders($ownerToken), null);
}
if ($ownerToken) {
    runTest('doctor', 'info-success', 'GET', "$V1/doctors/$doctorId/info", authHeaders($ownerToken), null);
}
if ($doctorToken) {
    runTest('doctor', 'info-self-success', 'GET', "$V1/doctors/$doctorId/info", authHeaders($doctorToken), null);
}

if ($patientToken) {
    runTest('doctor', 'info-unauthorized-patient', 'GET', "$V1/doctors/$doctorId/info", authHeaders($patientToken), null);
}

runTest('doctor', 'filter-unauthenticated', 'GET', "$V1/doctors/filter", ['Accept' => 'application/json'], null);
runTest('doctor', 'filter-invalid-token', 'GET', "$V1/doctors/filter", authHeaders('invalid-token'), null);
if ($doctorToken) {
    runTest('doctor', 'filter-unauthorized-doctor', 'GET', "$V1/doctors/filter", authHeaders($doctorToken), null);
}
if ($secretaryToken) {
    runTest('doctor', 'filter-secretary-success', 'GET', "$V1/doctors/filter", authHeaders($secretaryToken), null);
}

runTest('doctor', 'update-unauthenticated', 'POST', "$V1/doctors/update", ['Accept' => 'application/json'],
    ['consultation_fee' => 250]);
if ($patientToken) {
    runTest('doctor', 'update-unauthorized-patient', 'POST', "$V1/doctors/update",
        authHeaders($patientToken), ['consultation_fee' => 250]);
}
if ($doctorToken) {
    runTest('doctor', 'update-success', 'POST', "$V1/doctors/update",
        authHeaders($doctorToken), ['consultation_fee' => 350]);
}

runTest('doctor', 'delete-unauthenticated', 'DELETE', "$V1/doctors/$doctorId/leave", ['Accept' => 'application/json'], null);
if ($doctorToken) {
    runTest('doctor', 'delete-unauthorized-doctor', 'DELETE', "$V1/doctors/$doctorId/leave",
        authHeaders($doctorToken), null);
}
if ($ownerToken) {
    runTest('doctor', 'delete-success', 'DELETE', "$V1/doctors/$doctorId/leave",
        authHeaders($ownerToken), null);
    runTest('doctor', 'restore-unauthenticated', 'POST', "$V1/doctors/$doctorId/restore", ['Accept' => 'application/json'], []);
    runTest('doctor', 'restore-success', 'POST', "$V1/doctors/$doctorId/restore",
        authHeaders($ownerToken), []);
}

runTest('doctor', 'force-delete-unauthenticated', 'DELETE', "$V1/doctors/$doctorId/force", ['Accept' => 'application/json'], null);
if ($ownerToken) {
    runTest('doctor', 'force-delete-success', 'DELETE', "$V1/doctors/$doctorId/force",
        authHeaders($ownerToken), null);
}

summary('doctor');
return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
