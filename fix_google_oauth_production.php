<?php

/**
 * Check Google OAuth Configuration on Production
 * 
 * This script checks if Google OAuth credentials are properly configured
 * and provides instructions to fix the "missing the required client identifier" error.
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n===========================================\n";
echo "   Google OAuth Configuration Check\n";
echo "===========================================\n\n";

echo "🔍 Checking Google OAuth credentials...\n\n";

// Check environment variables
$googleClientId = env('GOOGLE_CLIENT_ID');
$googleClientSecret = env('GOOGLE_CLIENT_SECRET');
$googleRedirect = env('GOOGLE_REDIRECT');
$googleRedirectUri = env('GOOGLE_REDIRECT_URI');

echo "📋 Current Configuration:\n";
echo "   GOOGLE_CLIENT_ID: " . ($googleClientId ? '✅ Set (' . substr($googleClientId, 0, 20) . '...)' : '❌ NOT SET') . "\n";
echo "   GOOGLE_CLIENT_SECRET: " . ($googleClientSecret ? '✅ Set (' . substr($googleClientSecret, 0, 20) . '...)' : '❌ NOT SET') . "\n";
echo "   GOOGLE_REDIRECT: " . ($googleRedirect ? '✅ Set (' . $googleRedirect . ')' : '❌ NOT SET') . "\n";
echo "   GOOGLE_REDIRECT_URI: " . ($googleRedirectUri ? '✅ Set (' . $googleRedirectUri . ')' : '❌ NOT SET') . "\n\n";

// Check config/services.php
$googleConfig = config('services.google');
echo "📋 Config Services (config/services.php):\n";
echo "   client_id: " . ($googleConfig['client_id'] ?? '❌ NOT SET') . "\n";
echo "   client_secret: " . ($googleConfig['client_secret'] ?? '❌ NOT SET') . "\n";
echo "   redirect: " . ($googleConfig['redirect'] ?? '❌ NOT SET') . "\n\n";

// Determine the issue
$hasIssue = false;

if (!$googleClientId || !$googleClientSecret) {
    $hasIssue = true;
    echo "===========================================\n";
    echo "   ❌ ISSUE FOUND\n";
    echo "===========================================\n\n";
    
    echo "The error \"missing the required client identifier\" means\n";
    echo "your Google OAuth credentials are not set in the .env file\n";
    echo "on your PRODUCTION server (hahucare.com).\n\n";
    
    echo "===========================================\n";
    echo "   🔧 HOW TO FIX\n";
    echo "===========================================\n\n";
    
    echo "**Step 1: Get Your Google OAuth Credentials**\n\n";
    
    echo "If you already have them, skip to Step 2.\n";
    echo "If not, get them from Google Cloud Console:\n\n";
    
    echo "1. Go to: https://console.cloud.google.com/\n";
    echo "2. Select your project (or create one)\n";
    echo "3. Go to: APIs & Services → Credentials\n";
    echo "4. Click 'Create Credentials' → 'OAuth 2.0 Client ID'\n";
    echo "5. Application type: 'Web application'\n";
    echo "6. Name: HahuCare\n";
    echo "7. Authorized redirect URIs:\n";
    echo "   - https://hahucare.com/app/auth/google/callback\n";
    echo "8. Click 'Create'\n";
    echo "9. Copy your Client ID and Client Secret\n\n";
    
    echo "**Step 2: Add to .env on Production Server**\n\n";
    
    echo "Login to cPanel → File Manager → Edit .env file\n\n";
    
    echo "Add these lines (replace with YOUR actual credentials):\n\n";
    
    echo "```\n";
    echo "GOOGLE_CLIENT_ID=your-client-id-here.apps.googleusercontent.com\n";
    echo "GOOGLE_CLIENT_SECRET=your-client-secret-here\n";
    echo "GOOGLE_REDIRECT=https://hahucare.com/app/auth/google/callback\n";
    echo "GOOGLE_REDIRECT_URI=https://hahucare.com/app/auth/google/callback\n";
    echo "```\n\n";
    
    echo "**Step 3: Clear Config Cache**\n\n";
    
    echo "SSH:\n";
    echo "   cd public_html\n";
    echo "   php artisan config:clear\n";
    echo "   php artisan cache:clear\n\n";
    
    echo "OR create clear_cache.php:\n";
    echo "```php\n";
    echo "<?php\n";
    echo "require __DIR__ . '/vendor/autoload.php';\n";
    echo "\$app = require_once __DIR__ . '/bootstrap/app.php';\n";
    echo "\$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);\n";
    echo "\$kernel->bootstrap();\n";
    echo "Artisan::call('config:clear');\n";
    echo "Artisan::call('cache:clear');\n";
    echo "echo 'Done! Delete this file.';\n";
    echo "```\n\n";
    
    echo "**Step 4: Test Again**\n\n";
    echo "1. Clear browser cache\n";
    echo "2. Go to Profile → Google Calendar\n";
    echo "3. Click 'Connect Google Account'\n";
    echo "4. Should redirect to Google login (no error)\n\n";
    
} else {
    echo "===========================================\n";
    echo "   ✅ Configuration Looks Good Locally\n";
    echo "===========================================\n\n";
    
    echo "Your local .env has Google credentials set.\n\n";
    
    echo "⚠️  But the error is on PRODUCTION (hahucare.com)\n\n";
    
    echo "This means the .env file on your cPanel server\n";
    echo "is missing these credentials.\n\n";
    
    echo "===========================================\n";
    echo "   🔧 FIX FOR PRODUCTION\n";
    echo "===========================================\n\n";
    
    echo "**Step 1: Copy Your Credentials**\n\n";
    echo "From your local .env file:\n\n";
    echo "GOOGLE_CLIENT_ID=" . $googleClientId . "\n";
    echo "GOOGLE_CLIENT_SECRET=" . $googleClientSecret . "\n";
    echo "GOOGLE_REDIRECT=" . ($googleRedirect ?: 'https://hahucare.com/app/auth/google/callback') . "\n";
    echo "GOOGLE_REDIRECT_URI=" . ($googleRedirectUri ?: 'https://hahucare.com/app/auth/google/callback') . "\n\n";
    
    echo "**Step 2: Add to Production .env**\n\n";
    echo "1. Login to cPanel\n";
    echo "2. File Manager → public_html/.env\n";
    echo "3. Click 'Edit'\n";
    echo "4. Add the above lines\n";
    echo "5. Save\n\n";
    
    echo "**Step 3: Update Redirect URI in Google Console**\n\n";
    echo "IMPORTANT: Make sure your Google Cloud Console has:\n\n";
    echo "Authorized redirect URIs:\n";
    echo "   ✅ https://hahucare.com/app/auth/google/callback\n\n";
    
    echo "If not:\n";
    echo "1. Go to: https://console.cloud.google.com/\n";
    echo "2. APIs & Services → Credentials\n";
    echo "3. Click on your OAuth 2.0 Client ID\n";
    echo "4. Add: https://hahucare.com/app/auth/google/callback\n";
    echo "5. Save\n\n";
    
    echo "**Step 4: Clear Cache on Production**\n\n";
    echo "SSH:\n";
    echo "   cd public_html\n";
    echo "   php artisan config:clear\n";
    echo "   php artisan cache:clear\n\n";
}

echo "===========================================\n";
echo "   📝 Summary\n";
echo "===========================================\n\n";

echo "The error means:\n";
echo "   ❌ Google OAuth credentials missing in production .env\n\n";

echo "To fix:\n";
echo "   1. Add GOOGLE_CLIENT_ID to production .env\n";
echo "   2. Add GOOGLE_CLIENT_SECRET to production .env\n";
echo "   3. Add GOOGLE_REDIRECT to production .env\n";
echo "   4. Clear config cache on production\n";
echo "   5. Test again\n\n";

echo "Done!\n\n";
