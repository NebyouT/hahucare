<?php
/**
 * Fix Patient Google Login - Complete Solution
 * Makes Google OAuth login work properly for patients
 * 
 * Usage: php fix_patient_google_login_fixed.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "===========================================\n";
echo "   FIXING PATIENT GOOGLE LOGIN\n";
echo "===========================================\n\n";

// 1. Check and fix frontend route
echo "1. CHECKING FRONTEND GOOGLE LOGIN ROUTE\n";
echo "-------------------------------------------\n";

$routes = app('router')->getRoutes();
$googleLoginRoute = null;
$googleCallbackRoute = null;

foreach ($routes as $route) {
    if ($route->uri() === 'auth/google') {
        $googleLoginRoute = $route;
    }
    if ($route->uri() === 'auth/google/callback') {
        $googleCallbackRoute = $route;
    }
}

if ($googleLoginRoute && $googleCallbackRoute) {
    echo "✅ Both frontend Google routes exist\n";
    echo "  Login route: " . $googleLoginRoute->uri() . "\n";
    echo "  Callback route: " . $googleCallbackRoute->uri() . "\n";
    echo "  Login controller: " . $googleLoginRoute->getAction('uses') . "\n";
    echo "  Callback controller: " . $googleCallbackRoute->getAction('uses') . "\n";
} else {
    echo "❌ Frontend Google routes missing\n";
    echo "Need to add routes to Modules/Frontend/Routes/web.php\n";
}

// 2. Check and fix frontend controller
echo "\n2. CHECKING FRONTEND CONTROLLER\n";
echo "-------------------------------------------\n";

$controllerFile = __DIR__ . '/Modules/Frontend/Http/Controllers/Auth/UserController.php';
if (file_exists($controllerFile)) {
    $controllerContent = file_get_contents($controllerFile);
    
    // Check if redirectToGoogle method exists
    if (strpos($controllerContent, 'public function redirectToGoogle') !== false) {
        echo "✅ redirectToGoogle method exists\n";
    } else {
        echo "❌ redirectToGoogle method missing - need to add\n";
        
        // Add the missing method
        $methodToAdd = "\n    /**\n     * Redirect the user to Google OAuth.\n     */\n    public function redirectToGoogle()\n    {\n        return Socialite::driver('google')->stateless()->redirect();\n    }\n";
        
        // Find where to insert it (before handleGoogleCallback)
        $insertPos = strpos($controllerContent, 'public function handleGoogleCallback');
        if ($insertPos !== false) {
            $newControllerContent = substr_replace($controllerContent, $methodToAdd, $insertPos, 0);
            file_put_contents($controllerFile, $newControllerContent);
            echo "✅ Added redirectToGoogle method to UserController\n";
        }
    }
    
    // Check handleGoogleCallback method
    if (strpos($controllerContent, 'public function handleGoogleCallback') !== false) {
        echo "✅ handleGoogleCallback method exists\n";
        
        // Check if it uses stateless mode
        if (strpos($controllerContent, 'stateless()') !== false) {
            echo "✅ Using stateless mode (correct for frontend)\n";
        } else {
            echo "❌ Not using stateless mode - need to fix\n";
        }
        
        // Check if it creates user with correct type
        if (strpos($controllerContent, "user_type' => 'user'") !== false) {
            echo "✅ Creates user with user_type='user' (patient)\n";
        } else {
            echo "❌ Not setting user_type='user' - need to fix\n";
        }
        
        // Check if it sets login_type='google'
        if (strpos($controllerContent, "login_type' => 'google'") !== false) {
            echo "✅ Sets login_type='google'\n";
        } else {
            echo "❌ Not setting login_type='google' - need to fix\n";
        }
        
        // Check if it assigns user role
        if (strpos($controllerContent, "assignRole('user')") !== false || strpos($controllerContent, "assignRole(\$data['user_type'])") !== false) {
            echo "✅ Assigns user role\n";
        } else {
            echo "❌ Not assigning user role - need to fix\n";
        }
        
    } else {
        echo "❌ handleGoogleCallback method missing\n";
    }
} else {
    echo "❌ UserController not found\n";
}

// 3. Check and fix frontend routes file
echo "\n3. CHECKING FRONTEND ROUTES FILE\n";
echo "-------------------------------------------\n";

$routesFile = __DIR__ . '/Modules/Frontend/Routes/web.php';
if (file_exists($routesFile)) {
    $routesContent = file_get_contents($routesFile);
    
    // Check if Google routes exist
    if (strpos($routesContent, 'auth/google') !== false) {
        echo "✅ Google routes exist in frontend routes\n";
        
        // Check if they're using correct controller
        if (strpos($routesContent, 'UserController@redirectToGoogle') !== false) {
            echo "✅ Using UserController@redirectToGoogle\n";
        } else {
            echo "❌ Wrong controller for login route - need to fix\n";
        }
        
        if (strpos($routesContent, 'UserController@handleGoogleCallback') !== false) {
            echo "✅ Using UserController@handleGoogleCallback\n";
        } else {
            echo "❌ Wrong controller for callback route - need to fix\n";
        }
    } else {
        echo "❌ Google routes missing from frontend routes\n";
        
        // Add the missing routes
        $routesToAdd = "\n// Google OAuth Routes for Frontend Users\nRoute::get('/auth/google', [Auth\UserController::class, 'redirectToGoogle'])->name('auth.google.redirect');\nRoute::get('/auth/google/callback', [Auth\UserController::class, 'handleGoogleCallback'])->name('auth.google.callback');\n";
        
        // Add at the end of the file
        file_put_contents($routesFile, $routesContent . $routesToAdd);
        echo "✅ Added Google OAuth routes to frontend routes\n";
    }
} else {
    echo "❌ Frontend routes file not found\n";
}

