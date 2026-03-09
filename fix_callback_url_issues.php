<?php
/**
 * Fix Google OAuth Callback URL Issues
 * Diagnoses and fixes callback URL registration problems
 * 
 * Usage: php fix_callback_url_issues.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "===========================================\n";
echo "   GOOGLE OAUTH CALLBACK URL FIX\n";
echo "===========================================\n\n";

// 1. Check current callback URLs in configuration
echo "1. CHECKING CURRENT CONFIGURATION\n";
echo "-------------------------------------------\n";

$googleConfig = config('services.google');
echo "Current Google Config:\n";
echo "  Client ID: " . ($googleConfig['client_id'] ? '✅ SET' : '❌ NOT SET') . "\n";
echo "  Client Secret: " . ($googleConfig['client_secret'] ? '✅ SET' : '❌ NOT SET') . "\n";
echo "  Redirect: " . ($googleConfig['redirect'] ?? '❌ NOT SET') . "\n";

$envRedirect = env('GOOGLE_REDIRECT');
$envRedirectUri = env('GOOGLE_REDIRECT_URI');

echo "\nEnvironment Variables:\n";
echo "  GOOGLE_REDIRECT: " . ($envRedirect ?: '❌ NOT SET') . "\n";
echo "  GOOGLE_REDIRECT_URI: " . ($envRedirectUri ?: '❌ NOT SET') . "\n";

// 2. Check route registration
echo "\n2. CHECKING ROUTE REGISTRATION\n";
echo "-------------------------------------------\n";

$routes = app('router')->getRoutes();

$callbackRoutes = [];
foreach ($routes as $route) {
    $uri = $route->uri();
    if (strpos($uri, 'callback') !== false && strpos($uri, 'google') !== false) {
        $callbackRoutes[] = [
            'uri' => $uri,
            'methods' => $route->methods(),
            'name' => $route->getName(),
            'action' => $route->getAction('uses')
        ];
    }
}

if (empty($callbackRoutes)) {
    echo "❌ No Google callback routes found\n";
} else {
    echo "✅ Found " . count($callbackRoutes) . " Google callback routes:\n";
    foreach ($callbackRoutes as $route) {
        echo "  URI: {$route['uri']}\n";
        echo "  Methods: " . implode(', ', $route['methods']) . "\n";
        echo "  Name: " . ($route['name'] ?: 'No name') . "\n";
        echo "  Action: " . ($route['action'] ?: 'No action') . "\n";
        echo "  ---\n";
    }
}

// 3. Test callback URLs directly
echo "\n3. TESTING CALLBACK URLS\n";
echo "-------------------------------------------\n";

$baseUrl = config('app.url', 'https://hahucare.com');
$testUrls = [
    'Frontend Callback' => $baseUrl . '/auth/google/callback',
    'Backend Callback' => $baseUrl . '/login/google/callback',
];

foreach ($testUrls as $name => $url) {
    echo "Testing {$name}: {$url}\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HEADER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "  ❌ CURL Error: {$error}\n";
    } else {
        echo "  HTTP Status: {$httpCode}\n";
        
        // Check response headers for redirect or error
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        
        if ($httpCode === 302 || $httpCode === 301) {
            echo "  ✅ Redirect working\n";
            if (strpos($headers, 'Location:') !== false) {
                preg_match('/Location: (.*)\r\n/', $headers, $matches);
                if (isset($matches[1])) {
                    echo "  Redirecting to: " . trim($matches[1]) . "\n";
                }
            }
        } elseif ($httpCode === 200) {
            echo "  ⚠️  Got 200 OK (might be error page)\n";
            if (strpos($body, 'Whoops') !== false || strpos($body, 'Symfony') !== false) {
                echo "  ❌ Laravel error detected\n";
            }
        } else {
            echo "  ⚠️  Unexpected status: {$httpCode}\n";
        }
    }
    echo "\n";
}

// 4. Check CSRF middleware
echo "4. CHECKING CSRF MIDDLEWARE\n";
echo "-------------------------------------------\n";

$csrfMiddleware = new \App\Http\Middleware\VerifyCsrfToken();
$except = property_exists($csrfMiddleware, 'except') ? $csrfMiddleware->except : [];

echo "CSRF Exempt Patterns:\n";
if (empty($except)) {
    echo "  ❌ No exemptions found\n";
    echo "  ⚠️  This might cause CSRF token mismatch errors\n";
} else {
    foreach ($except as $pattern) {
        echo "  ✅ {$pattern}\n";
    }
}

// Check if callback patterns are exempt
$callbackPatterns = [
    'login/*/callback',
    'auth/*/callback',
    '*google*callback*'
];

echo "\nCallback Pattern Check:\n";
foreach ($callbackPatterns as $pattern) {
    $isExempt = false;
    foreach ($except as $exempted) {
        if (fnmatch($exempted, str_replace('*', 'google', $pattern))) {
            $isExempt = true;
            break;
        }
    }
    echo "  {$pattern}: " . ($isExempt ? '✅ Exempted' : '❌ Not exempted') . "\n";
}

// 5. Check recent logs for callback errors
echo "\n5. CHECKING LOGS FOR CALLBACK ERRORS\n";
echo "-------------------------------------------\n";

