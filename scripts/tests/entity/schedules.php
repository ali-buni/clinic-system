<?php
require_once __DIR__ . '/../helpers.php';

runTest('schedule', 'store-unauthenticated', 'POST', "$V1/schedules", ['Accept' => 'application/json'],
    ['day_of_week' => 1, 'start_time' => '09:00', 'end_time' => '17:00', 'doctor_id' => $doctorId]);
runTest('schedule', 'store-invalid-token', 'POST', "$V1/schedules", authHeaders('invalid-token'),
    ['day_of_week' => 1, 'start_time' => '09:00', 'end_time' => '17:00', 'doctor_id' => $doctorId]);
if ($patientToken) {
    runTest('schedule', 'store-unauthorized-patient', 'POST', "$V1/schedules", authHeaders($patientToken),
        ['day_of_week' => 1, 'start_time' => '09:00', 'end_time' => '17:00', 'doctor_id' => $doctorId]);
}
if ($ownerToken) {
    runTest('schedule', 'store-success', 'POST', "$V1/schedules", authHeaders($ownerToken),
        ['day_of_week' => 6, 'start_time' => '09:00', 'end_time' => '17:00', 'doctor_id' => $doctorId, 'max_patients_per_day' => 10]);
}

if ($ownerToken) {
    runTest('schedule', 'store-validation', 'POST', "$V1/schedules", authHeaders($ownerToken), []);
}

runTest('schedule', 'update-unauthenticated', 'PUT', "$V1/schedules", ['Accept' => 'application/json'],
    ['day_of_week' => 1, 'doctor_id' => $doctorId, 'start_time' => '10:00', 'end_time' => '16:00']);
if ($ownerToken) {
    runTest('schedule', 'update-success', 'PUT', "$V1/schedules", authHeaders($ownerToken),
        ['day_of_week' => 2, 'doctor_id' => $doctorId, 'start_time' => '10:00', 'end_time' => '16:00']);
}

runTest('schedule', 'delete-unauthenticated', 'DELETE', "$V1/schedules/1/$doctorId", ['Accept' => 'application/json'], null);
if ($ownerToken) {
    runTest('schedule', 'delete-success', 'DELETE', "$V1/schedules/2/$doctorId", authHeaders($ownerToken), null);
}
runTest('schedule', 'weekly-public-success', 'GET', "$V1/schedules/weekly/$doctorId", ['Accept' => 'application/json'], null);
runTest('schedule', 'weekly-not-found', 'GET', "$V1/schedules/weekly/999", ['Accept' => 'application/json'], null);
runTest('schedule', 'work-hour-public-success', 'GET', "$V1/schedules/work-hour/$doctorId", ['Accept' => 'application/json'], ['date' => now()->format("Y-m-d")]);

summary('schedule');
return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