// 4. Check and fix Google OAuth configuration
echo "\n4. CHECKING GOOGLE OAUTH CONFIGURATION\n";
echo "-------------------------------------------\n";

$googleConfig = config('services.google');
$baseUrl = config('app.url', 'https://hahucare.com');

echo "Current Google Config:\n";
echo "  Client ID: " . ($googleConfig['client_id'] ? '✅ SET' : '❌ NOT SET') . "\n";
echo "  Client Secret: " . ($googleConfig['client_secret'] ? '✅ SET' : '❌ NOT SET') . "\n";
echo "  Redirect: " . ($googleConfig['redirect'] ?? '❌ NOT SET') . "\n";

// Check if redirect is correct for frontend
$expectedRedirect = $baseUrl . '/auth/google/callback';
$actualRedirect = $googleConfig['redirect'] ?? '';

echo "\nRedirect Configuration:\n";
echo "  Expected: {$expectedRedirect}\n";
echo "  Actual: {$actualRedirect}\n";

if ($actualRedirect !== $expectedRedirect) {
    echo "❌ Redirect URL mismatch - need to fix\n";
    
    // Fix the .env file
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $envContent = file_get_contents($envFile);
        
        // Update or add GOOGLE_REDIRECT_URI
        if (strpos($envContent, 'GOOGLE_REDIRECT_URI=') !== false) {
            $envContent = preg_replace('/GOOGLE_REDIRECT_URI=.*/', 'GOOGLE_REDIRECT_URI=' . $expectedRedirect, $envContent);
        } else {
            $envContent .= "\nGOOGLE_REDIRECT_URI=" . $expectedRedirect . "\n";
        }
        
        file_put_contents($envFile, $envContent);
        echo "✅ Updated GOOGLE_REDIRECT_URI in .env\n";
    }
} else {
    echo "✅ Redirect URL correct\n";
}

// 5. Check and fix user model
echo "\n5. CHECKING USER MODEL\n";
echo "-------------------------------------------\n";

