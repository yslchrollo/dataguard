<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;

echo "=== DATAGUARD REGISTRATION & AUTHENTICATION VERIFICATION ===\n\n";

$baseUrl = 'http://127.0.0.1:8000';
$passed = 0;
$failed = 0;

function checkStep($cond, $title, $details = '') {
    global $passed, $failed;
    if ($cond) {
        $passed++;
        echo "[PASS] " . $title . ($details ? " - {$details}" : "") . "\n";
    } else {
        $failed++;
        echo "[FAIL] " . $title . ($details ? " ({$details})" : "") . "\n";
    }
}

// 1. Check Register Page HTML
$html = file_get_contents("$baseUrl/register");
checkStep(strlen($html) > 500, "Register page responds HTTP 200");
checkStep(str_contains($html, 'Create Employee Account'), "Page title and header match design");
checkStep(str_contains($html, 'name="name"'), "Full Name field present");
checkStep(str_contains($html, 'name="email"'), "Email field present");
checkStep(str_contains($html, 'name="password"'), "Password field present");
checkStep(str_contains($html, 'name="password_confirmation"'), "Password confirmation field present");
checkStep(str_contains($html, 'Regular Employee / User'), "Default role disclosure badge present");
checkStep(str_contains($html, 'togglePasswordVisibility'), "Password visibility toggle present");

// 2. Test Validation: Password Mismatch
$cookieJar = sys_get_temp_dir() . '/reg_test_cookie.txt';
@unlink($cookieJar);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$baseUrl/register");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
$regPage = curl_exec($ch);

preg_match('/<input[^>]+name="_token"[^>]+value="([^"]+)"/', $regPage, $m);
$token = $m[1] ?? '';

// Attempt submission with mismatched password
curl_setopt($ch, CURLOPT_URL, "$baseUrl/register");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    '_token' => $token,
    'name' => 'Test Employee',
    'email' => 'mismatch.test@dataguard.corp',
    'password' => 'Password123!',
    'password_confirmation' => 'DifferentPassword456!',
]));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$resMismatch = curl_exec($ch);

checkStep(str_contains($resMismatch, 'The password confirmation does not match.'), "Password confirmation validation enforced");

// 3. Test Successful Self-Registration
$uniqueEmail = 'employee_' . uniqid() . '@company.com';
User::where('email', $uniqueEmail)->delete();

// Re-fetch register page for fresh token
curl_setopt($ch, CURLOPT_URL, "$baseUrl/register");
curl_setopt($ch, CURLOPT_POST, false);
$regPage2 = curl_exec($ch);
preg_match('/<input[^>]+name="_token"[^>]+value="([^"]+)"/', $regPage2, $m2);
$token2 = $m2[1] ?? '';

curl_setopt($ch, CURLOPT_URL, "$baseUrl/register");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    '_token' => $token2,
    'name' => 'Maria Clara Santos',
    'email' => $uniqueEmail,
    'password' => 'SecurityPass2026!',
    'password_confirmation' => 'SecurityPass2026!',
]));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$resSuccess = curl_exec($ch);
$urlSuccess = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

checkStep(str_contains($urlSuccess, '/dashboard'), "Registration redirects to Dashboard", "URL: {$urlSuccess}");

// 4. Verify in MySQL Database
$savedUser = User::where('email', $uniqueEmail)->first();
checkStep(!is_null($savedUser), "New account actually saved in MySQL database", "User ID: " . ($savedUser->id ?? 'null'));
checkStep($savedUser && $savedUser->name === 'Maria Clara Santos', "Correct user name saved in database");
checkStep($savedUser && $savedUser->role === 'user', "Default role 'user' strictly assigned in database", "Role: " . ($savedUser->role ?? 'null'));
checkStep($savedUser && Hash::check('SecurityPass2026!', $savedUser->password), "Password securely hashed with Bcrypt in database");
checkStep($savedUser && $savedUser->status === 'active', "Account status is active");

// Verify audit log
$log = ActivityLog::where('action', 'auth_registered')->where('user_id', $savedUser->id)->first();
checkStep(!is_null($log), "Registration event recorded in ActivityLog audit trail");

// 5. Test Duplicate Email Validation with a fresh guest session
$cookieGuest = sys_get_temp_dir() . '/guest_dup_test.txt';
@unlink($cookieGuest);
$chDup = curl_init();
curl_setopt($chDup, CURLOPT_URL, "$baseUrl/register");
curl_setopt($chDup, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chDup, CURLOPT_COOKIEJAR, $cookieGuest);
curl_setopt($chDup, CURLOPT_COOKIEFILE, $cookieGuest);
$dupPage = curl_exec($chDup);

preg_match('/<input[^>]+name="_token"[^>]+value="([^"]+)"/', $dupPage, $mDup);
$tokenDup = $mDup[1] ?? '';

curl_setopt($chDup, CURLOPT_URL, "$baseUrl/register");
curl_setopt($chDup, CURLOPT_POST, true);
curl_setopt($chDup, CURLOPT_POSTFIELDS, http_build_query([
    '_token' => $tokenDup,
    'name' => 'Duplicate Attempt',
    'email' => $uniqueEmail,
    'password' => 'SecurityPass2026!',
    'password_confirmation' => 'SecurityPass2026!',
]));
curl_setopt($chDup, CURLOPT_FOLLOWLOCATION, true);
$resDup = curl_exec($chDup);

checkStep(str_contains($resDup, 'This email address is already registered in DataGuard.'), "Duplicate email prevents re-registration");
curl_close($chDup);
@unlink($cookieGuest);

// 6. Test Logging In With Newly Created User Credentials
$cookieLogin = sys_get_temp_dir() . '/new_user_login.txt';
@unlink($cookieLogin);

$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, "$baseUrl/login");
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_COOKIEJAR, $cookieLogin);
curl_setopt($ch2, CURLOPT_COOKIEFILE, $cookieLogin);
$loginPage = curl_exec($ch2);

preg_match('/<input[^>]+name="_token"[^>]+value="([^"]+)"/', $loginPage, $mL);
$tokenL = $mL[1] ?? '';

curl_setopt($ch2, CURLOPT_URL, "$baseUrl/login");
curl_setopt($ch2, CURLOPT_POST, true);
curl_setopt($ch2, CURLOPT_POSTFIELDS, http_build_query([
    '_token' => $tokenL,
    'email' => $uniqueEmail,
    'password' => 'SecurityPass2026!',
]));
curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);
$resLogin = curl_exec($ch2);
$urlLogin = curl_getinfo($ch2, CURLINFO_EFFECTIVE_URL);

checkStep(str_contains($urlLogin, '/dashboard'), "New user can successfully log in via Login page", "URL: {$urlLogin}");
checkStep(str_contains($resLogin, 'Maria Clara Santos') || str_contains($resLogin, 'Dashboard'), "Dashboard renders for newly logged in user");

curl_close($ch);
curl_close($ch2);
@unlink($cookieJar);
@unlink($cookieLogin);

echo "\n============================================================\n";
echo "REGISTRATION TEST SUMMARY: Total: " . ($passed + $failed) . " | PASSED: {$passed} | FAILED: {$failed}\n";
echo "============================================================\n";
