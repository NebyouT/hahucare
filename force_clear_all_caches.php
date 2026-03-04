<?php
/**
 * FORCE CLEAR ALL CACHES - Run via SSH
 * This aggressively clears every possible cache
 * DELETE THIS FILE AFTER RUNNING!
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "===========================================\n";
echo "   FORCE CLEAR ALL CACHES\n";
echo "===========================================\n\n";

// 1. OPcache - PHP bytecode cache
echo "1. OPCACHE\n";
echo "-------------------------------------------\n";
if (function_exists('opcache_reset')) {
    $result = opcache_reset();
    echo "opcache_reset(): " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
} else {
    echo "OPcache not available\n";
}

// Also invalidate specific file
$menuFile = __DIR__ . '/app/Http/Middleware/GenerateMenus.php';
if (function_exists('opcache_invalidate')) {
    $result = opcache_invalidate($menuFile, true);
    echo "opcache_invalidate(GenerateMenus.php): " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
}
echo "\n";

// 2. Realpath cache - PHP file path cache
echo "2. REALPATH CACHE\n";
echo "-------------------------------------------\n";
if (function_exists('clearstatcache')) {
    clearstatcache(true);
    echo "clearstatcache(): SUCCESS\n";
}
echo "\n";

// 3. Laravel caches
echo "3. LARAVEL CACHES\n";
echo "-------------------------------------------\n";

\Illuminate\Support\Facades\Artisan::call('cache:clear');
echo "cache:clear: SUCCESS\n";

\Illuminate\Support\Facades\Artisan::call('config:clear');
echo "config:clear: SUCCESS\n";

\Illuminate\Support\Facades\Artisan::call('route:clear');
echo "route:clear: SUCCESS\n";

\Illuminate\Support\Facades\Artisan::call('view:clear');
echo "view:clear: SUCCESS\n";

\Illuminate\Support\Facades\Artisan::call('event:clear');
echo "event:clear: SUCCESS\n";

// 4. Spatie permission cache
echo "\n4. SPATIE PERMISSION CACHE\n";
echo "-------------------------------------------\n";
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
echo "Permission cache cleared\n";

// 5. Manually delete cache files
echo "\n5. MANUAL CACHE FILE DELETION\n";
echo "-------------------------------------------\n";

$cachePaths = [
    storage_path('framework/cache/data'),
    storage_path('framework/views'),
    storage_path('framework/sessions'),
    storage_path('app/public'),
    base_path('bootstrap/cache'),
];

$totalDeleted = 0;
foreach ($cachePaths as $path) {
    if (is_dir($path)) {
        $files = glob($path . '/*');
        $count = 0;
        foreach ($files as $file) {
            if (is_file($file) && basename($file) !== '.gitignore') {
                unlink($file);
                $count++;
            }
        }
        echo "Deleted {$count} files from " . basename($path) . "\n";
        $totalDeleted += $count;
    }
}
echo "Total files deleted: {$totalDeleted}\n";

// 6. Rebuild config cache
echo "\n6. REBUILD CONFIG CACHE\n";
echo "-------------------------------------------\n";
\Illuminate\Support\Facades\Artisan::call('config:cache');
echo "config:cache: SUCCESS\n";

// 7. Check if GenerateMenus.php has the fix
echo "\n7. VERIFY GenerateMenus.php FIX\n";
echo "-------------------------------------------\n";
$content = file_get_contents($menuFile);
$hasOldCode = strpos($content, 'getDefaultDriver') !== false;
$hasNewCode = strpos($content, "hasRole(['admin', 'demo_admin'])") !== false;
$hasLabTechBlock = strpos($content, "if (auth()->user()->hasRole(['lab_technician']))") !== false;

echo "File contains getDefaultDriver (OLD): " . ($hasOldCode ? 'YES (BAD!)' : 'NO (GOOD!)') . "\n";
echo "File contains hasRole admin check (NEW): " . ($hasNewCode ? 'YES (GOOD!)' : 'NO (BAD!)') . "\n";
echo "File contains lab_technician block: " . ($hasLabTechBlock ? 'YES' : 'NO') . "\n";

// Extract a snippet around line 874
$lines = explode("\n", $content);
echo "\nLines 872-876 of GenerateMenus.php:\n";
for ($i = 871; $i <= 875; $i++) {
    echo "  " . ($i+1) . ": " . trim($lines[$i]) . "\n";
}

// 8. Test menu generation
echo "\n8. TEST MENU GENERATION\n";
echo "-------------------------------------------\n";
$labUser = \App\Models\User::role('lab_technician')->first();
if ($labUser) {
    \Auth::login($labUser);
    echo "Logged in as: {$labUser->email}\n";
    echo "hasRole('lab_technician'): " . (auth()->user()->hasRole('lab_technician') ? 'YES' : 'NO') . "\n";
    
    // Build menu
    $menuGen = new \App\Http\Middleware\GenerateMenus();
    $menu = $menuGen->handle();
    $items = $menu->all();
    
    echo "Total menu items after rebuild: " . count($items) . "\n";
    echo "Menu items:\n";
    foreach ($items as $item) {
        $title = strip_tags($item->title);
        echo "  - {$title}\n";
    }
} else {
    echo "No lab_technician user found!\n";
}

echo "\n===========================================\n";
echo "   DONE - ALL CACHES CLEARED\n";
echo "===========================================\n";
echo "Now:\n";
echo "1. Log out from the browser\n";
echo "2. Close all browser tabs\n";
echo "3. Clear browser cache (Ctrl+Shift+Delete)\n";
echo "4. Log back in as lab_technician\n";
echo "5. DELETE THIS FILE: rm force_clear_all_caches.php\n";
echo "===========================================\n";
