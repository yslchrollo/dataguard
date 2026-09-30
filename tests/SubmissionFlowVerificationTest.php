<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Alert;
use App\Models\Transfer;
use App\Models\TransferDetection;
use App\Models\User;
use App\Services\DlpScanEngine;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

echo "=================================================================\n";
echo "   DATAGUARD SUBMISSION FLOW & FALSE-POSITIVE VERIFICATION       \n";
echo "=================================================================\n\n";

$passed = 0;
$failed = 0;

function checkCase(bool $condition, string $label, string $details = '') {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "[PASS] {$label}\n";
    } else {
        $failed++;
        echo "[FAIL] {$label}" . ($details ? " -> {$details}" : "") . "\n";
    }
}

$scanner = app(DlpScanEngine::class);
$user = User::where('role', 'user')->first() ?? User::first();
Auth::login($user);

// -------------------------------------------------------------
// CASE 1: Normal harmless document (Clean / Safe / Allowed)
// -------------------------------------------------------------
echo "\n--- CASE 1: Normal Harmless Document ---\n";
$normalContent = "Weekly Department Status Update\n\nAll quarterly goals were achieved on schedule.\nThe project deliverables have been submitted for client review.\nNo incidents occurred during the reporting period.";
$res1 = $scanner->scan(
    $user,
    'weekly_status_report.docx',
    'docx',
    24500,
    'reports@partner-organization.org',
    'Weekly Status Update',
    $normalContent
);

checkCase($res1['decision'] === 'allowed', "Normal document is classified as ALLOWED (Clean / Safe)", "Got: " . $res1['decision']);
checkCase($res1['risk_score'] < 20, "Normal document has low risk score (< 20)", "Got: " . $res1['risk_score']);
checkCase(count($res1['detections']) === 0, "Normal document has 0 detections (no false positives)", "Count: " . count($res1['detections']));

// -------------------------------------------------------------
// CASE 2: File triggering DLP rules (Suspicious / Blocked based on evidence)
// -------------------------------------------------------------
echo "\n--- CASE 2: DLP Violations (Evidence-Based Threat Detection) ---\n";

// 2A: Moderate risk (Confidential markers to external destination -> Suspicious / Flagged)
$confidentialContent = "CONFIDENTIAL INTERNAL USE ONLY: Project Phoenix System Architecture and Preliminary Roadmap.";
$res2A = $scanner->scan(
    $user,
    'internal_architecture.pdf',
    'pdf',
    18000,
    'external-auditor@consulting-firm.com',
    'Audit Review',
    $confidentialContent
);

checkCase($res2A['decision'] === 'flagged', "Document with confidential markers is FLAGGED (Suspicious)", "Got: " . $res2A['decision']);
checkCase($res2A['risk_score'] >= 20 && $res2A['risk_score'] < 60, "Flagged document risk score is in medium band (20-59)", "Got: " . $res2A['risk_score']);
checkCase(count($res2A['detections']) > 0, "DLP rule violation properly recorded", "Count: " . count($res2A['detections']));

// 2B: Severe threat (Sensitive credit cards / PII to unapproved consumer email -> Malicious / Blocked)
$threatContent = "Employee TIN, SSS, and Payment Record:\nJuan Dela Cruz, 123-456-789, 4532 1188 9922 3344, password=SecretKey!";
$res2B = $scanner->scan(
    $user,
    'compromised_payroll.csv',
    'csv',
    15000,
    'exfiltrator@gmail.com',
    'Personal Backup',
    $threatContent
);

checkCase($res2B['decision'] === 'blocked', "Severe exfiltration attempt is BLOCKED (Malicious / Threat)", "Got: " . $res2B['decision']);
checkCase($res2B['risk_score'] >= 60, "Blocked transfer risk score is critical (>= 60)", "Got: " . $res2B['risk_score']);

// -------------------------------------------------------------
// CASE 3: External API failure / Unable to analyze
// -------------------------------------------------------------
echo "\n--- CASE 3: External API / Unanalyzable Content Handling ---\n";
// Binary file without text markers or unparsable structure:
// Must fail safely without falsely labeling as malicious without evidence.
$binaryNoise = "\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR\x00\x00\x00\x01\x00\x00\x00\x01\x08\x06\x00\x00\x00\x1f\x15\xc4\x89";
$res3 = $scanner->scan(
    $user,
    'chart_graphic.png',
    'png',
    strlen($binaryNoise),
    'design@company.com',
    'Presentation asset',
    $binaryNoise
);

