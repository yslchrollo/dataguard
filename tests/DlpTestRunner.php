<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('role', 'user')->first();
$scanner = app(App\Services\DlpScanEngine::class);

echo "=== DATAGUARD DLP ENGINE VERIFICATION ===\n";

// Test 1: Blocked Scenario
$res1 = $scanner->scan(
    $user,
    'employee_payroll.csv',
    'csv',
    5000,
    'hacker@gmail.com',
    'Personal Backup',
    "Name,CreditCard,TIN\nJuan Dela Cruz,4532-1188-9922-3344,123-456-789"
);
echo "[Test 1: PII & Financial to Gmail]\n";
echo "Decision: " . strtoupper($res1['decision']) . "\n";
echo "Risk Score: " . $res1['risk_score'] . "/100 (" . strtoupper($res1['risk_level']) . ")\n";
echo "Detections: " . count($res1['detections']) . "\n\n";

// Test 2: Allowed Scenario
$res2 = $scanner->scan(
    $user,
    'quarterly_report.pdf',
    'pdf',
    15000,
    'audit@company.com',
    'Internal Audit',
    "Standard quarterly compliance documentation for company internal review."
);
echo "[Test 2: Clean Document to Corporate Domain]\n";
echo "Decision: " . strtoupper($res2['decision']) . "\n";
echo "Risk Score: " . $res2['risk_score'] . "/100 (" . strtoupper($res2['risk_level']) . ")\n";
echo "Detections: " . count($res2['detections']) . "\n";

echo "=========================================\n";