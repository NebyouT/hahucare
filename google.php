<?php
/**
 * Verify Google OAuth Credentials in Production .env
 * 
 * Upload this file to public_html/ and access it via browser
 * to check if Google credentials are properly set.
 */

echo "<html><head><title>Google OAuth Verification</title>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;} code{background:#f4f4f4;padding:2px 6px;}</style>";
echo "</head><body>";

echo "<h1>🔍 Google OAuth Configuration Verification</h1>";
echo "<p><strong>Server:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";
echo "<hr>";

// Load Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<h2>Step 1: Check .env File Directly</h2>";

$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    echo "<p class='success'>✅ .env file exists at: <code>$envPath</code></p>";
    
    $envContent = file_get_contents($envPath);
    
    // Check for Google credentials
    $hasClientId = strpos($envContent, 'GOOGLE_CLIENT_ID') !== false;
    $hasClientSecret = strpos($envContent, 'GOOGLE_CLIENT_SECRET') !== false;
    $hasRedirect = strpos($envContent, 'GOOGLE_REDIRECT') !== false;
    
    echo "<h3>Credentials in .env file:</h3>";
    echo "<ul>";
    echo "<li>" . ($hasClientId ? "<span class='success'>✅</span>" : "<span class='error'>❌</span>") . " GOOGLE_CLIENT_ID found</li>";
    echo "<li>" . ($hasClientSecret ? "<span class='success'>✅</span>" : "<span class='error'>❌</span>") . " GOOGLE_CLIENT_SECRET found</li>";
    echo "<li>" . ($hasRedirect ? "<span class='success'>✅</span>" : "<span class='error'>❌</span>") . " GOOGLE_REDIRECT found</li>";
    echo "</ul>";
    
    // Extract actual values
    if ($hasClientId) {
        preg_match('/GOOGLE_CLIENT_ID=(.*)/', $envContent, $matches);
        $clientIdLine = isset($matches[1]) ? trim($matches[1]) : 'NOT FOUND';
        echo "<p><strong>GOOGLE_CLIENT_ID value:</strong> <code>" . htmlspecialchars(substr($clientIdLine, 0, 50)) . "...</code></p>";
    }
    
    if ($hasClientSecret) {
        preg_match('/GOOGLE_CLIENT_SECRET=(.*)/', $envContent, $matches);
        $clientSecretLine = isset($matches[1]) ? trim($matches[1]) : 'NOT FOUND';
        echo "<p><strong>GOOGLE_CLIENT_SECRET value:</strong> <code>" . htmlspecialchars(substr($clientSecretLine, 0, 30)) . "...</code></p>";
    }
    
    if ($hasRedirect) {
        preg_match('/GOOGLE_REDIRECT=(.*)/', $envContent, $matches);
        $redirectLine = isset($matches[1]) ? trim($matches[1]) : 'NOT FOUND';
        echo "<p><strong>GOOGLE_REDIRECT value:</strong> <code>" . htmlspecialchars($redirectLine) . "</code></p>";
    }
    
} else {
    echo "<p class='error'>❌ .env file NOT FOUND at: <code>$envPath</code></p>";
}

echo "<hr>";
echo "<h2>Step 2: Check Laravel env() Function</h2>";

$clientId = env('GOOGLE_CLIENT_ID');
$clientSecret = env('GOOGLE_CLIENT_SECRET');
$redirect = env('GOOGLE_REDIRECT');
$redirectUri = env('GOOGLE_REDIRECT_URI');

echo "<ul>";
echo "<li>" . ($clientId ? "<span class='success'>✅</span>" : "<span class='error'>❌</span>") . " GOOGLE_CLIENT_ID: " . ($clientId ? "<code>" . substr($clientId, 0, 30) . "...</code>" : "<span class='error'>NOT SET</span>") . "</li>";
echo "<li>" . ($clientSecret ? "<span class='success'>✅</span>" : "<span class='error'>❌</span>") . " GOOGLE_CLIENT_SECRET: " . ($clientSecret ? "<code>" . substr($clientSecret, 0, 20) . "...</code>" : "<span class='error'>NOT SET</span>") . "</li>";
echo "<li>" . ($redirect ? "<span class='success'>✅</span>" : "<span class='error'>❌</span>") . " GOOGLE_REDIRECT: " . ($redirect ? "<code>" . $redirect . "</code>" : "<span class='error'>NOT SET</span>") . "</li>";
echo "<li>" . ($redirectUri ? "<span class='success'>✅</span>" : "<span class='error'>❌</span>") . " GOOGLE_REDIRECT_URI: " . ($redirectUri ? "<code>" . $redirectUri . "</code>" : "<span class='error'>NOT SET</span>") . "</li>";
echo "</ul>";

