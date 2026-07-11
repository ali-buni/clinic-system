<?php

require_once __DIR__ . '/../helpers.php';

$endpoint = "$V1/patients/search/doctors";

section('Patient Doctor Search — Unauthenticated');

runTest('patient_doctor_search', 'search-unauthenticated', 'GET', $endpoint, ['Accept' => 'application/json'], null);
runTest('patient_doctor_search', 'search-invalid-token', 'GET', $endpoint, authHeaders('invalid-token'), null);

section('Patient Doctor Search — Forbidden Roles');

if ($doctorToken) {
    runTest('patient_doctor_search', 'search-forbidden-doctor', 'GET', $endpoint, authHeaders($doctorToken), null);
}

section('Patient Doctor Search — Success (No Filters)');

if ($patientToken) {
    runTest('patient_doctor_search', 'search-success', 'GET', $endpoint, authHeaders($patientToken), null);
}

section('Patient Doctor Search — Filter by Name');

if ($patientToken) {
    runTest('patient_doctor_search', 'search-by-name', 'GET', $endpoint, authHeaders($patientToken), null,
        ['name' => 'Amiraa']);
}
if ($patientToken) {
    runTest('patient_doctor_search', 'search-by-name-no-results', 'GET', $endpoint, authHeaders($patientToken), null,
        ['name' => 'ZZZZNOTEXIST']);
}

section('Patient Doctor Search — Filter by Location');

if ($patientToken) {
    runTest('patient_doctor_search', 'search-by-location', 'GET', $endpoint, authHeaders($patientToken), null,
        ['location' => 'Damascus']);
}

section('Patient Doctor Search — Filter by Specialty');

if ($patientToken) {
    runTest('patient_doctor_search', 'search-by-specialty', 'GET', $endpoint, authHeaders($patientToken), null,
        ['specialty' => 'Cardiology']);
}

section('Patient Doctor Search — Filter by Consultation Fee Range');

if ($patientToken) {
    runTest('patient_doctor_search', 'search-by-fee-range', 'GET', $endpoint, authHeaders($patientToken), null,
        ['consultation_fee_min' => 50, 'consultation_fee_max' => 200]);
}

section('Patient Doctor Search — Sorting');

if ($patientToken) {
    runTest('patient_doctor_search', 'search-sort-by-fee-asc', 'GET', $endpoint, authHeaders($patientToken), null,
        ['sort_by' => 'consultation_fee', 'sort_direction' => 'asc']);
}
if ($patientToken) {
    runTest('patient_doctor_search', 'search-sort-by-fee-desc', 'GET', $endpoint, authHeaders($patientToken), null,
        ['sort_by' => 'consultation_fee', 'sort_direction' => 'desc']);
}
if ($patientToken) {
    runTest('patient_doctor_search', 'search-sort-by-duration', 'GET', $endpoint, authHeaders($patientToken), null,
        ['sort_by' => 'appointment_duration']);
}
if ($patientToken) {
    runTest('patient_doctor_search', 'search-sort-by-name', 'GET', $endpoint, authHeaders($patientToken), null,
        ['sort_by' => 'name', 'sort_direction' => 'asc']);
}

section('Patient Doctor Search — Pagination');

if ($patientToken) {
    runTest('patient_doctor_search', 'search-pagination', 'GET', $endpoint, authHeaders($patientToken), null,
        ['per_page' => 2, 'page' => 1]);
}

section('Patient Doctor Search — Combined Filters');

if ($patientToken) {
    runTest('patient_doctor_search', 'search-combined-filters', 'GET', $endpoint, authHeaders($patientToken), null,
        ['name' => 'Amira', 'specialty' => 'Cardiology', 'sort_by' => 'consultation_fee']);
}

section('Patient Doctor Search — Validation Errors');

if ($patientToken) {
    runTest('patient_doctor_search', 'search-validation-invalid-sort', 'GET', $endpoint, authHeaders($patientToken), null,
        ['sort_by' => 'invalid_field']);
}
if ($patientToken) {
    runTest('patient_doctor_search', 'search-validation-negative-fee', 'GET', $endpoint, authHeaders($patientToken), null,
        ['consultation_fee_min' => -10]);
}

summary('patient_doctor_search');

return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
