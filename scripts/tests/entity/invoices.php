<?php
require_once __DIR__ . '/../helpers.php';

$itemId = 1;
$createdInvoiceId = null;

section('List invoices');

runTest('invoice', 'list-unauthenticated', 'GET', "$V1/invoices", ['Accept' => 'application/json'], null);
runTest('invoice', 'list-invalid-token', 'GET', "$V1/invoices", authHeaders('invalid-token'), null);
if ($patientToken) {
    runTest('invoice', 'list-unauthorized-patient', 'GET', "$V1/invoices", authHeaders($patientToken), null);
}
if ($doctorToken) {
    runTest('invoice', 'list-unauthorized-doctor', 'GET', "$V1/invoices", authHeaders($doctorToken), null);
}
if ($secretaryToken) {
    runTest('invoice', 'list-unauthorized-secretary', 'GET', "$V1/invoices", authHeaders($secretaryToken), null);
}
if ($ownerToken) {
    runTest('invoice', 'list-success', 'GET', "$V1/invoices", authHeaders($ownerToken), null);
}
if ($ownerToken) {
    runTest('invoice', 'list-filter-status', 'GET', "$V1/invoices", authHeaders($ownerToken), null, ['status' => 'draft']);
}
if ($ownerToken) {
    runTest('invoice', 'list-filter-invalid-status', 'GET', "$V1/invoices", authHeaders($ownerToken), null, ['status' => 'invalid']);
}
if ($ownerToken) {
    runTest('invoice', 'list-filter-date-range', 'GET', "$V1/invoices", authHeaders($ownerToken), null,
        ['date_from' => '2025-01-01', 'date_to' => '2025-12-31']);
}

section('Store invoice');

runTest('invoice', 'store-unauthenticated', 'POST', "$V1/invoices", ['Accept' => 'application/json'],
    ['clinic_id' => $clinicId, 'patient_id' => $patientId, 'appointment_id' => 1,
     'invoice_items' => [['item_id' => $itemId, 'quantity' => 1, 'price' => 100]]]);
runTest('invoice', 'store-invalid-token', 'POST', "$V1/invoices", authHeaders('invalid-token'),
    ['clinic_id' => $clinicId, 'patient_id' => $patientId, 'appointment_id' => 1,
     'invoice_items' => [['item_id' => $itemId, 'quantity' => 1, 'price' => 100]]]);
if ($patientToken) {
    runTest('invoice', 'store-unauthorized-patient', 'POST', "$V1/invoices", authHeaders($patientToken),
        ['clinic_id' => $clinicId, 'patient_id' => $patientId, 'appointment_id' => 1,
         'invoice_items' => [['item_id' => $itemId, 'quantity' => 1, 'price' => 100]]]);
}
if ($ownerToken) {
    runTest('invoice', 'store-unauthorized-owner', 'POST', "$V1/invoices", authHeaders($ownerToken),
        ['clinic_id' => $clinicId, 'patient_id' => $patientId, 'appointment_id' => 1,
         'invoice_items' => [['item_id' => $itemId, 'quantity' => 1, 'price' => 100]]]);
}
if ($doctorToken) {
    $r = runTest('invoice', 'store-doctor-success', 'POST', "$V1/invoices", authHeaders($doctorToken),
        ['clinic_id' => $clinicId, 'patient_id' => $patientId, 'appointment_id' => 1,
         'invoice_items' => [['item_id' => $itemId, 'quantity' => 1, 'price' => 100]]]);
    if (($r['status'] ?? 0) === 201) {
        $createdInvoiceId = $r['body']['data']['id'] ?? null;
    }
}
if ($secretaryToken) {
    runTest('invoice', 'store-secretary-success', 'POST', "$V1/invoices", authHeaders($secretaryToken),
        ['clinic_id' => $clinicId, 'patient_id' => $patientId, 'appointment_id' => 1,
         'invoice_items' => [['item_id' => $itemId, 'quantity' => 1, 'price' => 100]]]);
}
if ($doctorToken) {
    runTest('invoice', 'store-validation-empty', 'POST', "$V1/invoices", authHeaders($doctorToken), []);
}
if ($doctorToken) {
    runTest('invoice', 'store-validation-missing-items', 'POST', "$V1/invoices", authHeaders($doctorToken),
        ['clinic_id' => $clinicId, 'patient_id' => $patientId, 'appointment_id' => 1]);
}
if ($doctorToken) {
    runTest('invoice', 'store-not-found-clinic', 'POST', "$V1/invoices", authHeaders($doctorToken),
        ['clinic_id' => 99999, 'patient_id' => $patientId, 'appointment_id' => 1,
         'invoice_items' => [['item_id' => $itemId, 'quantity' => 1, 'price' => 100]]]);
}
if ($doctorToken) {
    runTest('invoice', 'store-not-found-patient', 'POST', "$V1/invoices", authHeaders($doctorToken),
        ['clinic_id' => $clinicId, 'patient_id' => 99999, 'appointment_id' => 1,
         'invoice_items' => [['item_id' => $itemId, 'quantity' => 1, 'price' => 100]]]);
}