echo "<hr>";
echo "<h2>Step 3: Check config/services.php</h2>";

$googleConfig = config('services.google');

echo "<ul>";
echo "<li>" . (isset($googleConfig['client_id']) && $googleConfig['client_id'] ? "<span class='success'>✅</span>" : "<span class='error'>❌</span>") . " client_id: " . (isset($googleConfig['client_id']) && $googleConfig['client_id'] ? "<code>" . substr($googleConfig['client_id'], 0, 30) . "...</code>" : "<span class='error'>NOT SET</span>") . "</li>";
echo "<li>" . (isset($googleConfig['client_secret']) && $googleConfig['client_secret'] ? "<span class='success'>✅</span>" : "<span class='error'>❌</span>") . " client_secret: " . (isset($googleConfig['client_secret']) && $googleConfig['client_secret'] ? "<code>" . substr($googleConfig['client_secret'], 0, 20) . "...</code>" : "<span class='error'>NOT SET</span>") . "</li>";
echo "<li>" . (isset($googleConfig['redirect']) && $googleConfig['redirect'] ? "<span class='success'>✅</span>" : "<span class='error'>❌</span>") . " redirect: " . (isset($googleConfig['redirect']) && $googleConfig['redirect'] ? "<code>" . $googleConfig['redirect'] . "</code>" : "<span class='error'>NOT SET</span>") . "</li>";
echo "</ul>";

echo "<hr>";
echo "<h2>📋 Diagnosis</h2>";

if (!$clientId || !$clientSecret) {
    echo "<div style='background:#ffebee;padding:15px;border-left:4px solid #f44336;'>";
    echo "<h3 class='error'>❌ Problem Found: Credentials Not Being Read</h3>";
    echo "<p><strong>Possible causes:</strong></p>";
    echo "<ol>";
    echo "<li><strong>Cache not cleared:</strong> Laravel is using old cached config</li>";
    echo "<li><strong>Syntax error in .env:</strong> Extra spaces, quotes, or line breaks</li>";
    echo "<li><strong>Wrong .env file:</strong> Edited a different .env file</li>";
    echo "<li><strong>.env not saved:</strong> Changes weren't saved properly</li>";
    echo "</ol>";
    
    echo "<h3>🔧 Solutions:</h3>";
    echo "<ol>";
    echo "<li><strong>Clear cache again:</strong>";
    echo "<pre style='background:#f4f4f4;padding:10px;'>cd public_html\nphp artisan config:clear\nphp artisan cache:clear</pre>";
    echo "</li>";
    
    echo "<li><strong>Check .env syntax:</strong> Make sure lines look like this:";
    echo "<pre style='background:#f4f4f4;padding:10px;'>GOOGLE_CLIENT_ID=864058558222-272i0ut7kc4nl5peim49on08h7aoko23.apps.googleusercontent.com\nGOOGLE_CLIENT_SECRET=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</pre>";
    echo "<p><strong>NO spaces around =</strong><br><strong>NO quotes around values</strong><br><strong>NO extra line breaks</strong></p>";
    echo "</li>";
    
    echo "<li><strong>Verify .env location:</strong> Must be in <code>public_html/.env</code></li>";
    
    echo "<li><strong>Re-add credentials:</strong> Open .env, scroll to bottom, paste the 5 lines again</li>";
    
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div style='background:#e8f5e9;padding:15px;border-left:4px solid #4caf50;'>";
    echo "<h3 class='success'>✅ Credentials Are Set!</h3>";
    echo "<p>Google OAuth credentials are properly configured.</p>";
    echo "<p>If you're still getting errors, check:</p>";
    echo "<ul>";
    echo "<li>Google Cloud Console has correct redirect URI</li>";
    echo "<li>Client ID and Secret match Google Console exactly</li>";
    echo "<li>Browser cache is cleared</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<hr>";
echo "<h2>⚠️ Security Warning</h2>";
echo "<p style='color:red;'><strong>DELETE THIS FILE NOW!</strong></p>";
echo "<p>This file exposes sensitive configuration information.</p>";
echo "<p>Go to File Manager and delete <code>verify_google_env.php</code></p>";

echo "</body></html>";
