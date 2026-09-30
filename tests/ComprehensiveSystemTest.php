<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\SecurityPolicy;
use App\Models\SensitiveDataCategory;
use App\Models\Transfer;
use App\Models\TransferDetection;
use App\Models\User;
use App\Services\DlpScanEngine;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

$passed = 0;
$failed = 0;
$results = [];

function assertTest($condition, $testName, &$passed, &$failed, &$results, $details = '') {
    if ($condition) {
        $passed++;
        $results[] = "[PASS] " . $testName;
    } else {
        $failed++;
        $results[] = "[FAIL] " . $testName . ($details ? " - Details: {$details}" : "");
    }
}

echo "========================================================\n";
echo "   DATAGUARD DLP FULL SYSTEM VERIFICATION SUITE       \n";
echo "========================================================\n\n";

$scanner = app(DlpScanEngine::class);

$admin = User::where('role', 'admin')->first();
$analyst = User::where('role', 'analyst')->first();
$user = User::where('role', 'user')->first();

assertTest($admin !== null, "Admin account exists in database", $passed, $failed, $results);
assertTest($analyst !== null, "Security Analyst account exists in database", $passed, $failed, $results);
assertTest($user !== null, "Standard User account exists in database", $passed, $failed, $results);

// TEST 1: Scenario A - Allowed Transfer
$resA = $scanner->scan(
    $user,
    'clean_proposal_doc.pdf',
    'pdf',
    20480,
    'compliance@company.com',
    'Internal Compliance Review',
    "DataGuard DLP proposal specifications. Clean organizational documentation for internal audit."
);
assertTest($resA['decision'] === 'allowed', "Scenario A: Compliant transfer returns ALLOWED", $passed, $failed, $results, "Got: " . $resA['decision']);
assertTest($resA['risk_score'] < 20, "Scenario A: Risk score is low (<20)", $passed, $failed, $results, "Got: " . $resA['risk_score']);
assertTest(count($resA['detections']) === 0, "Scenario A: Zero rule violations detected", $passed, $failed, $results);

// TEST 2: Scenario B - Blocked Transfer (PII & Financial to Gmail)
$resB = $scanner->scan(
    $user,
    'payroll_card_records.csv',
    'csv',
    12000,
    'leak_dump@gmail.com',
    'Personal Work',
    "Employee,CreditCard,TIN,Salary\nJuan Dela Cruz,4532-1188-9922-3344,123-456-789,85000\nMaria Santos,5424-0011-2233-4455,987-654-321,95000"
);
assertTest($resB['decision'] === 'blocked', "Scenario B: PII & Financial to Gmail returns BLOCKED", $passed, $failed, $results, "Got: " . $resB['decision']);
assertTest($resB['risk_score'] >= 60, "Scenario B: Risk score is Critical (>=60)", $passed, $failed, $results, "Got: " . $resB['risk_score']);
$hasLuhn = collect($resB['detections'])->contains(fn($d) => str_contains($d['rule_name'], 'Credit Card'));
assertTest($hasLuhn, "Scenario B: Credit Card detection rule triggered via Luhn algorithm", $passed, $failed, $results);
$hasDest = collect($resB['detections'])->contains(fn($d) => str_contains($d['rule_name'], 'Unauthorized External Destination'));
assertTest($hasDest, "Scenario B: Unauthorized Destination rule triggered for Gmail", $passed, $failed, $results);

// TEST 3: Scenario C - Flagged Transfer (Confidential Keywords)
$resC = $scanner->scan(
    $user,
    'vendor_agreement.docx',
    'docx',
    50000,
    'consultant@external-audit.org',
    'Vendor Assessment',
    "This document contains CONFIDENTIAL and INTERNAL USE ONLY corporate information."
);
assertTest($resC['decision'] === 'flagged', "Scenario C: Moderate risk returns FLAGGED", $passed, $failed, $results, "Got: " . $resC['decision']);
assertTest($resC['risk_score'] >= 20 && $resC['risk_score'] < 60, "Scenario C: Risk score is Medium (20-59)", $passed, $failed, $results, "Got: " . $resC['risk_score']);

// TEST 4: Suspicious File Detection (Dangerous extension .bat)
$resD = $scanner->scan(
    $user,
    'dump_vault.bat',
    'bat',
    4000,
    'dest@partner.org',
    'Maintenance',
    "@echo off\nmysqldump -u root -p pass > dump.sql"
);
assertTest($resD['decision'] === 'blocked', "Suspicious File (.bat) returns BLOCKED", $passed, $failed, $results, "Got: " . $resD['decision']);
$hasExt = collect($resD['detections'])->contains(fn($d) => $d['detection_technique'] === 'suspicious_file');
assertTest($hasExt, "Suspicious File detection technique triggered", $passed, $failed, $results);