section('Show invoice');

runTest('invoice', 'show-unauthenticated', 'GET', "$V1/invoices/999", ['Accept' => 'application/json'], null);
runTest('invoice', 'show-invalid-token', 'GET', "$V1/invoices/999", authHeaders('invalid-token'), null);
if ($ownerToken) {
    runTest('invoice', 'show-not-found', 'GET', "$V1/invoices/999", authHeaders($ownerToken), null);
}
if ($ownerToken && $createdInvoiceId) {
    runTest('invoice', 'show-owner-success', 'GET', "$V1/invoices/$createdInvoiceId", authHeaders($ownerToken), null);
}
if ($doctorToken && $createdInvoiceId) {
    runTest('invoice', 'show-doctor-success', 'GET', "$V1/invoices/$createdInvoiceId", authHeaders($doctorToken), null);
}
if ($secretaryToken && $createdInvoiceId) {
    runTest('invoice', 'show-secretary-success', 'GET', "$V1/invoices/$createdInvoiceId", authHeaders($secretaryToken), null);
}
if ($patientToken && $createdInvoiceId) {
    runTest('invoice', 'show-patient-success', 'GET', "$V1/invoices/$createdInvoiceId", authHeaders($patientToken), null);
}

section('Update invoice');

runTest('invoice', 'update-unauthenticated', 'PUT', "$V1/invoices/999", ['Accept' => 'application/json'],
    ['description' => 'Updated description']);
runTest('invoice', 'update-invalid-token', 'PUT', "$V1/invoices/999", authHeaders('invalid-token'),
    ['description' => 'Updated description']);
if ($patientToken) {
    runTest('invoice', 'update-unauthorized-patient', 'PUT', "$V1/invoices/999", authHeaders($patientToken),
        ['description' => 'Updated description']);
}
if ($ownerToken) {
    runTest('invoice', 'update-unauthorized-owner', 'PUT', "$V1/invoices/999", authHeaders($ownerToken),
        ['description' => 'Updated description']);
}
if ($ownerToken) {
    runTest('invoice', 'update-not-found', 'PUT', "$V1/invoices/999", authHeaders($ownerToken),
        ['description' => 'Updated description']);
}
if ($doctorToken && $createdInvoiceId) {
    runTest('invoice', 'update-doctor-success', 'PUT', "$V1/invoices/$createdInvoiceId", authHeaders($doctorToken),
        ['description' => 'Updated by doctor ' . uniqid()]);
}
if ($secretaryToken && $createdInvoiceId) {
    runTest('invoice', 'update-secretary-success', 'PUT', "$V1/invoices/$createdInvoiceId", authHeaders($secretaryToken),
        ['description' => 'Updated by secretary ' . uniqid()]);
}

section('Patient invoices');

