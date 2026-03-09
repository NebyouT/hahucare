<?php
/**
 * Complete Google OAuth Testing Script
 * Tests all three Google OAuth flows: Frontend Web, Backend Admin, and Mobile API
 * 
 * Usage: php test_google_oauth_complete.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\UserProvider;

echo "===========================================\n";
echo "   GOOGLE OAUTH COMPLETE TESTING\n";
echo "===========================================\n\n";

// Clear caches first
echo "1. CLEARING CACHES\n";
echo "-------------------------------------------\n";
\Artisan::call('cache:clear');
\Artisan::call('config:clear');
\Artisan::call('route:clear');
\Artisan::call('view:clear');
echo "✅ All caches cleared\n\n";

// Test Google OAuth Configuration
echo "2. GOOGLE OAUTH CONFIGURATION\n";
echo "-------------------------------------------\n";

$googleConfig = config('services.google');
echo "Google Service Config:\n";
echo "  Client ID: " . ($googleConfig['client_id'] ? '✅ SET' : '❌ NOT SET') . "\n";
echo "  Client Secret: " . ($googleConfig['client_secret'] ? '✅ SET' : '❌ NOT SET') . "\n";
echo "  Redirect: " . ($googleConfig['redirect'] ?? '❌ NOT SET') . "\n";

// Check environment variables
echo "\nEnvironment Variables:\n";
echo "  GOOGLE_CLIENT_ID: " . (env('GOOGLE_CLIENT_ID') ? '✅ SET' : '❌ NOT SET') . "\n";
echo "  GOOGLE_CLIENT_SECRET: " . (env('GOOGLE_CLIENT_SECRET') ? '✅ SET' : '❌ NOT SET') . "\n";
echo "  GOOGLE_REDIRECT: " . (env('GOOGLE_REDIRECT') ?: '❌ NOT SET') . "\n";
echo "  GOOGLE_REDIRECT_URI: " . (env('GOOGLE_REDIRECT_URI') ?: '❌ NOT SET') . "\n";

// Test Socialite driver
echo "\nSocialite Driver Test:\n";
try {
    $driver = \Laravel\Socialite\Facades\Socialite::driver('google');
    echo "  ✅ Socialite Google driver loaded\n";
    
    // Test redirect URL generation
    try {
        $redirectUrl = $driver->redirect()->getTargetUrl();
        echo "  ✅ Can generate redirect URL\n";
        echo "    Redirect URL: " . substr($redirectUrl, 0, 100) . "...\n";
    } catch (\Exception $e) {
        echo "  ❌ Cannot generate redirect URL\n";
        echo "    Error: " . $e->getMessage() . "\n";
    }
} catch (\Exception $e) {
    echo "  ❌ Socialite driver failed\n";
    echo "    Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test Routes
echo "3. ROUTE VERIFICATION\n";
echo "-------------------------------------------\n";

$routes = [
    'Frontend Google Login' => '/auth/google',
    'Frontend Google Callback' => '/auth/google/callback',
    'Backend Google Login' => '/login/google',
    'Backend Google Callback' => '/login/google/callback',
    'API Social Login' => '/api/auth/social-login',
];

foreach ($routes as $name => $url) {
    $exists = Route::has($url) || \Route::getRoutes()->match(\Illuminate\Http\Request::create($url));
    echo "  {$name}: " . ($exists ? '✅ Registered' : '❌ Not found') . "\n";
}

// Test database tables
echo "\n4. DATABASE VERIFICATION\n";
echo "-------------------------------------------\n";

// Check if users table exists and has required columns
try {
    $userColumns = \Schema::getColumnListing('users');
    echo "Users Table:\n";
    echo "  ✅ Table exists\n";
    echo "  Has login_type column: " . (in_array('login_type', $userColumns) ? '✅ Yes' : '❌ No') . "\n";
    echo "  Has user_type column: " . (in_array('user_type', $userColumns) ? '✅ Yes' : '❌ No') . "\n";
    echo "  Has email column: " . (in_array('email', $userColumns) ? '✅ Yes' : '❌ No') . "\n";
} catch (\Exception $e) {
    echo "Users Table: ❌ Error - " . $e->getMessage() . "\n";
}

// Check UserProvider table
try {
    $providerColumns = \Schema::getColumnListing('user_providers');
    echo "\nUserProvider Table:\n";
    echo "  ✅ Table exists\n";
    echo "  Has provider_id column: " . (in_array('provider_id', $providerColumns) ? '✅ Yes' : '❌ No') . "\n";
    echo "  Has provider column: " . (in_array('provider', $providerColumns) ? '✅ Yes' : '❌ No') . "\n";
    echo "  Has user_id column: " . (in_array('user_id', $providerColumns) ? '✅ Yes' : '❌ No') . "\n";
} catch (\Exception $e) {
    echo "UserProvider Table: ❌ Error - " . $e->getMessage() . "\n";
}

// Check existing Google users
echo "\n5. EXISTING GOOGLE USERS\n";
echo "-------------------------------------------\n";

$googleUsers = User::where('login_type', 'google')->get();
echo "Users with login_type='google': " . $googleUsers->count() . "\n";

if ($googleUsers->count() > 0) {
    echo "\nSample Google Users:\n";
    foreach ($googleUsers->take(3) as $user) {
        echo "  ID: {$user->id}, Email: {$user->email}, Type: {$user->user_type}\n";
    }
}

$providerUsers = UserProvider::where('provider', 'google')->get();
echo "\nUsers in UserProvider table: " . $providerUsers->count() . "\n";

if ($providerUsers->count() > 0) {
    echo "\nSample UserProvider entries:\n";
    foreach ($providerUsers->take(3) as $provider) {
        echo "  User ID: {$provider->user_id}, Provider ID: {$provider->provider_id}\n";
    }
}

// Test Controllers
echo "\n6. CONTROLLER VERIFICATION\n";
echo "-------------------------------------------\n";

// Test Frontend UserController
try {
    $frontendController = new \Modules\Frontend\Http\Controllers\Auth\UserController();
    echo "Frontend UserController: ✅ Can instantiate\n";
} catch (\Exception $e) {
    echo "Frontend UserController: ❌ Error - " . $e->getMessage() . "\n";
}

// Test Backend SocialLoginController
try {
    $backendController = new \App\Http\Controllers\Auth\SocialLoginController();
    echo "Backend SocialLoginController: ✅ Can instantiate\n";
} catch (\Exception $e) {
    echo "Backend SocialLoginController: ❌ Error - " . $e->getMessage() . "\n";
}

// Test API AuthController
try {
    $apiController = new \App\Http\Controllers\Auth\API\AuthController();
    echo "API AuthController: ✅ Can instantiate\n";
} catch (\Exception $e) {
    echo "API AuthController: ❌ Error - " . $e->getMessage() . "\n";
}

// Test CSRF Middleware
echo "\n7. CSRF MIDDLEWARE CHECK\n";
echo "-------------------------------------------\n";

$csrfMiddleware = new \App\Http\Middleware\VerifyCsrfToken();
$except = $csrfMiddleware->except ?? [];

echo "CSRF Exempt Routes:\n";
$exemptRoutes = [
    'login/*/callback',
    'auth/*/callback',
];

