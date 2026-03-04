<?php
/**
 * Comprehensive Google OAuth Test - Full Flow Analysis
 * Run via SSH: php comprehensive_oauth_test.php
 * This will test EVERYTHING and show exactly where it fails
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "===========================================\n";
echo "   COMPREHENSIVE OAUTH DIAGNOSTIC\n";
echo "===========================================\n\n";

// 1. Environment Check
echo "1. ENVIRONMENT VARIABLES\n";
echo "-------------------------------------------\n";
$envFile = __DIR__ . '/.env';
$envContent = file_get_contents($envFile);

$clientId = '';
$clientSecret = '';
$redirect = '';

if (preg_match('/^GOOGLE_CLIENT_ID=(.*)$/m', $envContent, $m)) {
    $clientId = trim($m[1]);
    echo "GOOGLE_CLIENT_ID: " . substr($clientId, 0, 30) . "... (" . strlen($clientId) . " chars)\n";
}
if (preg_match('/^GOOGLE_CLIENT_SECRET=(.*)$/m', $envContent, $m)) {
    $clientSecret = trim($m[1]);
    echo "GOOGLE_CLIENT_SECRET: " . substr($clientSecret, 0, 15) . "... (" . strlen($clientSecret) . " chars)\n";
}
if (preg_match('/^GOOGLE_REDIRECT=(.*)$/m', $envContent, $m)) {
    $redirect = trim($m[1]);
    echo "GOOGLE_REDIRECT: {$redirect}\n";
}

if (empty($clientId) || empty($clientSecret) || empty($redirect)) {
    echo "\n❌ ERROR: Missing environment variables!\n";
    exit(1);
}

// 2. Config Check
echo "\n2. LARAVEL CONFIG\n";
echo "-------------------------------------------\n";
try {
    $config = config('services.google');
    echo "Config loaded: YES\n";
    echo "  client_id: " . (isset($config['client_id']) ? substr($config['client_id'], 0, 30) . '...' : 'NOT SET') . "\n";
    echo "  client_secret: " . (isset($config['client_secret']) ? substr($config['client_secret'], 0, 15) . '...' : 'NOT SET') . "\n";
    echo "  redirect: " . ($config['redirect'] ?? 'NOT SET') . "\n";
} catch (\Exception $e) {
    echo "Config ERROR: " . $e->getMessage() . "\n";
}

// 3. Routes Check
echo "\n3. ROUTES\n";
echo "-------------------------------------------\n";
$routes = \Illuminate\Support\Facades\Route::getRoutes();
$found = false;
foreach ($routes as $route) {
    $name = $route->getName();
    if ($name === 'social.login' || $name === 'social.login.callback') {
        echo "{$name}: " . $route->uri() . " -> " . $route->getActionName() . "\n";
        $found = true;
    }
}
if (!$found) {
    echo "❌ ERROR: Social login routes not found!\n";
}

// 4. Test Socialite Driver
echo "\n4. SOCIALITE DRIVER TEST\n";
echo "-------------------------------------------\n";
try {
    // Create a mock request with session
    $request = \Illuminate\Http\Request::create('/test', 'GET');
    $session = new \Illuminate\Session\Store(
        'test-session',
        new \Illuminate\Session\ArraySessionHandler(60)
    );
    $request->setLaravelSession($session);
    app()->instance('request', $request);
    
    $driver = \Laravel\Socialite\Facades\Socialite::driver('google');
    echo "Driver created: YES\n";
    
    // Get redirect response
    $response = $driver->redirect();
    $redirectUrl = $response->getTargetUrl();
    echo "Redirect URL generated: YES\n";
    echo "URL: " . substr($redirectUrl, 0, 120) . "...\n";
    
    // Parse URL
    $parsed = parse_url($redirectUrl);
    parse_str($parsed['query'] ?? '', $params);
    
    echo "\nOAuth Parameters:\n";
    echo "  client_id: " . (isset($params['client_id']) ? substr($params['client_id'], 0, 30) . '...' : 'MISSING') . "\n";
    echo "  redirect_uri: " . ($params['redirect_uri'] ?? 'MISSING') . "\n";
    echo "  scope: " . ($params['scope'] ?? 'MISSING') . "\n";
    echo "  response_type: " . ($params['response_type'] ?? 'MISSING') . "\n";
    echo "  state: " . (isset($params['state']) ? 'SET' : 'MISSING') . "\n";
    
    // Verify redirect_uri
    $expectedRedirect = 'https://hahucare.com/login/google/callback';
    if (isset($params['redirect_uri'])) {
        if ($params['redirect_uri'] === $expectedRedirect) {
            echo "\n✅ Redirect URI matches expected\n";
        } else {
            echo "\n❌ Redirect URI MISMATCH!\n";
            echo "  Expected: {$expectedRedirect}\n";
            echo "  Got: {$params['redirect_uri']}\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Socialite ERROR:\n";
    echo "  Message: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "  Trace:\n";
    foreach (explode("\n", $e->getTraceAsString()) as $line) {
        echo "    " . $line . "\n";
    }
}

// 5. Check SocialLoginController
echo "\n5. SOCIALLOGINCONTROLLER\n";
echo "-------------------------------------------\n";
$controllerFile = __DIR__ . '/app/Http/Controllers/Auth/SocialLoginController.php';
if (file_exists($controllerFile)) {
    echo "Controller exists: YES\n";
    $content = file_get_contents($controllerFile);
    
    // Check for error handling
    $hasErrorLog = strpos($content, 'Log::error') !== false;
    $hasTryCatch = strpos($content, 'try {') !== false && strpos($content, 'catch') !== false;
    $hasFlash = strpos($content, 'flash(') !== false;
    
    echo "Has error logging: " . ($hasErrorLog ? 'YES' : 'NO') . "\n";
    echo "Has try-catch: " . ($hasTryCatch ? 'YES' : 'NO') . "\n";
    echo "Has flash messages: " . ($hasFlash ? 'YES' : 'NO') . "\n";
    
    // Check redirect paths
    if (preg_match_all('/redirect\([\'"]([^\'"]+)[\'"]\)/', $content, $matches)) {
        echo "Redirect paths found:\n";
        foreach (array_unique($matches[1]) as $path) {
            echo "  - {$path}\n";
        }
    }
} else {
    echo "❌ Controller NOT FOUND!\n";
}

// 6. Check Laravel Logs
echo "\n6. LARAVEL LOGS\n";
echo "-------------------------------------------\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logSize = filesize($logFile);
    echo "Log file exists: YES ({$logSize} bytes)\n";
    
    // Read last 100 lines
    $lines = file($logFile);
    $lastLines = array_slice($lines, -100);
    
    $relevantLines = [];
    foreach ($lastLines as $line) {
        if (stripos($line, 'google') !== false || 
            stripos($line, 'social') !== false || 
            stripos($line, 'oauth') !== false ||
            stripos($line, 'socialite') !== false ||
            stripos($line, 'redirect') !== false) {
            $relevantLines[] = trim($line);
        }
    }
    
    if (count($relevantLines) > 0) {
        echo "\nRecent Google/OAuth log entries (" . count($relevantLines) . " found):\n";
        foreach (array_slice($relevantLines, -10) as $line) {
            echo "  " . substr($line, 0, 200) . "\n";
        }
    } else {
        echo "No recent Google/OAuth log entries\n";
    }
} else {
    echo "Log file NOT FOUND\n";
    echo "Creating log directory...\n";
    @mkdir(storage_path('logs'), 0755, true);
    @touch($logFile);
    echo "Log file created\n";
}

// 7. Test actual OAuth callback simulation
echo "\n7. SIMULATE OAUTH CALLBACK\n";
echo "-------------------------------------------\n";
echo "Simulating what happens when Google redirects back...\n";

try {
    // Create mock callback request
    $callbackRequest = \Illuminate\Http\Request::create(
        '/login/google/callback',
        'GET',
        ['code' => 'test_code_12345', 'state' => 'test_state']
    );
    $session = new \Illuminate\Session\Store(
        'callback-session',
        new \Illuminate\Session\ArraySessionHandler(60)
    );
    $session->put('state', 'test_state');
    $callbackRequest->setLaravelSession($session);
    
    echo "Mock callback request created\n";
    echo "  URL: /login/google/callback?code=test_code_12345&state=test_state\n";
    echo "  This simulates Google redirecting back after authentication\n";
    
} catch (\Exception $e) {
    echo "Simulation ERROR: " . $e->getMessage() . "\n";
}

// 8. Google Cloud Console Checklist
echo "\n8. GOOGLE CLOUD CONSOLE CHECKLIST\n";
echo "-------------------------------------------\n";
echo "Verify these settings in Google Cloud Console:\n";
echo "https://console.cloud.google.com/apis/credentials\n\n";
echo "1. OAuth 2.0 Client ID:\n";
echo "   Client ID: " . substr($clientId, 0, 40) . "...\n";
echo "\n2. Authorized JavaScript origins:\n";
echo "   - https://hahucare.com\n";
echo "\n3. Authorized redirect URIs:\n";
echo "   - https://hahucare.com/login/google/callback\n";
echo "\n4. OAuth consent screen:\n";
echo "   - Must be configured\n";
echo "   - Publishing status: In production OR Testing with test users\n";
echo "\n5. Enabled APIs:\n";
echo "   - Google+ API OR People API\n";

// 9. Manual Test Instructions
echo "\n9. MANUAL TEST INSTRUCTIONS\n";
echo "-------------------------------------------\n";
echo "To test Google OAuth manually:\n\n";
echo "1. Visit: https://hahucare.com/login/google\n";
echo "   This should redirect to Google\n\n";
echo "2. After Google authentication, it redirects to:\n";
echo "   https://hahucare.com/login/google/callback?code=...&state=...\n\n";
echo "3. Check Laravel logs:\n";
echo "   tail -f storage/logs/laravel.log\n\n";
echo "4. Look for errors in the log\n";

// 10. Summary
echo "\n===========================================\n";
echo "   DIAGNOSTIC SUMMARY\n";
echo "===========================================\n";

$issues = [];

if (empty($clientId)) $issues[] = "GOOGLE_CLIENT_ID is empty";
if (empty($clientSecret)) $issues[] = "GOOGLE_CLIENT_SECRET is empty";
if (empty($redirect)) $issues[] = "GOOGLE_REDIRECT is empty";
if ($redirect !== 'https://hahucare.com/login/google/callback') {
    $issues[] = "GOOGLE_REDIRECT has wrong value: {$redirect}";
}

if (count($issues) > 0) {
    echo "❌ ISSUES FOUND:\n";
    foreach ($issues as $i => $issue) {
        echo "  " . ($i + 1) . ". {$issue}\n";
    }
} else {
    echo "✅ Configuration looks correct\n";
    echo "\nIf login still fails:\n";
    echo "1. Check Google Cloud Console settings above\n";
    echo "2. Try manual test and check Laravel logs\n";
    echo "3. Ensure OAuth consent screen is published\n";
    echo "4. Verify test users are added (if in testing mode)\n";
}

echo "\nDELETE THIS FILE: rm comprehensive_oauth_test.php\n";
echo "===========================================\n";
