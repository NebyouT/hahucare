<?php
/**
 * Check Routes - Verify OAuth routes are registered
 * Run via SSH: php check_routes.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "===========================================\n";
echo "   ROUTE CHECKER\n";
echo "===========================================\n\n";

// Get all routes
$routes = \Illuminate\Support\Facades\Route::getRoutes();

echo "1. CHECKING GOOGLE OAUTH ROUTES\n";
echo "-------------------------------------------\n";

$oauthRoutes = [
    '/login/google' => null,
    '/login/google/callback' => null,
    '/auth/google' => null,
    '/auth/google/callback' => null,
];

foreach ($routes as $route) {
    $uri = $route->uri();
    $name = $route->getName();
    $action = $route->getActionName();
    $methods = implode('|', $route->methods());
    $middleware = implode(', ', $route->middleware());
    
    // Check if this is one of our OAuth routes
    if (isset($oauthRoutes[$uri]) || strpos($uri, 'google') !== false) {
        echo "\nRoute: {$uri}\n";
        echo "  Name: " . ($name ?: 'UNNAMED') . "\n";
        echo "  Methods: {$methods}\n";
        echo "  Action: {$action}\n";
        echo "  Middleware: " . ($middleware ?: 'NONE') . "\n";
        
        if (isset($oauthRoutes[$uri])) {
            $oauthRoutes[$uri] = [
                'name' => $name,
                'action' => $action,
                'middleware' => $middleware,
            ];
        }
    }
}

echo "\n\n2. OAUTH ROUTE STATUS\n";
echo "-------------------------------------------\n";

foreach ($oauthRoutes as $uri => $info) {
    if ($info === null) {
        echo "❌ {$uri} - NOT REGISTERED\n";
    } else {
        echo "✅ {$uri} - REGISTERED\n";
        echo "   Controller: {$info['action']}\n";
        echo "   Middleware: {$info['middleware']}\n";
    }
}

echo "\n\n3. TEST ROUTE MATCHING\n";
echo "-------------------------------------------\n";

$testUrls = [
    '/login/google',
    '/login/google/callback',
    '/auth/google',
    '/auth/google/callback',
];

foreach ($testUrls as $url) {
    try {
        $request = \Illuminate\Http\Request::create($url, 'GET');
        $route = \Illuminate\Support\Facades\Route::getRoutes()->match($request);
        
        echo "\n{$url}:\n";
        echo "  ✅ Matches route: {$route->uri()}\n";
        echo "  Controller: {$route->getActionName()}\n";
        echo "  Middleware: " . implode(', ', $route->middleware()) . "\n";
    } catch (\Exception $e) {
        echo "\n{$url}:\n";
        echo "  ❌ NO MATCH - " . $e->getMessage() . "\n";
    }
}

echo "\n\n4. CHECK MIDDLEWARE ALIASES\n";
echo "-------------------------------------------\n";

$middlewareAliases = app('router')->getMiddleware();
echo "Registered middleware aliases:\n";
foreach ($middlewareAliases as $alias => $class) {
    if (in_array($alias, ['guest', 'auth', 'web'])) {
        echo "  {$alias} => {$class}\n";
    }
}

echo "\n\n5. SIMULATE CALLBACK REQUEST\n";
echo "-------------------------------------------\n";

// Simulate what happens when Google redirects back
$callbackUrl = '/login/google/callback';
echo "Simulating GET request to: {$callbackUrl}\n";

try {
    $request = \Illuminate\Http\Request::create($callbackUrl, 'GET', [
        'code' => 'test_code_12345',
        'state' => 'test_state',
    ]);
    
    // Add session
    $session = new \Illuminate\Session\Store(
        'test-session',
        new \Illuminate\Session\ArraySessionHandler(60)
    );
    $request->setLaravelSession($session);
    
    $route = \Illuminate\Support\Facades\Route::getRoutes()->match($request);
    
    echo "\n✅ Route matched successfully\n";
    echo "Controller: {$route->getActionName()}\n";
    echo "Middleware stack:\n";
    
    $middleware = $route->middleware();
    if (empty($middleware)) {
        echo "  (none)\n";
    } else {
        foreach ($middleware as $m) {
            echo "  - {$m}\n";
        }
    }
    
    // Check if 'guest' middleware will redirect
    if (in_array('guest', $middleware)) {
        echo "\n'guest' middleware is active\n";
        echo "This will redirect authenticated users away\n";
    }
    
} catch (\Exception $e) {
    echo "\n❌ Route matching failed\n";
    echo "Error: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}:{$e->getLine()}\n";
}

echo "\n===========================================\n";
echo "   DONE\n";
echo "===========================================\n";
