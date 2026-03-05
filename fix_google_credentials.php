<?php
/**
 * Fix Google OAuth Credentials
 * This script checks and fixes Google OAuth credentials in .env
 */

echo "===========================================\n";
echo "   GOOGLE OAUTH CREDENTIALS FIX\n";
echo "===========================================\n\n";

$envFile = __DIR__ . '/.env';

if (!file_exists($envFile)) {
    echo "❌ .env file not found at: {$envFile}\n";
    exit(1);
}

echo "1. READING .ENV FILE\n";
echo "-------------------------------------------\n";

$envContent = file_get_contents($envFile);
$envLines = file($envFile, FILE_IGNORE_NEW_LINES);

// Parse current values
$currentValues = [];
foreach ($envLines as $line) {
    if (empty(trim($line)) || strpos(trim($line), '#') === 0) {
        continue;
    }
    
    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        $currentValues[$key] = $value;
    }
}

$googleKeys = [
    'GOOGLE_CLIENT_ID',
    'GOOGLE_CLIENT_SECRET',
    'GOOGLE_REDIRECT',
    'GOOGLE_REDIRECT_URI',
];

echo "Current Google credentials in .env:\n";
foreach ($googleKeys as $key) {
    $value = $currentValues[$key] ?? 'NOT SET';
    $display = ($value === 'NOT SET' || empty($value)) ? '❌ NOT SET' : '✅ SET (' . substr($value, 0, 20) . '...)';
    echo "  {$key}: {$display}\n";
}

echo "\n2. CHECKING WHAT'S MISSING\n";
echo "-------------------------------------------\n";

$missing = [];
$needsUpdate = false;

foreach ($googleKeys as $key) {
    if (!isset($currentValues[$key]) || empty($currentValues[$key])) {
        $missing[] = $key;
        $needsUpdate = true;
    }
}

if (empty($missing)) {
    echo "✅ All Google credentials are set in .env\n";
} else {
    echo "❌ Missing credentials:\n";
    foreach ($missing as $key) {
        echo "  - {$key}\n";
    }
}

echo "\n3. INSTRUCTIONS TO FIX\n";
echo "-------------------------------------------\n";
echo "You need to add these values to your .env file:\n\n";

if (in_array('GOOGLE_CLIENT_ID', $missing)) {
    echo "GOOGLE_CLIENT_ID=your_google_client_id_here\n";
}
if (in_array('GOOGLE_CLIENT_SECRET', $missing)) {
    echo "GOOGLE_CLIENT_SECRET=your_google_client_secret_here\n";
}
if (in_array('GOOGLE_REDIRECT', $missing)) {
    echo "GOOGLE_REDIRECT=https://hahucare.com/login/google/callback\n";
}
if (in_array('GOOGLE_REDIRECT_URI', $missing)) {
    echo "GOOGLE_REDIRECT_URI=https://hahucare.com/auth/google/callback\n";
}

echo "\n4. WHERE TO GET CREDENTIALS\n";
echo "-------------------------------------------\n";
echo "1. Go to: https://console.cloud.google.com/\n";
echo "2. Select your project (or create one)\n";
echo "3. Go to: APIs & Services > Credentials\n";
echo "4. Find your OAuth 2.0 Client ID\n";
echo "5. Copy the Client ID and Client Secret\n";
echo "6. Make sure these redirect URIs are added:\n";
echo "   - https://hahucare.com/login/google/callback\n";
echo "   - https://hahucare.com/auth/google/callback\n";

echo "\n5. HOW TO UPDATE .ENV\n";
echo "-------------------------------------------\n";
echo "Option 1 - Manual edit:\n";
echo "  nano .env\n";
echo "  (Add the missing lines, save with Ctrl+X, Y, Enter)\n\n";

echo "Option 2 - Use this command:\n";
echo "  cat >> .env << 'EOF'\n";
if (in_array('GOOGLE_CLIENT_ID', $missing)) {
    echo "GOOGLE_CLIENT_ID=YOUR_CLIENT_ID_HERE\n";
}
if (in_array('GOOGLE_CLIENT_SECRET', $missing)) {
    echo "GOOGLE_CLIENT_SECRET=YOUR_CLIENT_SECRET_HERE\n";
}
if (in_array('GOOGLE_REDIRECT', $missing)) {
    echo "GOOGLE_REDIRECT=https://hahucare.com/login/google/callback\n";
}
if (in_array('GOOGLE_REDIRECT_URI', $missing)) {
    echo "GOOGLE_REDIRECT_URI=https://hahucare.com/auth/google/callback\n";
}
echo "EOF\n";

