<?php
/**
 * Diagnose Google OAuth Login Issues
 * Run via SSH: php diagnose_google_oauth.php
 * DELETE THIS FILE AFTER RUNNING!
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "===========================================\n";
echo "   GOOGLE OAUTH DIAGNOSTIC\n";
echo "===========================================\n\n";

// 1. Check environment variables
echo "1. ENVIRONMENT VARIABLES\n";
echo "-------------------------------------------\n";
$googleClientId = env('GOOGLE_CLIENT_ID');
$googleClientSecret = env('GOOGLE_CLIENT_SECRET');
$googleRedirect = env('GOOGLE_REDIRECT');
$googleRedirectUri = env('GOOGLE_REDIRECT_URI');

echo "GOOGLE_CLIENT_ID: " . ($googleClientId ? substr($googleClientId, 0, 30) . '...' : 'NOT SET') . "\n";
echo "GOOGLE_CLIENT_SECRET: " . ($googleClientSecret ? substr($googleClientSecret, 0, 15) . '...' : 'NOT SET') . "\n";
echo "GOOGLE_REDIRECT: " . ($googleRedirect ?: 'NOT SET') . "\n";
echo "GOOGLE_REDIRECT_URI: " . ($googleRedirectUri ?: 'NOT SET') . "\n";

// 2. Check config
echo "\n2. CONFIG VALUES\n";
echo "-------------------------------------------\n";
$configGoogle = config('services.google');
echo "config('services.google.client_id'): " . (isset($configGoogle['client_id']) ? substr($configGoogle['client_id'], 0, 30) . '...' : 'NOT SET') . "\n";
echo "config('services.google.client_secret'): " . (isset($configGoogle['client_secret']) ? substr($configGoogle['client_secret'], 0, 15) . '...' : 'NOT SET') . "\n";
echo "config('services.google.redirect'): " . ($configGoogle['redirect'] ?? 'NOT SET') . "\n";

// 3. Check routes
echo "\n3. ROUTES\n";
echo "-------------------------------------------\n";
try {
    $loginRoute = route('social.login', 'google');
    echo "Login route: {$loginRoute}\n";
} catch (\Exception $e) {
    echo "Login route ERROR: " . $e->getMessage() . "\n";
}

try {
    $callbackRoute = route('social.login.callback', 'google');
    echo "Callback route: {$callbackRoute}\n";
} catch (\Exception $e) {
    echo "Callback route ERROR: " . $e->getMessage() . "\n";
}

// 4. Check if routes are registered
echo "\n4. ROUTE REGISTRATION\n";
echo "-------------------------------------------\n";
$routes = \Illuminate\Support\Facades\Route::getRoutes();
$socialRoutes = [];
foreach ($routes as $route) {
    $name = $route->getName();
    if ($name && (strpos($name, 'social') !== false || strpos($name, 'google') !== false)) {
        $socialRoutes[] = [
            'name' => $name,
            'uri' => $route->uri(),
            'method' => implode('|', $route->methods()),
            'action' => $route->getActionName(),
        ];
    }
}

if (count($socialRoutes) > 0) {
    echo "Found " . count($socialRoutes) . " social login routes:\n";
    foreach ($socialRoutes as $r) {
        echo "  [{$r['method']}] {$r['uri']} -> {$r['name']}\n";
        echo "      Action: {$r['action']}\n";
    }
} else {
    echo "NO social login routes found!\n";
}

// 5. Check SocialLoginController
echo "\n5. SOCIALLOGINCONTROLLER\n";
echo "-------------------------------------------\n";
$controllerFile = __DIR__ . '/app/Http/Controllers/Auth/SocialLoginController.php';
if (file_exists($controllerFile)) {
    echo "Controller exists: YES\n";
    $content = file_get_contents($controllerFile);
    
    // Check for error logging
    $hasErrorLog = strpos($content, 'Log::error') !== false;
    echo "Has error logging: " . ($hasErrorLog ? 'YES' : 'NO') . "\n";
    
    // Check handleProviderCallback method
    if (preg_match('/public function handleProviderCallback.*?\{(.*?)\n    \}/s', $content, $matches)) {
        $method = $matches[0];
        $hasTryCatch = strpos($method, 'try {') !== false && strpos($method, 'catch') !== false;
        echo "handleProviderCallback has try-catch: " . ($hasTryCatch ? 'YES' : 'NO') . "\n";
        
        // Check if it logs errors
        $logErrors = strpos($method, 'Log::error') !== false;
        echo "handleProviderCallback logs errors: " . ($logErrors ? 'YES' : 'NO') . "\n";
    }
} else {
    echo "Controller exists: NO - FILE NOT FOUND!\n";
}

// 6. Check Laravel logs for Google errors
echo "\n6. LARAVEL LOGS (Google/OAuth errors)\n";
echo "-------------------------------------------\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logSize = filesize($logFile);
    echo "Log file size: " . number_format($logSize) . " bytes\n";
    
    // Read last 100KB
    $fh = fopen($logFile, 'r');
    $readSize = min($logSize, 100000);
    fseek($fh, -$readSize, SEEK_END);
    $logContent = fread($fh, $readSize);
    fclose($fh);
    
    $lines = explode("\n", $logContent);
    $googleErrors = [];
    
    foreach ($lines as $line) {
        if (stripos($line, 'google') !== false || 
            stripos($line, 'social') !== false || 
            stripos($line, 'oauth') !== false || 
            stripos($line, 'socialite') !== false ||
            stripos($line, 'redirect') !== false) {
            $googleErrors[] = $line;
        }
    }
    
    if (count($googleErrors) > 0) {
        echo "\nFound " . count($googleErrors) . " relevant log entries (showing last 20):\n";
        foreach (array_slice($googleErrors, -20) as $line) {
            echo "  " . trim(substr($line, 0, 300)) . "\n";
        }
    } else {
        echo "\nNo Google/OAuth related errors found in logs\n";
    }
} else {
    echo "Laravel log file not found!\n";
}

// 7. Test Socialite driver
echo "\n7. TEST SOCIALITE DRIVER\n";
echo "-------------------------------------------\n";
try {
    $driver = \Laravel\Socialite\Facades\Socialite::driver('google');
    echo "Socialite Google driver: LOADED\n";
    
    // Try to get redirect URL
    try {
        $redirectUrl = $driver->redirect()->getTargetUrl();
        echo "Redirect URL generated: YES\n";
        echo "URL: " . substr($redirectUrl, 0, 100) . "...\n";
        
        // Check if it contains the correct redirect_uri
        if (strpos($redirectUrl, 'redirect_uri=') !== false) {
            preg_match('/redirect_uri=([^&]+)/', $redirectUrl, $matches);
            if (isset($matches[1])) {
                $redirectUri = urldecode($matches[1]);
                echo "Redirect URI in OAuth URL: {$redirectUri}\n";
                
                $expectedUri = route('social.login.callback', 'google');
                if ($redirectUri === $expectedUri) {
                    echo "Redirect URI matches route: YES ✓\n";
                } else {
                    echo "Redirect URI matches route: NO ✗\n";
                    echo "  Expected: {$expectedUri}\n";
                    echo "  Got: {$redirectUri}\n";
                }
            }
        }
    } catch (\Exception $e) {
        echo "Redirect URL generation FAILED: " . $e->getMessage() . "\n";
    }
} catch (\Exception $e) {
    echo "Socialite driver ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

// 8. Check Google Cloud Console requirements
echo "\n8. GOOGLE CLOUD CONSOLE CHECKLIST\n";
echo "-------------------------------------------\n";
echo "In Google Cloud Console, you MUST have:\n";
echo "1. OAuth 2.0 Client ID created\n";
echo "2. Authorized JavaScript origins:\n";
echo "   - https://hahucare.com\n";
echo "3. Authorized redirect URIs:\n";
try {
    $callbackUrl = route('social.login.callback', 'google');
    echo "   - {$callbackUrl}\n";
} catch (\Exception $e) {
    echo "   - ERROR: Could not generate callback URL\n";
}
echo "\n4. OAuth consent screen configured\n";
echo "5. API enabled: Google+ API or People API\n";

// 9. Simulate a login attempt
echo "\n9. SIMULATE LOGIN FLOW\n";
echo "-------------------------------------------\n";
echo "Step 1: User clicks 'Login with Google'\n";
try {
    $loginUrl = route('social.login', 'google');
    echo "  Redirects to: {$loginUrl}\n";
    echo "  This should redirect to Google OAuth\n";
} catch (\Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}

echo "\nStep 2: Google redirects back to callback\n";
try {
    $callbackUrl = route('social.login.callback', 'google');
    echo "  Callback URL: {$callbackUrl}\n";
    echo "  This URL must be in Google Cloud Console!\n";
} catch (\Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}

// 10. Check for common issues
echo "\n10. COMMON ISSUES CHECK\n";
echo "-------------------------------------------\n";

$issues = [];

if (!$googleClientId) {
    $issues[] = "GOOGLE_CLIENT_ID is not set in .env";
}

if (!$googleClientSecret) {
    $issues[] = "GOOGLE_CLIENT_SECRET is not set in .env";
}

if (!$googleRedirect && !$googleRedirectUri) {
    $issues[] = "Neither GOOGLE_REDIRECT nor GOOGLE_REDIRECT_URI is set in .env";
}

if ($googleRedirect && strpos($googleRedirect, 'http://') === 0) {
    $issues[] = "GOOGLE_REDIRECT uses HTTP instead of HTTPS";
}

if ($googleRedirect && strpos($googleRedirect, '/app/auth/') !== false) {
    $issues[] = "GOOGLE_REDIRECT has wrong path (/app/auth/ instead of /login/)";
}

$expectedCallback = 'https://hahucare.com/login/google/callback';
if ($googleRedirect && $googleRedirect !== $expectedCallback) {
    $issues[] = "GOOGLE_REDIRECT doesn't match expected: {$expectedCallback}";
}

if (count($issues) > 0) {
    echo "FOUND " . count($issues) . " ISSUES:\n";
    foreach ($issues as $i => $issue) {
        echo "  " . ($i + 1) . ". {$issue}\n";
    }
} else {
    echo "No obvious configuration issues found\n";
}

echo "\n===========================================\n";
echo "   NEXT STEPS\n";
echo "===========================================\n";
echo "1. Fix any issues listed above\n";
echo "2. Ensure Google Cloud Console redirect URI matches:\n";
try {
    echo "   " . route('social.login.callback', 'google') . "\n";
} catch (\Exception $e) {
    echo "   ERROR generating URL\n";
}
echo "3. Try logging in with Google\n";
echo "4. Check storage/logs/laravel.log for errors\n";
echo "5. DELETE THIS FILE: rm diagnose_google_oauth.php\n";
echo "===========================================\n";
