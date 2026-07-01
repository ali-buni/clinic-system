<?php
require_once __DIR__ . '/../helpers.php';

runTest('room', 'user-rooms-unauthenticated', 'GET', "$V1/rooms/user", ['Accept' => 'application/json'], null);
runTest('room', 'user-rooms-invalid-token', 'GET', "$V1/rooms/user", authHeaders('invalid-token'), null);

if ($doctorToken) {
    runTest('room', 'user-rooms-success', 'GET', "$V1/rooms/user", authHeaders($doctorToken), null);
}
if ($secretaryToken) {
    runTest('room', 'user-rooms-success', 'GET', "$V1/rooms/user", authHeaders($secretaryToken), null);
}

runTest('room', 'list-unauthenticated', 'GET', "$V1/rooms/$clinicId", ['Accept' => 'application/json'], null);
if ($doctorToken) {
    runTest('room', 'list-unauthorized-doctor', 'GET', "$V1/rooms/$clinicId", authHeaders($doctorToken), null);
}
if ($ownerToken) {
    runTest('room', 'list-success', 'GET', "$V1/rooms/$clinicId", authHeaders($ownerToken), null);
}

runTest('room', 'list-with-info-unauthenticated', 'GET', "$V1/rooms/$clinicId/info", ['Accept' => 'application/json'], null);
if ($ownerToken) {
    runTest('room', 'list-with-info-success', 'GET', "$V1/rooms/$clinicId/info", authHeaders($ownerToken), null);
}

runTest('room', 'details-unauthenticated', 'GET', "$V1/rooms/$roomId/details", ['Accept' => 'application/json'], null);
if ($doctorToken) {
    runTest('room', 'details-success', 'GET', "$V1/rooms/$roomId/details", authHeaders($doctorToken), null);
}
if ($doctorToken) {
    runTest('room', 'details-not-found', 'GET', "$V1/rooms/999/details", authHeaders($doctorToken), null);
}

runTest('room', 'create-unauthenticated', 'POST', "$V1/rooms", ['Accept' => 'application/json'],
    ['name' => 'New Room', 'clinic_id' => $clinicId]);
if ($ownerToken) {
    runTest('room', 'create-validation', 'POST', "$V1/rooms", authHeaders($ownerToken), []);
}
if ($ownerToken) {
    runTest('room', 'create-success', 'POST', "$V1/rooms", authHeaders($ownerToken),
        ['name' => 'New Room ' . uniqid(), 'clinic_id' => $clinicId]);
}

runTest('room', 'update-unauthenticated', 'PATCH', "$V1/rooms/$roomId", ['Accept' => 'application/json'],
    ['name' => 'Updated Room']);
if ($ownerToken) {
    runTest('room', 'update-success', 'PATCH', "$V1/rooms/$roomId", authHeaders($ownerToken),
        ['name' => 'Updated Room ' . uniqid()]);
}

runTest('room', 'delete-unauthenticated', 'DELETE', "$V1/rooms/$roomId", ['Accept' => 'application/json'], null);
if ($ownerToken) {
    runTest('room', 'delete-success', 'DELETE', "$V1/rooms/$roomId", authHeaders($ownerToken), null);
}

runTest('room', 'add-doctor-unauthenticated', 'POST', "$V1/rooms/add/doctors", ['Accept' => 'application/json'],
    ['doctor_id' => $doctorId]);
if ($ownerToken) {
    runTest('room', 'add-doctor-success', 'POST', "$V1/rooms/add/doctors", authHeaders($ownerToken),
        ['doctor_id' => $doctorId, 'room_id' => 2]);
}

runTest('room', 'add-secretary-unauthenticated', 'POST', "$V1/rooms/add/secretaries", ['Accept' => 'application/json'],
    ['secretary_id' => $secretaryId]);
if ($ownerToken) {
    runTest('room', 'add-secretary-success', 'POST', "$V1/rooms/add/secretaries", authHeaders($ownerToken),
        ['secretary_id' => $secretaryId, 'room_ids' => [1, 2]]);
}
runTest('room', 'remove-doctor-unauthenticated', 'DELETE', "$V1/rooms/remove/doctors",
    ['Accept' => 'application/json'], null);
if ($ownerToken) {
    runTest('room', 'remove-doctor-success', 'DELETE', "$V1/rooms/remove/doctors",
        authHeaders($ownerToken),
        ['doctor_id' => $doctorId, 'room_id' => 2]);
}

runTest('room', 'remove-secretary-unauthenticated', 'DELETE', "$V1/rooms/remove/secretaries",
    ['Accept' => 'application/json'], null);
if ($ownerToken) {
    runTest('room', 'remove-secretary-success', 'DELETE', "$V1/rooms/remove/secretaries",
        authHeaders($ownerToken), ['secretary_id' => $secretaryId, 'room_ids' => [1]]);
}

summary('room');
return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