echo "\n6. AFTER UPDATING .ENV\n";
echo "-------------------------------------------\n";
echo "Run these commands to reload the configuration:\n";
echo "  php artisan config:clear\n";
echo "  php artisan cache:clear\n";
echo "  php artisan config:cache\n";

echo "\n7. VERIFY CREDENTIALS ARE LOADED\n";
echo "-------------------------------------------\n";
echo "After updating .env and clearing cache, run:\n";
echo "  php verify_google_config.php\n";

echo "\n===========================================\n";

// Create verification script
$verifyScript = <<<'PHP'
<?php
/**
 * Verify Google OAuth Configuration
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "===========================================\n";
echo "   GOOGLE OAUTH CONFIG VERIFICATION\n";
echo "===========================================\n\n";

echo "1. ENV FILE VALUES\n";
echo "-------------------------------------------\n";
$envVars = [
    'GOOGLE_CLIENT_ID' => env('GOOGLE_CLIENT_ID'),
    'GOOGLE_CLIENT_SECRET' => env('GOOGLE_CLIENT_SECRET'),
    'GOOGLE_REDIRECT' => env('GOOGLE_REDIRECT'),
    'GOOGLE_REDIRECT_URI' => env('GOOGLE_REDIRECT_URI'),
];

foreach ($envVars as $key => $value) {
    $display = empty($value) ? '❌ NOT SET' : '✅ SET (' . substr($value, 0, 30) . '...)';
    echo "{$key}: {$display}\n";
}

echo "\n2. CONFIG VALUES (from config/services.php)\n";
echo "-------------------------------------------\n";
$googleConfig = config('services.google');

echo "client_id: " . (empty($googleConfig['client_id']) ? '❌ NOT SET' : '✅ SET (' . substr($googleConfig['client_id'], 0, 30) . '...)') . "\n";
echo "client_secret: " . (empty($googleConfig['client_secret']) ? '❌ NOT SET' : '✅ SET') . "\n";
echo "redirect: " . (empty($googleConfig['redirect']) ? '❌ NOT SET' : '✅ ' . $googleConfig['redirect']) . "\n";

echo "\n3. SOCIALITE DRIVER TEST\n";
echo "-------------------------------------------\n";
try {
    $driver = \Laravel\Socialite\Facades\Socialite::driver('google');
    echo "✅ Socialite Google driver loaded successfully\n";
    
    // Try to get redirect URL
    try {
        $redirectUrl = $driver->redirect()->getTargetUrl();
        echo "✅ Can generate redirect URL\n";
        echo "Redirect URL: " . substr($redirectUrl, 0, 100) . "...\n";
    } catch (\Exception $e) {
        echo "❌ Cannot generate redirect URL\n";
        echo "Error: " . $e->getMessage() . "\n";
    }
} catch (\Exception $e) {
    echo "❌ Socialite driver failed\n";
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n4. DIAGNOSIS\n";
echo "-------------------------------------------\n";
if (empty($googleConfig['client_id']) || empty($googleConfig['client_secret'])) {
    echo "❌ PROBLEM: Google credentials are not configured\n";
    echo "SOLUTION: Add credentials to .env file and run:\n";
    echo "  php artisan config:clear\n";
    echo "  php artisan cache:clear\n";
} else {
    echo "✅ Google OAuth is properly configured\n";
    echo "You should be able to login with Google now\n";
}

echo "\n===========================================\n";
PHP;

file_put_contents(__DIR__ . '/verify_google_config.php', $verifyScript);
echo "Created verification script: verify_google_config.php\n";

echo "\n===========================================\n";
echo "   NEXT STEPS\n";
echo "===========================================\n";
echo "1. Get your Google OAuth credentials from Google Cloud Console\n";
echo "2. Add them to .env file (see instructions above)\n";
echo "3. Run: php artisan config:clear && php artisan cache:clear\n";
echo "4. Run: php verify_google_config.php\n";
echo "5. Test Google login\n";
echo "===========================================\n";