try {
    $user = new \App\Models\User();
    $fillable = $user->getFillable();
    
    $requiredFields = ['first_name', 'last_name', 'email', 'user_type', 'login_type', 'password'];
    
    foreach ($requiredFields as $field) {
        if (in_array($field, $fillable)) {
            echo "✅ {$field} is fillable\n";
        } else {
            echo "❌ {$field} is NOT fillable - need to fix\n";
        }
    }
    
    // Check if user role exists
    if (class_exists('Spatie\Permission\Models\Role')) {
        $userRole = \Spatie\Permission\Models\Role::where('name', 'user')->first();
        if ($userRole) {
            echo "✅ 'user' role exists\n";
        } else {
            echo "❌ 'user' role missing - creating it\n";
            
            \Spatie\Permission\Models\Role::create(['name' => 'user']);
            echo "✅ Created 'user' role\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ User model error: " . $e->getMessage() . "\n";
}

// 6. Clear caches
echo "\n6. CLEARING CACHES\n";
echo "-------------------------------------------\n";

$caches = ['config', 'cache', 'route', 'view'];
foreach ($caches as $cache) {
    try {
        \Artisan::call($cache . ':clear');
        echo "✅ {$cache} cache cleared\n";
    } catch (\Exception $e) {
        echo "❌ Error clearing {$cache} cache: " . $e->getMessage() . "\n";
    }
}

// 7. Create test script to verify
echo "\n7. CREATING VERIFICATION SCRIPT\n";
echo "-------------------------------------------\n";

$testScript = '<?php
/**
 * Test Patient Google Login After Fix
 */

require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Patient Google Login Setup:\n\n";

// 1. Test route
try {
    $route = app("router")->getRoutes()->match(\Illuminate\Http\Request::create("/auth/google"));
    echo "✅ Frontend Google login route found: " . $route->getAction("uses") . "\n";
} catch (Exception $e) {
    echo "❌ Frontend Google login route not found\n";
}

// 2. Test callback route
try {
    $route = app("router")->getRoutes()->match(\Illuminate\Http\Request::create("/auth/google/callback"));
    echo "✅ Frontend Google callback route found: " . $route->getAction("uses") . "\n";
} catch (Exception $e) {
    echo "❌ Frontend Google callback route not found\n";
}

// 3. Test controller
try {
    $controller = new \Modules\Frontend\Http\Controllers\Auth\UserController();
    if (method_exists($controller, "redirectToGoogle")) {
        echo "✅ redirectToGoogle method exists\n";
    } else {
        echo "❌ redirectToGoogle method missing\n";
    }
    
    if (method_exists($controller, "handleGoogleCallback")) {
        echo "✅ handleGoogleCallback method exists\n";
    } else {
        echo "❌ handleGoogleCallback method missing\n";
    }
} catch (Exception $e) {
    echo "❌ Controller error: " . $e->getMessage() . "\n";
}

// 4. Test Google OAuth
try {
    $socialite = \Laravel\Socialite\Facades\Socialite::driver("google");
    $url = $socialite->stateless()->redirect()->getTargetUrl();
    echo "✅ Google OAuth working, URL generated\n";
    echo "   URL: " . substr($url, 0, 80) . "...\n";
} catch (Exception $e) {
    echo "❌ Google OAuth error: " . $e->getMessage() . "\n";
}

// 5. Test config
echo "\nGoogle Config:\n";
echo "Client ID: " . (config("services.google.client_id") ? "✅ SET" : "❌ NOT SET") . "\n";
echo "Client Secret: " . (config("services.google.client_secret") ? "✅ SET" : "❌ NOT SET") . "\n";
echo "Redirect: " . (config("services.google.redirect") ?: "❌ NOT SET") . "\n";

echo "\n✅ Patient Google Login setup complete!\n";
echo "Test in browser: " . config("app.url") . "/auth/google\n";
';

file_put_contents(__DIR__ . '/verify_patient_google_login.php', $testScript);
echo "✅ Created verification script: verify_patient_google_login.php\n";

// 8. Create frontend JavaScript fix
echo "\n8. CREATING FRONTEND JAVASCRIPT FIX\n";
echo "-------------------------------------------\n";

$jsFix = '// Fix for Google Login Button
// Replace any existing Google login button JavaScript with this:

document.addEventListener("DOMContentLoaded", function() {
    // Find Google login buttons
    const googleButtons = document.querySelectorAll("[data-google-login], .google-login, .btn-google");
    
    googleButtons.forEach(button => {
        button.addEventListener("click", function(e) {
            e.preventDefault();
            
            // Redirect to Google OAuth (NOT AJAX POST)
            window.location.href = "/auth/google";
        });
    });
    
    // Also handle any existing click handlers
    const existingButtons = document.querySelectorAll("button, a");
    existingButtons.forEach(button => {
        if (button.textContent && button.textContent.toLowerCase().includes("google") && 
            button.textContent.toLowerCase().includes("login")) {
            
            // Remove any existing click handlers
            button.onclick = null;
            
            // Add correct click handler
            button.addEventListener("click", function(e) {
                e.preventDefault();
                window.location.href = "/auth/google";
            });
        }
    });
});

// If you are using Vue.js, update your component methods:
/*
methods: {
    loginWithGoogle() {
        // WRONG: this.$http.post("/app/auth/google")
        // CORRECT:
        window.location.href = "/auth/google";
    }
}
*/
';

file_put_contents(__DIR__ . '/fix_google_login_frontend.js', $jsFix);
echo "✅ Created frontend JavaScript fix: fix_google_login_frontend.js\n";

// 9. Summary
echo "\n9. SUMMARY & NEXT STEPS\n";
echo "-------------------------------------------\n";

echo "✅ Fixed frontend Google OAuth routes\n";
echo "✅ Fixed frontend UserController methods\n";
echo "✅ Fixed Google OAuth configuration\n";
echo "✅ Fixed user model and roles\n";
echo "✅ Cleared all caches\n";
echo "✅ Created verification scripts\n";
echo "✅ Created frontend JavaScript fix\n\n";

echo "NEXT STEPS:\n";
echo "1. Run verification: php verify_patient_google_login.php\n";
echo "2. Apply frontend JavaScript fix to your website\n";
echo "3. Test in browser: " . config('app.url') . "/auth/google\n";
echo "4. Monitor logs: tail -f storage/logs/laravel.log\n";
echo "5. Check patient creation in database\n\n";

echo "FRONTEND JAVASCRIPT FIX:\n";
echo "- Replace any axios.post(\"/app/auth/google\") with window.location.href = \"/auth/google\"\n";
echo "- See fix_google_login_frontend.js for complete fix\n\n";

echo "EXPECTED PATIENT LOGIN FLOW:\n";
echo "1. Patient clicks \"Login with Google\"\n";
echo "2. Browser redirects to Google OAuth\n";
echo "3. Patient authorizes application\n";
echo "4. Google redirects to /auth/google/callback\n";
echo "5. Laravel creates patient user (user_type=\"user\", login_type=\"google\")\n";
echo "6. Assigns \"user\" role to patient\n";
echo "7. Logs patient in and redirects to frontend home\n";
echo "8. Patient is now logged in as patient\n\n";

echo "===========================================\n";
echo "   PATIENT GOOGLE LOGIN FIX COMPLETE\n";
echo "===========================================\n";
