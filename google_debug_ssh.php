<?php

/**
 * Google OAuth Debug Script for SSH
 * 
 * Usage: php google_debug_ssh.php
 * 
 * This script will debug Google OAuth issues and provide detailed output
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "===========================================\n";
echo "   Google OAuth Debug Information\n";
echo "===========================================\n\n";

// Check environment variables
echo "📋 Environment Variables:\n";
echo "GOOGLE_CLIENT_ID: " . (env('GOOGLE_CLIENT_ID') ? '✅ Set (' . substr(env('GOOGLE_CLIENT_ID'), 0, 20) . '...)' : '❌ NOT SET') . "\n";
echo "GOOGLE_CLIENT_SECRET: " . (env('GOOGLE_CLIENT_SECRET') ? '✅ Set (' . substr(env('GOOGLE_CLIENT_SECRET'), 0, 20) . '...)' : '❌ NOT SET') . "\n";
echo "GOOGLE_REDIRECT: " . (env('GOOGLE_REDIRECT') ?: '❌ NOT SET') . "\n";
echo "GOOGLE_REDIRECT_URI: " . (env('GOOGLE_REDIRECT_URI') ?: '❌ NOT SET') . "\n\n";

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
    
    $clientId = env('GOOGLE_CLIENT_ID');
    $clientSecret = env('GOOGLE_CLIENT_SECRET');
    $redirectUri = env('GOOGLE_REDIRECT');
    
    echo "Setting client ID: " . substr($clientId, 0, 20) . "...\n";
    $client->setClientId($clientId);
    
    echo "Setting client secret: " . substr($clientSecret, 0, 20) . "...\n";
    $client->setClientSecret($clientSecret);
    
    echo "Setting redirect URI: " . $redirectUri . "\n";
    $client->setRedirectUri($redirectUri);
    
    echo "✅ Google Client created successfully\n";
    
    // Try to create auth URL
    $authUrl = $client->createAuthUrl();
    echo "✅ Auth URL generated: " . substr($authUrl, 0, 100) . "...\n";
    
} catch (\Exception $e) {
    echo "❌ Error creating Google Client: " . $e->getMessage() . "\n";
    echo "Error code: " . $e->getCode() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    
    if (strpos($e->getMessage(), 'client identifier') !== false) {
        echo "\n🔍 This error typically means:\n";
        echo "1. Client ID is missing or invalid\n";
        echo "2. Client ID format is wrong (should end with .apps.googleusercontent.com)\n";
        echo "3. Client ID is not properly set in Google Cloud Console\n";
    }
}

// Check config/services.php
echo "\n📋 Config Services Check:\n";
$googleConfig = config('services.google');
echo "client_id: " . ($googleConfig['client_id'] ?? '❌ NOT SET') . "\n";
echo "client_secret: " . ($googleConfig['client_secret'] ?? '❌ NOT SET') . "\n";
echo "redirect: " . ($googleConfig['redirect'] ?? '❌ NOT SET') . "\n";

// Check recent logs
echo "\n📋 Recent Laravel Logs (last 10 lines):\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -10);
    foreach ($lastLines as $line) {
        if (strpos($line, 'Google') !== false || strpos($line, 'OAuth') !== false) {
            echo "🔍 " . trim($line) . "\n";
        }
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
echo "3. Check if Google Calendar API is enabled\n";
echo "4. Ensure OAuth consent screen is configured\n\n";

echo "To check logs in real-time:\n";
echo "tail -f storage/logs/laravel.log | grep -i google\n\n";

echo "Done!\n";
