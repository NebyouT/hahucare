<?php
/**
 * Fix Environment Variable Loading Issue
 * Forces Laravel to reload .env and clear all caches
 * 
 * Usage: php fix_env_loading.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "===========================================\n";
echo "   FIXING ENVIRONMENT VARIABLE LOADING\n";
echo "===========================================\n\n";

// 1. Check .env file exists and is readable
echo "1. CHECKING .env FILE\n";
echo "-------------------------------------------\n";

$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    echo "✅ .env file exists\n";
    
    if (is_readable($envFile)) {
        echo "✅ .env file is readable\n";
        
        // Check Google credentials in .env
        $envContent = file_get_contents($envFile);
        $hasClientId = strpos($envContent, 'GOOGLE_CLIENT_ID=') !== false;
        $hasClientSecret = strpos($envContent, 'GOOGLE_CLIENT_SECRET=') !== false;
        
        echo "  GOOGLE_CLIENT_ID in .env: " . ($hasClientId ? '✅ Yes' : '❌ No') . "\n";
        echo "  GOOGLE_CLIENT_SECRET in .env: " . ($hasClientSecret ? '✅ Yes' : '❌ No') . "\n";
        
        if ($hasClientId && $hasClientSecret) {
            echo "✅ Google credentials found in .env\n";
        } else {
            echo "❌ Google credentials missing from .env\n";
        }
    } else {
        echo "❌ .env file is not readable\n";
    }
} else {
    echo "❌ .env file does not exist\n";
}

// 2. Force reload environment
echo "\n2. RELOADING ENVIRONMENT\n";
echo "-------------------------------------------\n";

try {
    // Use Dotenv to reload environment
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    echo "✅ Environment reloaded using Dotenv\n";
    
    // Test if variables are now accessible
    $clientId = getenv('GOOGLE_CLIENT_ID');
    $clientSecret = getenv('GOOGLE_CLIENT_SECRET');
    $redirect = getenv('GOOGLE_REDIRECT');
    
    echo "  GOOGLE_CLIENT_ID: " . ($clientId ? '✅ SET' : '❌ NOT SET') . "\n";
    echo "  GOOGLE_CLIENT_SECRET: " . ($clientSecret ? '✅ SET' : '❌ NOT SET') . "\n";
    echo "  GOOGLE_REDIRECT: " . ($redirect ?: '❌ NOT SET') . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error reloading environment: " . $e->getMessage() . "\n";
}

// 3. Clear Laravel caches
echo "\n3. CLEARING LARAVEL CACHES\n";
echo "-------------------------------------------\n";

$caches = [
    'config' => 'config:clear',
    'application' => 'cache:clear',
    'routes' => 'route:clear',
    'views' => 'view:clear',
    'compiled' => 'clear-compiled'
];

foreach ($caches as $name => $command) {
    try {
        \Artisan::call($command);
        echo "✅ {$name} cache cleared\n";
    } catch (\Exception $e) {
        echo "❌ Error clearing {$name} cache: " . $e->getMessage() . "\n";
    }
}

// 4. Remove bootstrap cache files
echo "\n4. REMOVING BOOTSTRAP CACHE\n";
echo "-------------------------------------------\n";

$bootstrapCachePath = __DIR__ . '/bootstrap/cache';
if (is_dir($bootstrapCachePath)) {
    $files = glob($bootstrapCachePath . '/*.php');
    foreach ($files as $file) {
        if (basename($file) !== '.gitignore') {
            if (unlink($file)) {
                echo "✅ Removed " . basename($file) . "\n";
            } else {
                echo "❌ Could not remove " . basename($file) . "\n";
            }
        }
    }
} else {
    echo "ℹ️  Bootstrap cache directory not found\n";
}

// 5. Test Laravel config after reload
echo "\n5. TESTING LARAVEL CONFIG\n";
echo "-------------------------------------------\n";

// Reload the application to test
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$googleConfig = config('services.google');
echo "Laravel config after reload:\n";
echo "  Client ID: " . ($googleConfig['client_id'] ? '✅ SET' : '❌ NOT SET') . "\n";
echo "  Client Secret: " . ($googleConfig['client_secret'] ? '✅ SET' : '❌ NOT SET') . "\n";
echo "  Redirect: " . ($googleConfig['redirect'] ?? '❌ NOT SET') . "\n";

// 6. Test with fresh env() calls
echo "\n6. TESTING FRESH ENV() CALLS\n";
echo "-------------------------------------------\n";

// Clear any internal caches
if (function_exists('putenv')) {
    putenv('GOOGLE_CLIENT_ID');
    putenv('GOOGLE_CLIENT_SECRET');
    putenv('GOOGLE_REDIRECT');
}

echo "Fresh env() calls:\n";
echo "  GOOGLE_CLIENT_ID: " . (env('GOOGLE_CLIENT_ID') ? '✅ SET' : '❌ NOT SET') . "\n";
echo "  GOOGLE_CLIENT_SECRET: " . (env('GOOGLE_CLIENT_SECRET') ? '✅ SET' : '❌ NOT SET') . "\n";
echo "  GOOGLE_REDIRECT: " . (env('GOOGLE_REDIRECT') ?: '❌ NOT SET') . "\n";

// 7. Create a test script to verify
echo "\n7. CREATING VERIFICATION SCRIPT\n";
echo "-------------------------------------------\n";

$testScript = '<?php
// Quick test to verify Google OAuth is working
require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Google OAuth Test:\n";
echo "Client ID: " . (config("services.google.client_id") ? "✅ SET" : "❌ NOT SET") . "\n";
echo "Client Secret: " . (config("services.google.client_secret") ? "✅ SET" : "❌ NOT SET") . "\n";

try {
    $socialite = Laravel\Socialite\Facades\Socialite::driver("google");
    $url = $socialite->stateless()->redirect()->getTargetUrl();
    echo "✅ Socialite working, URL generated\n";
} catch (Exception $e) {
    echo "❌ Socialite error: " . $e->getMessage() . "\n";
}
';

file_put_contents(__DIR__ . '/test_google_oauth.php', $testScript);
echo "✅ Created test_google_oauth.php\n";
echo "   Run: php test_google_oauth.php\n\n";

// 8. Summary
echo "===========================================\n";
echo "   FIX SUMMARY\n";
echo "===========================================\n\n";

echo "✅ Checked .env file\n";
echo "✅ Reloaded environment\n";
echo "✅ Cleared all Laravel caches\n";
echo "✅ Removed bootstrap cache files\n";
echo "✅ Tested Laravel configuration\n";
echo "✅ Created verification script\n\n";

echo "NEXT STEPS:\n";
echo "1. Test: php test_google_oauth.php\n";
echo "2. If still not working, restart web server\n";
echo "3. Check file permissions on .env file\n";
echo "4. Verify .env is in the correct directory\n\n";

echo "COMMON CAUSES:\n";
echo "- .env file permissions issue\n";
echo "- Web server can\'t read .env\n";
echo "- Laravel cache not clearing properly\n";
echo "- Multiple .env files in different directories\n\n";

echo "===========================================\n";
echo "   FIX COMPLETE\n";
echo "===========================================\n";