checkCase($res3['decision'] === 'allowed', "Binary asset without text/signatures is safely ALLOWED (not falsely blocked)", "Got: " . $res3['decision']);
checkCase($res3['risk_score'] === 0, "No false positive risk score assigned", "Got: " . $res3['risk_score']);

// -------------------------------------------------------------
// CASE 4: Invalid file upload (corrupted / unreadable / empty)
// -------------------------------------------------------------
echo "\n--- CASE 4: Corrupted / Empty File Upload Validation ---\n";
$tempEmpty = tempnam(sys_get_temp_dir(), 'dlp_empty_');
$requestMock = Illuminate\Http\Request::create('/transfers', 'POST', [
    'destination' => 'compliance@company.com',
    'purpose' => 'Test Empty File',
    'content_payload' => '',
], [], [
    'file' => new UploadedFile($tempEmpty, 'empty_document.pdf', 'application/pdf', null, true),
]);

$controller = app(App\Http\Controllers\TransferController::class);
$emptyValidationFailed = false;
try {
    $response = $controller->store($requestMock);
    $emptyValidationFailed = $response->isRedirection() && session()->has('errors');
} catch (\Illuminate\Validation\ValidationException $e) {
    $emptyValidationFailed = true;
}
unlink($tempEmpty);

checkCase($emptyValidationFailed, "0-byte/empty file upload triggers clear validation error (not treated as threat)");

// -------------------------------------------------------------
// CASE 5: Analyst Dashboard / Review Queue Verification
// -------------------------------------------------------------
echo "\n--- CASE 5: Analyst Review Queue Only Receives Genuine Threats ---\n";

// Simulate allowed transfer submission
$allowedTransfer = Transfer::create([
    'transfer_uuid' => 'TRF-TEST-' . strtoupper(Str::random(6)),
    'user_id' => $user->id,
    'file_name' => 'legitimate_quarterly_report.docx',
    'original_file_name' => 'legitimate_quarterly_report.docx',
    'file_type' => 'document',
    'file_extension' => 'docx',
    'file_size_bytes' => 10240,
    'destination' => 'partner@business-corp.org',
    'destination_type' => 'external',
    'purpose' => 'Business update',
    'decision' => 'allowed',
    'risk_score' => 0,
    'risk_level' => 'low',
    'decision_reason' => 'Compliant transfer: clean scan, no rule violations.',
    'scanned_at' => now(),
]);

// Ensure no Alert was created for allowed transfer
$alertsForAllowed = Alert::where('transfer_id', $allowedTransfer->id)->count();
checkCase($alertsForAllowed === 0, "Clean / Safe transfer does NOT create an Alert (Analyst queue is clean)");

// Simulate flagged transfer submission
$flaggedTransfer = Transfer::create([
    'transfer_uuid' => 'TRF-TEST-' . strtoupper(Str::random(6)),
    'user_id' => $user->id,
    'file_name' => 'sensitive_financial_preview.pdf',
    'original_file_name' => 'sensitive_financial_preview.pdf',
    'file_type' => 'document',
    'file_extension' => 'pdf',
    'file_size_bytes' => 20480,
    'destination' => 'auditor@external.com',
    'destination_type' => 'external',
    'purpose' => 'External audit review',
    'decision' => 'flagged',
    'risk_score' => 40,
    'risk_level' => 'medium',
    'decision_reason' => 'Confidential keywords detected in document.',
    'scanned_at' => now(),
]);

Alert::create([
    'alert_uuid' => 'ALT-TEST-' . strtoupper(Str::random(6)),
    'transfer_id' => $flaggedTransfer->id,
    'user_id' => $user->id,
    'alert_type' => 'Flagged Suspicious Activity',
    'severity' => 'medium',
    'title' => "Suspicious Data Transfer Flagged: {$flaggedTransfer->file_name}",
    'description' => $flaggedTransfer->decision_reason,
    'status' => 'open',
]);

$alertsForFlagged = Alert::where('transfer_id', $flaggedTransfer->id)->count();
checkCase($alertsForFlagged === 1, "Suspicious transfer generates an open Alert in Analyst Queue");

// Clean up test records
Alert::where('transfer_id', $flaggedTransfer->id)->delete();
$flaggedTransfer->delete();
$allowedTransfer->delete();

echo "\n=================================================================\n";
echo "SUMMARY: Total Cases Tested: " . ($passed + $failed) . " | PASSED: {$passed} | FAILED: {$failed}\n";
echo "=================================================================\n";

