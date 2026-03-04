<?php
/**
 * FINAL FIX - Directly patch GenerateMenus.php on production
 * This moves the lab menu items to the correct location
 * Run via SSH: php fix_menu_final.php
 * DELETE THIS FILE AFTER RUNNING!
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "===========================================\n";
echo "   FINAL MENU FIX\n";
echo "===========================================\n\n";

$menuFile = __DIR__ . '/app/Http/Middleware/GenerateMenus.php';
$content = file_get_contents($menuFile);
$lines = explode("\n", $content);

echo "Current situation:\n";
echo "- Line 76-86: lab_technician dashboard (WORKS - we see Dashboard)\n";
echo "- Line 874-921: lab_technician menu items (DOESN'T WORK - not appearing)\n\n";

echo "Checking line 874 context...\n";
for ($i = 870; $i <= 876; $i++) {
    echo "  " . ($i+1) . ": " . trim($lines[$i]) . "\n";
}

// The issue: line 874 is checking hasRole but it's not executing
// Let's add the lab menu items right after the dashboard in the else-if block
echo "\n=== APPLYING FIX ===\n";
echo "Moving lab menu items to line 86 (right after dashboard creation)\n\n";

// Find the lab_technician else-if block (line 76-86)
$labTechBlockStart = 75; // 0-indexed, so line 76
$labTechBlockEnd = 85;   // line 86

// The new menu items to insert
$labMenuItems = <<<'PHP'

                // Laboratory menu items for lab_technician
                $this->staticMenu($menu, ['title' => 'Laboratory', 'order' => 0]);

                $this->mainRoute($menu, [
                    'icon' => 'ph ph-hospital',
                    'title' => 'Labs',
                    'route' => 'backend.labs.index',
                    'active' => ['app/labs'],
                    'permission' => ['view_labs'],
                    'order' => 0,
                ]);

                $this->mainRoute($menu, [
                    'icon' => 'ph ph-folder',
                    'title' => 'Lab Categories',
                    'route' => 'backend.lab-categories.index',
                    'active' => ['app/lab-categories'],
                    'permission' => ['view_lab_categories'],
                    'order' => 0,
                ]);

                $this->mainRoute($menu, [
                    'icon' => 'ph ph-test-tube',
                    'title' => 'Lab Services',
                    'route' => 'backend.lab-services.index',
                    'active' => ['app/lab-services'],
                    'permission' => ['view_lab_services'],
                    'order' => 0,
                ]);

                $this->mainRoute($menu, [
                    'icon' => 'ph ph-clipboard-text',
                    'title' => 'Lab Results',
                    'route' => 'backend.lab-results.index',
                    'active' => ['app/lab-results'],
                    'permission' => ['view_lab_results'],
                    'order' => 0,
                ]);

                $this->mainRoute($menu, [
                    'icon' => 'ph ph-list-bullets',
                    'title' => 'Lab Orders',
                    'route' => 'backend.lab-orders.index',
                    'active' => ['app/lab-orders'],
                    'permission' => ['view_lab_orders'],
                    'order' => 0,
                ]);
PHP;

// Insert before the closing brace of the lab_technician block
// Line 86 is the closing brace, so insert at line 85 (0-indexed 84)
array_splice($lines, 85, 0, explode("\n", $labMenuItems));

$newContent = implode("\n", $lines);
file_put_contents($menuFile, $newContent);

echo "Lab menu items inserted at line 86\n";

// Also comment out the duplicate block at line 874
$content = file_get_contents($menuFile);
$oldBlock = <<<'PHP'
if (auth()->user()->hasRole(['lab_technician'])) {
    $this->staticMenu($menu, ['title' => 'Laboratory', 'order' => 0]);

    $this->mainRoute($menu, [
        'icon' => 'ph ph-hospital',
        'title' => 'Labs',
        'route' => 'backend.labs.index',
        'active' => ['app/labs'],
        'permission' => ['view_labs'],
        'order' => 0,
    ]);

    $this->mainRoute($menu, [
        'icon' => 'ph ph-folder',
        'title' => 'Lab Categories',
        'route' => 'backend.lab-categories.index',
        'active' => ['app/lab-categories'],
        'permission' => ['view_lab_categories'],
        'order' => 0,
    ]);

    $this->mainRoute($menu, [
        'icon' => 'ph ph-test-tube',
        'title' => 'Lab Services',
        'route' => 'backend.lab-services.index',
        'active' => ['app/lab-services'],
        'permission' => ['view_lab_services'],
        'order' => 0,
    ]);

    $this->mainRoute($menu, [
        'icon' => 'ph ph-clipboard-text',
        'title' => 'Lab Results',
        'route' => 'backend.lab-results.index',
        'active' => ['app/lab-results'],
        'permission' => ['view_lab_results'],
        'order' => 0,
    ]);

    $this->mainRoute($menu, [
        'icon' => 'ph ph-list-bullets',
        'title' => 'Lab Orders',
        'route' => 'backend.lab-orders.index',
        'active' => ['app/lab-orders'],
        'permission' => ['view_lab_orders'],
        'order' => 0,
    ]);
}
PHP;

$commentedBlock = "// Lab menu items moved to line 86 (inside the else-if block)\n// " . str_replace("\n", "\n// ", $oldBlock);

$content = str_replace($oldBlock, $commentedBlock, $content);
file_put_contents($menuFile, $content);

echo "Commented out duplicate block at original location\n";

// Clear caches
echo "\n=== CLEARING CACHES ===\n";
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache reset\n";
}
if (function_exists('opcache_invalidate')) {
    opcache_invalidate($menuFile, true);
    echo "GenerateMenus.php invalidated from OPcache\n";
}

\Illuminate\Support\Facades\Artisan::call('cache:clear');
\Illuminate\Support\Facades\Artisan::call('config:clear');
\Illuminate\Support\Facades\Artisan::call('view:clear');
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
echo "All Laravel caches cleared\n";

// Test the fix
echo "\n=== TESTING FIX ===\n";
$labUser = \App\Models\User::role('lab_technician')->first();
if ($labUser) {
    \Auth::login($labUser);
    $menuGen = new \App\Http\Middleware\GenerateMenus();
    $menu = $menuGen->handle();
    $items = $menu->all();
    
    echo "Total menu items: " . count($items) . "\n";
    echo "Menu items:\n";
    foreach ($items as $item) {
        $title = strip_tags($item->title);
        if (trim($title)) {
            echo "  - {$title}\n";
        }
    }
    
    if (count($items) > 5) {
        echo "\n*** SUCCESS! Lab menu items are now showing! ***\n";
    } else {
        echo "\n*** STILL ONLY " . count($items) . " ITEMS - FIX DID NOT WORK ***\n";
    }
}

echo "\n===========================================\n";
echo "   DONE\n";
echo "===========================================\n";
echo "Now:\n";
echo "1. Log out from browser\n";
echo "2. Clear browser cache\n";
echo "3. Log back in as lab_technician\n";
echo "4. DELETE THIS FILE: rm fix_menu_final.php\n";
echo "===========================================\n";
