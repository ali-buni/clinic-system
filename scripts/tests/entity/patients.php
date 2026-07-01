<?php
require_once __DIR__ . '/../helpers.php';

runTest('patient', 'list-unauthenticated', 'GET', "$V1/patients", ['Accept' => 'application/json'], null);
runTest('patient', 'list-invalid-token', 'GET', "$V1/patients", authHeaders('invalid-token'), null);
if ($patientToken) {
    runTest('patient', 'list-unauthorized-patient', 'GET', "$V1/patients", authHeaders($patientToken), null);
}
if ($ownerToken) {
    runTest('patient', 'list-success', 'GET', "$V1/patients", authHeaders($ownerToken), null);
}

if ($ownerToken) {
    runTest('patient', 'show-not-found', 'GET', "$V1/patients/999", authHeaders($ownerToken), null);
}
runTest('patient', 'show-unauthenticated', 'GET', "$V1/patients/$patientId", ['Accept' => 'application/json'], null);
if ($secretaryToken) {
    runTest('patient', 'show-success', 'GET', "$V1/patients/$patientId", authHeaders($secretaryToken), null);
}

runTest('patient', 'medical-history-unauthenticated', 'GET', "$V1/patients/$patientId/medical-history",
    ['Accept' => 'application/json'], null);
if ($secretaryToken) {
    runTest('patient', 'medical-history-success', 'GET', "$V1/patients/$patientId/medical-history",
        authHeaders($secretaryToken), null);
}
if ($patientToken) {
    runTest('patient', 'medical-history-patient-self-success', 'GET', "$V1/patients/$patientId/medical-history",
        authHeaders($patientToken), null);
}
if ($patientToken) {
    runTest('patient', 'medical-history-not-found', 'GET', "$V1/patients/999/medical-history",
        authHeaders($patientToken), null);
}

runTest('patient', 'update-unauthenticated', 'POST', "$V1/patients/update", ['Accept' => 'application/json'],
    ['dob' => '1990-01-01']);
runTest('patient', 'update-invalid-token', 'POST', "$V1/patients/update", authHeaders('invalid-token'),
    ['dob' => '1990-01-01']);
if ($patientToken) {
    runTest('patient', 'update-success', 'POST', "$V1/patients/update",
        authHeaders($patientToken), ['patient_id' => $patientId,'dob' => '1995-06-15']);
}
runTest('patient', 'trashed-unauthenticated', 'GET', "$V1/patients/trashed", ['Accept' => 'application/json'], null);
runTest('patient', 'delete-unauthenticated', 'DELETE', "$V1/patients/delete", ['Accept' => 'application/json'], []);
runTest('patient', 'restore-unauthenticated', 'GET', "$V1/patients/restore", ['Accept' => 'application/json'], null);

if ($ownerToken) {
    runTest('patient', 'delete-success', 'DELETE', "$V1/patients/delete",
        authHeaders($ownerToken), ['patient_id' => $patientId]);
}
if ($ownerToken) {
    runTest('patient', 'trashed-success', 'GET', "$V1/patients/trashed",
        authHeaders($ownerToken), null, ['clinic_id' => $clinicId]);
}
if ($ownerToken) {
    runTest('patient', 'restore-success', 'GET', "$V1/patients/restore/patient",
        authHeaders($ownerToken), ['patient_id' => $patientId]);
}

summary('patient');
return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
