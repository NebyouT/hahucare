<?php

/**
 * Check Google OAuth Logs
 * 
 * Upload to production and run: https://hahucare.com/check_google_logs.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<pre>";
echo "===========================================\n";
echo "   Google OAuth Debug Information\n";
echo "===========================================\n\n";

// Check environment variables
echo "📋 Environment Variables:\n";
echo "GOOGLE_CLIENT_ID: " . (env('GOOGLE_CLIENT_ID') ? '✅ Set' : '❌ NOT SET') . "\n";
echo "GOOGLE_CLIENT_SECRET: " . (env('GOOGLE_CLIENT_SECRET') ? '✅ Set' : '❌ NOT SET') . "\n";
echo "GOOGLE_REDIRECT: " . (env('GOOGLE_REDIRECT') ?: '❌ NOT SET') . "\n\n";

// Check if Google Client class exists
echo "📋 Google Client Library:\n";
if (class_exists('Google\Client')) {
    echo "✅ Google Client Library installed\n";
} else {
    echo "❌ Google Client Library NOT installed\n";
}

// Try to create Google Client
echo "\n📋 Testing Google Client Creation:\n";
try {
    $client = new \Google\Client();
    $client->setClientId(env('GOOGLE_CLIENT_ID'));
    $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
    $client->setRedirectUri(env('GOOGLE_REDIRECT'));
    
    echo "✅ Google Client created successfully\n";
    
    // Try to create auth URL
    $authUrl = $client->createAuthUrl();
    echo "✅ Auth URL generated: " . substr($authUrl, 0, 100) . "...\n";
    
} catch (\Exception $e) {
    echo "❌ Error creating Google Client: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Check recent logs
echo "\n📋 Recent Laravel Logs (last 10 lines):\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -10);
    foreach ($lastLines as $line) {
        echo trim($line) . "\n";
    }
} else {
    echo "❌ No log file found\n";
}

echo "\n===========================================\n";
echo "   📝 Next Steps\n";
echo "===========================================\n\n";

echo "If Google Client creation fails:\n";
echo "1. Check Client ID format (should end with .apps.googleusercontent.com)\n";
echo "2. Verify redirect URI in Google Cloud Console\n";
echo "3. Check if Google APIs are enabled\n\n";

echo "DELETE THIS FILE: check_google_logs.php\n";
echo "</pre>";
