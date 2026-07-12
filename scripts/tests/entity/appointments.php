<?php

require_once __DIR__.'/../helpers.php';

$apptId = 1;

section('Book / Show');

runTest('appointment', 'book-unauthenticated', 'POST', "$V1/appointments/book", ['Accept' => 'application/json'],
    ['doctor_id' => $doctorId, 'patient_id' => $patientId, 'start_time' => '2099-01-01 10:00:00', 'appointment_type_id' => $apptTypeId]);
runTest('appointment', 'book-invalid-token', 'POST', "$V1/appointments/book", authHeaders('invalid-token'),
    ['doctor_id' => $doctorId, 'patient_id' => $patientId, 'start_time' => '2099-01-01 10:00:00', 'appointment_type_id' => $apptTypeId]);
if ($patientToken) {
    runTest('appointment', 'book-validation', 'POST', "$V1/appointments/book", authHeaders($patientToken), []);
}
if ($patientToken) {
    runTest('appointment', 'book-not-found-doctor', 'POST', "$V1/appointments/book", authHeaders($patientToken),
        ['doctor_id' => 99999, 'patient_id' => $patientId, 'start_time' => '2099-01-01 10:00:00', 'appointment_type_id' => $apptTypeId]);
}
if ($patientToken) {
    runTest('appointment', 'book-patient-success', 'POST', "$V1/appointments/book", authHeaders($patientToken),
        ['doctor_id' => $doctorId, 'patient_id' => $patientId, 'clinic_id' => $clinicId, 'appointment_type_id' => $apptTypeId, 'date' => '2099-06-01', 'start_time' => '10:00']);
}

if ($patientToken) {
    runTest('appointment', 'book-today-validation', 'POST', "$V1/appointments/book", authHeaders($patientToken),
        ['doctor_id' => $doctorId, 'patient_id' => $patientId, 'clinic_id' => $clinicId, 'appointment_type_id' => $apptTypeId, 'date' => date('Y-m-d'), 'start_time' => '10:00']);
}

section('Invoice auto-creation on booking');

if ($patientToken) {
    $r = runTest('appointment', 'book-creates-invoice', 'POST', "$V1/appointments/book", authHeaders($patientToken),
        ['doctor_id' => $doctorId, 'patient_id' => $patientId, 'clinic_id' => $clinicId, 'appointment_type_id' => $apptTypeId, 'date' => '2099-06-01', 'start_time' => '10:00']);
    if (($r['status'] ?? 0) === 201) {
        $invoices = $r['body']['data']['invoices'] ?? [];
        if (count($invoices) > 0) {
            $inv = $invoices[0];
            echo "  OK invoice_id:{$inv['id']} status:{$inv['status']}\n";
        } else {
            echo "  FAIL invoices array empty in response\n";
            $failedTests++;
        }
    }
}

if ($patientToken) {
    $r = runTest('appointment', 'reschedule-includes-invoices', 'POST', "$V1/appointments/book", authHeaders($patientToken),
        ['doctor_id' => $doctorId, 'patient_id' => $patientId, 'clinic_id' => $clinicId, 'appointment_type_id' => $apptTypeId, 'date' => '2099-06-01', 'start_time' => '17:00']);
    if (($r['status'] ?? 0) === 201) {
        $id = $r['body']['data']['id'] ?? null;
        if ($id) {
            $r2 = runTest('appointment', 'reschedule-success-with-invoices', 'POST', "$V1/appointments/$id/reschedule",
                authHeaders($patientToken), ['start_time' => '18:00', 'date' => '2099-06-01']);
            if (($r2['status'] ?? 0) === 200) {
                $invoices = $r2['body']['data']['invoices'] ?? [];
                if (count($invoices) > 0) {
                    echo "  OK reschedule invoice_id:{$invoices[0]['id']}\n";
                } else {
                    echo "  FAIL invoices not in reschedule response\n";
                    $failedTests++;
                }
            }
        }
    }
}

if ($ownerToken) {
    runTest('appointment', 'show-owner-success', 'GET', "$V1/appointments/$apptId", authHeaders($ownerToken), null);
}
if ($patientToken) {
    runTest('appointment', 'show-patient-success', 'GET', "$V1/appointments/$apptId", authHeaders($patientToken), null);
}

runTest('appointment', 'show-unauthenticated', 'GET', "$V1/appointments/$apptId", ['Accept' => 'application/json'], null);
if ($ownerToken) {
    runTest('appointment', 'show-not-found', 'GET', "$V1/appointments/999", authHeaders($ownerToken), null);
}

section('State transitions');

runTest('appointment', 'reschedule-unauthenticated', 'POST', "$V1/appointments/$apptId/reschedule",
    ['Accept' => 'application/json'], ['start_time' => '2099-01-02 11:00:00']);
if ($patientToken) {
    runTest('appointment', 'reschedule-not-found', 'POST', "$V1/appointments/999/reschedule",
        authHeaders($patientToken), ['start_time' => '11:00', 'date' => '2099-01-05']);
}
runTest('appointment', 'cancel-unauthenticated', 'POST', "$V1/appointments/$apptId/cancel",
    ['Accept' => 'application/json'], []);
if ($patientToken) {
    runTest('appointment', 'cancel-not-found', 'POST', "$V1/appointments/999/cancel",
        authHeaders($patientToken), []);
}
runTest('appointment', 'complete-unauthenticated', 'POST', "$V1/appointments/$apptId/complete",
    ['Accept' => 'application/json'], []);
