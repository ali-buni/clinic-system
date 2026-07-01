<?php
require_once __DIR__ . '/../helpers.php';

$dr   = ['from' => '2020-01-01', 'to' => date('Y-m-d')];

$periods = [[], ['period' => 'year'], ['period' => 'month'], ['period' => 'day'], ['period' => 'total']];
$roles = [
    [$ownerToken, 'owner'],
    [$doctorToken, 'doctor'],
    [$secretaryToken, 'secretary'],
    [$patientToken, 'patient'],
];

section('Unauthenticated / Invalid Token');

foreach (['operational', 'financial', 'patients', 'predictive', 'health-score', 'dashboard'] as $ep) {
    runTest('analytics', "$ep-unauthenticated", 'POST', "$V1/analytics/$ep", ['Accept' => 'application/json'], []);
    runTest('analytics', "$ep-invalid-token", 'POST', "$V1/analytics/$ep", authHeaders('invalid-token'), []);
}
runTest('analytics', 'medical-unauthenticated', 'GET', "$V1/analytics/medical", ['Accept' => 'application/json'], null);
runTest('analytics', 'medical-invalid-token', 'GET', "$V1/analytics/medical", authHeaders('invalid-token'), null);
runTest(
    'analytics',
    'nla-unauthenticated',
    'POST',
    "$V1/analytics/nla",
    ['Accept' => 'application/json'],
    ['question' => 'How many patients?']
);
runTest(
    'analytics',
    'nla-invalid-token',
    'POST',
    "$V1/analytics/nla",
    authHeaders('invalid-token'),
    ['question' => 'How many patients?']
);

section('Validation — empty / missing fields');

foreach (['operational', 'financial', 'predictive', 'health-score', 'dashboard'] as $ep) {
    if ($ownerToken) {
        runTest('analytics', "$ep-empty-body", 'POST', "$V1/analytics/$ep", authHeaders($ownerToken), []);
        runTest(
            'analytics',
            "$ep-bad-period",
            'POST',
            "$V1/analytics/$ep",
            authHeaders($ownerToken),
            ['period' => 'decade', 'from' => 'bad-date', 'to' => 'bad-date']
        );
    }
}
if ($ownerToken) {
    runTest('analytics', 'nla-empty-body', 'POST', "$V1/analytics/nla", authHeaders($ownerToken), []);
    runTest(
        'analytics',
        'health-score-bad-patient',
        'POST',
        "$V1/analytics/health-score",
        authHeaders($ownerToken),
        ['patient_id' => 99999]
    );
}

section('Operational — all roles × period variants');

[$tok, $role] = $roles[0];
foreach ($periods as $p) {
    $label = $p['period'] ?? 'total';
    runTest(
        'analytics',
        "operational-{$role}-period-{$label}",
        'POST',
        "$V1/analytics/operational",
        authHeaders($tok),
        array_merge($p, $dr)
    );
}


section('Financial — all roles × period variants');

[$tok, $role] = $roles[0];
foreach ($periods as $p) {
    $label = $p['period'] ?? 'total';
    runTest(
        'analytics',
        "financial-{$role}-period-{$label}",
        'POST',
        "$V1/analytics/financial",
        authHeaders($tok),
        array_merge($p, $dr)
    );
}

section('Patients — all roles × period variants');

[$tok, $role] = $roles[0];

foreach ($periods as $p) {
    $label = $p['period'] ?? 'total';
    runTest(
        'analytics',
        "patients-{$role}-period-{$label}",
        'POST',
        "$V1/analytics/patients",
        authHeaders($tok),
        $p
    );
}


section('Medical — all roles');

[$tok, $role] = $roles[0];
runTest('analytics', "medical-{$role}-success", 'GET', "$V1/analytics/medical", authHeaders($tok), null);

section('Predictive — all roles × period variants');

[$tok, $role] = $roles[0];
foreach ($periods as $p) {
    $label = $p['period'] ?? 'total';
    runTest(
        'analytics',
        "predictive-{$role}-period-{$label}",
        'POST',
        "$V1/analytics/predictive",
        authHeaders($tok),
        array_merge($p, $dr)
    );
}

section('NLA / Ask Analytics — all roles');

$questions = [
    'owner'     => ['How many patients?', 'What is the revenue trend?'],
    // 'doctor'    => ['Show my appointment stats'],
    // 'secretary' => ['How many appointments today?'],
    // 'patient'   => ['My health summary'],
];
[$tok, $role] = $roles[0];
foreach ($questions[$role] ?? ['Default question'] as $q) {
    $qLabel = substr(preg_replace('/[^a-z]/', '', strtolower($q)), 0, 20);
    runTest(
        'analytics',
        "nla-{$role}-{$qLabel}",
        'POST',
        "$V1/analytics/nla",
        authHeaders($tok),
        ['question' => $q]
    );
}

section('Health Score — all roles × period variants');

[$tok, $role] = $roles[0];
foreach ($periods as $p) {
    $label = $p['period'] ?? 'total';
    $body = array_merge($p, $dr);
    if ($role === 'patient') {
        $body = $p;
    } elseif ($patientId) {
        $body['patient_id'] = $patientId;
    }
    runTest(
        'analytics',
        "health-score-{$role}-period-{$label}",
        'POST',
        "$V1/analytics/health-score",
        authHeaders($tok),
        $body
    );
}

section('Dashboard — all roles × period variants');

[$tok, $role] = $roles[0];
foreach ($periods as $p) {
    $label = $p['period'] ?? 'total';
    runTest(
        'analytics',
        "dashboard-{$role}-period-{$label}",
        'POST',
        "$V1/analytics/dashboard",
        authHeaders($tok),
        array_merge($p, $dr)
    );
}

summary('analytics');
return ['total' => $totalTests, 'passed' => $passedTests, 'failed' => $failedTests];