runTest('invoice', 'patient-invoices-unauthenticated', 'GET', "$V1/invoices/patient/$patientId",
    ['Accept' => 'application/json'], null);
runTest('invoice', 'patient-invoices-invalid-token', 'GET', "$V1/invoices/patient/$patientId",
    authHeaders('invalid-token'), null);
if ($ownerToken) {
    runTest('invoice', 'patient-invoices-not-found', 'GET', "$V1/invoices/patient/999",
        authHeaders($ownerToken), null);
}
if ($ownerToken) {
    runTest('invoice', 'patient-invoices-owner-success', 'GET', "$V1/invoices/patient/$patientId",
        authHeaders($ownerToken), null);
}
if ($doctorToken) {
    runTest('invoice', 'patient-invoices-doctor-success', 'GET', "$V1/invoices/patient/$patientId",
        authHeaders($doctorToken), null);
}
if ($secretaryToken) {
    runTest('invoice', 'patient-invoices-secretary-success', 'GET', "$V1/invoices/patient/$patientId",
        authHeaders($secretaryToken), null);
}
if ($patientToken) {
    runTest('invoice', 'patient-invoices-self-success', 'GET', "$V1/invoices/patient/$patientId",
        authHeaders($patientToken), null);
}

section('Doctor invoices');

runTest('invoice', 'doctor-invoices-unauthenticated', 'GET', "$V1/invoices/doctor/$doctorId",
    ['Accept' => 'application/json'], null);
runTest('invoice', 'doctor-invoices-invalid-token', 'GET', "$V1/invoices/doctor/$doctorId",
    authHeaders('invalid-token'), null);
if ($patientToken) {
    runTest('invoice', 'doctor-invoices-unauthorized-patient', 'GET', "$V1/invoices/doctor/$doctorId",
        authHeaders($patientToken), null);
}
if ($ownerToken) {
    runTest('invoice', 'doctor-invoices-not-found', 'GET', "$V1/invoices/doctor/999",
        authHeaders($ownerToken), null);
}
if ($ownerToken) {
    runTest('invoice', 'doctor-invoices-owner-success', 'GET', "$V1/invoices/doctor/$doctorId",
        authHeaders($ownerToken), null);
}
if ($doctorToken) {
    runTest('invoice', 'doctor-invoices-doctor-success', 'GET', "$V1/invoices/doctor/$doctorId",
        authHeaders($doctorToken), null);
}
if ($secretaryToken) {
    runTest('invoice', 'doctor-invoices-secretary-success', 'GET', "$V1/invoices/doctor/$doctorId",
        authHeaders($secretaryToken), null);
}

section('Delete invoice');

if ($patientToken && $createdInvoiceId) {
    runTest('invoice', 'delete-unauthorized-patient', 'DELETE', "$V1/invoices/$createdInvoiceId", authHeaders($patientToken), null);
}
if ($doctorToken && $createdInvoiceId) {
    runTest('invoice', 'delete-unauthorized-doctor', 'DELETE', "$V1/invoices/$createdInvoiceId", authHeaders($doctorToken), null);
}
if ($secretaryToken && $createdInvoiceId) {
    runTest('invoice', 'delete-unauthorized-secretary', 'DELETE', "$V1/invoices/$createdInvoiceId", authHeaders($secretaryToken), null);
}
runTest('invoice', 'delete-unauthenticated', 'DELETE', "$V1/invoices/999", ['Accept' => 'application/json'], null);
runTest('invoice', 'delete-invalid-token', 'DELETE', "$V1/invoices/999", authHeaders('invalid-token'), null);
if ($ownerToken) {
    runTest('invoice', 'delete-not-found', 'DELETE', "$V1/invoices/999", authHeaders($ownerToken), null);
}
if ($ownerToken && $createdInvoiceId) {
    runTest('invoice', 'delete-success', 'DELETE', "$V1/invoices/$createdInvoiceId", authHeaders($ownerToken), null);
}

summary('invoice');
return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
