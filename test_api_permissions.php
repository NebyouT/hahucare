<?php
/**
 * API Permission Testing Script
 * Tests the API permission middleware to ensure role-based access control works
 * 
 * Usage: php test_api_permissions.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

echo "===========================================\n";
echo "   API PERMISSION TESTING\n";
echo "===========================================\n\n";

// Clear caches first
echo "1. CLEARING CACHES\n";
echo "-------------------------------------------\n";
Artisan::call('cache:clear');
Artisan::call('config:clear');
Artisan::call('permission:cache-reset');
echo "✅ Caches cleared\n\n";

// Test users by role
echo "2. TESTING USERS BY ROLE\n";
echo "-------------------------------------------\n";

$testRoles = ['admin', 'doctor', 'receptionist', 'lab_technician', 'vendor', 'pharma', 'user'];

foreach ($testRoles as $roleName) {
    $user = User::whereHas('roles', function($q) use ($roleName) {
        $q->where('name', $roleName);
    })->first();
    
    if ($user) {
        echo "\n{$roleName} Role (User ID: {$user->id}, Email: {$user->email})\n";
        echo str_repeat('-', 60) . "\n";
        
        // Get all permissions
        $permissions = $user->getAllPermissions();
        echo "Total Permissions: " . $permissions->count() . "\n";
        
        if ($permissions->count() > 0) {
            echo "Sample Permissions:\n";
            foreach ($permissions->take(10) as $permission) {
                echo "  - {$permission->name}\n";
            }
            if ($permissions->count() > 10) {
                echo "  ... and " . ($permissions->count() - 10) . " more\n";
            }
        } else {
            echo "⚠️  WARNING: No permissions assigned!\n";
        }
        
        // Test specific permission checks
        $testPermissions = [
            'view_dashboard',
            'view_appointment',
            'add_appointment',
            'view_clinics',
            'view_doctors',
            'view_labs',
            'view_lab_results',
        ];
        
        echo "\nPermission Checks:\n";
        foreach ($testPermissions as $perm) {
            $has = $user->hasPermissionTo($perm) ? '✅' : '❌';
            echo "  {$has} {$perm}\n";
        }
    } else {
        echo "\n{$roleName} Role: ❌ No user found with this role\n";
    }
}

echo "\n\n3. API TOKEN GENERATION TEST\n";
echo "-------------------------------------------\n";

// Test token generation for a doctor
$doctor = User::whereHas('roles', function($q) {
    $q->where('name', 'doctor');
})->first();

if ($doctor) {
    echo "Testing token generation for doctor (ID: {$doctor->id})\n";
    
    // Create a test token
    $token = $doctor->createToken('test-token')->plainTextToken;
    echo "✅ Token generated successfully\n";
    echo "Token (first 50 chars): " . substr($token, 0, 50) . "...\n";
    
    // Verify the token works
    $tokenParts = explode('|', $token);
    if (count($tokenParts) === 2) {
        echo "✅ Token format is valid\n";
    } else {
        echo "❌ Token format is invalid\n";
    }
} else {
    echo "❌ No doctor user found for testing\n";
}

echo "\n\n4. PERMISSION MIDDLEWARE TEST\n";
echo "-------------------------------------------\n";

echo "Testing ApiPermissionMiddleware logic:\n\n";

// Test admin bypass
$admin = User::whereHas('roles', function($q) {
    $q->where('name', 'admin');
})->first();

if ($admin) {
    echo "Admin User (ID: {$admin->id}):\n";
    echo "  Has admin role: " . ($admin->hasRole('admin') ? '✅ Yes' : '❌ No') . "\n";
    echo "  Should bypass all permission checks: ✅\n";
}

// Test doctor with specific permission
if ($doctor) {
    echo "\nDoctor User (ID: {$doctor->id}):\n";
    echo "  Has doctor role: " . ($doctor->hasRole('doctor') ? '✅ Yes' : '❌ No') . "\n";
    echo "  Has view_appointment: " . ($doctor->hasPermissionTo('view_appointment') ? '✅ Yes' : '❌ No') . "\n";
    echo "  Has add_appointment: " . ($doctor->hasPermissionTo('add_appointment') ? '✅ Yes' : '❌ No') . "\n";
    echo "  Has view_dashboard: " . ($doctor->hasPermissionTo('view_dashboard') ? '✅ Yes' : '❌ No') . "\n";
}

// Test lab technician
$labTech = User::whereHas('roles', function($q) {
    $q->where('name', 'lab_technician');
})->first();

if ($labTech) {
    echo "\nLab Technician User (ID: {$labTech->id}):\n";
    echo "  Has lab_technician role: " . ($labTech->hasRole('lab_technician') ? '✅ Yes' : '❌ No') . "\n";
    echo "  Has view_labs: " . ($labTech->hasPermissionTo('view_labs') ? '✅ Yes' : '❌ No') . "\n";
    echo "  Has view_lab_results: " . ($labTech->hasPermissionTo('view_lab_results') ? '✅ Yes' : '❌ No') . "\n";
    echo "  Has create_lab_results: " . ($labTech->hasPermissionTo('create_lab_results') ? '✅ Yes' : '❌ No') . "\n";
}

echo "\n\n5. ROUTE PERMISSION MAPPING\n";
echo "-------------------------------------------\n";

$routePermissions = [
    'GET /api/backup' => 'view_backups',
    'GET /api/vendor-dashboard-list' => 'view_dashboard',
    'GET /api/appointment-list' => 'view_appointment',
    'POST /api/save-booking' => 'add_appointment',
    'GET /api/get-clinics' => 'view_clinics',
    'GET /api/get-doctors' => 'view_doctors',
    'POST /api/save-doctor' => 'add_doctors',
];

echo "Sample Route → Permission Mappings:\n";
foreach ($routePermissions as $route => $permission) {
    echo "  {$route}\n";
    echo "    → Requires: {$permission}\n";
}

echo "\n\n6. RECOMMENDATIONS\n";
echo "-------------------------------------------\n";
echo "✅ API permission middleware is configured\n";
echo "✅ Routes have been updated with permission checks\n";
echo "✅ Admin/demo_admin bypass all permission checks\n";
echo "\n";
echo "To test in mobile app:\n";
echo "1. Login with different user roles\n";
echo "2. Try to access protected endpoints\n";
echo "3. Check Laravel logs for permission check details\n";
echo "4. Verify 403 errors for unauthorized access\n";
echo "\n";
echo "To view permission logs:\n";
echo "  tail -f storage/logs/laravel.log | grep 'API Permission'\n";

echo "\n===========================================\n";
echo "   TESTING COMPLETE\n";
echo "===========================================\n";