// TEST 5: Suspicious File Detection (Disguised Double Extension)
$resE = $scanner->scan(
    $user,
    'invoice_august.pdf.exe',
    'exe',
    10000,
    'receiver@company.com',
    'Invoice Share',
    "Binary payload"
);
$hasDoubleExt = collect($resE['detections'])->contains(fn($d) => str_contains($d['rule_name'], 'Double Extension'));
assertTest($hasDoubleExt, "Disguised Double Extension (.pdf.exe) detected and flagged", $passed, $failed, $results);

// TEST 6: Policy Management CRUD
$initialPolicies = SecurityPolicy::count();
$testPolicy = SecurityPolicy::create([
    'policy_code' => 'POL-TEST-' . strtoupper(Str::random(4)),
    'name' => 'Automated Verification Policy',
    'description' => 'Test policy for automated verification',
    'policy_type' => 'destination_check',
    'action_on_violation' => 'flag',
    'severity' => 'medium',
    'is_active' => true,
    'created_by' => $admin->id,
]);
assertTest($testPolicy->id > 0, "Security Policy creation successful", $passed, $failed, $results);

$testPolicy->update(['is_active' => false]);
assertTest($testPolicy->fresh()->is_active === false, "Security Policy toggle status successful", $passed, $failed, $results);

$testPolicy->delete();
assertTest(SecurityPolicy::count() === $initialPolicies, "Security Policy deletion successful", $passed, $failed, $results);

// TEST 7: User Management CRUD
$initialUsers = User::count();
$newUser = User::create([
    'name' => 'Test Subject User',
    'email' => 'test.subject.' . uniqid() . '@dataguard.corp',
    'password' => Hash::make('password'),
    'role' => 'user',
    'department' => 'Quality Assurance',
    'phone' => '+63 900 000 0000',
    'status' => 'active',
]);
assertTest($newUser->id > 0, "User account creation successful", $passed, $failed, $results);
assertTest($newUser->isUser(), "User role helper isUser() returns true", $passed, $failed, $results);

$newUser->update(['status' => 'inactive']);
assertTest(!$newUser->fresh()->isActive(), "User status deactivation works correctly", $passed, $failed, $results);

$newUser->delete();
assertTest(User::count() === $initialUsers, "User deletion successful", $passed, $failed, $results);

// TEST 8: Alert Investigation Workflow
$testTrf = Transfer::create([
    'transfer_uuid' => 'TRF-TEST-' . strtoupper(Str::random(6)),
    'user_id' => $user->id,
    'file_name' => 'test_investigation_artifact.csv',
    'file_type' => 'text',
    'file_extension' => 'csv',
    'file_size_bytes' => 8000,
    'destination' => 'external@test.com',
    'destination_type' => 'external_unapproved',
    'purpose' => 'Automated test investigation',
    'decision' => 'blocked',
    'risk_score' => 85,
    'risk_level' => 'critical',
    'decision_reason' => 'BLOCKED: Test investigation payload',
    'scanned_at' => now(),
]);

$testAlert = Alert::create([
    'alert_uuid' => 'ALT-TEST-' . strtoupper(Str::random(6)),
    'transfer_id' => $testTrf->id,
    'user_id' => $user->id,
    'alert_type' => 'Policy Violation',
    'severity' => 'critical',
    'title' => 'Test Investigation Alert',
    'description' => 'Test investigation description',
    'status' => 'open',
]);
assertTest($testAlert->isOpen(), "New Alert starts with status 'open'", $passed, $failed, $results);

// Analyst investigates alert
$testAlert->update([
    'status' => 'resolved',
    'action_taken' => 'Confirmed Policy Violation - Block Maintained',
    'investigation_notes' => 'Automated test suite verified remediation workflow.',
    'investigated_by' => $analyst->id,
    'resolved_at' => now(),
]);
assertTest($testAlert->fresh()->isResolved(), "Analyst update marks Alert as 'resolved'", $passed, $failed, $results);
assertTest($testAlert->fresh()->investigated_by === $analyst->id, "Alert correctly attributes investigating Analyst", $passed, $failed, $results);

// Clean up test records
$testAlert->delete();
$testTrf->delete();

// Output All Results
foreach ($results as $res) {
    echo $res . "\n";
}

echo "\n--------------------------------------------------------\n";
echo "SUMMARY: Total Tests: " . ($passed + $failed) . " | PASSED: {$passed} | FAILED: {$failed}\n";
echo "--------------------------------------------------------\n";