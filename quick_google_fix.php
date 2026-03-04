<?php
/**
 * Quick Google OAuth Fix - Simple and direct
 * Run via SSH: php quick_google_fix.php
 * DELETE THIS FILE AFTER RUNNING!
 */

echo "===========================================\n";
echo "   QUICK GOOGLE OAUTH FIX\n";
echo "===========================================\n\n";

// 1. Check .env directly
echo "1. CHECKING .env FILE\n";
echo "-------------------------------------------\n";
$envFile = __DIR__ . '/.env';
$envContent = file_get_contents($envFile);

$patterns = [
    'GOOGLE_CLIENT_ID' => '/^GOOGLE_CLIENT_ID=(.*)$/m',
    'GOOGLE_CLIENT_SECRET' => '/^GOOGLE_CLIENT_SECRET=(.*)$/m',
    'GOOGLE_REDIRECT' => '/^GOOGLE_REDIRECT=(.*)$/m',
];

$envVars = [];
foreach ($patterns as $key => $pattern) {
    if (preg_match($pattern, $envContent, $matches)) {
        $envVars[$key] = $matches[1];
        echo "{$key}: " . (strlen($matches[1]) > 0 ? substr($matches[1], 0, 30) . '...' : 'EMPTY') . "\n";
    } else {
        echo "{$key}: NOT FOUND\n";
    }
}

// 2. Check if values are empty
echo "\n2. VALIDATING VALUES\n";
echo "-------------------------------------------\n";
$issues = [];

if (!isset($envVars['GOOGLE_CLIENT_ID']) || empty($envVars['GOOGLE_CLIENT_ID'])) {
    $issues[] = "GOOGLE_CLIENT_ID is empty or missing";
}

if (!isset($envVars['GOOGLE_CLIENT_SECRET']) || empty($envVars['GOOGLE_CLIENT_SECRET'])) {
    $issues[] = "GOOGLE_CLIENT_SECRET is empty or missing";
}

if (!isset($envVars['GOOGLE_REDIRECT']) || empty($envVars['GOOGLE_REDIRECT'])) {
    $issues[] = "GOOGLE_REDIRECT is empty or missing";
} elseif ($envVars['GOOGLE_REDIRECT'] !== 'https://hahucare.com/login/google/callback') {
    $issues[] = "GOOGLE_REDIRECT has wrong value: " . $envVars['GOOGLE_REDIRECT'];
}

if (count($issues) > 0) {
    echo "FOUND ISSUES:\n";
    foreach ($issues as $i => $issue) {
        echo "  " . ($i + 1) . ". {$issue}\n";
    }
} else {
    echo "All .env values look correct ✓\n";
}

// 3. Clear caches
echo "\n3. CLEARING CACHES\n";
echo "-------------------------------------------\n";

// Clear Laravel caches
\Illuminate\Support\Facades\Artisan::call('config:clear');
echo "config:clear - DONE\n";

\Illuminate\Support\Facades\Artisan::call('cache:clear');
echo "cache:clear - DONE\n";

\Illuminate\Support\Facades\Artisan::call('view:clear');
echo "view:clear - DONE\n";

// Clear OPcache if available
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "opcache_reset - DONE\n";
}

// 4. Test config after cache clear
echo "\n4. TESTING CONFIG\n";
echo "-------------------------------------------\n";

try {
    $config = config('services.google');
    echo "Config loaded successfully\n";
    echo "  Client ID: " . (isset($config['client_id']) ? substr($config['client_id'], 0, 30) . '...' : 'NOT SET') . "\n";
    echo "  Client Secret: " . (isset($config['client_secret']) ? substr($config['client_secret'], 0, 15) . '...' : 'NOT SET') . "\n";
    echo "  Redirect: " . ($config['redirect'] ?? 'NOT SET') . "\n";
} catch (\Exception $e) {
    echo "Config test FAILED: " . $e->getMessage() . "\n";
}

// 5. Test Socialite
echo "\n5. TESTING SOCIALITE\n";
echo "-------------------------------------------\n";

try {
    $driver = \Laravel\Socialite\Facades\Socialite::driver('google');
    echo "Socialite driver loaded ✓\n";
    
    // Try to generate redirect URL
    $redirectUrl = $driver->redirect()->getTargetUrl();
    echo "Redirect URL generated ✓\n";
    echo "URL: " . substr($redirectUrl, 0, 100) . "...\n";
    
    // Check if redirect_uri is correct
    if (strpos($redirectUrl, 'redirect_uri=') !== false) {
        preg_match('/redirect_uri=([^&]+)/', $redirectUrl, $matches);
        if (isset($matches[1])) {
            $redirectUri = urldecode($matches[1]);
            echo "Redirect URI: {$redirectUri}\n";
            
            $expected = 'https://hahucare.com/login/google/callback';
            if ($redirectUri === $expected) {
                echo "Redirect URI matches expected: YES ✓\n";
            } else {
                echo "Redirect URI matches expected: NO ✗\n";
                echo "Expected: {$expected}\n";
            }
        }
    }
} catch (\Exception $e) {
    echo "Socialite test FAILED: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

// 6. Check Laravel logs
echo "\n6. CHECKING LARAVEL LOGS\n";
echo "-------------------------------------------\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    // Read last 50 lines
    $lines = file($logFile);
    $lastLines = array_slice($lines, -50);
    
    $googleErrors = [];
    foreach ($lastLines as $line) {
        if (stripos($line, 'google') !== false || 
            stripos($line, 'social') !== false || 
            stripos($line, 'oauth') !== false ||
            stripos($line, 'socialite') !== false) {
            $googleErrors[] = trim($line);
        }
    }
    
    if (count($googleErrors) > 0) {
        echo "Found " . count($googleErrors) . " recent Google/OAuth log entries:\n";
        foreach ($googleErrors as $line) {
            echo "  " . substr($line, 0, 200) . "\n";
        }
    } else {
        echo "No recent Google/OAuth errors in logs\n";
    }
} else {
    echo "Laravel log file not found\n";
}

// 7. Manual test suggestion
echo "\n7. MANUAL TEST\n";
echo "-------------------------------------------\n";
echo "To test Google OAuth manually:\n";
echo "1. Visit: " . url('/login/google') . "\n";
echo "2. This should redirect to Google OAuth\n";
echo "3. After authentication, Google should redirect to:\n";
echo "   " . url('/login/google/callback') . "\n";
echo "4. Check Laravel logs if it fails\n";

// 8. API OAuth check
echo "\n8. API OAUTH CHECK\n";
echo "-------------------------------------------\n";
echo "For API users, check:\n";
echo "1. API endpoint: " . url('/api/auth/social-login') . "\n";
echo "2. Required fields: login_type=google, email, user_type\n";
echo "3. Google should return access_token that can be exchanged for user info\n";

echo "\n===========================================\n";
echo "   SUMMARY\n";
echo "===========================================\n";
if (count($issues) > 0) {
    echo "❌ Issues found - fix the .env variables first\n";
} else {
    echo "✅ Configuration looks correct\n";
    echo "If login still fails, check:\n";
    echo "1. Google Cloud Console redirect URI\n";
    echo "2. Laravel logs for actual errors\n";
    echo "3. OAuth consent screen configuration\n";
}
echo "\nDELETE THIS FILE: rm quick_google_fix.php\n";
echo "===========================================\n";
