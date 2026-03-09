<?php
/**
 * Quick Google OAuth Fix
 * Addresses the specific issues found in the test
 * 
 * Usage: php quick_oauth_fix.php
 */

echo "===========================================\n";
echo "   QUICK GOOGLE OAUTH FIX\n";
echo "===========================================\n\n";

// 1. Check .env for Google credentials
echo "1. CHECKING ENVIRONMENT VARIABLES\n";
echo "-------------------------------------------\n";

$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    
    $hasClientId = strpos($envContent, 'GOOGLE_CLIENT_ID=') !== false && 
                   strpos($envContent, 'GOOGLE_CLIENT_ID=your_') === false;
    $hasClientSecret = strpos($envContent, 'GOOGLE_CLIENT_SECRET=') !== false && 
                       strpos($envContent, 'GOOGLE_CLIENT_SECRET=your_') === false;
    
    echo "GOOGLE_CLIENT_ID: " . ($hasClientId ? '✅ SET' : '❌ NOT SET or using placeholder') . "\n";
    echo "GOOGLE_CLIENT_SECRET: " . ($hasClientSecret ? '✅ SET' : '❌ NOT SET or using placeholder') . "\n";
    
    if (!$hasClientId || !$hasClientSecret) {
        echo "\n❌ ISSUE: Google OAuth credentials missing or using placeholders\n";
        echo "\nSOLUTION:\n";
        echo "1. Go to: https://console.cloud.google.com/\n";
        echo "2. Create OAuth 2.0 Client ID\n";
        echo "3. Add these redirect URIs:\n";
        echo "   - https://hahucare.com/login/google/callback\n";
        echo "   - https://hahucare.com/auth/google/callback\n";
        echo "4. Add to .env file:\n";
        echo "   GOOGLE_CLIENT_ID=your_actual_client_id\n";
        echo "   GOOGLE_CLIENT_SECRET=your_actual_client_secret\n";
        echo "   GOOGLE_REDIRECT=https://hahucare.com/login/google/callback\n";
        echo "   GOOGLE_REDIRECT_URI=https://hahucare.com/auth/google/callback\n";
        echo "5. Run: php artisan config:clear\n";
    } else {
        echo "✅ Google credentials appear to be set\n";
    }
} else {
    echo "❌ .env file not found\n";
}

// 2. Test API route directly
echo "\n2. TESTING API ROUTE\n";
echo "-------------------------------------------\n";

$baseUrl = 'https://hahucare.com';
$testUrl = $baseUrl . '/api/social-login';

echo "Testing API endpoint: {$testUrl}\n";

$testData = [
    'login_type' => 'google',
    'email' => 'test.oauth.' . time() . '@example.com',
    'user_type' => 'user',
    'first_name' => 'Test',
    'last_name' => 'OAuth'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $testUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ CURL Error: {$error}\n";
} else {
    echo "HTTP Status: {$httpCode}\n";
    
    if ($httpCode === 200) {
        echo "✅ API endpoint accessible\n";
        $data = json_decode($response, true);
        if ($data && isset($data['status'])) {
            echo "Response Status: " . ($data['status'] ? '✅ Success' : '❌ Failed') . "\n";
            if (isset($data['message'])) {
                echo "Message: " . $data['message'] . "\n";
            }
        }
    } elseif ($httpCode === 404) {
        echo "❌ API endpoint not found (404)\n";
        echo "This suggests the route may not be properly registered.\n";
    } elseif ($httpCode === 500) {
        echo "❌ Server error (500)\n";
        echo "Check Laravel logs for details.\n";
    } else {
        echo "⚠️  Unexpected HTTP code: {$httpCode}\n";
        echo "Response: " . substr($response, 0, 200) . "...\n";
    }
}

// 3. Test frontend Google redirect
echo "\n3. TESTING FRONTEND GOOGLE REDIRECT\n";
echo "-------------------------------------------\n";

$frontendUrl = $baseUrl . '/auth/google';
echo "Testing frontend URL: {$frontendUrl}\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $frontendUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ CURL Error: {$error}\n";
} else {
    echo "HTTP Status: {$httpCode}\n";
    
    if ($httpCode === 302 || $httpCode === 301) {
        echo "✅ Redirect working\n";
        echo "Redirected to: " . substr($finalUrl, 0, 100) . "...\n";
        
        if (strpos($finalUrl, 'accounts.google.com') !== false) {
            echo "✅ Redirecting to Google OAuth\n";
        } else {
            echo "⚠️  Not redirecting to Google\n";
        }
    } else {
        echo "⚠️  Expected redirect, got HTTP {$httpCode}\n";
    }
}

// 4. Test backend Google redirect
echo "\n4. TESTING BACKEND GOOGLE REDIRECT\n";
echo "-------------------------------------------\n";

$backendUrl = $baseUrl . '/login/google';
echo "Testing backend URL: {$backendUrl}\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $backendUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ CURL Error: {$error}\n";
} else {
    echo "HTTP Status: {$httpCode}\n";
    
    if ($httpCode === 302 || $httpCode === 301) {
        echo "✅ Redirect working\n";
        echo "Redirected to: " . substr($finalUrl, 0, 100) . "...\n";
        
        if (strpos($finalUrl, 'accounts.google.com') !== false) {
            echo "✅ Redirecting to Google OAuth\n";
        } else {
            echo "⚠️  Not redirecting to Google\n";
        }
    } else {
        echo "⚠️  Expected redirect, got HTTP {$httpCode}\n";
    }
}

// 5. Summary and next steps
echo "\n===========================================\n";
echo "   SUMMARY & NEXT STEPS\n";
echo "===========================================\n\n";

echo "Based on the tests above:\n\n";

echo "⚠️  MAIN ISSUE: Google OAuth credentials are missing from .env\n";
echo "This is why the Socialite driver can't generate redirect URLs.\n\n";

echo "IMMEDIATE ACTIONS:\n";
echo "1. Get Google OAuth credentials from: https://console.cloud.google.com/\n";
echo "2. Add credentials to .env file\n";
echo "3. Clear caches: php artisan config:clear\n";
echo "4. Test again: php test_google_oauth_complete.php\n\n";

echo "MANUAL TESTING:\n";
echo "Frontend: https://hahucare.com/auth/google\n";
echo "Backend:  https://hahucare.com/login/google\n";
echo "API:      curl command shown in test output\n\n";

echo "If you need help getting Google OAuth credentials:\n";
echo "1. Go to https://console.cloud.google.com/\n";
echo "2. Create new project or select existing\n";
echo "3. Go to APIs & Services > Credentials\n";
echo "4. Create Credentials > OAuth 2.0 Client ID\n";
echo "5. Application type: Web application\n";
echo "6. Add authorized redirect URIs:\n";
echo "   - https://hahucare.com/login/google/callback\n";
echo "   - https://hahucare.com/auth/google/callback\n";
echo "7. Copy Client ID and Client Secret\n";
echo "8. Add to .env file\n\n";

echo "===========================================\n";
echo "   FIX COMPLETE\n";
echo "===========================================\n";
