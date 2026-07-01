<?php
require_once __DIR__ . '/../helpers.php';

// runTest('schedule-override', 'store-unauthenticated', 'POST', "$V1/schedule-overrides", ['Accept' => 'application/json'],
//     ['doctor_id' => $doctorId, 'override_date' => '2099-06-15', 'override_type' => 'closed', 'is_closed' => true]);
// if ($ownerToken) {
//     runTest('schedule-override', 'store-success', 'POST', "$V1/schedule-overrides", authHeaders($ownerToken),
//         ['doctor_id' => $doctorId, 'override_date' => '2099-06-15', 'override_type' => 'closed', 'is_closed' => true,]);
// }
// if ($doctorToken) {
//     runTest('schedule-override', 'store-validation', 'POST', "$V1/schedule-overrides", authHeaders($doctorToken), []);
// }
// runTest('schedule-override', 'update-unauthenticated', 'PUT', "$V1/schedule-overrides/", ['Accept' => 'application/json'],
//     ['reason' => 'Updated']);
// if ($ownerToken) {
//     runTest('schedule-override', 'update-success', 'PUT', "$V1/schedule-overrides/", authHeaders($ownerToken),
//         ['reason' => 'Updated reason',
//          'override_date' => '2099-07-01', 'override_type' => 'time_change',
//          'start_time' => '14:00', 'end_time' => '18:00', 'doctor_id' => $doctorId]);
// }
// runTest('schedule-override', 'delete-unauthenticated', 'DELETE', "$V1/schedule-overrides/1", ['Accept' => 'application/json'], null);
// if ($ownerToken) {
//     runTest('schedule-override', 'delete-success', 'DELETE', "$V1/schedule-overrides/1", authHeaders($ownerToken), null);
// }
// runTest('schedule-override', 'show-unauthenticated', 'GET', "$V1/schedule-overrides/1", ['Accept' => 'application/json'], null);
if ($ownerToken) {
    runTest('schedule-override', 'show-success', 'GET', "$V1/schedule-overrides/2", authHeaders($ownerToken), ['doctor_id' => $doctorId]);
}
// if ($doctorToken) {
//     runTest('schedule-override', 'show-not-found', 'GET', "$V1/schedule-overrides/999", authHeaders($doctorToken), null);
// }
// runTest('schedule-override', 'list-unauthenticated', 'GET', "$V1/schedule-overrides", ['Accept' => 'application/json'], null);
// if ($ownerToken) {
//     runTest('schedule-override', 'list-success', 'GET', "$V1/schedule-overrides", authHeaders($ownerToken), ['doctor_id' => $doctorId]);
// }
// runTest('schedule-override', 'by-date-unauthenticated', 'GET', "$V1/schedule-overrides/date/single",
//     ['Accept' => 'application/json'], null,
//     ['date' => date('Y-m-d', strtotime('+10 days'))]);
// if ($ownerToken) {
//     runTest('schedule-override', 'by-date-success', 'GET', "$V1/schedule-overrides/date/single",
//         authHeaders($ownerToken), null,
//         ['date' => '2026-07-27', 'doctor_id' => $doctorId]);
// }
// runTest('schedule-override', 'by-date-range-unauthenticated', 'GET', "$V1/schedule-overrides/date/range",
//     ['Accept' => 'application/json'], null,
//     ['from' => date('Y-m-d'), 'to' => date('Y-m-d', strtotime('+30 days')), 'doctor_id' => $doctorId]);
// if ($ownerToken) {
//     runTest('schedule-override', 'by-date-range-success', 'GET', "$V1/schedule-overrides/date/range",
//         authHeaders($ownerToken), null,
//         ['from' => date('Y-m-d'), 'to' => date('Y-m-d', strtotime('+30 days')), 'doctor_id' => $doctorId]);
// }

summary('schedule-override');
return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
