<?php
require_once __DIR__ . '/../helpers.php';

section('Summarize');

runTest('ai', 'summarize-unauthenticated', 'POST', "$V1/ai/report/summarize", ['Accept' => 'application/json'],
    ['report_id' => 1]);
if ($doctorToken) {
    runTest('ai', 'summarize-validation', 'POST', "$V1/ai/report/summarize", authHeaders($doctorToken), []);
}
if ($doctorToken) {
    runTest('ai', 'summarize-doctor', 'POST', "$V1/ai/report/summarize",
        authHeaders($doctorToken), ['report_id' => 1]);
}
if ($patientToken) {
    runTest('ai', 'summarize-patient', 'POST', "$V1/ai/report/summarize",
        authHeaders($patientToken), ['report_id' => 1]);
}
if ($doctorToken) {
    runTest('ai', 'summarize-doctor', 'POST', "$V1/ai/report/summarize",
        authHeaders($doctorToken), ['report_id' => 4]);
}
if ($patientToken) {
    runTest('ai', 'summarize-patient', 'POST', "$V1/ai/report/summarize",
        authHeaders($patientToken), ['report_id' => 4]);
}

section('Assistant');

runTest('ai', 'assist-unauthenticated', 'POST', "$V1/ai/appointment/assist", ['Accept' => 'application/json'],
    ['query' => 'I need help booking an appointment']);
if ($patientToken) {
    runTest('ai', 'assist-validation', 'POST', "$V1/ai/appointment/assist", authHeaders($patientToken), []);
}
if ($patientToken) {
    runTest('ai', 'assist-patient-symptoms', 'POST', "$V1/ai/appointment/assist",
        authHeaders($patientToken), ['query' => 'I have chest pain and shortness of breath']);
}
if ($patientToken) {
    runTest('ai', 'assist-patient-specialty', 'POST', "$V1/ai/appointment/assist",
        authHeaders($patientToken), ['query' => 'I want to see a cardiologist']);
}
if ($patientToken) {
    runTest('ai', 'assist-patient-location', 'POST', "$V1/ai/appointment/assist",
        authHeaders($patientToken), ['query' => 'I need a dermatologist in Riyadh']);
}
if ($patientToken) {
    runTest('ai', 'assist-patient-arabic', 'POST', "$V1/ai/appointment/assist",
        authHeaders($patientToken), ['query' => 'أعاني من صداع شديد']);
}
if ($doctorToken) {
    runTest('ai', 'assist-doctor', 'POST', "$V1/ai/appointment/assist",
        authHeaders($doctorToken), ['query' => 'Show available slots for Dr. Ahmed']);
}
if ($secretaryToken) {
    runTest('ai', 'assist-secretary', 'POST', "$V1/ai/appointment/assist",
        authHeaders($secretaryToken), ['query' => 'Find available appointments for cardiology']);
}

section('Chat');

runTest('ai', 'chat-unauthenticated', 'POST', "$V1/ai/chat/patient", ['Accept' => 'application/json'],
    ['message' => 'Hello']);
runTest('ai', 'chat-history-unauthenticated', 'GET', "$V1/ai/chat/patient/history", ['Accept' => 'application/json'], null);
if ($patientToken) {
    runTest('ai', 'chat-patient', 'POST', "$V1/ai/chat/patient",
        authHeaders($patientToken), ['message' => 'Hello, I have a question']);
}
if ($patientToken) {
    runTest('ai', 'chat-history-patient', 'GET', "$V1/ai/chat/patient/history",
        authHeaders($patientToken), null);
}
if ($doctorToken) {
    runTest('ai', 'chat-unauthorized-doctor', 'POST', "$V1/ai/chat/patient",
        authHeaders($doctorToken), ['message' => 'Hello']);
}

summary('ai');
return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
