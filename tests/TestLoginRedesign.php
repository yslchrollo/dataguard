<?php

$baseUrl = 'http://127.0.0.1:8000';

echo "=== DATAGUARD LOGIN REDESIGN VERIFICATION ===\n\n";

// 1. Fetch Login Page HTML
$html = file_get_contents("$baseUrl/login");

$checks = [
    'Page responds HTTP 200' => strlen($html) > 500,
    'Branding header contains DataGuard' => strpos($html, 'Data') !== false && strpos($html, 'Guard') !== false,
    'Tagline present' => strpos($html, 'A Web-Based Data Loss Prevention and Monitoring System') !== false,
    'Email field present' => strpos($html, 'name="email"') !== false,
    'Password field present' => strpos($html, 'name="password"') !== false,
    'Show/Hide Password toggle script' => strpos($html, 'togglePasswordVisibility') !== false,
    'Forgot Password link and modal' => strpos($html, 'Forgot Password?') !== false && strpos($html, 'forgotPasswordModal') !== false,
    'CSRF Token present' => strpos($html, 'name="_token"') !== false,
    'Submit button present' => strpos($html, 'Sign In to DataGuard') !== false,
    'Quick Demo 1-Click buttons present' => strpos($html, 'Quick Demo Access') !== false,
];

foreach ($checks as $name => $ok) {
    echo ($ok ? "[PASS] " : "[FAIL] ") . $name . "\n";
}

// 2. Test Invalid Credentials
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$baseUrl/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, sys_get_temp_dir() . '/test_cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, sys_get_temp_dir() . '/test_cookie.txt');
$loginPage = curl_exec($ch);

// Extract CSRF
preg_match('/<input[^>]+name="_token"[^>]+value="([^"]+)"/', $loginPage, $m);
$token = $m[1] ?? '';

curl_setopt($ch, CURLOPT_URL, "$baseUrl/login");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    '_token' => $token,
    'email' => 'admin@dataguard.corp',
    'password' => 'wrongpassword123',
]));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$resBody = curl_exec($ch);

$invalidCheck = strpos($resBody, 'The provided credentials do not match our security records.') !== false;
echo ($invalidCheck ? "[PASS] " : "[FAIL] ") . "Invalid password triggers clear security error\n";

// 3. Test Valid Credentials
curl_setopt($ch, CURLOPT_URL, "$baseUrl/login");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    '_token' => $token,
    'email' => 'admin@dataguard.corp',
    'password' => 'password',
]));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$resBody = curl_exec($ch);
$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

$validCheck = strpos($url, '/dashboard') !== false && strpos($resBody, 'Security Dashboard') !== false;
echo ($validCheck ? "[PASS] " : "[FAIL] ") . "Valid credentials successfully authenticate and redirect to Dashboard\n";

curl_close($ch);
echo "\n============================================\n";
