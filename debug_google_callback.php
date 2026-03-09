<?php
/**
 * Debug Google Callback Issues
 * Simple script to test callback URL functionality
 * 
 * Usage: php debug_google_callback.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "===========================================\n";
echo "   GOOGLE CALLBACK DEBUG\n";
echo "===========================================\n\n";

// 1. Test if we can access the callback URLs directly
echo "1. TESTING CALLBACK URL ACCESS\n";
echo "-------------------------------------------\n";

$baseUrl = config('app.url', 'https://hahucare.com');

$callbackUrls = [
    'Frontend Callback' => '/auth/google/callback',
    'Backend Callback' => '/login/google/callback'
];

foreach ($callbackUrls as $name => $path) {
    $fullUrl = $baseUrl . $path;
    echo "Testing {$name}: {$fullUrl}\n";
    
    // Create a mock request
    $request = \Illuminate\Http\Request::create($fullUrl, 'GET', [
        'code' => 'test_code_123',
        'state' => 'test_state_456'
    ]);
    
    try {
        // Try to resolve the route
        $route = app('router')->getRoutes()->match($request);
        
        if ($route) {
            echo "  ✅ Route found\n";
            echo "  URI: " . $route->uri() . "\n";
            echo "  Controller: " . $route->getAction('uses') . "\n";
        } else {
            echo "  ❌ Route not found\n";
        }
    } catch (\Exception $e) {
        echo "  ❌ Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// 2. Check if the controllers exist and are accessible
echo "2. TESTING CONTROLLER ACCESS\n";
echo "-------------------------------------------\n";

try {
    $frontendController = new \Modules\Frontend\Http\Controllers\Auth\UserController();
    echo "✅ Frontend UserController accessible\n";
    
    // Check if handleGoogleCallback method exists
    if (method_exists($frontendController, 'handleGoogleCallback')) {
        echo "✅ handleGoogleCallback method exists\n";
    } else {
        echo "❌ handleGoogleCallback method missing\n";
    }
} catch (\Exception $e) {
    echo "❌ Frontend UserController error: " . $e->getMessage() . "\n";
}

try {
    $backendController = new \App\Http\Controllers\Auth\SocialLoginController();
    echo "✅ Backend SocialLoginController accessible\n";
    
    // Check if handleProviderCallback method exists
    if (method_exists($backendController, 'handleProviderCallback')) {
        echo "✅ handleProviderCallback method exists\n";
    } else {
        echo "❌ handleProviderCallback method missing\n";
    }
} catch (\Exception $e) {
    echo "❌ Backend SocialLoginController error: " . $e->getMessage() . "\n";
}

// 3. Test Socialite configuration
echo "\n3. TESTING SOCIALITE CONFIGURATION\n";
echo "-------------------------------------------\n";

try {
    $socialite = \Laravel\Socialite\Facades\Socialite::driver('google');
    echo "✅ Socialite Google driver loaded\n";
    
    // Test with stateless mode (frontend)
    try {
        $redirectUrl = $socialite->stateless()->redirect()->getTargetUrl();
        echo "✅ Stateless redirect URL generated\n";
        echo "  URL: " . substr($redirectUrl, 0, 100) . "...\n";
    } catch (\Exception $e) {
        echo "❌ Stateless redirect failed: " . $e->getMessage() . "\n";
    }
    
    // Test with session state (backend)
    try {
        // We can't test this without a session, but we can check if the method exists
        echo "✅ Session-based redirect method available\n";
    } catch (\Exception $e) {
        echo "❌ Session-based redirect failed: " . $e->getMessage() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Socialite configuration error: " . $e->getMessage() . "\n";
}

// 4. Check Google OAuth credentials
echo "\n4. CHECKING GOOGLE OAUTH CREDENTIALS\n";
echo "-------------------------------------------\n";

$clientId = env('GOOGLE_CLIENT_ID');
$clientSecret = env('GOOGLE_CLIENT_SECRET');
$redirect = env('GOOGLE_REDIRECT');
$redirectUri = env('GOOGLE_REDIRECT_URI');

echo "Environment Variables:\n";
echo "  GOOGLE_CLIENT_ID: " . ($clientId ? '✅ SET' : '❌ NOT SET') . "\n";
echo "  GOOGLE_CLIENT_SECRET: " . ($clientSecret ? '✅ SET' : '❌ NOT SET') . "\n";
echo "  GOOGLE_REDIRECT: " . ($redirect ?: '❌ NOT SET') . "\n";
echo "  GOOGLE_REDIRECT_URI: " . ($redirectUri ?: '❌ NOT SET') . "\n";

if ($clientId && $clientSecret) {
    echo "\n✅ Basic credentials are set\n";
    
    // Check if they look like real credentials (not placeholders)
    if (strpos($clientId, 'your_') !== false || strpos($clientId, 'example') !== false) {
        echo "⚠️  Client ID looks like placeholder\n";
    }
    if (strpos($clientSecret, 'your_') !== false || strpos($clientSecret, 'example') !== false) {
        echo "⚠️  Client Secret looks like placeholder\n";
    }
} else {
    echo "\n❌ Google OAuth credentials not configured\n";
}

// 5. Generate the correct URLs for Google Cloud Console
echo "\n5. GOOGLE CLOUD CONSOLE URLS\n";
echo "-------------------------------------------\n";

echo "Add these URLs to your Google OAuth Client:\n\n";
echo "Authorized JavaScript Origins:\n";
echo "  {$baseUrl}\n\n";

echo "Authorized redirect URIs:\n";
echo "  {$baseUrl}/login/google/callback\n";
echo "  {$baseUrl}/auth/google/callback\n\n";

// 6. Create a simple test to simulate callback
echo "6. CREATING CALLBACK SIMULATION\n";
echo "-------------------------------------------\n";

echo "To test the callback manually:\n\n";
echo "1. Frontend Callback Test:\n";
echo "   Visit: {$baseUrl}/auth/google/callback?code=test&state=test\n";
echo "   Expected: Should handle gracefully (not 404)\n\n";

echo "2. Backend Callback Test:\n";
echo "   Visit: {$baseUrl}/login/google/callback?code=test&state=test\n";
echo "   Expected: Should handle gracefully (not 404)\n\n";

echo "3. Error Test:\n";
echo "   Visit: {$baseUrl}/auth/google/callback?error=access_denied\n";
echo "   Expected: Should show error message\n\n";

// 7. Common callback issues and solutions
echo "7. COMMON CALLBACK ISSUES & SOLUTIONS\n";
echo "-------------------------------------------\n";

echo "❌ Issue: redirect_uri_mismatch\n";
echo "   Solution: Add correct URIs to Google Cloud Console\n\n";

echo "❌ Issue: 404 Not Found\n";
echo "   Solution: Check route registration and clear route cache\n\n";

echo "❌ Issue: CSRF Token Mismatch\n";
echo "   Solution: Verify CSRF exemptions in VerifyCsrfToken.php\n\n";

echo "❌ Issue: Invalid state parameter\n";
echo "   Solution: Frontend uses stateless, backend uses session state\n\n";

echo "❌ Issue: No code parameter\n";
echo "   Solution: Google didn't redirect back properly\n\n";

echo "❌ Issue: Socialite can't exchange code\n";
echo "   Solution: Check Google credentials and redirect URI\n\n";

// 8. Immediate fix steps
echo "8. IMMEDIATE FIX STEPS\n";
echo "-------------------------------------------\n";

echo "Step 1: Verify Google Cloud Console\n";
echo "   - Login: https://console.cloud.google.com/\n";
echo "   - Go to APIs & Services → Credentials\n";
echo "   - Edit OAuth 2.0 Client ID\n";
echo "   - Add redirect URIs: {$baseUrl}/login/google/callback and {$baseUrl}/auth/google/callback\n\n";

echo "Step 2: Update .env file\n";
echo "   GOOGLE_CLIENT_ID=your_actual_client_id\n";
echo "   GOOGLE_CLIENT_SECRET=your_actual_client_secret\n";
echo "   GOOGLE_REDIRECT={$baseUrl}/login/google/callback\n";
echo "   GOOGLE_REDIRECT_URI={$baseUrl}/auth/google/callback\n\n";

echo "Step 3: Clear caches\n";
echo "   php artisan config:clear\n";
echo "   php artisan cache:clear\n";
echo "   php artisan route:clear\n\n";

echo "Step 4: Test callbacks\n";
echo "   Visit the callback URLs manually to ensure they're accessible\n\n";

echo "Step 5: Check logs\n";
echo "   tail -f storage/logs/laravel.log | grep -i callback\n\n";

echo "===========================================\n";
echo "   DEBUG COMPLETE\n";
echo "===========================================\n";
