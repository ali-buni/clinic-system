<?php

/**
 * Shared helpers for API entity test scripts.
 * Include this file at the top of each entity test.
 *
 * Provides:
 *   - Laravel bootstrapping & auth token generation
 *   - HTTP request helper (cURL)
 *   - Structured logging to docs/api/{entity}/
 *   - Standardized runTest() function
 */

// ─── Config ──────────────────────────────────────────────────────────────────

$BASE_URL = $argv[1] ?? 'http://localhost:8000';
$API      = $BASE_URL . '/api';
$V1       = $API . '/v1/clinic-system';
$OUTPUT   = __DIR__ . '/endpoint';

// ─── Laravel Bootstrap ───────────────────────────────────────────────────────

require_once __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Invoice;
use App\Models\User;

/** @var User|null $ownerUser */
$ownerUser     = User::role('owner')->first();
/** @var User|null $doctorUser */
$doctorUser    = User::role('doctor')->first();
/** @var User|null $secretaryUser */
$secretaryUser = User::role('secretary')->first();
/** @var User|null $patientUser */
$patientUser   = User::role('patient')->first();
/** @var User|null $patientUser2 */
$patientUser2  = User::role('patient')->skip(1)->first() ?? $patientUser;
/** @var Invoice|null $invoice */
$invoice = Invoice::where('status', 'draft')->first();

// Ensure emails are verified so login returns tokens
foreach ([$ownerUser, $doctorUser, $secretaryUser, $patientUser, $patientUser2] as $u) {
    if ($u && !$u->email_verified_at) {
        $u->email_verified_at = now();
        $u->save();
    }
}

$ownerToken     = $ownerUser?->createToken('api-test')->plainTextToken;
$doctorToken    = $doctorUser?->createToken('api-test')->plainTextToken;
$secretaryToken = $secretaryUser?->createToken('api-test')->plainTextToken;
$patientToken   = $patientUser?->createToken('api-test')->plainTextToken;

// Related models
$doctor    = $doctorUser?->doctor;
$secretary = $secretaryUser?->secretary;
$patient   = $patientUser?->patientInfo;
$patient2  = $patientUser2?->patientInfo;
$clinic    = $ownerUser?->clinic;

$doctorId    = $doctor?->id ?? (\App\Models\Doctor::whereNotNull('room_id')->first()?->id ?? 1);
$secretaryId = $secretary?->id ?? 1;
$patientId   = $patient?->id ?? 1;
$patientId2  = $patient2?->id ?? 2;
$clinicId    = $clinic?->id ?? 1;
$roomId      = \App\Models\Room::first()?->id ?? 1;
$apptTypeId  = 1;

// ─── Globals for test tracking ───────────────────────────────────────────────

$totalTests  = 0;
$passedTests = 0;
$failedTests = 0;

// ─── Helpers ─────────────────────────────────────────────────────────────────

function authHeaders(string $token): array
{
    return [
        'Authorization' => "Bearer $token",
        'Accept'        => 'application/json',
    ];
}

function request(string $method, string $url, array $headers = [], $body = null, array $query = []): array
{
    static $curlHandle = null;

    $start = microtime(true);

    $fullUrl = $url;
    if ($query) {
        $fullUrl .= (str_contains($url, '?') ? '&' : '?') . http_build_query($query);
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $fullUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_CUSTOMREQUEST  => strtoupper($method),
    ]);

    $headerList = [];
    foreach ($headers as $k => $v) {
        $headerList[] = "$k: $v";
    }

    $hasFiles = is_array($body) && array_reduce($body, fn($carry, $v) => $carry || $v instanceof \CURLFile, false);

    if ($body !== null && !$hasFiles && !isset($headers['Content-Type'])) {
        $headerList[] = 'Content-Type: application/json';
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headerList);

    if ($body !== null) {
        if ($hasFiles) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        } else {
            $encoded = is_string($body) ? $body : json_encode($body);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
        }
    }

    $response     = curl_exec($ch);
    $headerSize   = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error        = curl_error($ch);
    $duration     = (microtime(true) - $start) * 1000;
    curl_close($ch);

    $responseHeaders = [];
    $rawHeaders = substr($response, 0, $headerSize);
    foreach (explode("\r\n", $rawHeaders) as $line) {
        if (str_contains($line, ': ')) {
            [$k, $v] = explode(': ', $line, 2);
            $responseHeaders[strtolower($k)] = $v;
        }
    }

    $responseBody = substr($response, $headerSize);
    $decoded = json_decode($responseBody, true);

    return [
        'status'  => $httpCode,
        'headers' => $responseHeaders,
        'body'    => $decoded !== null ? $decoded : $responseBody,
        'error'   => $error,
        'latency' => round($duration, 2),
    ];
}

function saveLog(string $entity, string $case, string $method, string $endpoint, array $requestData, array $responseData, ?string $notes = null): void
{
    global $OUTPUT;

    $dir = "$OUTPUT/$entity";
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $log = [
        'entity'     => $entity,
        'case'       => $case,
        'method'     => $method,
        'endpoint'   => $endpoint,
        'request'    => $requestData,
        'response'   => [
            'status'  => $responseData['status'],
            // 'headers' => $responseData['headers'],
            'body'    => $responseData['body'],
        ],
        'latency_ms' => $responseData['latency'],
        'timestamp'  => date('c'),
    ];
    if ($notes) {
        $log['notes'] = $notes;
    }

    $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $case) . '.json';
    file_put_contents("$dir/$filename", json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function runTest(string $entity, string $case, string $method, string $endpoint, array $headers, $body, array $query = [], ?string $notes = null): array
{
    global $totalTests, $passedTests, $failedTests;

    $totalTests++;
    $res = request($method, $endpoint, $headers, $body, $query);

    $status = $res['status'];
    $icon   = $status >= 200 && $status < 300 ? 'OK' : ($status >= 400 ? 'FAIL' : '???');
    printf("  %s %s %s [%d] (%sms)\n", $icon, $method, $endpoint, $status, $res['latency']);

    saveLog($entity, $case, $method, $endpoint, [
        'headers' => $headers,
        'body'    => $body,
        'query'   => $query,
    ], $res, $notes);

    if ($status >= 200 && $status < 300) {
        $passedTests++;
    } else {
        $failedTests++;
    }
    return $res;
}

function section(string $title): void
{
    echo "\n─── $title ───\n";
}

function summary(string $entity, int $total = 0, int $passed = 0, int $failed = 0): void
{
    global $totalTests, $passedTests, $failedTests;
    $total  = $total ?: $totalTests;
    $passed = $passed ?: $passedTests;
    $failed = $failed ?: $failedTests;
    echo "\n  [$entity] Total: $total | Passed: $passed | Failed: $failed\n";
}
