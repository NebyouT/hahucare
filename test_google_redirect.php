<?php
/**
 * Test Google OAuth Redirect URL
 * Shows exactly what URL Laravel sends to Google
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Google OAuth Redirect URL Test\n";
echo "================================\n\n";

// Test what Laravel sends to Google
try {
    $socialite = \Laravel\Socialite\Facades\Socialite::driver('google');
    $redirectUrl = $socialite->stateless()->redirect()->getTargetUrl();
    
    echo "Laravel sends this URL to Google:\n";
    echo $redirectUrl . "\n\n";
    
    // Extract the redirect_uri parameter
    $parsedUrl = parse_url($redirectUrl);
    parse_str($parsedUrl['query'], $queryParams);
    
    echo "Redirect URI parameter:\n";
    echo $queryParams['redirect_uri'] . "\n\n";
    
    echo "This EXACT URL must be in Google Cloud Console!\n";
    echo "Go to: https://console.cloud.google.com/\n";
    echo "APIs & Services → Credentials → Edit your OAuth Client\n";
    echo "Add this to 'Authorized redirect URIs':\n";
    echo $queryParams['redirect_uri'] . "\n\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
