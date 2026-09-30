<?php

function request($url, $method = 'GET', $data = [], $cookieJar = null, $followRedirect = false) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    if ($cookieJar) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    }
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $followRedirect);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    return ['code' => $httpCode, 'body' => $response, 'url' => $effectiveUrl];
}

$baseUrl = 'http://127.0.0.1:8000';
$passed = 0;
$failed = 0;
$log = [];

function check($cond, $name, &$passed, &$failed, &$log, $details = '') {
    if ($cond) {
        $passed++;
        $log[] = "[PASS] " . $name;
    } else {
        $failed++;
        $log[] = "[FAIL] " . $name . ($details ? " ({$details})" : "");
    }
}

echo "=== DATAGUARD HTTP & RBAC MATRIX VERIFICATION ===\n\n";

// 1. Guest Access
$res = request("$baseUrl/");
check($res['code'] === 200, "Guest can view Homepage (Landing Page)", $passed, $failed, $log);

$res = request("$baseUrl/login");
check($res['code'] === 200, "Guest can view Login page", $passed, $failed, $log);

$res = request("$baseUrl/register");
check($res['code'] === 200, "Guest can view Register page", $passed, $failed, $log);

// Unauthenticated user trying to access /dashboard should redirect to /login
$res = request("$baseUrl/dashboard");
check($res['code'] === 302, "Guest access to Dashboard redirects to Login", $passed, $failed, $log);

// 2. Administrator Session
$adminCookie = sys_get_temp_dir() . '/admin_cookie_' . uniqid() . '.txt';
$res = request("$baseUrl/login/quick/admin", 'GET', [], $adminCookie, true);
check($res['code'] === 200, "Administrator 1-click login succeeds", $passed, $failed, $log);

// Admin accesses Admin-only pages
$res = request("$baseUrl/admin/users", 'GET', [], $adminCookie);
check($res['code'] === 200, "Admin can access /admin/users (Screen #1)", $passed, $failed, $log, "Code: " . $res['code']);

$res = request("$baseUrl/admin/policies", 'GET', [], $adminCookie);
check($res['code'] === 200, "Admin can access /admin/policies (Screen #6)", $passed, $failed, $log, "Code: " . $res['code']);

$res = request("$baseUrl/admin/categories", 'GET', [], $adminCookie);
check($res['code'] === 200, "Admin can access /admin/categories", $passed, $failed, $log, "Code: " . $res['code']);

$res = request("$baseUrl/reports", 'GET', [], $adminCookie);
check($res['code'] === 200, "Admin can access /reports", $passed, $failed, $log, "Code: " . $res['code']);

$res = request("$baseUrl/system-flow", 'GET', [], $adminCookie);
check($res['code'] === 200, "Admin can access /system-flow (Screen #8)", $passed, $failed, $log, "Code: " . $res['code']);

// 3. Security Analyst Session
$analystCookie = sys_get_temp_dir() . '/analyst_cookie_' . uniqid() . '.txt';
$res = request("$baseUrl/login/quick/analyst", 'GET', [], $analystCookie, true);
check($res['code'] === 200, "Security Analyst 1-click login succeeds", $passed, $failed, $log);

$res = request("$baseUrl/alerts", 'GET', [], $analystCookie);
check($res['code'] === 200, "Analyst can access /alerts (Screen #5)", $passed, $failed, $log, "Code: " . $res['code']);

$res = request("$baseUrl/reports", 'GET', [], $analystCookie);
check($res['code'] === 200, "Analyst can access /reports", $passed, $failed, $log, "Code: " . $res['code']);

// Analyst attempting to access Admin-only /admin/users should get 403 Forbidden
$res = request("$baseUrl/admin/users", 'GET', [], $analystCookie);
check($res['code'] === 403, "Analyst blocked with 403 from /admin/users", $passed, $failed, $log, "Code: " . $res['code']);

// 4. Standard User Session
$userCookie = sys_get_temp_dir() . '/user_cookie_' . uniqid() . '.txt';
$res = request("$baseUrl/login/quick/user", 'GET', [], $userCookie, true);
check($res['code'] === 200, "Standard User 1-click login succeeds", $passed, $failed, $log);

$res = request("$baseUrl/transfers/create", 'GET', [], $userCookie);
check($res['code'] === 200, "User can access /transfers/create (Screen #2)", $passed, $failed, $log, "Code: " . $res['code']);

$res = request("$baseUrl/transfers", 'GET', [], $userCookie);
check($res['code'] === 200, "User can access /transfers logs (Screen #3)", $passed, $failed, $log, "Code: " . $res['code']);

// User attempting to access /admin/users or /reports should get 403 Forbidden
$res = request("$baseUrl/admin/users", 'GET', [], $userCookie);
check($res['code'] === 403, "User blocked with 403 from /admin/users", $passed, $failed, $log, "Code: " . $res['code']);

$res = request("$baseUrl/reports", 'GET', [], $userCookie);
check($res['code'] === 403, "User blocked with 403 from /reports", $passed, $failed, $log, "Code: " . $res['code']);

// Cleanup
@unlink($adminCookie);
@unlink($analystCookie);
@unlink($userCookie);

foreach ($log as $l) {
    echo $l . "\n";
}

echo "\n--------------------------------------------------------\n";
echo "HTTP MATRIX: Total: " . ($passed + $failed) . " | PASSED: {$passed} | FAILED: {$failed}\n";
echo "--------------------------------------------------------\n";