<?php
require_once __DIR__ . '/../helpers.php';

runTest('specialty', 'index-public-success', 'GET', "$V1/specialties", ['Accept' => 'application/json'], null);
runTest('specialty', 'attach-unauthenticated', 'POST', "$V1/specialties", ['Accept' => 'application/json'],
    ['specialty_ids' => [1, 2]]);
runTest('specialty', 'attach-invalid-token', 'POST', "$V1/specialties", authHeaders('invalid-token'),
    ['specialty_ids' => [1, 2]]);
if ($patientToken) {
    runTest('specialty', 'attach-unauthorized-patient', 'POST', "$V1/specialties",
        authHeaders($patientToken), ['specialty_ids' => [1, 2]]);
}
if ($doctorToken) {
    runTest('specialty', 'attach-success', 'POST', "$V1/specialties",
        authHeaders($doctorToken), ['doctor_id' => $doctorId, 'specialty_ids' => [1, 2]]);
}

runTest('specialty', 'detach-unauthenticated', 'DELETE', "$V1/specialties/1", ['Accept' => 'application/json'], null);
if ($doctorToken) {
    runTest('specialty', 'detach-success', 'DELETE', "$V1/specialties/2", authHeaders($doctorToken), null);
}
runTest('specialty', 'change-primary-unauthenticated', 'POST', "$V1/specialties/1/primary",
    ['Accept' => 'application/json'], []);
if ($doctorToken) {
    runTest('specialty', 'change-primary-success', 'POST', "$V1/specialties/1/primary",
        authHeaders($doctorToken), []);
}

runTest('specialty', 'show-primary-unauthenticated', 'GET', "$V1/specialties/doctor/$doctorId/primary",
    ['Accept' => 'application/json'], null);
if ($doctorToken) {
    runTest('specialty', 'show-primary-success', 'GET', "$V1/specialties/doctor/$doctorId/primary",
        authHeaders($doctorToken), null);
}

runTest('specialty', 'show-doctor-specialties-unauthenticated', 'GET', "$V1/specialties/doctor/$doctorId",
    ['Accept' => 'application/json'], null);
if ($doctorToken) {
    runTest('specialty', 'show-doctor-specialties-success', 'GET', "$V1/specialties/doctor/$doctorId",
        authHeaders($doctorToken), null);
}

if ($doctorToken) {
    runTest('specialty', 'show-doctor-specialties-not-found', 'GET', "$V1/specialties/doctor/999",
        authHeaders($doctorToken), null);
}

summary('specialty');
return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
