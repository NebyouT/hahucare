<?php

/**
 * Fix Lab Technician Permissions
 * 
 * Upload this file to your production server root and run it via browser:
 * https://hahucare.com/fix_lab_technician_permissions.php
 * 
 * DELETE THIS FILE AFTER RUNNING!
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

echo "<pre>";
echo "===========================================\n";
echo "   Lab Technician Permissions Fix\n";
echo "===========================================\n\n";

// Step 1: Create all lab permissions
$permissions = [
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
];

echo "📋 Step 1: Creating permissions...\n";
foreach ($permissions as $permission) {
    $perm = Permission::firstOrCreate(
        ['name' => $permission],
        ['guard_name' => 'web']
    );
    echo "   ✅ {$permission}\n";
}

// Step 2: Get or create lab_technician role
echo "\n📋 Step 2: Setting up lab_technician role...\n";
$technicianRole = Role::firstOrCreate(
    ['name' => 'lab_technician'],
    ['guard_name' => 'web', 'title' => 'Lab Technician']
);
echo "   ✅ Role: lab_technician (ID: {$technicianRole->id})\n";

// Step 3: Assign permissions to lab_technician
echo "\n📋 Step 3: Assigning permissions to lab_technician...\n";
$labTechPermissions = [
    'view_labs',
    'edit_labs',
    'view_lab_categories',
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
];

// Sync permissions (this will add missing ones)
$technicianRole->syncPermissions($labTechPermissions);
echo "   ✅ Permissions synced!\n";

// Step 4: Verify permissions
echo "\n📋 Step 4: Verifying permissions...\n";
$assignedPermissions = $technicianRole->permissions->pluck('name')->toArray();
echo "   Assigned permissions:\n";
foreach ($assignedPermissions as $perm) {
    echo "   - {$perm}\n";
}

// Step 5: Assign permissions to admin and demo_admin
echo "\n📋 Step 5: Assigning all permissions to admin roles...\n";
$adminRole = Role::where('name', 'admin')->first();
if ($adminRole) {
    $adminRole->syncPermissions($permissions);
    echo "   ✅ Admin role updated\n";
}

$demoAdminRole = Role::where('name', 'demo_admin')->first();
if ($demoAdminRole) {
    $demoAdminRole->syncPermissions(array_merge($demoAdminRole->permissions->pluck('name')->toArray(), $permissions));
    echo "   ✅ Demo Admin role updated\n";
}

// Step 6: Clear caches
echo "\n📋 Step 6: Clearing caches...\n";
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
\Artisan::call('cache:clear');
\Artisan::call('config:clear');
echo "   ✅ Caches cleared!\n";

// Step 7: Check lab_technician users
echo "\n📋 Step 7: Checking lab_technician users...\n";
$labTechUsers = \App\Models\User::role('lab_technician')->get();
echo "   Found " . $labTechUsers->count() . " lab technician user(s):\n";
foreach ($labTechUsers as $user) {
    echo "   - {$user->email} (ID: {$user->id})\n";
    $userPerms = $user->getAllPermissions()->pluck('name')->toArray();
    echo "     Permissions: " . count($userPerms) . " total\n";
}

echo "\n===========================================\n";
echo "   ✅ DONE!\n";
echo "===========================================\n\n";

echo "Next steps:\n";
echo "1. DELETE this file from your server\n";
echo "2. Logout and login again as lab_technician\n";
echo "3. You should now see the Lab menu items\n\n";

echo "⚠️  DELETE THIS FILE NOW: fix_lab_technician_permissions.php\n";
echo "</pre>";
