<?php
/**
 * Create Missing API Permissions
 * Creates all necessary permissions for API endpoints and assigns them to roles
 * 
 * Usage: php create_api_permissions.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Artisan;

echo "===========================================\n";
echo "   CREATE API PERMISSIONS\n";
echo "===========================================\n\n";

// Clear permission cache
Artisan::call('permission:cache-reset');
echo "✅ Permission cache cleared\n\n";

// Define all API permissions needed
$apiPermissions = [
    // Dashboard
    'view_dashboard' => 'View dashboard data',
    
    // Appointments
    'view_appointment' => 'View appointments',
    'add_appointment' => 'Create appointments',
    'edit_appointment' => 'Edit appointments',
    'delete_appointment' => 'Delete appointments',
    
    // Encounters
    'view_encounter' => 'View patient encounters',
    'add_encounter' => 'Create patient encounters',
    'edit_encounter' => 'Edit patient encounters',
    'delete_encounter' => 'Delete patient encounters',
    
    // Medical Reports
    'view_medical_report' => 'View medical reports',
    'add_medical_report' => 'Create medical reports',
    'edit_medical_report' => 'Edit medical reports',
    'delete_medical_report' => 'Delete medical reports',
    
    // Prescriptions
    'view_prescription' => 'View prescriptions',
    'add_prescription' => 'Create prescriptions',
    'edit_prescription' => 'Edit prescriptions',
    'delete_prescription' => 'Delete prescriptions',
    
    // Billing
    'view_billing' => 'View billing records',
    'add_billing' => 'Create billing records',
    'edit_billing' => 'Edit billing records',
    'delete_billing' => 'Delete billing records',
    
    // Categories
    'view_categories' => 'View clinic categories',
    'add_categories' => 'Create clinic categories',
    'edit_categories' => 'Edit clinic categories',
    'delete_categories' => 'Delete clinic categories',
    
    // Services
    'view_services' => 'View clinic services',
    'add_services' => 'Create clinic services',
    'edit_services' => 'Edit clinic services',
    'delete_services' => 'Delete clinic services',
    
    // Clinics
    'view_clinics' => 'View clinics',
    'add_clinics' => 'Create clinics',
    'edit_clinics' => 'Edit clinics',
    'delete_clinics' => 'Delete clinics',
    
    // Doctors
    'view_doctors' => 'View doctors',
    'add_doctors' => 'Create doctors',
    'edit_doctors' => 'Edit doctors',
    'delete_doctors' => 'Delete doctors',
    
    // Receptionists
    'view_receptionists' => 'View receptionists',
    'add_receptionists' => 'Create receptionists',
    'edit_receptionists' => 'Edit receptionists',
    'delete_receptionists' => 'Delete receptionists',
    
    // Patients
    'view_patients' => 'View patients',
    'add_patients' => 'Create patients',
    'edit_patients' => 'Edit patients',
    'delete_patients' => 'Delete patients',
    
    // Backups
    'view_backups' => 'View database backups',
];

echo "1. CREATING PERMISSIONS\n";
echo "-------------------------------------------\n";

$created = 0;
$existing = 0;

foreach ($apiPermissions as $name => $description) {
    $permission = Permission::firstOrCreate(
        ['name' => $name, 'guard_name' => 'web'],
        ['description' => $description]
    );
    
    if ($permission->wasRecentlyCreated) {
        echo "✅ Created: {$name}\n";
        $created++;
    } else {
        echo "⏭️  Exists: {$name}\n";
        $existing++;
    }
}

echo "\nSummary: {$created} created, {$existing} already existed\n\n";

// Assign permissions to roles
echo "2. ASSIGNING PERMISSIONS TO ROLES\n";
echo "-------------------------------------------\n";

// Admin gets all permissions
$adminRole = Role::where('name', 'admin')->first();
if ($adminRole) {
    $adminRole->givePermissionTo(array_keys($apiPermissions));
    echo "✅ Admin: All permissions assigned\n";
}

$demoAdminRole = Role::where('name', 'demo_admin')->first();
if ($demoAdminRole) {
    $demoAdminRole->givePermissionTo(array_keys($apiPermissions));
    echo "✅ Demo Admin: All permissions assigned\n";
}

// Doctor permissions
$doctorRole = Role::where('name', 'doctor')->first();
if ($doctorRole) {
    $doctorPermissions = [
        'view_dashboard',
        'view_appointment', 'add_appointment', 'edit_appointment',
        'view_encounter', 'add_encounter', 'edit_encounter',
        'view_medical_report', 'add_medical_report', 'edit_medical_report',
        'view_prescription', 'add_prescription', 'edit_prescription',
        'view_billing', 'add_billing',
        'view_patients',
        'view_clinics', 'view_services',
    ];
    $doctorRole->givePermissionTo($doctorPermissions);
    echo "✅ Doctor: " . count($doctorPermissions) . " permissions assigned\n";
}

// Receptionist permissions
$receptionistRole = Role::where('name', 'receptionist')->first();
if ($receptionistRole) {
    $receptionistPermissions = [
        'view_dashboard',
        'view_appointment', 'add_appointment', 'edit_appointment',
        'view_patients', 'add_patients', 'edit_patients',
        'view_clinics', 'view_services',
        'view_doctors',
    ];
    $receptionistRole->givePermissionTo($receptionistPermissions);
    echo "✅ Receptionist: " . count($receptionistPermissions) . " permissions assigned\n";
}

// Lab Technician permissions
$labTechRole = Role::where('name', 'lab_technician')->first();
if ($labTechRole) {
    $labTechPermissions = [
        'view_dashboard',
        'view_patients',
    ];
    $labTechRole->givePermissionTo($labTechPermissions);
    echo "✅ Lab Technician: " . count($labTechPermissions) . " permissions assigned\n";
}

// Vendor permissions
$vendorRole = Role::where('name', 'vendor')->first();
if ($vendorRole) {
    $vendorPermissions = [
        'view_dashboard',
        'view_clinics', 'add_clinics', 'edit_clinics',
        'view_services', 'add_services', 'edit_services',
        'view_doctors', 'add_doctors', 'edit_doctors',
        'view_receptionists', 'add_receptionists', 'edit_receptionists',
        'view_appointment',
        'view_patients',
    ];
    $vendorRole->givePermissionTo($vendorPermissions);
    echo "✅ Vendor: " . count($vendorPermissions) . " permissions assigned\n";
}

// Pharma permissions
$pharmaRole = Role::where('name', 'pharma')->first();
if ($pharmaRole) {
    $pharmaPermissions = [
        'view_dashboard',
        'view_prescription',
        'view_patients',
    ];
    $pharmaRole->givePermissionTo($pharmaPermissions);
    echo "✅ Pharma: " . count($pharmaPermissions) . " permissions assigned\n";
}

// User (Patient) permissions
$userRole = Role::where('name', 'user')->first();
if ($userRole) {
    $userPermissions = [
        'view_appointment', 'add_appointment',
        'view_encounter',
        'view_medical_report',
        'view_prescription',
        'view_clinics', 'view_services', 'view_doctors',
    ];
    $userRole->givePermissionTo($userPermissions);
    echo "✅ User (Patient): " . count($userPermissions) . " permissions assigned\n";
}

echo "\n3. CLEARING CACHES\n";
echo "-------------------------------------------\n";
Artisan::call('permission:cache-reset');
Artisan::call('cache:clear');
Artisan::call('config:clear');
echo "✅ All caches cleared\n\n";

echo "===========================================\n";
echo "   PERMISSIONS CREATED SUCCESSFULLY\n";
echo "===========================================\n\n";

echo "Next steps:\n";
echo "1. Run: php test_api_permissions.php\n";
echo "2. Test API endpoints with mobile app\n";
echo "3. Check logs: tail -f storage/logs/laravel.log | grep 'API Permission'\n";
