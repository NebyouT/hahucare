<?php
/**
 * Debug Menu - Run as SSH: php debug_menu.php
 * This simulates exactly what the sidebar does and shows every menu item
 * DELETE THIS FILE AFTER RUNNING!
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

// Find the lab technician user
$labUser = User::role('lab_technician')->first();
if (!$labUser) {
    echo "ERROR: No lab_technician user found!\n";
    exit(1);
}

echo "=== DEBUG MENU FOR LAB TECHNICIAN ===\n";
echo "User: {$labUser->email} (ID: {$labUser->id})\n";
echo "Roles: " . $labUser->getRoleNames()->implode(', ') . "\n\n";

// Login as this user
\Auth::login($labUser);
echo "Logged in as: " . auth()->user()->email . "\n";
echo "hasRole('lab_technician'): " . (auth()->user()->hasRole('lab_technician') ? 'YES' : 'NO') . "\n\n";

// Now build the menu exactly like the sidebar does
echo "=== BUILDING MENU ===\n\n";

$menuGenerator = new \App\Http\Middleware\GenerateMenus();
$menu = $menuGenerator->handle();

echo "Menu type: " . get_class($menu) . "\n";

// Get all items BEFORE any filtering might happen
// The menu object from lavary/laravel-menu
$allItems = $menu->all();
echo "Total menu items: " . count($allItems) . "\n\n";

echo "=== ALL MENU ITEMS (after build + filter) ===\n";
foreach ($allItems as $item) {
    $perm = $item->data('permission');
    $permStr = is_array($perm) ? implode(',', $perm) : ($perm ?: 'NONE');
    $title = strip_tags($item->title);
    $url = $item->url();
    $hasParent = $item->hasParent() ? 'child of ' . $item->parent()->id : 'root';
    echo "  [{$item->id}] {$title} | url={$url} | perm={$permStr} | {$hasParent}\n";
}

echo "\n=== ROOT ITEMS (what sidebar renders) ===\n";
$roots = $menu->roots();
echo "Root items count: " . count($roots) . "\n";
foreach ($roots as $item) {
    $perm = $item->data('permission');
    $permStr = is_array($perm) ? implode(',', $perm) : ($perm ?: 'NONE');
    $title = strip_tags($item->title);
    echo "  [{$item->id}] {$title} | perm={$permStr}\n";
    
    if ($item->hasChildren()) {
        foreach ($item->children() as $child) {
            $childPerm = $child->data('permission');
            $childPermStr = is_array($childPerm) ? implode(',', $childPerm) : ($childPerm ?: 'NONE');
            $childTitle = strip_tags($child->title);
            echo "    [{$child->id}] {$childTitle} | perm={$childPermStr}\n";
        }
    }
}

// Now test the permission check directly
echo "\n=== PERMISSION CHECK SIMULATION ===\n";
$testPerms = ['view_labs', 'view_lab_categories', 'view_lab_services', 'view_lab_results', 'view_lab_orders'];
foreach ($testPerms as $p) {
    try {
        $result = auth()->user()->hasAnyPermission([$p]);
        echo "  hasAnyPermission(['{$p}']): " . ($result ? 'YES' : 'NO') . "\n";
    } catch (\Exception $e) {
        echo "  hasAnyPermission(['{$p}']): EXCEPTION - " . $e->getMessage() . "\n";
    }
}

// Check if there's a PermissionDoesNotExist exception being thrown
echo "\n=== CHECK IF ANY PERMISSIONS DON'T EXIST ===\n";
$allPermsInDb = \Spatie\Permission\Models\Permission::where('guard_name', 'web')->pluck('name')->toArray();
foreach ($testPerms as $p) {
    echo "  '{$p}' exists in DB: " . (in_array($p, $allPermsInDb) ? 'YES' : 'NO') . "\n";
}

// Check the actual GenerateMenus.php file content for the filter
echo "\n=== CHECKING GenerateMenus.php FILTER CODE ===\n";
$menuFile = __DIR__ . '/app/Http/Middleware/GenerateMenus.php';
$content = file_get_contents($menuFile);

// Find the filter section
if (preg_match('/\/\/ Access Permission Check(.{500})/s', $content, $m)) {
    echo substr($m[0], 0, 500) . "\n";
} else {
    echo "Could not find 'Access Permission Check' in file!\n";
}

// Check for OPcache
echo "\n=== OPCACHE STATUS ===\n";
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status(false);
    if ($status && $status['opcache_enabled']) {
        echo "OPcache is ENABLED!\n";
        echo "This could be serving the OLD GenerateMenus.php from cache!\n";
        echo "Invalidating GenerateMenus.php from OPcache...\n";
        $result = opcache_invalidate($menuFile, true);
        echo "opcache_invalidate result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
        
        // Also try to reset all
        if (function_exists('opcache_reset')) {
            opcache_reset();
            echo "Full OPcache reset done!\n";
        }
    } else {
        echo "OPcache is disabled or not available\n";
    }
} else {
    echo "OPcache extension not loaded\n";
}

// Check for recent Laravel log errors about menu
echo "\n=== RECENT LOG ERRORS (menu/permission related) ===\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logSize = filesize($logFile);
    echo "Log file size: " . number_format($logSize) . " bytes\n";
    
    // Read last 50KB
    $fh = fopen($logFile, 'r');
    $readSize = min($logSize, 50000);
    fseek($fh, -$readSize, SEEK_END);
    $logContent = fread($fh, $readSize);
    fclose($fh);
    
    // Find menu/permission related entries
    $lines = explode("\n", $logContent);
    $relevant = [];
    foreach ($lines as $line) {
        if (stripos($line, 'menu') !== false || stripos($line, 'permission') !== false || stripos($line, 'Menu permission check failed') !== false) {
            $relevant[] = trim($line);
        }
    }
    
    if (count($relevant) > 0) {
        echo "Found " . count($relevant) . " relevant log entries:\n";
        foreach (array_slice($relevant, -10) as $line) {
            echo "  " . substr($line, 0, 200) . "\n";
        }
    } else {
        echo "No menu/permission related log entries found in last 50KB\n";
    }

    // Also check for Google login errors
    echo "\n=== RECENT GOOGLE LOGIN ERRORS ===\n";
    $googleLines = [];
    foreach ($lines as $line) {
        if (stripos($line, 'google') !== false || stripos($line, 'social') !== false || stripos($line, 'oauth') !== false || stripos($line, 'socialite') !== false) {
            $googleLines[] = trim($line);
        }
    }
    if (count($googleLines) > 0) {
        echo "Found " . count($googleLines) . " Google/OAuth log entries:\n";
        foreach (array_slice($googleLines, -15) as $line) {
            echo "  " . substr($line, 0, 300) . "\n";
        }
    } else {
        echo "No Google/OAuth log entries found\n";
    }
} else {
    echo "Log file not found!\n";
}

echo "\n=== DONE ===\n";
echo "DELETE THIS FILE: rm debug_menu.php\n";
