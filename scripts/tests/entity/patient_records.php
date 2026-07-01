<?php
require_once __DIR__ . '/../helpers.php';

$recId = \App\Models\Patient_record::latest('id')->first()?->id ?? 1;

$validRoomId = \App\Models\Room::first()?->id;
if (!$validRoomId) {
    $validRoomId = \App\Models\Room::create([
        'clinic_id' => $clinicId, 'name' => 'Test Room', 'type' => 'examination',
    ])->id;
}

section('CRUD');

runTest('patient-record', 'store-unauthenticated', 'POST', "$V1/patient-records", ['Accept' => 'application/json'],
    ['patient_id' => $patientId, 'diagnosis_summary' => 'Test diagnosis']);
runTest('patient-record', 'store-invalid-token', 'POST', "$V1/patient-records", authHeaders('invalid-token'),
    ['patient_id' => $patientId, 'diagnosis_summary' => 'Test diagnosis']);
if ($patientToken) {
    runTest('patient-record', 'store-unauthorized-patient', 'POST', "$V1/patient-records",
        authHeaders($patientToken), ['patient_id' => $patientId, 'diagnosis_summary' => 'Test diagnosis']);
}
if ($doctorToken) {
    runTest('patient-record', 'store-validation', 'POST', "$V1/patient-records", authHeaders($doctorToken), []);
}
if ($doctorToken) {
    $apptId = \App\Models\Appointment::first()?->id ?? 1;
    runTest('patient-record', 'store-success', 'POST', "$V1/patient-records", authHeaders($doctorToken),
        ['patient_id' => $patientId, 'doctor_id' => $doctorId, 'clinic_id' => $clinicId, 'appointment_id' => $apptId, 'diagnosis_summary' => 'Test diagnosis ' . uniqid()]);
}
runTest('patient-record', 'show-unauthenticated', 'GET', "$V1/patient-records/$recId", ['Accept' => 'application/json'], null);

if ($patientToken) {
    runTest('patient-record', 'show-success', 'GET', "$V1/patient-records/$recId", authHeaders($patientToken), null);
}

if ($doctorToken) {
    runTest('patient-record', 'show-not-found', 'GET', "$V1/patient-records/999", authHeaders($doctorToken), null);
}
runTest('patient-record', 'list-unauthenticated', 'GET', "$V1/patient-records", ['Accept' => 'application/json'], null);
if ($patientToken) {
    runTest('patient-record', 'list-unauthorized-patient', 'GET', "$V1/patient-records", authHeaders($patientToken), null);
}

if ($doctorToken) {
    runTest('patient-record', 'list-success', 'GET', "$V1/patient-records", authHeaders($doctorToken), null);
}

runTest('patient-record', 'update-unauthenticated', 'PUT', "$V1/patient-records/$recId", ['Accept' => 'application/json'],
    ['diagnosis_summary' => 'Updated']);
if ($doctorToken) {
    runTest('patient-record', 'update-success', 'PUT', "$V1/patient-records/$recId", authHeaders($doctorToken),
        ['diagnosis_summary' => 'Updated diagnosis ' . uniqid()]);
}

runTest('patient-record', 'delete-unauthenticated', 'DELETE', "$V1/patient-records/$recId", ['Accept' => 'application/json'], null);
if ($doctorToken) {
    runTest('patient-record', 'delete-success', 'DELETE', "$V1/patient-records/$recId", authHeaders($doctorToken), null);
}

section('Relationship queries');

runTest('patient-record', 'history-unauthenticated', 'GET', "$V1/patient-records/patient/$patientId/history",
    ['Accept' => 'application/json'], null);

if ($patientToken) {
    runTest('patient-record', 'history-self-success', 'GET', "$V1/patient-records/patient/$patientId/history",
        authHeaders($patientToken), null);
}

runTest('patient-record', 'get-by-doctor-unauthenticated', 'GET', "$V1/patient-records/patient/$patientId/doctor/$doctorId",
    ['Accept' => 'application/json'], null);
if ($doctorToken) {
    runTest('patient-record', 'get-by-doctor-success', 'GET', "$V1/patient-records/patient/$patientId/doctor/$doctorId",
        authHeaders($doctorToken), null);
}

runTest('patient-record', 'get-by-room-unauthenticated', 'POST', "$V1/patient-records/rooms/search",
    ['Accept' => 'application/json'], ['room_ids' => [$validRoomId]]);

if ($secretaryToken) {
    runTest('patient-record', 'get-by-room-secretary-success', 'POST', "$V1/patient-records/rooms/search",
        authHeaders($secretaryToken), ['room_ids' => [$validRoomId]]);
}
runTest('patient-record', 'get-all-by-doctor-unauthenticated', 'GET', "$V1/patient-records/doctor/$doctorId/all",
    ['Accept' => 'application/json'], null);
if ($doctorToken) {
    runTest('patient-record', 'get-all-by-doctor-success', 'GET', "$V1/patient-records/doctor/$doctorId/all",
        authHeaders($doctorToken), null);
}

summary('patient-record');
return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
