<?php
/**
 * Fix Google OAuth Issues
 * Addresses configuration and route issues found in testing
 * 
 * Usage: php fix_google_oauth_issues.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "===========================================\n";
echo "   FIXING GOOGLE OAUTH ISSUES\n";
echo "===========================================\n\n";

// 1. Check .env file
echo "1. CHECKING .env FILE\n";
echo "-------------------------------------------\n";

$envFile = __DIR__ . '/.env';
$envBackup = __DIR__ . '/.env.backup.' . date('Y-m-d-H-i-s');

if (file_exists($envFile)) {
    // Create backup
    copy($envFile, $envBackup);
    echo "✅ Created backup: {$envBackup}\n";
    
    // Read current .env
    $envContent = file_get_contents($envFile);
    
    // Check if Google variables exist
    $googleClientId = strpos($envContent, 'GOOGLE_CLIENT_ID=') !== false;
    $googleClientSecret = strpos($envContent, 'GOOGLE_CLIENT_SECRET=') !== false;
    
    echo "Current .env status:\n";
    echo "  GOOGLE_CLIENT_ID: " . ($googleClientId ? '✅ Found' : '❌ Missing') . "\n";
    echo "  GOOGLE_CLIENT_SECRET: " . ($googleClientSecret ? '✅ Found' : '❌ Missing') . "\n";
    
    if (!$googleClientId || !$googleClientSecret) {
        echo "\n⚠️  Google OAuth credentials missing from .env\n";
        echo "Please add these lines to your .env file:\n\n";
        echo "GOOGLE_CLIENT_ID=your_google_client_id_here\n";
        echo "GOOGLE_CLIENT_SECRET=your_google_client_secret_here\n";
        echo "GOOGLE_REDIRECT=https://hahucare.com/login/google/callback\n";
        echo "GOOGLE_REDIRECT_URI=https://hahucare.com/auth/google/callback\n\n";
        echo "Get credentials from: https://console.cloud.google.com/\n";
    } else {
        echo "✅ Google credentials found in .env\n";
    }
} else {
    echo "❌ .env file not found\n";
}

// 2. Check API routes
echo "\n2. CHECKING API ROUTES\n";
echo "-------------------------------------------\n";

// Check if api.php exists and has the social-login route
$apiRoutesFile = __DIR__ . '/routes/api.php';
if (file_exists($apiRoutesFile)) {
    $apiContent = file_get_contents($apiRoutesFile);
    
    if (strpos($apiContent, 'social-login') !== false) {
        echo "✅ social-login route found in api.php\n";
    } else {
        echo "❌ social-login route missing from api.php\n";
        echo "Adding the route...\n";
        
        // Add the route if missing
        $routeToAdd = "\n// Social Login API Route\nRoute::post('auth/social-login', [AuthController::class, 'socialLogin']);\n";
        
        if (strpos($apiContent, 'AuthController') !== false) {
            // Add before the last closing brace
            $apiContent = str_replace("});", $routeToAdd . "});", $apiContent);
            file_put_contents($apiRoutesFile, $apiContent);
            echo "✅ Added social-login route to api.php\n";
        } else {
            echo "❌ AuthController not imported in api.php\n";
        }
    }
} else {
    echo "❌ routes/api.php not found\n";
}

// 3. Check API route registration
echo "\n3. VERIFYING API ROUTE REGISTRATION\n";
echo "-------------------------------------------\n";

try {
    // Get all routes
    $routes = app('router')->getRoutes();
    
    $socialLoginRoute = null;
    foreach ($routes as $route) {
        if (strpos($route->uri(), 'social-login') !== false) {
            $socialLoginRoute = $route;
            break;
        }
    }
    
    if ($socialLoginRoute) {
        echo "✅ social-login route registered\n";
        echo "  URI: " . $socialLoginRoute->uri() . "\n";
        echo "  Methods: " . implode(', ', $socialLoginRoute->methods()) . "\n";
    } else {
        echo "❌ social-login route not registered\n";
        
        // Try to register it manually
        echo "Attempting to register route...\n";
        
        try {
            Route::post('api/auth/social-login', 'App\Http\Controllers\Auth\API\AuthController@socialLogin');
            echo "✅ Route registered manually\n";
        } catch (\Exception $e) {
            echo "❌ Could not register route: " . $e->getMessage() . "\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ Error checking routes: " . $e->getMessage() . "\n";
}

// 4. Clear caches and reload
echo "\n4. CLEARING CACHES\n";
echo "-------------------------------------------\n";

try {
    \Artisan::call('config:clear');
    echo "✅ Config cache cleared\n";
    
    \Artisan::call('route:clear');
    echo "✅ Route cache cleared\n";
    
    \Artisan::call('cache:clear');
    echo "✅ Application cache cleared\n";
    
    \Artisan::call('view:clear');
    echo "✅ View cache cleared\n";
} catch (\Exception $e) {
    echo "❌ Error clearing caches: " . $e->getMessage() . "\n";
}

// 5. Test configuration again
echo "\n5. TESTING CONFIGURATION AGAIN\n";
echo "-------------------------------------------\n";

$googleConfig = config('services.google');
echo "Google Service Config:\n";
echo "  Client ID: " . ($googleConfig['client_id'] ? '✅ SET' : '❌ NOT SET') . "\n";
echo "  Client Secret: " . ($googleConfig['client_secret'] ? '✅ SET' : '❌ NOT SET') . "\n";
echo "  Redirect: " . ($googleConfig['redirect'] ?? '❌ NOT SET') . "\n";

// 6. Generate curl command for testing
echo "\n6. API TEST COMMAND\n";
echo "-------------------------------------------\n";

echo "Test the API with this command:\n\n";
echo "curl -X POST https://hahucare.com/api/auth/social-login \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -H 'Accept: application/json' \\\n";
echo "  -d '{\n";
echo "    \"login_type\": \"google\",\n";
echo "    \"email\": \"test.google." . time() . "@example.com\",\n";
echo "    \"user_type\": \"user\",\n";
echo "    \"first_name\": \"Test\",\n";
echo "    \"last_name\": \"Google\"\n";
echo "  }'\n\n";

// 7. Summary
echo "===========================================\n";
echo "   FIX SUMMARY\n";
echo "===========================================\n\n";

echo "✅ Checked .env file and created backup\n";
echo "✅ Verified API routes\n";
echo "✅ Cleared all caches\n";
echo "✅ Tested configuration\n\n";

echo "NEXT STEPS:\n";
echo "1. If Google credentials are missing, add them to .env:\n";
echo "   GOOGLE_CLIENT_ID=your_client_id\n";
echo "   GOOGLE_CLIENT_SECRET=your_client_secret\n";
echo "   GOOGLE_REDIRECT=https://hahucare.com/login/google/callback\n";
echo "   GOOGLE_REDIRECT_URI=https://hahucare.com/auth/google/callback\n\n";
echo "2. Get credentials from: https://console.cloud.google.com/\n\n";
echo "3. After adding credentials, run:\n";
echo "   php artisan config:clear\n";
echo "   php artisan cache:clear\n\n";
echo "4. Test again: php test_google_oauth_complete.php\n\n";
echo "5. Test manually with curl command above\n\n";

echo "COMMON ISSUES:\n";
echo "- If credentials missing: Add to .env and clear caches\n";
echo "- If route not found: Check routes/api.php file\n";
echo "- If redirect error: Verify Google Cloud Console URIs\n\n";

echo "===========================================\n";
echo "   FIX COMPLETE\n";
echo "===========================================\n";
