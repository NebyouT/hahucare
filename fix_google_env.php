<?php
/**
 * Fix Google OAuth Environment Variables
 * Run via SSH: php fix_google_env.php
 * DELETE THIS FILE AFTER RUNNING!
 */

echo "===========================================\n";
echo "   FIX GOOGLE OAUTH ENVIRONMENT\n";
echo "===========================================\n\n";

$envFile = __DIR__ . '/.env';
$envExample = __DIR__ . '/.env.example';

echo "Current .env file status:\n";
if (!file_exists($envFile)) {
    echo "ERROR: .env file does not exist!\n";
    if (file_exists($envExample)) {
        echo "Found .env.example - copying to .env\n";
        copy($envExample, $envFile);
        echo ".env.example copied to .env\n";
    } else {
        echo "ERROR: Neither .env nor .env.example found!\n";
        exit(1);
    }
} else {
    echo ".env file exists\n";
}

// Read current .env
$envContent = file_get_contents($envFile);
echo "Current .env size: " . number_format(strlen($envContent)) . " bytes\n\n";

// Check if Google variables exist
$hasClientId = strpos($envContent, 'GOOGLE_CLIENT_ID=') !== false;
$hasClientSecret = strpos($envContent, 'GOOGLE_CLIENT_SECRET=') !== false;
$hasRedirect = strpos($envContent, 'GOOGLE_REDIRECT=') !== false;

echo "Current .env Google variables:\n";
echo "  GOOGLE_CLIENT_ID: " . ($hasClientId ? 'PRESENT' : 'MISSING') . "\n";
echo "  GOOGLE_CLIENT_SECRET: " . ($hasClientSecret ? 'PRESENT' : 'MISSING') . "\n";
echo "  GOOGLE_REDIRECT: " . ($hasRedirect ? 'PRESENT' : 'MISSING') . "\n\n";

// Get current config values (these are cached but we can extract them)
echo "Extracting Google credentials from cached config...\n";
$configGoogle = config('services.google');
$currentClientId = $configGoogle['client_id'] ?? '';
$currentClientSecret = $configGoogle['client_secret'] ?? '';
$currentRedirect = $configGoogle['redirect'] ?? '';

echo "Current config values:\n";
echo "  Client ID: " . ($currentClientId ? substr($currentClientId, 0, 30) . '...' : 'NOT SET') . "\n";
echo "  Client Secret: " . ($currentClientSecret ? substr($currentClientSecret, 0, 15) . '...' : 'NOT SET') . "\n";
echo "  Redirect: " . ($currentRedirect ?: 'NOT SET') . "\n\n";

// Check if we have actual values to use
if (!$currentClientId || !$currentClientSecret) {
    echo "ERROR: Cannot extract Google credentials from config!\n";
    echo "You need to manually add them to .env:\n\n";
    echo "GOOGLE_CLIENT_ID=your-google-client-id\n";
    echo "GOOGLE_CLIENT_SECRET=your-google-client-secret\n";
    echo "GOOGLE_REDIRECT=https://hahucare.com/login/google/callback\n\n";
    echo "Get these from: https://console.cloud.google.com/apis/credentials\n";
    exit(1);
}

// Prepare the lines to add
$redirectUrl = 'https://hahucare.com/login/google/callback';
$linesToAdd = [
    "GOOGLE_CLIENT_ID={$currentClientId}",
    "GOOGLE_CLIENT_SECRET={$currentClientSecret}",
    "GOOGLE_REDIRECT={$redirectUrl}",
];

echo "Adding these lines to .env:\n";
foreach ($linesToAdd as $line) {
    echo "  {$line}\n";
}
echo "\n";

// Add to .env
$envLines = explode("\n", $envContent);
$addedLines = [];

foreach ($linesToAdd as $newLine) {
    $key = explode('=', $newLine)[0];
    $found = false;
    
    // Check if line already exists
    foreach ($envLines as $i => $envLine) {
        if (strpos($envLine, $key . '=') === 0) {
            // Replace existing line
            $envLines[$i] = $newLine;
            $addedLines[] = "  UPDATED: {$newLine}";
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        // Add new line at end
        $envLines[] = $newLine;
        $addedLines[] = "  ADDED: {$newLine}";
    }
}

// Write back to .env
$newEnvContent = implode("\n", $envLines);
file_put_contents($envFile, $newEnvContent);

echo "Result:\n";
foreach ($addedLines as $result) {
    echo "  {$result}\n";
}
echo "\n";

// Clear config cache
echo "Clearing config cache...\n";
\Illuminate\Support\Facades\Artisan::call('config:clear');
echo "config:clear: DONE\n";

\Illuminate\Support\Facades\Artisan::call('config:cache');
echo "config:cache: DONE\n";

// Verify the fix
echo "\n=== VERIFICATION ===\n";

// Reload config
$app->make('config')->set('services.google', []);
$configGoogle = config('services.google');

echo "New config values:\n";
echo "  Client ID: " . (isset($configGoogle['client_id']) ? substr($configGoogle['client_id'], 0, 30) . '...' : 'NOT SET') . "\n";
echo "  Client Secret: " . (isset($configGoogle['client_secret']) ? substr($configGoogle['client_secret'], 0, 15) . '...' : 'NOT SET') . "\n";
echo "  Redirect: " . ($configGoogle['redirect'] ?? 'NOT SET') . "\n";

// Test Socialite again
echo "\n=== TESTING SOCIALITE ===\n";
try {
    $driver = \Laravel\Socialite\Facades\Socialite::driver('google');
    $redirectUrl = $driver->redirect()->getTargetUrl();
    echo "Socialite redirect URL: SUCCESS\n";
    echo "URL: " . substr($redirectUrl, 0, 100) . "...\n";
    
    // Check redirect_uri in the URL
    if (strpos($redirectUrl, 'redirect_uri=') !== false) {
        preg_match('/redirect_uri=([^&]+)/', $redirectUrl, $matches);
        if (isset($matches[1])) {
            $redirectUri = urldecode($matches[1]);
            echo "Redirect URI in OAuth URL: {$redirectUri}\n";
            
            if ($redirectUri === $redirectUrl) {
                echo "Redirect URI matches expected: YES ✓\n";
            } else {
                echo "Redirect URI matches expected: NO ✗\n";
            }
        }
    }
} catch (\Exception $e) {
    echo "Socialite test FAILED: " . $e->getMessage() . "\n";
}

echo "\n===========================================\n";
echo "   NEXT STEPS\n";
echo "===========================================\n";
echo "1. Google OAuth environment variables are now set\n";
echo "2. Ensure Google Cloud Console has this redirect URI:\n";
echo "   {$redirectUrl}\n";
echo "3. Try logging in with Google\n";
echo "4. If it fails, check storage/logs/laravel.log for errors\n";
echo "5. DELETE THIS FILE: rm fix_google_env.php\n";
echo "===========================================\n";