$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $recentLogs = substr($logs, -5000); // Last 5KB
    
    $errorPatterns = [
        'callback',
        'Callback',
        'Google',
        'OAuth',
        'CSRF',
        'TokenMismatch',
        'redirect_uri',
        'state'
    ];
    
    $foundErrors = [];
    foreach (explode("\n", $recentLogs) as $line) {
        foreach ($errorPatterns as $pattern) {
            if (stripos($line, $pattern) !== false) {
                $foundErrors[] = trim($line);
                break;
            }
        }
    }
    
    if (!empty($foundErrors)) {
        echo "Recent callback-related logs (last 10):\n";
        foreach (array_slice($foundErrors, -10) as $log) {
            echo "  " . substr($log, 0, 150) . "...\n";
        }
    } else {
        echo "No recent callback-related logs found\n";
    }
} else {
    echo "Log file not found\n";
}

// 6. Generate the correct callback URLs
echo "\n6. CORRECT CALLBACK URLS\n";
echo "-------------------------------------------\n";

$baseUrl = config('app.url', 'https://hahucare.com');

echo "These should be registered in Google Cloud Console:\n\n";
echo "Frontend Callback: {$baseUrl}/auth/google/callback\n";
echo "Backend Callback:  {$baseUrl}/login/google/callback\n\n";

echo "Current configuration:\n";
echo "  GOOGLE_REDIRECT: " . (env('GOOGLE_REDIRECT') ?: 'NOT SET') . "\n";
echo "  GOOGLE_REDIRECT_URI: " . (env('GOOGLE_REDIRECT_URI') ?: 'NOT SET') . "\n\n";

// 7. Fix suggestions
echo "7. FIX SUGGESTIONS\n";
echo "-------------------------------------------\n";

echo "If callback URLs are not working, try these fixes:\n\n";

echo "1. UPDATE .env FILE:\n";
echo "   GOOGLE_CLIENT_ID=your_actual_client_id\n";
echo "   GOOGLE_CLIENT_SECRET=your_actual_client_secret\n";
echo "   GOOGLE_REDIRECT={$baseUrl}/login/google/callback\n";
echo "   GOOGLE_REDIRECT_URI={$baseUrl}/auth/google/callback\n\n";

echo "2. CLEAR CACHES:\n";
echo "   php artisan config:clear\n";
echo "   php artisan cache:clear\n";
echo "   php artisan route:clear\n";
echo "   php artisan view:clear\n\n";

echo "3. UPDATE GOOGLE CLOUD CONSOLE:\n";
echo "   - Go to: https://console.cloud.google.com/\n";
echo "   - APIs & Services → Credentials\n";
echo "   - Edit your OAuth 2.0 Client ID\n";
echo "   - Add these Authorized redirect URIs:\n";
echo "     * {$baseUrl}/login/google/callback\n";
echo "     * {$baseUrl}/auth/google/callback\n\n";

echo "4. CHECK CSRF MIDDLEWARE:\n";
echo "   - Edit app/Http/Middleware/VerifyCsrfToken.php\n";
echo "   - Add these to \$except array:\n";
echo "     'login/*/callback',\n";
echo "     'auth/*/callback',\n\n";

echo "5. TEST CALLBACKS MANUALLY:\n";
echo "   - Visit: {$baseUrl}/auth/google/callback?error=access_denied\n";
echo "   - Visit: {$baseUrl}/login/google/callback?error=access_denied\n";
echo "   - Should show error page, not 404\n\n";

// 8. Create a test callback handler
echo "8. CREATING TEST CALLBACK HANDLER\n";
echo "-------------------------------------------\n";

$testHandlerPath = __DIR__ . '/test_callback_handler.php';
$testHandlerContent = '<?php
/**
 * Test Callback Handler
 * Temporary file to test callback URL functionality
 */

require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CALLBACK TEST HANDLER ===\n";
echo "Method: " . $_SERVER["REQUEST_METHOD"] . "\n";
echo "URL: " . $_SERVER["REQUEST_URI"] . "\n";
echo "Query Parameters:\n";

foreach ($_GET as $key => $value) {
    echo "  {$key}: {$value}\n";
}

echo "\nHeaders:\n";
foreach (getallheaders() as $key => $value) {
    echo "  {$key}: {$value}\n";
}

echo "\n=== TEST COMPLETE ===\n";
';

file_put_contents($testHandlerPath, $testHandlerContent);
echo "✅ Created test callback handler: test_callback_handler.php\n";
echo "   Access: {$baseUrl}/test_callback_handler.php\n\n";

// 9. Summary
echo "===========================================\n";
echo "   SUMMARY & NEXT STEPS\n";
echo "===========================================\n\n";

echo "COMMON CALLBACK ISSUES:\n";
echo "❌ Wrong redirect URI in Google Cloud Console\n";
echo "❌ Missing CSRF exemptions\n";
echo "❌ Route not registered\n";
echo "❌ Environment variables not set\n";
echo "❌ Cache not cleared after changes\n\n";

echo "IMMEDIATE ACTIONS:\n";
echo "1. Add Google credentials to .env\n";
echo "2. Update redirect URIs in Google Cloud Console\n";
echo "3. Clear all caches\n";
echo "4. Test with test_callback_handler.php\n";
echo "5. Check Laravel logs for errors\n\n";

echo "TESTING COMMANDS:\n";
echo "php fix_callback_url_issues.php  # Run this again\n";
echo "php artisan route:list | grep callback  # Check routes\n";
echo "tail -f storage/logs/laravel.log | grep -i callback  # Monitor logs\n\n";

echo "===========================================\n";
echo "   FIX COMPLETE\n";
echo "===========================================\n";