runTest('appointment', 'confirmed-unauthenticated', 'POST', "$V1/appointments/$apptId/confirmed",
    ['Accept' => 'application/json'], []);

// Cancel flow
if ($patientToken) {
    $r = runTest('appointment', 'cancel-book-success', 'POST', "$V1/appointments/book", authHeaders($patientToken),
        ['doctor_id' => $doctorId, 'patient_id' => $patientId, 'clinic_id' => $clinicId, 'appointment_type_id' => $apptTypeId, 'date' => '2099-06-01', 'start_time' => '11:00']);
    if (($r['status'] ?? 0) === 201) {
        $id = $r['body']['data']['id'] ?? null;
        if ($id && $patientToken) {
            runTest('appointment', 'cancel-success', 'POST', "$V1/appointments/$id/cancel",
                authHeaders($patientToken), ['cancel_reason' => 'Patient changed mind']);
        }
    }
}

// Reschedule flow
if ($patientToken) {
    $r = runTest('appointment', 'reschedule-book-success', 'POST', "$V1/appointments/book", authHeaders($patientToken),
        ['doctor_id' => $doctorId, 'patient_id' => $patientId, 'clinic_id' => $clinicId, 'appointment_type_id' => $apptTypeId, 'date' => '2099-06-01', 'start_time' => '12:00']);
    if (($r['status'] ?? 0) === 201) {
        $id = $r['body']['data']['id'] ?? null;
        if ($id && $patientToken) {
            runTest('appointment', 'reschedule-success', 'POST', "$V1/appointments/$id/reschedule",
                authHeaders($patientToken), ['start_time' => '14:00', 'date' => '2099-06-01']);
        }
    }
}

if ($patientToken) {
    runTest('appointment', 'reschedule-today-validation', 'POST', "$V1/appointments/$apptId/reschedule",
        authHeaders($patientToken), ['start_time' => '10:00', 'date' => date('Y-m-d')]);
}

// Confirm + Complete flow
if ($patientToken) {
    $r = runTest('appointment', 'confirm-book-success', 'POST', "$V1/appointments/book", authHeaders($patientToken),
        ['doctor_id' => $doctorId, 'patient_id' => $patientId, 'clinic_id' => $clinicId, 'appointment_type_id' => $apptTypeId, 'date' => '2099-06-01', 'start_time' => '13:00']);
    if (($r['status'] ?? 0) === 201) {
        $id = $r['body']['data']['id'] ?? null;
        if ($id && $doctorToken) {
            runTest('appointment', 'confirm-success', 'POST', "$V1/appointments/$id/confirmed",
                authHeaders($doctorToken), null);
        }
        if ($id && $doctorToken) {
            runTest('appointment', 'complete-success', 'POST', "$V1/appointments/$id/complete",
                authHeaders($doctorToken), null);
        }
    }
}

section('List endpoints');

runTest('appointment', 'patient-appointments-unauthenticated', 'GET', "$V1/appointments/patient/$patientId",
    ['Accept' => 'application/json'], null);
if ($patientToken) {
    runTest('appointment', 'patient-appointments-self-success', 'GET', "$V1/appointments/patient/$patientId",
        authHeaders($patientToken), null);
}

runTest('appointment', 'doctor-appointments-unauthenticated', 'GET', "$V1/appointments/doctor/$doctorId",
    ['Accept' => 'application/json'], null);
if ($doctorToken) {
    runTest('appointment', 'doctor-appointments-self-success', 'GET', "$V1/appointments/doctor/$doctorId",
        authHeaders($doctorToken), null);
}

runTest('appointment', 'clinic-appointments-unauthenticated', 'GET', "$V1/appointments/clinic/$clinicId",
    ['Accept' => 'application/json'], null);
if ($ownerToken) {
    runTest('appointment', 'clinic-appointments-owner-success', 'GET', "$V1/appointments/clinic/$clinicId",
        authHeaders($ownerToken), null);
}

runTest('appointment', 'room-appointments-unauthenticated', 'GET', "$V1/appointments/room",
    ['Accept' => 'application/json'], null);

if ($secretaryToken) {
    runTest('appointment', 'room-appointments-secretary-success', 'GET', "$V1/appointments/room",
        authHeaders($secretaryToken), null, ['roomIds[]' => $roomId]);
}
runTest('appointment', 'doctor-schedule-unauthenticated', 'GET', "$V1/appointments/doctor/$doctorId/schedule",
    ['Accept' => 'application/json'], null);
if ($doctorToken) {
    runTest('appointment', 'doctor-schedule-self-success', 'GET', "$V1/appointments/doctor/$doctorId/schedule",
        authHeaders($doctorToken), null, ['date' => '2099-06-01']);
}

runTest('appointment', 'clinic-schedule-unauthenticated', 'GET', "$V1/appointments/clinic/$clinicId/schedule",
    ['Accept' => 'application/json'], null);
if ($ownerToken) {
    runTest('appointment', 'clinic-schedule-owner-success', 'GET', "$V1/appointments/clinic/$clinicId/schedule",
        authHeaders($ownerToken), null, ['date' => '2099-06-01']);
}

runTest('appointment', 'available-slots-unauthenticated', 'GET', "$V1/appointments/available-slots",
    ['Accept' => 'application/json'], null);
if ($patientToken) {
    runTest('appointment', 'available-slots-success', 'GET', "$V1/appointments/available-slots",
        authHeaders($patientToken), null,
        ['doctor_id' => (string) $doctorId, 'date' => date('Y-m-d', strtotime('+1 day'))]);
}

summary('appointment');

return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
