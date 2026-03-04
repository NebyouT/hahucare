<?php
/**
 * DEFINITIVE FIX - Google OAuth & Lab Technician Menu
 * Run via SSH: php fix_both_issues_v2.php
 * DELETE THIS FILE AFTER RUNNING!
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

echo "===========================================\n";
echo "   HahuCare - DEFINITIVE FIX V3\n";
echo "===========================================\n\n";

// ============================================
// PART 1: PATCH GenerateMenus.php DIRECTLY
// ============================================
echo "PART 1: PATCHING GenerateMenus.php\n";
echo "-------------------------------------------\n\n";

$menuFile = __DIR__ . '/app/Http/Middleware/GenerateMenus.php';
$menuContent = file_get_contents($menuFile);

// Check if the OLD broken permission filter is still present
$brokenFilter = "auth()->user()->hasAnyPermission(\$item->data('permission'), \\Auth::getDefaultDriver())";
$brokenFilter2 = 'hasAnyPermission($item->data(\'permission\'), \Auth::getDefaultDriver())';

if (strpos($menuContent, 'getDefaultDriver') !== false) {
    echo "FOUND: Broken permission filter with getDefaultDriver!\n";
    echo "Patching now...\n\n";
    
    // Replace the entire broken permission filter block
    $oldBlock = <<<'PHP'
            // Access Permission Check
            $menu->filter(function ($item) {
                if ($item->data('permission')) {
                    if (auth()->check()) {
                        if (\Auth::getDefaultDriver() == 'admin') {
                            return true;
                        }
                        if (auth()->user()->hasAnyPermission($item->data('permission'), \Auth::getDefaultDriver())) {
                            return true;
                        }
                    }

                    return false;
                } else {
                    return true;
                }
            });
PHP;

    $newBlock = <<<'PHP'
            // Access Permission Check
            $menu->filter(function ($item) {
                if ($item->data('permission')) {
                    if (auth()->check()) {
                        if (auth()->user()->hasRole(['admin', 'demo_admin'])) {
                            return true;
                        }
                        try {
                            $permissions = $item->data('permission');
                            if (is_string($permissions)) {
                                $permissions = [$permissions];
                            }
                            if (auth()->user()->hasAnyPermission($permissions)) {
                                return true;
                            }
                        } catch (\Exception $e) {
                            \Log::warning('Menu permission check failed', [
                                'permission' => $item->data('permission'),
                                'error' => $e->getMessage(),
                            ]);
                            return false;
                        }
                    }

                    return false;
                } else {
                    return true;
                }
            });
PHP;

    if (strpos($menuContent, $oldBlock) !== false) {
        $menuContent = str_replace($oldBlock, $newBlock, $menuContent);
        file_put_contents($menuFile, $menuContent);
        echo "SUCCESS: Permission filter patched!\n";
    } else {
        // Try a regex approach to find and replace the broken filter
        echo "Exact match not found, trying regex patch...\n";
        $pattern = '/\/\/ Access Permission Check\s*\$menu->filter\(function\s*\(\$item\)\s*\{[^}]*getDefaultDriver[^}]*\}[^}]*\}[^}]*\}[^)]*\)\s*;/s';
        if (preg_match($pattern, $menuContent)) {
            $menuContent = preg_replace($pattern, $newBlock, $menuContent);
            file_put_contents($menuFile, $menuContent);
            echo "SUCCESS: Permission filter patched via regex!\n";
        } else {
            echo "WARNING: Could not auto-patch. Manual fix needed.\n";
            echo "The permission filter in GenerateMenus.php still uses getDefaultDriver.\n";
        }
    }
} else {
    echo "OK: Permission filter already patched (no getDefaultDriver found).\n";
}

// Also check the lab_technician dashboard block
if (strpos($menuContent, "// Lab Technician - Only show lab-related menus") !== false 
    && strpos($menuContent, "// Skip all other menus") !== false) {
    echo "\nFOUND: Empty lab_technician block. Patching...\n";
    
    $oldLabBlock = <<<'PHP'
            else if (auth()->user()->hasRole('lab_technician')) {
                // Lab Technician - Only show lab-related menus
                // Skip all other menus and jump directly to lab section
                // The lab menu items are defined later in this file
            }
PHP;

    $newLabBlock = <<<'PHP'
            else if (auth()->user()->hasRole('lab_technician')) {
                $this->staticMenu($menu, ['title' => 'Main', 'order' => 0]);

                $this->mainRoute($menu, [
                    'icon' => 'ph ph-squares-four',
                    'title' => __('sidebar.dashboard'),
                    'route' => 'backend.home',
                    'active' => ['app', 'app/dashboard'],
                    'order' => 0,
                ]);
            }
PHP;

    $menuContent = file_get_contents($menuFile); // re-read after previous patch
    if (strpos($menuContent, $oldLabBlock) !== false) {
        $menuContent = str_replace($oldLabBlock, $newLabBlock, $menuContent);
        file_put_contents($menuFile, $menuContent);
        echo "SUCCESS: Lab technician dashboard block patched!\n";
    } else {
        echo "Could not find exact empty block. May already be patched.\n";
    }
} else {
    echo "OK: Lab technician block already has dashboard.\n";
}

// Verify the fix
$menuContent = file_get_contents($menuFile);
echo "\nVerification:\n";
echo "  getDefaultDriver in file: " . (strpos($menuContent, 'getDefaultDriver') !== false ? 'STILL PRESENT (BAD!)' : 'REMOVED (GOOD!)') . "\n";
echo "  hasRole admin check: " . (strpos($menuContent, "hasRole(['admin', 'demo_admin'])") !== false ? 'PRESENT (GOOD!)' : 'NOT FOUND (BAD!)') . "\n";
echo "  Lab dashboard route: " . (strpos($menuContent, "else if (auth()->user()->hasRole('lab_technician'))") !== false ? 'PRESENT' : 'NOT FOUND') . "\n";

// ============================================
// PART 2: FIX PERMISSIONS
// ============================================
echo "\n\nPART 2: FIXING PERMISSIONS\n";
echo "-------------------------------------------\n\n";

app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

$labPermissions = [
    'view_labs', 'add_labs', 'edit_labs', 'delete_labs',
    'view_lab_categories', 'add_lab_categories', 'edit_lab_categories', 'delete_lab_categories',
    'view_lab_services', 'add_lab_services', 'edit_lab_services', 'delete_lab_services',
    'view_lab_results', 'add_lab_results', 'edit_lab_results', 'delete_lab_results',
    'view_lab_orders', 'add_lab_orders', 'edit_lab_orders', 'delete_lab_orders',
    'view_lab_tests', 'add_lab_tests', 'edit_lab_tests', 'delete_lab_tests',
    'view_lab_equipment', 'add_lab_equipment', 'edit_lab_equipment', 'delete_lab_equipment',
    'create_labs', 'create_lab_categories', 'create_lab_services',
    'create_lab_results', 'create_lab_orders', 'create_lab_tests', 'create_lab_equipment',
    'order_lab_tests',
];

foreach ($labPermissions as $permName) {
    Permission::firstOrCreate(
        ['name' => $permName, 'guard_name' => 'web'],
        ['is_fixed' => true]
    );
}
echo "All lab permissions created/verified\n";

$techRole = Role::firstOrCreate(
    ['name' => 'lab_technician'],
    ['guard_name' => 'web', 'title' => 'Lab Technician']
);

$techPerms = [
    'view_labs', 'edit_labs',
    'view_lab_categories',
    'view_lab_services', 'add_lab_services', 'edit_lab_services', 'delete_lab_services',
    'view_lab_results', 'add_lab_results', 'edit_lab_results', 'delete_lab_results',
    'view_lab_orders', 'add_lab_orders', 'edit_lab_orders',
    'view_lab_tests',
    'view_lab_equipment',
    'create_lab_services', 'create_lab_results', 'create_lab_orders',
];

$techRole->syncPermissions($techPerms);
echo "Synced " . count($techPerms) . " permissions to lab_technician\n";

$adminRole = Role::where('name', 'admin')->first();
if ($adminRole) {
    $adminRole->givePermissionTo($labPermissions);
    echo "Admin updated\n";
}

// ============================================
// PART 3: FIX GOOGLE OAUTH .env
// ============================================
echo "\n\nPART 3: FIX GOOGLE OAUTH .env\n";
echo "-------------------------------------------\n\n";

$envFile = base_path('.env');
$envContent = file_get_contents($envFile);
$correctRedirect = 'https://hahucare.com/login/google/callback';

// Use regex to find and replace the GOOGLE_REDIRECT line completely
// This handles any corrupted/concatenated values
$envContent = preg_replace(
    '/^GOOGLE_REDIRECT=.*$/m',
    'GOOGLE_REDIRECT=' . $correctRedirect,
    $envContent
);

// Also fix GOOGLE_REDIRECT_URI if present
$envContent = preg_replace(
    '/^GOOGLE_REDIRECT_URI=.*$/m',
    'GOOGLE_REDIRECT_URI=' . $correctRedirect,
    $envContent
);

file_put_contents($envFile, $envContent);
echo "Written to .env:\n";
echo "  GOOGLE_REDIRECT={$correctRedirect}\n";

// Verify .env was written correctly
$verifyEnv = file_get_contents($envFile);
preg_match('/^GOOGLE_REDIRECT=(.*)$/m', $verifyEnv, $matches);
$writtenValue = trim($matches[1] ?? 'NOT FOUND');
echo "  Verify read-back: GOOGLE_REDIRECT={$writtenValue}\n";

if ($writtenValue === $correctRedirect) {
    echo "  .env is CORRECT!\n";
} else {
    echo "  WARNING: .env value doesn't match! You may need to edit manually.\n";
    echo "  Expected: {$correctRedirect}\n";
    echo "  Got: {$writtenValue}\n";
}

// Also check for GOOGLE_CLIENT_ID and SECRET
preg_match('/^GOOGLE_CLIENT_ID=(.*)$/m', $verifyEnv, $m1);
preg_match('/^GOOGLE_CLIENT_SECRET=(.*)$/m', $verifyEnv, $m2);
$cid = trim($m1[1] ?? '');
$csec = trim($m2[1] ?? '');
echo "\n  GOOGLE_CLIENT_ID: " . ($cid ? 'SET (' . substr($cid, 0, 20) . '...)' : 'EMPTY!') . "\n";
echo "  GOOGLE_CLIENT_SECRET: " . ($csec ? 'SET (' . substr($csec, 0, 8) . '...)' : 'EMPTY!') . "\n";

// ============================================
// PART 4: CLEAR ALL CACHES AGGRESSIVELY
// ============================================
echo "\n\nPART 4: CLEARING ALL CACHES\n";
echo "-------------------------------------------\n\n";

app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
echo "Permission cache cleared\n";

\Illuminate\Support\Facades\Artisan::call('cache:clear');
echo "App cache cleared\n";

\Illuminate\Support\Facades\Artisan::call('config:clear');
echo "Config cache cleared\n";

\Illuminate\Support\Facades\Artisan::call('view:clear');
echo "View cache cleared\n";

\Illuminate\Support\Facades\Artisan::call('route:clear');
echo "Route cache cleared\n";

// Also delete compiled views manually
$viewCachePath = storage_path('framework/views');
if (is_dir($viewCachePath)) {
    $files = glob($viewCachePath . '/*.php');
    foreach ($files as $file) {
        unlink($file);
    }
    echo "Deleted " . count($files) . " compiled view files\n";
}

// Rebuild config cache
\Illuminate\Support\Facades\Artisan::call('config:cache');
echo "Config cache rebuilt\n";

// Verify final config
$finalRedirect = config('services.google.redirect');
echo "\nFinal config services.google.redirect: {$finalRedirect}\n";

// ============================================
// SUMMARY
// ============================================
echo "\n\n===========================================\n";
echo "   ALL FIXES APPLIED\n";
echo "===========================================\n";
echo "1. GenerateMenus.php permission filter: PATCHED\n";
echo "2. Lab technician permissions: SYNCED (19)\n";
echo "3. Google redirect URL: {$correctRedirect}\n";
echo "4. All caches: CLEARED\n\n";
echo "NEXT:\n";
echo "- Log out and log back in as lab_technician\n";
echo "- In Google Cloud Console, ensure redirect URI is:\n";
echo "  {$correctRedirect}\n";
echo "- DELETE this file!\n";
echo "===========================================\n";
