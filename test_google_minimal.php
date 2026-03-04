<?php
/**
 * Minimal Google OAuth Test - No cache clearing
 * Run via SSH: php test_google_minimal.php
 * DELETE THIS FILE AFTER RUNNING!
 */

echo "===========================================\n";
echo "   MINIMAL GOOGLE OAUTH TEST\n";
echo "===========================================\n\n";

// 1. Check .env
echo "1. .env VALUES:\n";
$envFile = __DIR__ . '/.env';
$envContent = file_get_contents($envFile);

if (preg_match('/^GOOGLE_CLIENT_ID=(.*)$/m', $envContent, $m)) {
    echo "  CLIENT_ID: " . (strlen($m[1]) > 0 ? substr($m[1], 0, 30) . '...' : 'EMPTY') . "\n";
}
if (preg_match('/^GOOGLE_CLIENT_SECRET=(.*)$/m', $envContent, $m)) {
    echo "  CLIENT_SECRET: " . (strlen($m[1]) > 0 ? substr($m[1], 0, 15) . '...' : 'EMPTY') . "\n";
}
if (preg_match('/^GOOGLE_REDIRECT=(.*)$/m', $envContent, $m)) {
    echo "  REDIRECT: " . $m[1] . "\n";
}

// 2. Check config
echo "\n2. CONFIG VALUES:\n";
$config = config('services.google');
echo "  CLIENT_ID: " . (isset($config['client_id']) ? substr($config['client_id'], 0, 30) . '...' : 'NOT SET') . "\n";
echo "  CLIENT_SECRET: " . (isset($config['client_secret']) ? substr($config['client_secret'], 0, 15) . '...' : 'NOT SET') . "\n";
echo "  REDIRECT: " . ($config['redirect'] ?? 'NOT SET') . "\n";

// 3. Test Socialite
echo "\n3. SOCIALITE TEST:\n";
try {
    $driver = \Laravel\Socialite\Facades\Socialite::driver('google');
    echo "  Driver loaded: YES\n";
    
    $redirectUrl = $driver->redirect()->getTargetUrl();
    echo "  Redirect URL: " . substr($redirectUrl, 0, 100) . "...\n";
    
    // Extract redirect_uri
    if (preg_match('/redirect_uri=([^&]+)/', $redirectUrl, $m)) {
        $redirectUri = urldecode($m[1]);
        echo "  Redirect URI: {$redirectUri}\n";
        
        $expected = 'https://hahucare.com/login/google/callback';
        echo "  Matches expected: " . ($redirectUri === $expected ? 'YES ✓' : 'NO ✗') . "\n";
    }
} catch (\Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// 4. Check routes
echo "\n4. ROUTES:\n";
echo "  Login URL: " . url('/login/google') . "\n";
echo "  Callback URL: " . url('/login/google/callback') . "\n";

// 5. Check logs (last 20 lines)
echo "\n5. RECENT LOGS (Google/OAuth):\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $lines = array_slice(file($logFile), -20);
    foreach ($lines as $line) {
        if (stripos($line, 'google') !== false || 
            stripos($line, 'social') !== false || 
            stripos($line, 'oauth') !== false) {
            echo "  " . trim(substr($line, 0, 200)) . "\n";
        }
    }
} else {
    echo "  Log file not found\n";
}

echo "\n===========================================\n";
echo "DELETE THIS FILE: rm test_google_minimal.php\n";
echo "===========================================\n";
