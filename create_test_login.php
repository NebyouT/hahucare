<?php
/**
 * Create Test Login Page and Test Google OAuth
 * Run via SSH: php create_test_login.php
 * DELETE THIS FILE AFTER RUNNING!
 */

echo "===========================================\n";
echo "   CREATE TEST LOGIN PAGE\n";
echo "===========================================\n\n";

// 1. Create a simple test login page
$testPage = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <title>Google OAuth Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .btn { padding: 10px 20px; background: #4285f4; color: white; text-decoration: none; border-radius: 4px; }
        .error { color: red; margin: 20px 0; padding: 10px; border: 1px solid #ff0000; background: #ffeeee; }
        .success { color: green; margin: 20px 0; padding: 10px; border: 1px solid #00ff00; background: #eeffee; }
        .debug { background: #f5f5f5; padding: 10px; margin: 10px 0; font-family: monospace; }
    </style>
</head>
<body>
    <h1>Google OAuth Test</h1>
    
    <a href="<?php echo url('/login/google'); ?>" class="btn">Login with Google</a>
    
    <div class="debug">
        <h3>Debug Info:</h3>
        <p>Current URL: <?php echo url()->current(); ?></p>
        <p>Google Login URL: <?php echo url('/login/google'); ?></p>
        <p>Callback URL: <?php echo url('/login/google/callback'); ?></p>
        <p>Time: <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
    
    <?php
    // Show any session errors
    if (session()->has('error')) {
        echo '<div class="error">Error: ' . session('error') . '</div>';
    }
    if (session()->has('success')) {
        echo '<div class="success">Success: ' . session('success') . '</div>';
    }
    
    // Check if user is logged in
    if (auth()->check()) {
        echo '<div class="success">Logged in as: ' . auth()->user()->email . '</div>';
    }
    ?>
</body>
</html>
HTML;

// Save test page
$testFile = __DIR__ . '/public/test-google-login.php';
file_put_contents($testFile, $testPage);
echo "Test page created: /test-google-login.php\n";

// 2. Create a callback test page
$callbackPage = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <title>Google OAuth Callback Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .error { color: red; margin: 20px 0; padding: 10px; border: 1px solid #ff0000; background: #ffeeee; }
        .success { color: green; margin: 20px 0; padding: 10px; border: 1px solid #00ff00; background: #eeffee; }
        .debug { background: #f5f5f5; padding: 10px; margin: 10px 0; font-family: monospace; white-space: pre-wrap; }
    </style>
</head>
<body>
    <h1>Google OAuth Callback Test</h1>
    
    <div class="debug">
        <h3>Request Info:</h3>
        <?php
        echo "Current URL: " . url()->current() . "\n";
        echo "Full URL: " . request()->fullUrl() . "\n";
        echo "Method: " . request()->method() . "\n";
        echo "Query params: " . json_encode(request()->query()) . "\n";
        echo "Has 'code' param: " . (request()->has('code') ? 'YES' : 'NO') . "\n";
        echo "Has 'error' param: " . (request()->has('error') ? 'YES' : 'NO') . "\n";
        echo "Has 'state' param: " . (request()->has('state') ? 'YES' : 'NO') . "\n";
        
        if (request()->has('error')) {
            echo "\nOAuth Error:\n";
            echo "Error: " . request('error') . "\n";
            echo "Description: " . request('error_description', 'No description') . "\n";
        }
        
        if (request()->has('code')) {
            echo "\nOAuth Code received: " . substr(request('code'), 0, 20) . "...\n";
            
            // Try to exchange code for user
            try {
                $user = \Laravel\Socialite\Facades\Socialite::driver('google')->user();
                echo "\nSocialite User Data:\n";
                echo "ID: " . $user->getId() . "\n";
                echo "Email: " . $user->getEmail() . "\n";
                echo "Name: " . $user->getName() . "\n";
                echo "Avatar: " . $user->getAvatar() . "\n";
                
                // Check if user exists in database
                $dbUser = \App\Models\User::where('email', $user->getEmail())->first();
                if ($dbUser) {
                    echo "\nUser exists in database: YES\n";
                    echo "User ID: " . $dbUser->id . "\n";
                    echo "User Type: " . ($dbUser->user_type ?? 'NULL') . "\n";
                    echo "Login Type: " . ($dbUser->login_type ?? 'NULL') . "\n";
                } else {
                    echo "\nUser exists in database: NO\n";
                }
                
                // Try to login
                if ($dbUser) {
                    \Auth::login($dbUser);
                    echo "\nLogin attempted: SUCCESS\n";
                    echo "Now logged in as: " . auth()->user()->email . "\n";
                }
                
            } catch (\Exception $e) {
                echo "\nSocialite Error:\n";
                echo "Message: " . $e->getMessage() . "\n";
                echo "File: " . $e->getFile() . "\n";
                echo "Line: " . $e->getLine() . "\n";
                echo "Trace:\n" . $e->getTraceAsString() . "\n";
            }
        }
        ?>
    </div>
    
    <p><a href="/test-google-login.php">Back to Test Login</a></p>
</body>
</html>
HTML;

$callbackFile = __DIR__ . '/public/test-google-callback.php';
file_put_contents($callbackFile, $callbackPage);
echo "Callback test page created: /test-google-callback.php\n";

// 3. Check current configuration
echo "\n3. CURRENT CONFIGURATION:\n";
echo "-------------------------------------------\n";

// .env values
$envFile = __DIR__ . '/.env';
$envContent = file_get_contents($envFile);

if (preg_match('/^GOOGLE_CLIENT_ID=(.*)$/m', $envContent, $m)) {
    echo "CLIENT_ID: " . (strlen($m[1]) > 0 ? substr($m[1], 0, 30) . '...' : 'EMPTY') . "\n";
}
if (preg_match('/^GOOGLE_CLIENT_SECRET=(.*)$/m', $envContent, $m)) {
    echo "CLIENT_SECRET: " . (strlen($m[1]) > 0 ? substr($m[1], 0, 15) . '...' : 'EMPTY') . "\n";
}
if (preg_match('/^GOOGLE_REDIRECT=(.*)$/m', $envContent, $m)) {
    echo "REDIRECT: " . $m[1] . "\n";
}

// Routes
echo "\n4. TEST URLS:\n";
echo "-------------------------------------------\n";
echo "Test Login Page: https://hahucare.com/test-google-login.php\n";
echo "Actual Login Route: " . url('/login/google') . "\n";
echo "Callback Route: " . url('/login/google/callback') . "\n";
echo "Test Callback Page: https://hahucare.com/test-google-callback.php\n";

// Instructions
echo "\n5. TEST INSTRUCTIONS:\n";
echo "-------------------------------------------\n";
echo "1. Visit: https://hahucare.com/test-google-login.php\n";
echo "2. Click 'Login with Google'\n";
echo "3. If it redirects to Google, that's GOOD\n";
echo "4. After Google auth, it should redirect back\n";
echo "5. Check the callback page for errors\n";
echo "6. If you see errors, paste them here\n";

echo "\n===========================================\n";
echo "   NEXT STEPS\n";
echo "===========================================\n";
echo "1. Test the login using the URL above\n";
echo "2. If it works, the OAuth flow is correct\n";
echo "3. If it fails, the callback page will show the error\n";
echo "4. DELETE these files after testing:\n";
echo "   rm public/test-google-login.php\n";
echo "   rm public/test-google-callback.php\n";
echo "   rm create_test_login.php\n";
echo "===========================================\n";
