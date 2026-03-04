<?php

/**
 * Fix All Issues - Google OAuth & Lab Technician Permissions
 * 
 * Upload this file to your production server root and run it via browser:
 * https://yourdomain.com/fix_all_issues.php
 * 
 * DELETE THIS FILE AFTER RUNNING!
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

echo "<pre style='font-family: monospace; background: #1a1a2e; color: #eee; padding: 20px; border-radius: 10px;'>";
echo "===========================================\n";
echo "   HahuCare - Fix All Issues Script\n";
echo "===========================================\n\n";

// ============================================
// PART 1: FIX LAB TECHNICIAN PERMISSIONS
// ============================================
echo "<span style='color: #00d9ff;'>📋 PART 1: LAB TECHNICIAN PERMISSIONS</span>\n";
echo "-------------------------------------------\n\n";

// Step 1: Create all lab permissions (using both naming conventions)
$permissions = [
    // Using add_ convention (matches GenerateMenus.php)
    'view_labs',
    'add_labs',
    'edit_labs',
    'delete_labs',
    'view_lab_categories',
    'add_lab_categories',
    'edit_lab_categories',
    'delete_lab_categories',
    'view_lab_services',
    'add_lab_services',
    'edit_lab_services',
    'delete_lab_services',
    'view_lab_results',
    'add_lab_results',
    'edit_lab_results',
    'delete_lab_results',
    'view_lab_orders',
    'add_lab_orders',
    'edit_lab_orders',
    'delete_lab_orders',
    // Using create_ convention (for compatibility)
    'create_labs',
    'create_lab_categories',
    'create_lab_services',
    'create_lab_results',
    'create_lab_orders',
    // Lab tests
    'view_lab_tests',
    'add_lab_tests',
    'edit_lab_tests',
    'delete_lab_tests',
    'create_lab_tests',
    // Lab equipment
    'view_lab_equipment',
    'add_lab_equipment',
    'edit_lab_equipment',
    'delete_lab_equipment',
    'create_lab_equipment',
    // Order lab tests from encounter
    'order_lab_tests',
];

echo "Step 1: Creating permissions...\n";
$createdCount = 0;
foreach ($permissions as $permission) {
    $perm = Permission::firstOrCreate(
        ['name' => $permission],
        ['guard_name' => 'web', 'is_fixed' => true]
    );
    if ($perm->wasRecentlyCreated) {
        $createdCount++;
        echo "   <span style='color: #00ff88;'>✅ Created: {$permission}</span>\n";
    }
}
echo "   Created {$createdCount} new permissions\n\n";

// Step 2: Get or create lab_technician role
echo "Step 2: Setting up lab_technician role...\n";
$technicianRole = Role::firstOrCreate(
    ['name' => 'lab_technician'],
    ['guard_name' => 'web', 'title' => 'Lab Technician', 'is_fixed' => true]
);
echo "   <span style='color: #00ff88;'>✅ Role: lab_technician (ID: {$technicianRole->id})</span>\n\n";

// Step 3: Assign permissions to lab_technician
echo "Step 3: Assigning permissions to lab_technician...\n";
$labTechPermissions = [
    // Labs
    'view_labs',
    'edit_labs',
    // Categories
    'view_lab_categories',
    // Services
    'view_lab_services',
    'add_lab_services',
    'edit_lab_services',
    'delete_lab_services',
    'create_lab_services',
    // Results
    'view_lab_results',
    'add_lab_results',
    'edit_lab_results',
    'delete_lab_results',
    'create_lab_results',
    // Orders
    'view_lab_orders',
    'add_lab_orders',
    'edit_lab_orders',
    'create_lab_orders',
    // Tests
    'view_lab_tests',
    // Equipment
    'view_lab_equipment',
];

// Filter to only existing permissions
$existingPermissions = Permission::whereIn('name', $labTechPermissions)->pluck('name')->toArray();
$technicianRole->syncPermissions($existingPermissions);
echo "   <span style='color: #00ff88;'>✅ Synced " . count($existingPermissions) . " permissions</span>\n\n";

// Step 4: Assign all permissions to admin and demo_admin
echo "Step 4: Updating admin roles...\n";
$adminRole = Role::where('name', 'admin')->first();
if ($adminRole) {
    $adminRole->givePermissionTo($permissions);
    echo "   <span style='color: #00ff88;'>✅ Admin role updated</span>\n";
}

$demoAdminRole = Role::where('name', 'demo_admin')->first();
if ($demoAdminRole) {
    $demoAdminRole->givePermissionTo($permissions);
    echo "   <span style='color: #00ff88;'>✅ Demo Admin role updated</span>\n";
}

// Step 5: Check lab_technician users
echo "\nStep 5: Checking lab_technician users...\n";
$labTechUsers = User::role('lab_technician')->get();
echo "   Found <span style='color: #ffcc00;'>" . $labTechUsers->count() . "</span> lab technician user(s):\n";
foreach ($labTechUsers as $user) {
    echo "   - {$user->email} (ID: {$user->id})\n";
    $userPerms = $user->getAllPermissions()->pluck('name')->toArray();
    echo "     Permissions: " . count($userPerms) . " total\n";
}

// ============================================
// PART 2: GOOGLE OAUTH DIAGNOSTICS
// ============================================
echo "\n\n<span style='color: #00d9ff;'>📋 PART 2: GOOGLE OAUTH DIAGNOSTICS</span>\n";
echo "-------------------------------------------\n\n";

echo "Step 1: Checking environment variables...\n";
$googleClientId = env('GOOGLE_CLIENT_ID');
$googleClientSecret = env('GOOGLE_CLIENT_SECRET');
$googleRedirect = env('GOOGLE_REDIRECT');
$googleRedirectUri = env('GOOGLE_REDIRECT_URI');

if ($googleClientId) {
    echo "   <span style='color: #00ff88;'>✅ GOOGLE_CLIENT_ID: Set (" . substr($googleClientId, 0, 30) . "...)</span>\n";
} else {
    echo "   <span style='color: #ff4444;'>❌ GOOGLE_CLIENT_ID: NOT SET</span>\n";
}

if ($googleClientSecret) {
    echo "   <span style='color: #00ff88;'>✅ GOOGLE_CLIENT_SECRET: Set (" . substr($googleClientSecret, 0, 10) . "...)</span>\n";
} else {
    echo "   <span style='color: #ff4444;'>❌ GOOGLE_CLIENT_SECRET: NOT SET</span>\n";
}

if ($googleRedirect) {
    echo "   <span style='color: #00ff88;'>✅ GOOGLE_REDIRECT: {$googleRedirect}</span>\n";
} else {
    echo "   <span style='color: #ff4444;'>❌ GOOGLE_REDIRECT: NOT SET</span>\n";
}

echo "\nStep 2: Checking config/services.php...\n";
$googleConfig = config('services.google');
echo "   client_id: " . ($googleConfig['client_id'] ? '<span style="color: #00ff88;">✅ Set</span>' : '<span style="color: #ff4444;">❌ NOT SET</span>') . "\n";
echo "   client_secret: " . ($googleConfig['client_secret'] ? '<span style="color: #00ff88;">✅ Set</span>' : '<span style="color: #ff4444;">❌ NOT SET</span>') . "\n";
echo "   redirect: " . ($googleConfig['redirect'] ?? '<span style="color: #ff4444;">❌ NOT SET</span>') . "\n";

echo "\nStep 3: Checking routes...\n";
$socialLoginRoute = route('social.login', 'google');
$socialCallbackRoute = route('social.login.callback', 'google');
echo "   Login URL: {$socialLoginRoute}\n";
echo "   Callback URL: {$socialCallbackRoute}\n";

echo "\nStep 4: Checking recent Google-related logs...\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -100);
    $googleLogs = [];
    foreach ($lastLines as $line) {
        if (stripos($line, 'google') !== false || stripos($line, 'oauth') !== false || stripos($line, 'social') !== false) {
            $googleLogs[] = trim($line);
        }
    }
    if (count($googleLogs) > 0) {
        echo "   Found " . count($googleLogs) . " relevant log entries:\n";
        foreach (array_slice($googleLogs, -5) as $log) {
            echo "   <span style='color: #ffcc00;'>🔍 " . substr($log, 0, 150) . "...</span>\n";
        }
    } else {
        echo "   <span style='color: #ffcc00;'>⚠️ No recent Google/OAuth logs found</span>\n";
    }
} else {
    echo "   <span style='color: #ff4444;'>❌ No log file found</span>\n";
}

// ============================================
// PART 3: CLEAR CACHES
// ============================================
echo "\n\n<span style='color: #00d9ff;'>📋 PART 3: CLEARING CACHES</span>\n";
echo "-------------------------------------------\n\n";

app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
echo "   <span style='color: #00ff88;'>✅ Permission cache cleared</span>\n";

\Illuminate\Support\Facades\Artisan::call('cache:clear');
echo "   <span style='color: #00ff88;'>✅ Application cache cleared</span>\n";

\Illuminate\Support\Facades\Artisan::call('config:clear');
echo "   <span style='color: #00ff88;'>✅ Config cache cleared</span>\n";

\Illuminate\Support\Facades\Artisan::call('view:clear');
echo "   <span style='color: #00ff88;'>✅ View cache cleared</span>\n";

\Illuminate\Support\Facades\Artisan::call('route:clear');
echo "   <span style='color: #00ff88;'>✅ Route cache cleared</span>\n";

// ============================================
// SUMMARY
// ============================================
echo "\n\n===========================================\n";
echo "   <span style='color: #00ff88;'>✅ ALL FIXES APPLIED!</span>\n";
echo "===========================================\n\n";

echo "<span style='color: #ffcc00;'>📝 NEXT STEPS:</span>\n\n";

echo "1. <span style='color: #00d9ff;'>For Lab Technician:</span>\n";
echo "   - Logout and login again as lab_technician\n";
echo "   - You should now see Labs, Lab Categories, Lab Services, Lab Results, Lab Orders\n\n";

echo "2. <span style='color: #00d9ff;'>For Google OAuth:</span>\n";
echo "   - Ensure your .env has correct values:\n";
echo "     GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com\n";
echo "     GOOGLE_CLIENT_SECRET=your-secret\n";
echo "     GOOGLE_REDIRECT=https://yourdomain.com/login/google/callback\n\n";
echo "   - In Google Cloud Console, add this Authorized redirect URI:\n";
echo "     <span style='color: #00ff88;'>{$socialCallbackRoute}</span>\n\n";
echo "   - Try Google login again - errors will now be logged to storage/logs/laravel.log\n\n";

echo "3. <span style='color: #ff4444;'>⚠️ DELETE THIS FILE NOW:</span> fix_all_issues.php\n\n";

echo "</pre>";
