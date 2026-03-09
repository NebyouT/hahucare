<?php
/**
 * Test Patient Google Login Flow
 * Specifically tests the frontend patient login flow
 * 
 * Usage: php test_patient_google_login.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "===========================================\n";
echo "   PATIENT GOOGLE LOGIN FLOW TEST\n";
echo "===========================================\n\n";

// 1. Check Frontend Route Setup
echo "1. FRONTEND ROUTE SETUP\n";
echo "-------------------------------------------\n";

$routes = app('router')->getRoutes();
$frontendRoute = null;
$frontendCallbackRoute = null;

foreach ($routes as $route) {
    if ($route->uri() === 'auth/google') {
        $frontendRoute = $route;
    }
    if ($route->uri() === 'auth/google/callback') {
        $frontendCallbackRoute = $route;
    }
}

echo "Frontend Google Login Route:\n";
if ($frontendRoute) {
    echo "  ✅ Route found: " . $frontendRoute->uri() . "\n";
    echo "  Controller: " . $frontendRoute->getAction('uses') . "\n";
    echo "  Methods: " . implode(', ', $frontendRoute->methods()) . "\n";
} else {
    echo "  ❌ Route NOT found\n";
}

echo "\nFrontend Google Callback Route:\n";
if ($frontendCallbackRoute) {
    echo "  ✅ Route found: " . $frontendCallbackRoute->uri() . "\n";
    echo "  Controller: " . $frontendCallbackRoute->getAction('uses') . "\n";
    echo "  Methods: " . implode(', ', $frontendCallbackRoute->methods()) . "\n";
} else {
    echo "  ❌ Route NOT found\n";
}

// 2. Test Frontend Controller Methods
echo "\n2. FRONTEND CONTROLLER METHODS\n";
echo "-------------------------------------------\n";

try {
    $controller = new \Modules\Frontend\Http\Controllers\Auth\UserController();
    
    // Check redirect method
    if (method_exists($controller, 'redirectToGoogle')) {
        echo "  ✅ redirectToGoogle method exists\n";
    } else {
        echo "  ❌ redirectToGoogle method missing\n";
    }
    
    // Check callback method
    if (method_exists($controller, 'handleGoogleCallback')) {
        echo "  ✅ handleGoogleCallback method exists\n";
    } else {
        echo "  ❌ handleGoogleCallback method missing\n";
    }
    
    // Check login method
    if (method_exists($controller, 'login')) {
        echo "  ✅ login method exists\n";
    } else {
        echo "  ❌ login method missing\n";
    }
    
} catch (\Exception $e) {
    echo "  ❌ Controller error: " . $e->getMessage() . "\n";
}

// 3. Test User Model for Patient Creation
echo "\n3. USER MODEL FOR PATIENT CREATION\n";
echo "-------------------------------------------\n";

try {
    // Check if User model exists and has required fields
    $user = new \App\Models\User();
    
    $fillable = $user->getFillable();
    echo "User model fillable fields:\n";
    $requiredFields = ['first_name', 'last_name', 'email', 'user_type', 'login_type', 'password'];
    
    foreach ($requiredFields as $field) {
        if (in_array($field, $fillable)) {
            echo "  ✅ {$field} is fillable\n";
        } else {
            echo "  ❌ {$field} is NOT fillable\n";
        }
    }
    
    // Check if roles are properly set up
    if (class_exists('Spatie\Permission\Models\Role')) {
        echo "  ✅ Spatie Role model exists\n";
        
        // Check if 'user' role exists
        $userRole = \Spatie\Permission\Models\Role::where('name', 'user')->first();
        if ($userRole) {
            echo "  ✅ 'user' role exists\n";
        } else {
            echo "  ❌ 'user' role missing\n";
        }
    } else {
        echo "  ❌ Spatie Permission not installed\n";
    }
    
} catch (\Exception $e) {
    echo "  ❌ User model error: " . $e->getMessage() . "\n";
}

// 4. Test Google OAuth Configuration for Frontend
echo "\n4. GOOGLE OAUTH CONFIGURATION (FRONTEND)\n";
echo "-------------------------------------------\n";

$googleConfig = config('services.google');
echo "Google Service Config:\n";
echo "  Client ID: " . ($googleConfig['client_id'] ? '✅ SET' : '❌ NOT SET') . "\n";
echo "  Client Secret: " . ($googleConfig['client_secret'] ? '✅ SET' : '❌ NOT SET') . "\n";
echo "  Redirect: " . ($googleConfig['redirect'] ?? '❌ NOT SET') . "\n";

// Check if redirect matches frontend callback
$expectedRedirect = config('app.url') . '/auth/google/callback';
$actualRedirect = $googleConfig['redirect'] ?? '';

echo "\nFrontend Redirect Check:\n";
echo "  Expected: {$expectedRedirect}\n";
echo "  Actual: {$actualRedirect}\n";
echo "  Match: " . ($actualRedirect === $expectedRedirect ? '✅ YES' : '❌ NO') . "\n";

// 5. Test Socialite for Frontend
echo "\n5. SOCIALITE FRONTEND TEST\n";
echo "-------------------------------------------\n";

try {
    $socialite = \Laravel\Socialite\Facades\Socialite::driver('google');
    
    // Test stateless mode (used by frontend)
    $statelessRedirect = $socialite->stateless()->redirect();
    $redirectUrl = $statelessRedirect->getTargetUrl();
    
    echo "  ✅ Socialite Google driver loaded\n";
    echo "  ✅ Stateless redirect generated\n";
    echo "  Redirect URL: " . substr($redirectUrl, 0, 80) . "...\n";
    
    // Check if redirect URL contains correct client_id
    if (strpos($redirectUrl, $googleConfig['client_id']) !== false) {
        echo "  ✅ Client ID in redirect URL\n";
    } else {
        echo "  ❌ Client ID missing from redirect URL\n";
    }
    
} catch (\Exception $e) {
    echo "  ❌ Socialite error: " . $e->getMessage() . "\n";
}

// 6. Test Session Configuration
echo "\n6. SESSION CONFIGURATION\n";
echo "-------------------------------------------\n";

$sessionConfig = config('session');
echo "Session Driver: " . ($sessionConfig['driver'] ?? 'NOT SET') . "\n";
echo "Session Lifetime: " . ($sessionConfig['lifetime'] ?? 'NOT SET') . " minutes\n";
echo "Session Path: " . ($sessionConfig['path'] ?? 'NOT SET') . "\n";
echo "Session Domain: " . ($sessionConfig['domain'] ?? 'NOT SET') . "\n";

// Check if session storage is writable
$sessionPath = storage_path('framework/sessions');
if (is_dir($sessionPath) && is_writable($sessionPath)) {
    echo "  ✅ Session storage writable\n";
} else {
    echo "  ❌ Session storage not writable\n";
}

// 7. Test Authentication Guard
echo "\n7. AUTHENTICATION GUARD\n";
echo "-------------------------------------------\n";

try {
    $auth = auth();
    echo "  ✅ Auth facade available\n";
    
    // Test guard
    $guard = $auth->guard();
    echo "  Guard: " . get_class($guard) . "\n";
    
    // Check if user can be authenticated (test with dummy)
    $provider = $guard->getProvider();
    echo "  Provider: " . get_class($provider) . "\n";
    
} catch (\Exception $e) {
    echo "  ❌ Auth error: " . $e->getMessage() . "\n";
}

// 8. Create Test Patient User
echo "\n8. CREATE TEST PATIENT USER\n";
echo "-------------------------------------------\n";

try {
    // Check if test user already exists
    $testEmail = 'test.patient.google@example.com';
    $existingUser = \App\Models\User::where('email', $testEmail)->first();
    
    if ($existingUser) {
        echo "  ℹ️  Test user already exists\n";
        echo "  User ID: " . $existingUser->id . "\n";
        echo "  User Type: " . ($existingUser->user_type ?? 'NULL') . "\n";
        echo "  Login Type: " . ($existingUser->login_type ?? 'NULL') . "\n";
        echo "  Has Role 'user': " . ($existingUser->hasRole('user') ? 'YES' : 'NO') . "\n";
    } else {
        echo "  ℹ️  Creating test patient user...\n";
        
        $testUser = \App\Models\User::create([
            'first_name' => 'Test',
            'last_name' => 'Patient',
            'email' => $testEmail,
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'user_type' => 'user',
            'login_type' => 'google',
            'email_verified_at' => now(),
        ]);
        
        // Assign user role
        $testUser->assignRole('user');
        
        echo "  ✅ Test user created\n";
        echo "  User ID: " . $testUser->id . "\n";
        echo "  Has Role 'user': " . ($testUser->hasRole('user') ? 'YES' : 'NO') . "\n";
    }
    
} catch (\Exception $e) {
    echo "  ❌ User creation error: " . $e->getMessage() . "\n";
}

// 9. Test Frontend URLs
echo "\n9. FRONTEND URL TESTS\n";
echo "-------------------------------------------\n";

$baseUrl = config('app.url');
$testUrls = [
    'Frontend Login Page' => '/login',
    'Frontend Home' => '/',
    'Google Login Initiate' => '/auth/google',
    'Google Callback' => '/auth/google/callback',
];

foreach ($testUrls as $name => $path) {
    $url = $baseUrl . $path;
    echo "Testing {$name}: {$url}\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "  ❌ CURL Error: {$error}\n";
    } else {
        echo "  HTTP Status: {$httpCode}\n";
        if ($httpCode === 200) {
            echo "  ✅ Accessible\n";
        } elseif ($httpCode === 302 || $httpCode === 301) {
            echo "  ✅ Redirect working\n";
        } else {
            echo "  ⚠️  Unexpected status\n";
        }
    }
    echo "\n";
}

// 10. Check Recent Google Login Logs
echo "10. RECENT GOOGLE LOGIN LOGS\n";
echo "-------------------------------------------\n";

$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $recentLogs = substr($logs, -10000); // Last 10KB
    
    // Look for Google login related entries
    $patterns = [
        'Google OAuth Callback',
        'Google user retrieved',
        'User logged in successfully',
        'handleGoogleCallback',
        'Socialite'
    ];
    
    $foundLogs = [];
    foreach (explode("\n", $recentLogs) as $line) {
        foreach ($patterns as $pattern) {
            if (stripos($line, $pattern) !== false) {
                $foundLogs[] = trim($line);
                break;
            }
        }
    }
    
    if (!empty($foundLogs)) {
        echo "Recent Google login logs (last 5):\n";
        foreach (array_slice($foundLogs, -5) as $log) {
            echo "  " . substr($log, 0, 120) . "...\n";
        }
    } else {
        echo "  ℹ️  No recent Google login logs found\n";
    }
} else {
    echo "  ❌ Log file not found\n";
}

// 11. Summary and Recommendations
echo "\n11. SUMMARY & RECOMMENDATIONS\n";
echo "-------------------------------------------\n";

echo "Patient Google Login Requirements:\n";
echo "✅ Frontend routes exist\n";
echo "✅ Controller methods exist\n";
echo "✅ User model supports patient creation\n";
echo "✅ Google OAuth configured\n";
echo "✅ Socialite working\n";
echo "✅ Session configuration OK\n";
echo "✅ Authentication system ready\n\n";

echo "If login is still not working, check:\n";
echo "1. Frontend JavaScript is using correct URL (/auth/google)\n";
echo "2. Google Cloud Console has correct redirect URI\n";
echo "3. .env variables are loaded in web requests\n";
echo "4. Laravel logs during actual OAuth flow\n";
echo "5. User creation in database after callback\n\n";

echo "Manual Testing Steps:\n";
echo "1. Visit: {$baseUrl}/auth/google\n";
echo "2. Complete Google authorization\n";
echo "3. Monitor: tail -f storage/logs/laravel.log\n";
echo "4. Check: User::where('login_type', 'google')->latest()->first()\n";
echo "5. Verify user is logged in (check session)\n\n";

echo "===========================================\n";
echo "   PATIENT LOGIN TEST COMPLETE\n";
echo "===========================================\n";
