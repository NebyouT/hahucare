<?php
/**
 * Fix Both Issues V2 - Google OAuth & Lab Technician Menu
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
echo "   HahuCare - Fix Both Issues V2\n";
echo "===========================================\n\n";

// ============================================
// PART 1: DIAGNOSE LAB TECHNICIAN ISSUE
// ============================================
echo "PART 1: LAB TECHNICIAN DEEP DIAGNOSIS\n";
echo "-------------------------------------------\n\n";

// Check role exists
$techRole = Role::where('name', 'lab_technician')->first();
if (!$techRole) {
    echo "ERROR: lab_technician role does NOT exist!\n";
    $techRole = Role::create(['name' => 'lab_technician', 'guard_name' => 'web', 'title' => 'Lab Technician']);
    echo "CREATED lab_technician role (ID: {$techRole->id})\n";
} else {
    echo "OK: lab_technician role exists (ID: {$techRole->id}, guard: {$techRole->guard_name})\n";
}

// Check what permissions the role has
$rolePerms = $techRole->permissions()->pluck('name', 'guard_name')->toArray();
echo "\nRole permissions (" . count($rolePerms) . " total):\n";
foreach ($rolePerms as $guard => $name) {
    echo "  - {$name} (guard: {$guard})\n";
}

// Get all role permissions properly
$allRolePerms = $techRole->permissions()->get();
echo "\nDetailed role permissions:\n";
foreach ($allRolePerms as $p) {
    echo "  - {$p->name} (id: {$p->id}, guard: {$p->guard_name})\n";
}

// Check a specific lab_technician user
$labUser = User::role('lab_technician')->first();
if ($labUser) {
    echo "\nChecking user: {$labUser->email} (ID: {$labUser->id})\n";
    echo "  user_type: " . ($labUser->user_type ?? 'NULL') . "\n";
    
    // Check roles
    $userRoles = $labUser->getRoleNames()->toArray();
    echo "  Roles: " . implode(', ', $userRoles) . "\n";
    
    // Check hasRole
    echo "  hasRole('lab_technician'): " . ($labUser->hasRole('lab_technician') ? 'YES' : 'NO') . "\n";
    
    // Check specific permissions
    $checkPerms = ['view_labs', 'view_lab_categories', 'view_lab_services', 'view_lab_results', 'view_lab_orders'];
    echo "\n  Permission checks:\n";
    foreach ($checkPerms as $perm) {
        $hasPerm = $labUser->hasPermissionTo($perm);
        $canPerm = $labUser->can($perm);
        echo "    {$perm}: hasPermissionTo=" . ($hasPerm ? 'YES' : 'NO') . ", can=" . ($canPerm ? 'YES' : 'NO') . "\n";
    }
    
    // Check hasAnyPermission (this is what the menu filter uses)
    echo "\n  hasAnyPermission checks (menu filter uses this):\n";
    foreach ($checkPerms as $perm) {
        try {
            $has = $labUser->hasAnyPermission([$perm]);
            echo "    hasAnyPermission(['{$perm}']): " . ($has ? 'YES' : 'NO') . "\n";
        } catch (\Exception $e) {
            echo "    hasAnyPermission(['{$perm}']): ERROR - " . $e->getMessage() . "\n";
        }
    }
    
    // Check Auth guard default driver
    echo "\n  Auth::getDefaultDriver(): " . \Auth::getDefaultDriver() . "\n";
    
    // Simulate menu filter logic
    echo "\n  Simulating menu filter with guard param:\n";
    $guard = \Auth::getDefaultDriver();
    foreach ($checkPerms as $perm) {
        try {
            $has = $labUser->hasAnyPermission([$perm], $guard);
            echo "    hasAnyPermission(['{$perm}'], '{$guard}'): " . ($has ? 'YES' : 'NO') . "\n";
        } catch (\Exception $e) {
            echo "    hasAnyPermission(['{$perm}'], '{$guard}'): ERROR - " . $e->getMessage() . "\n";
        }
    }

    // Check all permissions the user actually has
    $allUserPerms = $labUser->getAllPermissions();
    echo "\n  All user permissions (" . $allUserPerms->count() . " total):\n";
    foreach ($allUserPerms as $p) {
        echo "    - {$p->name} (guard: {$p->guard_name})\n";
    }
} else {
    echo "\nWARNING: No users found with lab_technician role!\n";
}

// ============================================
// PART 2: FIX - ENSURE PERMISSIONS ARE CORRECT
// ============================================
echo "\n\nPART 2: FIXING PERMISSIONS\n";
echo "-------------------------------------------\n\n";

// Clear permission cache first
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
echo "Permission cache cleared\n";

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

// Create all permissions with guard_name = 'web'
foreach ($labPermissions as $permName) {
    Permission::firstOrCreate(
        ['name' => $permName, 'guard_name' => 'web'],
        ['is_fixed' => true]
    );
}
echo "All lab permissions created/verified\n";

// Permissions for lab_technician
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
echo "Synced " . count($techPerms) . " permissions to lab_technician role\n";

// Also update admin
$adminRole = Role::where('name', 'admin')->first();
if ($adminRole) {
    $adminRole->givePermissionTo($labPermissions);
    echo "Admin role updated with all lab permissions\n";
}

// Clear all caches
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
\Illuminate\Support\Facades\Artisan::call('cache:clear');
\Illuminate\Support\Facades\Artisan::call('config:clear');
\Illuminate\Support\Facades\Artisan::call('view:clear');
\Illuminate\Support\Facades\Artisan::call('route:clear');
echo "All caches cleared\n";

// Verify after fix
echo "\nVerification after fix:\n";
$techRole = Role::where('name', 'lab_technician')->first();
$afterPerms = $techRole->permissions()->pluck('name')->toArray();
echo "Lab technician now has " . count($afterPerms) . " permissions:\n";
foreach ($afterPerms as $p) {
    echo "  - {$p}\n";
}

if ($labUser) {
    // Re-fetch user
    $labUser = User::find($labUser->id);
    echo "\nRe-checking user {$labUser->email}:\n";
    foreach (['view_labs', 'view_lab_categories', 'view_lab_services', 'view_lab_results', 'view_lab_orders'] as $perm) {
        echo "  can('{$perm}'): " . ($labUser->can($perm) ? 'YES' : 'NO') . "\n";
    }
}

// ============================================
// PART 3: GOOGLE OAUTH REDIRECT FIX
// ============================================
echo "\n\nPART 3: GOOGLE OAUTH REDIRECT MISMATCH\n";
echo "-------------------------------------------\n\n";

$envRedirect = env('GOOGLE_REDIRECT');
$envRedirectUri = env('GOOGLE_REDIRECT_URI');
$configRedirect = config('services.google.redirect');

// Get actual route URL
try {
    $actualRoute = route('social.login.callback', 'google');
} catch (\Exception $e) {
    $actualRoute = 'ERROR: ' . $e->getMessage();
}

echo "GOOGLE_REDIRECT env:      {$envRedirect}\n";
echo "GOOGLE_REDIRECT_URI env:  {$envRedirectUri}\n";
echo "config services.google:   {$configRedirect}\n";
echo "Actual route URL:         {$actualRoute}\n";

if ($configRedirect !== $actualRoute) {
    echo "\n*** MISMATCH DETECTED! ***\n";
    echo "Your .env GOOGLE_REDIRECT is:  {$envRedirect}\n";
    echo "But the actual route URL is:   {$actualRoute}\n";
    echo "\nFIX: Update your .env file:\n";
    echo "  GOOGLE_REDIRECT={$actualRoute}\n";
    echo "\nAND in Google Cloud Console, set Authorized Redirect URI to:\n";
    echo "  {$actualRoute}\n";
    
    // Try to fix the .env file automatically
    $envFile = base_path('.env');
    if (file_exists($envFile)) {
        $envContent = file_get_contents($envFile);
        $oldLine = "GOOGLE_REDIRECT={$envRedirect}";
        $newLine = "GOOGLE_REDIRECT={$actualRoute}";
        
        if (strpos($envContent, $oldLine) !== false) {
            $envContent = str_replace($oldLine, $newLine, $envContent);
            file_put_contents($envFile, $envContent);
            echo "\n*** .env file UPDATED automatically! ***\n";
            echo "  Changed: {$oldLine}\n";
            echo "  To:      {$newLine}\n";
        } else {
            echo "\nCould not auto-fix .env - please update manually\n";
        }
        
        // Also fix GOOGLE_REDIRECT_URI if it exists
        if ($envRedirectUri && $envRedirectUri !== $actualRoute) {
            $oldLine2 = "GOOGLE_REDIRECT_URI={$envRedirectUri}";
            $newLine2 = "GOOGLE_REDIRECT_URI={$actualRoute}";
            if (strpos($envContent, $oldLine2) !== false) {
                $envContent = str_replace($oldLine2, $newLine2, $envContent);
                file_put_contents($envFile, $envContent);
                echo "  Also fixed GOOGLE_REDIRECT_URI\n";
            }
        }
    }
} else {
    echo "\nOK: Redirect URLs match!\n";
}

// Clear config cache after .env change
\Illuminate\Support\Facades\Artisan::call('config:clear');
\Illuminate\Support\Facades\Artisan::call('config:cache');
echo "\nConfig cache rebuilt after .env change\n";

// Verify new config
$newConfigRedirect = config('services.google.redirect');
echo "New config redirect: {$newConfigRedirect}\n";

echo "\n\n===========================================\n";
echo "   ALSO IMPORTANT:\n";
echo "===========================================\n";
echo "1. In Google Cloud Console -> APIs & Credentials:\n";
echo "   Set Authorized Redirect URI to: {$actualRoute}\n";
echo "2. Log out and log back in as lab_technician\n";
echo "3. DELETE THIS FILE: fix_both_issues_v2.php\n";
echo "===========================================\n";
