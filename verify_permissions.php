<?php

/**
 * Permission Verification Script
 * Checks if all permissions used in GenerateMenus.php and controllers are defined in the seeder
 */

// Extract all permissions from GenerateMenus.php
$generateMenusFile = 'app/Http/Middleware/GenerateMenus.php';
$seederFile = 'database/seeders/DashboardPermissionSeeder.php';

// Read GenerateMenus.php
$menuContent = file_get_contents($generateMenusFile);

// Extract all permission arrays from GenerateMenus.php
preg_match_all("/'permission'\s*=>\s*\[(.*?)\]/s", $menuContent, $menuMatches);

$menuPermissions = [];
foreach ($menuMatches[1] as $match) {
    preg_match_all("/'([^']+)'/", $match, $permMatches);
    foreach ($permMatches[1] as $perm) {
        if (!empty($perm) && $perm !== 'view_setting' && $perm !== 'view_pages' && $perm !== 'view_notification' && $perm !== 'view_backup' && $perm !== 'view_faqs' && $perm !== 'view_incidence_report') {
            $menuPermissions[$perm] = true;
        }
    }
}

// Read DashboardPermissionSeeder.php
$seederContent = file_get_contents($seederFile);

// Extract all permission names from seeder
preg_match_all("/'([^']+)'\s*=>\s*\[/", $seederContent, $seederMatches);

$seederPermissions = [];
foreach ($seederMatches[1] as $perm) {
    $seederPermissions[$perm] = true;
}

echo "=== PERMISSION VERIFICATION REPORT ===\n\n";
echo "Total permissions in GenerateMenus.php: " . count($menuPermissions) . "\n";
echo "Total permissions in DashboardPermissionSeeder.php: " . count($seederPermissions) . "\n\n";

// Check for permissions in menu but not in seeder
$missingInSeeder = array_diff_key($menuPermissions, $seederPermissions);
if (count($missingInSeeder) > 0) {
    echo "⚠️  PERMISSIONS IN MENU BUT NOT IN SEEDER:\n";
    foreach ($missingInSeeder as $perm => $val) {
        echo "  - $perm\n";
    }
    echo "\n";
} else {
    echo "✅ All menu permissions are defined in seeder\n\n";
}

// Check for permissions in seeder but not in menu
$extraInSeeder = array_diff_key($seederPermissions, $menuPermissions);
if (count($extraInSeeder) > 0) {
    echo "ℹ️  PERMISSIONS IN SEEDER BUT NOT IN MENU:\n";
    foreach ($extraInSeeder as $perm => $val) {
        echo "  - $perm\n";
    }
    echo "\n";
} else {
    echo "✅ All seeder permissions are used in menu\n\n";
}

// Extract permissions from controllers
$controllers = [
    'Modules/Appointment/Http/Controllers/Backend/ClinicAppointmentController.php',
    'Modules/Laboratory/Http/Controllers/Backend/LabController.php',
    'Modules/Clinic/Http/Controllers/DoctorController.php',
    'Modules/Clinic/Http/Controllers/ClinicesController.php',
    'app/Http/Controllers/Backend/UserController.php',
    'Modules/Pharma/Http/Controllers/Backend/PharmaController.php',
];

$controllerPermissions = [];
foreach ($controllers as $controller) {
    if (file_exists($controller)) {
        $content = file_get_contents($controller);
        preg_match_all("/permission:([^,\)]+)/", $content, $matches);
        foreach ($matches[1] as $perm) {
            $perm = trim($perm, "'");
            if (!empty($perm)) {
                $controllerPermissions[$perm] = true;
            }
        }
    }
}

echo "Total permissions in controllers: " . count($controllerPermissions) . "\n\n";

// Check for controller permissions not in seeder
$missingInSeederFromControllers = array_diff_key($controllerPermissions, $seederPermissions);
if (count($missingInSeederFromControllers) > 0) {
    echo "⚠️  CONTROLLER PERMISSIONS NOT IN SEEDER:\n";
    foreach ($missingInSeederFromControllers as $perm => $val) {
        echo "  - $perm\n";
    }
    echo "\n";
} else {
    echo "✅ All controller permissions are defined in seeder\n\n";
}

echo "=== VERIFICATION COMPLETE ===\n";
