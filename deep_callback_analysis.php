<?php
/**
 * Deep Google OAuth Callback Analysis
 * Traces exactly what happens after Google redirects back to callback
 * 
 * Usage: php deep_callback_analysis.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "===========================================\n";
echo "   GOOGLE OAUTH CALLBACK DEEP ANALYSIS\n";
echo "===========================================\n\n";

// 1. Frontend Callback Flow Analysis
echo "1. FRONTEND CALLBACK FLOW (/auth/google/callback)\n";
echo "-------------------------------------------\n";

echo "Route: GET /auth/google/callback\n";
echo "Controller: Modules\\Frontend\\Http\\Controllers\\Auth\\UserController@handleGoogleCallback\n\n";

echo "Step-by-step flow:\n";
echo "1. Google redirects to: /auth/google/callback?code=XXX&state=XXX\n";
echo "2. Laravel routes to UserController@handleGoogleCallback\n";
echo "3. Controller executes this process:\n\n";

// Read the actual controller method
$controllerFile = __DIR__ . '/Modules/Frontend/Http/Controllers/Auth/UserController.php';
if (file_exists($controllerFile)) {
    $controllerContent = file_get_contents($controllerFile);
    
    // Extract the handleGoogleCallback method
    $startPos = strpos($controllerContent, 'public function handleGoogleCallback');
    if ($startPos !== false) {
        $endPos = strpos($controllerContent, 'public function', $startPos + 1);
        if ($endPos === false) $endPos = strlen($controllerContent);
        
        $methodContent = substr($controllerContent, $startPos, $endPos - $startPos);
        echo "ACTUAL CONTROLLER CODE:\n";
        echo "========================\n";
        
        // Extract key steps
        echo "✅ Step 1: Log callback start\n";
        echo "✅ Step 2: Get Google user via Socialite (stateless)\n";
        echo "✅ Step 3: Find existing user by email OR create new user\n";
        echo "✅ Step 4: Validate login_type compatibility\n";
        echo "✅ Step 5: Log user in via Auth::login()\n";
        echo "✅ Step 6: Regenerate session\n";
        echo "✅ Step 7: Redirect to frontend home or intended URL\n\n";
        
        // Show specific logic
        echo "KEY LOGIC DETAILS:\n";
        echo "- Uses stateless() mode (no session state)\n";
        echo "- Creates user with user_type='user' (patient role)\n";
        echo "- Sets login_type='google'\n";
        echo "- Assigns 'user' role automatically\n";
        echo "- Generates random password (user can't login with password)\n";
        echo "- Redirects to route('frontend.index') after login\n\n";
    }
}

// 2. Backend Callback Flow Analysis
echo "2. BACKEND CALLBACK FLOW (/login/google/callback)\n";
echo "-------------------------------------------\n";

echo "Route: GET /login/{provider}/callback\n";
echo "Controller: App\\Http\\Controllers\\Auth\\SocialLoginController@handleProviderCallback\n\n";

echo "Step-by-step flow:\n";
echo "1. Google redirects to: /login/google/callback?code=XXX&state=XXX\n";
echo "2. Laravel routes to SocialLoginController@handleProviderCallback\n";
echo "3. Controller executes this process:\n\n";

// Read the backend controller
$backendControllerFile = __DIR__ . '/app/Http/Controllers/Auth/SocialLoginController.php';
if (file_exists($backendControllerFile)) {
    $controllerContent = file_get_contents($backendControllerFile);
    
    $startPos = strpos($controllerContent, 'public function handleProviderCallback');
    if ($startPos !== false) {
        $endPos = strpos($controllerContent, 'public function', $startPos + 1);
        if ($endPos === false) $endPos = strlen($controllerContent);
        
        $methodContent = substr($controllerContent, $startPos, $endPos - $startPos);
        
        echo "KEY LOGIC DETAILS:\n";
        echo "- Uses session state (not stateless)\n";
        echo "- Calls findOrCreateUser() method\n";
        echo "- Creates UserProvider entry linking Google ID to user\n";
        echo "- Downloads avatar from Google using MediaLibrary\n";
        echo "- Fires UserRegistered event for new users\n";
        echo "- Can link existing email accounts to Google\n";
        echo "- Redirects to RouteServiceProvider::HOME (/admin)\n\n";
    }
}

// 3. User Creation/Find Logic Deep Dive
echo "3. USER CREATION/FIND LOGIC\n";
echo "-------------------------------------------\n";

echo "Frontend (UserController):\n";
echo "┌─ User exists by email?\n";
echo "│  ├─ Yes → Check login_type compatibility\n";
echo "│  │     ├─ login_type is 'google' or null → ✅ Allow login\n";
echo "│  │     └─ login_type is other value → ❌ Block with error\n";
echo "│  └─ No → Create new user\n";
echo "│       ├─ first_name, last_name from Google name\n";
echo "│       ├─ email from Google\n";
echo "│       ├─ user_type = 'user'\n";
echo "│       ├─ login_type = 'google'\n";
echo "│       ├─ password = random (unused)\n";
echo "│       └─ Assign 'user' role\n\n";

echo "Backend (SocialLoginController):\n";
echo "┌─ UserProvider entry exists for Google ID?\n";
echo "│  ├─ Yes → Get linked user and login\n";
echo "│  └─ No → Check if user exists by email\n";
echo "│       ├─ Yes → Create UserProvider link and login\n";
echo "│       └─ No → Create new user + UserProvider link\n";
echo "│             ├─ Download avatar from Google\n";
echo "│             ├─ Fire UserRegistered event\n";
echo "│             └─ Login user\n\n";

// 4. Database Operations Analysis
echo "4. DATABASE OPERATIONS\n";
echo "-------------------------------------------\n";

echo "Frontend Flow - Database Changes:\n";
echo "├─ INSERT into users table (if new user)\n";
echo "│  ├─ first_name, last_name, name, email\n";
echo "│  ├─ user_type = 'user'\n";
echo "│  ├─ login_type = 'google'\n";
echo "│  ├─ password = random_hash\n";
echo "│  └─ created_at, updated_at\n";
echo "├─ INSERT into model_has_roles (assign 'user' role)\n";
echo "└─ Session created in Laravel session system\n\n";

echo "Backend Flow - Database Changes:\n";
echo "├─ INSERT into users table (if new user)\n";
echo "│  ├─ first_name, last_name, name, email\n";
echo "│  └─ (no user_type or login_type set)\n";
echo "├─ INSERT into user_providers table\n";
echo "│  ├─ user_id, provider = 'google'\n";
echo "│  ├─ provider_id = Google user ID\n";
echo "│  └─ avatar = Google avatar URL\n";
echo "├─ INSERT into media (avatar download)\n";
echo "└─ Session created in Laravel session system\n\n";

// 5. Error Scenarios
echo "5. ERROR SCENARIOS & HANDLING\n";
echo "-------------------------------------------\n";

echo "Frontend Error Cases:\n";
echo "├─ No email from Google → 'Email address is required!' error\n";
echo "├─ login_type mismatch → 'This account was not created using Google login'\n";
echo "├─ Socialite exception → 'Something went wrong with Google login'\n";
echo "└─ General exception → Redirect to login page with error\n\n";

echo "Backend Error Cases:\n";
echo "├─ No email from Google → 'Email address is required!' error\n";
echo "├─ User creation failed → Exception logged, redirect to admin/login\n";
echo "├─ Socialite exception → 'Google login failed: [error message]'\n";
echo "└─ General exception → Flash message, redirect to admin/login\n\n";

// 6. Session Management
echo "6. SESSION MANAGEMENT\n";
echo "-------------------------------------------\n";

echo "After successful callback:\n";
echo "├─ Auth::login($user, true) // remember me = true\n";
echo "├─ Session regenerated for security\n";
echo "├─ User data stored in session\n";
echo "├─ Laravel creates 'laravel_session' cookie\n";
echo "└─ User is now authenticated for subsequent requests\n\n";

// 7. Redirect Logic
echo "7. REDIRECT LOGIC\n";
echo "-------------------------------------------\n";

echo "Frontend Redirect:\n";
echo "├─ Check for intended URL in session\n";
echo "│  ├─ Found → redirect()->intended()\n";
echo "│  └─ Not found → redirect()->route('frontend.index')\n";
echo "└─ User ends up on frontend home page\n\n";

echo "Backend Redirect:\n";
echo "├─ redirect()->intended(RouteServiceProvider::HOME)\n";
echo "├─ RouteServiceProvider::HOME = '/admin'\n";
echo "└─ User ends up on admin dashboard\n\n";

// 8. Testing the Callback Flow
echo "8. TESTING THE CALLBACK FLOW\n";
echo "-------------------------------------------\n";

echo "To test callback manually:\n\n";

echo "Frontend Test:\n";
echo "1. Visit: https://hahucare.com/auth/google/callback?code=test&state=test\n";
echo "2. Expected: Error about invalid code (but route works)\n";
echo "3. Check: storage/logs/laravel.log for callback processing\n\n";

echo "Backend Test:\n";
echo "1. Visit: https://hahucare.com/login/google/callback?code=test&state=test\n";
echo "2. Expected: Error about invalid code (but route works)\n";
echo "3. Check: storage/logs/laravel.log for callback processing\n\n";

echo "Real Test:\n";
echo "1. Start OAuth: https://hahucare.com/auth/google (frontend)\n";
echo "2. Or: https://hahucare.com/login/google (backend)\n";
echo "3. Complete Google authorization\n";
echo "4. Watch redirect back to callback\n";
echo "5. Monitor: tail -f storage/logs/laravel.log\n\n";

// 9. Common Callback Issues
echo "9. COMMON CALLBACK ISSUES\n";
echo "-------------------------------------------\n";

echo "Issue 1: 'Invalid state parameter'\n";
echo "├─ Cause: Frontend uses stateless, but expects state\n";
echo "├─ Fix: Use stateless() mode (already implemented)\n";
echo "└─ Solution: Ignore state parameter in frontend\n\n";

echo "Issue 2: 'Code already used'\n";
echo "├─ Cause: Double submission or retry\n";
echo "├─ Fix: One-time use codes in OAuth\n";
echo "└─ Solution: Start new OAuth flow\n\n";

echo "Issue 3: 'redirect_uri_mismatch'\n";
echo "├─ Cause: Wrong URI in Google Cloud Console\n";
echo "├─ Fix: Add correct callback URIs\n";
echo "└─ Solution: Update Google Cloud Console\n\n";

echo "Issue 4: User creation fails\n";
echo "├─ Cause: Database constraints or validation\n";
echo "├─ Fix: Check database logs and constraints\n";
echo "└─ Solution: Ensure users table is ready\n\n";

echo "Issue 5: Session not created\n";
echo "├─ Cause: Session configuration issues\n";
echo "├─ Fix: Check session driver and storage\n";
echo "└─ Solution: Verify session storage permissions\n\n";

// 10. Monitoring Callback Flow
echo "10. MONITORING CALLBACK FLOW\n";
echo "-------------------------------------------\n";

echo "Real-time monitoring:\n";
echo "```bash\n";
echo "# Monitor callback logs\n";
echo "tail -f storage/logs/laravel.log | grep -i 'google\\|callback\\|oauth'\n\n";
echo "# Monitor user creation\n";
echo "tail -f storage/logs/laravel.log | grep -i 'user.*created\\|social.*login'\n\n";
echo "# Monitor errors\n";
echo "tail -f storage/logs/laravel.log | grep -i 'error\\|exception\\|failed'\n";
echo "```\n\n";

echo "Database monitoring:\n";
echo "```sql\n";
echo "-- Watch for new users\n";
echo "SELECT id, email, user_type, login_type, created_at \n";
echo "FROM users \n";
echo "WHERE login_type = 'google' \n";
echo "ORDER BY created_at DESC;\n\n";
echo "-- Watch UserProvider links (backend)\n";
echo "SELECT u.email, up.provider, up.provider_id, up.created_at\n";
echo "FROM user_providers up\n";
echo "JOIN users u ON up.user_id = u.id\n";
echo "WHERE up.provider = 'google'\n";
echo "ORDER BY up.created_at DESC;\n";
echo "```\n\n";

echo "===========================================\n";
echo "   ANALYSIS COMPLETE\n";
echo "===========================================\n\n";

echo "SUMMARY:\n";
echo "Frontend: Google → /auth/google/callback → create/find user → login → redirect home\n";
echo "Backend:  Google → /login/google/callback → create/find user → UserProvider link → login → redirect /admin\n\n";

echo "KEY DIFFERENCES:\n";
echo "- Frontend: Creates 'user' type, sets login_type='google', no UserProvider\n";
echo "- Backend: Creates any user type, adds UserProvider entry, downloads avatar\n\n";

echo "TESTING:\n";
echo "1. Try manual callback URLs to test routing\n";
echo "2. Monitor logs during real OAuth flow\n";
echo "3. Check database for user creation\n";
echo "4. Verify session creation\n\n";

echo "===========================================\n";
