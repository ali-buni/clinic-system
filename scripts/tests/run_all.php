<?php
/**
 * Global API Test Runner
 *
 * Orchestrates all individual entity test scripts under scripts/tests/.
 * Each entity script is include()-ed and should return a summary array.
 *
 * Usage:
 *   php scripts/tests/run_all.php [base_url]
 *
 *   base_url defaults to http://localhost:8000
 */

// Bootstrap helpers once (shared across all entities)
require_once __DIR__ . '/helpers.php';

// Only these fields from helpers are shared across entities:
// $ownerToken, $doctorToken, $secretaryToken, $patientToken, $doctorId, etc.

$BASE_URL  = $GLOBALS['BASE_URL']  ?? 'http://localhost:8000';
$SCRIPTDIR = __DIR__ . '/entity';
$OUTPUT    = $GLOBALS['OUTPUT'];

echo "============================================================\n";
echo "  Clinic System — Global API Test Runner\n";
echo "  Base URL: $BASE_URL\n";
echo "  Output:   $OUTPUT\n";
echo "============================================================\n\n";

// ─── Verify server ───────────────────────────────────────────────────────────
echo "[*] Verifying server connection...\n";
$health = request('GET', $BASE_URL, ['Accept' => 'application/json']);
if ($health['error']) {
    echo "  ERROR: Cannot reach $BASE_URL — {$health['error']}\n";
    echo "  Start server: php artisan serve\n";
    exit(1);
}
echo "  Server reachable (HTTP {$health['status']})\n\n";

echo "[*] Authenticated users:\n";
printf("  Owner:     %s (ID:%d)\n", $ownerUser?->email ?? 'N/A', $ownerUser?->id ?? 0);
printf("  Doctor:    %s (ID:%d)\n", $doctorUser?->email ?? 'N/A', $doctorUser?->id ?? 0);
printf("  Secretary: %s (ID:%d)\n", $secretaryUser?->email ?? 'N/A', $secretaryUser?->id ?? 0);
printf("  Patient:   %s (ID:%d)\n", $patientUser?->email ?? 'N/A', $patientUser?->id ?? 0);
echo "\n";

// ─── Entity test manifest (in dependency order) ──────────────────────────────
$entities = [
    'auth'               => 'Authentication endpoints',
    'verification'       => 'Email verification endpoints',
    'phone'              => 'Phone management endpoints',
    'devices'            => 'FCM device token registration',
    'appointment_types'  => 'Appointment type CRUD',
    'users'              => 'User info & profile endpoints',
    'doctors'            => 'Doctor management endpoints',
    'patients'           => 'Patient management endpoints',
    'appointments'       => 'Appointment booking & management',
    'clinic'             => 'Clinic info & staff creation',
    'specialties'        => 'Doctor specialty management',
    'schedules'          => 'Doctor work schedules',
    'schedule_overrides' => 'Schedule override management',
    'medicines'          => 'Medicine search & creation',
    'diseases'           => 'Disease search & creation',
    'rooms'              => 'Room management endpoints',
    'secretaries'        => 'Secretary management endpoints',
    'patient_records'    => 'Patient medical records',
    'analytics'          => 'Analytics & reporting endpoints',
    'ai'                 => 'AI assistant endpoints',
    'other'              => 'Miscellaneous endpoints',
];

$grandTotal  = 0;
$grandPassed = 0;
$grandFailed = 0;
$skipped     = [];

foreach ($entities as $name => $description) {
    $file = "$SCRIPTDIR/$name.php";
    $notRunFile = "$file.not-run";

    if (file_exists($notRunFile)) {
        $reason = trim(file_get_contents($notRunFile));
        echo "  ⏭  $name — SKIPPED ($reason)\n";
        $skipped[] = $name;
        continue;
    }

    if (!file_exists($file)) {
        echo "  ⏭  $name — no test file found\n";
        $skipped[] = $name;
        continue;
    }

    // Reset per-entity counters before including
    $GLOBALS['totalTests']  = 0;
    $GLOBALS['passedTests'] = 0;
    $GLOBALS['failedTests'] = 0;

    echo "─── [$name] $description ───\n";
    try {
        $result = include $file;
    } catch (\Throwable $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
        $result = ['total' => 0, 'passed' => 0, 'failed' => 0];
    }

    $t = $result['total']  ?? $GLOBALS['totalTests'];
    $p = $result['passed'] ?? $GLOBALS['passedTests'];
    $f = $result['failed'] ?? $GLOBALS['failedTests'];
    printf("  → %s: %d tests (%d passed, %d failed)\n\n", $name, $t, $p, $f);
    $grandTotal  += $t;
    $grandPassed += $p;
    $grandFailed += $f;
}

// ─── Grand summary ───────────────────────────────────────────────────────────
echo "============================================================\n";
echo "  GLOBAL TEST SUMMARY\n";
echo "  Entities run: " . (count($entities) - count($skipped)) . "/" . count($entities) . "\n";
if ($skipped) {
    echo "  Skipped:     " . implode(', ', $skipped) . "\n";
}
echo "  Total tests:  $grandTotal\n";
echo "  Passed (2xx): $grandPassed\n";
echo "  Failed:       $grandFailed\n";
echo "  Output:       $OUTPUT\n";
echo "============================================================\n";