foreach ($exemptRoutes as $pattern) {
    $isExempt = false;
    foreach ($except as $exempted) {
        if (fnmatch($exempted, str_replace('*', 'google', $pattern))) {
            $isExempt = true;
            break;
        }
    }
    echo "  {$pattern}: " . ($isExempt ? '✅ Exempted' : '❌ Not exempted') . "\n";
}

// Generate test URLs
echo "\n8. TEST URLS\n";
echo "-------------------------------------------\n";

$baseUrl = config('app.url', 'https://hahucare.com');

$testUrls = [
    'Frontend Google Login' => $baseUrl . '/auth/google',
    'Frontend Google Callback' => $baseUrl . '/auth/google/callback',
    'Backend Google Login' => $baseUrl . '/login/google',
    'Backend Google Callback' => $baseUrl . '/login/google/callback',
    'API Social Login' => $baseUrl . '/api/auth/social-login',
];

echo "Test these URLs in your browser:\n";
foreach ($testUrls as $name => $url) {
    echo "  {$name}:\n";
    echo "    {$url}\n";
}

// Create test user data for API
echo "\n9. API TEST DATA\n";
echo "-------------------------------------------\n";

$testApiData = [
    'login_type' => 'google',
    'email' => 'test.google.' . time() . '@example.com',
    'user_type' => 'user',
    'first_name' => 'Test',
    'last_name' => 'Google',
];

echo "Test API Request Data:\n";
echo json_encode($testApiData, JSON_PRETTY_PRINT) . "\n";

echo "\nCurl Command for API Test:\n";
echo "curl -X POST {$testUrls['API Social Login']} \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -H 'Accept: application/json' \\\n";
echo "  -d '" . json_encode($testApiData) . "'\n";

// Check recent logs
echo "\n10. RECENT LOGS\n";
echo "-------------------------------------------\n";

$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $recentLogs = substr($logs, -2000); // Last 2KB
    
    // Look for Google-related logs
    $googleLogs = [];
    foreach (explode("\n", $recentLogs) as $line) {
        if (strpos($line, 'google') !== false || strpos($line, 'Google') !== false || strpos($line, 'OAuth') !== false) {
            $googleLogs[] = $line;
        }
    }
    
    if (!empty($googleLogs)) {
        echo "Recent Google/OAuth logs (last 10 entries):\n";
        foreach (array_slice($googleLogs, -10) as $log) {
            echo "  " . trim($log) . "\n";
        }
    } else {
        echo "No recent Google/OAuth logs found\n";
    }
} else {
    echo "Log file not found\n";
}

// Summary
echo "\n===========================================\n";
echo "   TESTING SUMMARY\n";
echo "===========================================\n\n";

echo "✅ Configuration checked\n";
echo "✅ Routes verified\n";
echo "✅ Database schema verified\n";
echo "✅ Controllers verified\n";
echo "✅ CSRF middleware checked\n";
echo "✅ Test URLs generated\n";
echo "✅ API test data prepared\n";
echo "✅ Recent logs reviewed\n\n";

echo "NEXT STEPS:\n";
echo "1. Open the test URLs in your browser\n";
echo "2. Test frontend flow: {$testUrls['Frontend Google Login']}\n";
echo "3. Test backend flow: {$testUrls['Backend Google Login']}\n";
echo "4. Test API flow with curl command above\n";
echo "5. Check logs: tail -f storage/logs/laravel.log | grep -i google\n";
echo "6. Verify users are created correctly in database\n\n";

echo "COMMON ISSUES:\n";
echo "- If credentials missing: Run php create_api_permissions.php\n";
echo "- If CSRF error: Check VerifyCsrfToken.php exemptions\n";
echo "- If redirect error: Verify Google Cloud Console URIs\n";
echo "- If no email: Use different Google account\n\n";

echo "===========================================\n";
echo "   TESTING COMPLETE\n";
echo "===========================================\n";
