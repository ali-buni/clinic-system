<?php
require_once __DIR__ . '/../helpers.php';

if ($ownerToken) {
runTest('clinic', 'info-public-success', 'GET', "$V1/info", authHeaders($ownerToken), null);
}

runTest('clinic', 'update-unauthenticated', 'POST', "$V1/update/$clinicId", ['Accept' => 'application/json'],
    ['title' => 'Updated Clinic']);
if ($doctorToken) {
    runTest('clinic', 'update-unauthorized-doctor', 'POST', "$V1/update/$clinicId",
        authHeaders($doctorToken), ['title' => 'Updated Clinic']);
}
if ($ownerToken) {
    runTest('clinic', 'update-success', 'POST', "$V1/update/$clinicId",
        authHeaders($ownerToken), ['title' => 'Updated Clinic ' . uniqid()]);
}

runTest('clinic', 'create-doctor-unauthenticated', 'POST', "$V1/doctors/register",
    ['Accept' => 'application/json'],
    ['fname' => 'New', 'lname' => 'Doctor', 'email' => 'newdoc_' . uniqid() . '@test.com']);
if ($doctorToken) {
    runTest('clinic', 'create-secretary-unauthorized-doctor', 'POST', "$V1/secretaries/register",
        authHeaders($doctorToken),
        ['fname' => 'New', 'lname' => 'Sec', 'email' => 'newsec_' . uniqid() . '@test.com']);
}

if ($ownerToken) {
    runTest('clinic', 'create-doctor-success', 'POST', "$V1/doctors/register",
        authHeaders($ownerToken),
        ['fname' => 'NewDoc', 'lname' => 'Test', 'email' => 'newdoc_' . uniqid() . '@test.com', 'dob' => '1999-01-01', 'gender' => 'male', 'clinic_id' => 1, 'room_id' => 1, 'consultation_fee' => 10, 'specialty_ids' => [1, 3], 'appointment_duration' => 30]);
}
if ($ownerToken) {
    runTest('clinic', 'create-secretary-success', 'POST', "$V1/secretaries/register",
        authHeaders($ownerToken),
        ['fname' => 'NewSec', 'lname' => 'Test', 'email' => 'newsec_' . uniqid() . '@test.com', 'dob' => '1999-01-01', 'gender' => 'male', 'clinic_id' => 1, 'room_ids' => [1]]);
}

if ($ownerToken) {
    runTest('clinic', 'create-doctor-duplicate-email', 'POST', "$V1/doctors/register",
        authHeaders($ownerToken),
        ['fname' => 'Dup', 'lname' => 'Doc', 'email' => $doctorUser->email, 'dob' => '1999-01-01', 'gender' => 'male', 'clinic_id' => 1, 'room_id' => 1, 'consultation_fee' => 10, 'specialty_ids' => [1, 3], 'appointment_duration' => 30]);
}
if ($ownerToken) {
    runTest('clinic', 'create-doctor-validation', 'POST', "$V1/doctors/register",
        authHeaders($ownerToken), []);
}

summary('clinic');
return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
