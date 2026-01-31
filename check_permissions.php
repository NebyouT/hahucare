<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DATABASE PERMISSIONS ===" . PHP_EOL;
$permissions = \Spatie\Permission\Models\Permission::where('name', 'like', '%patient_referral%')->get();
foreach ($permissions as $perm) {
    echo 'DB: ' . $perm->name . ' (ID: ' . $perm->id . ')' . PHP_EOL;
}

echo PHP_EOL . "=== ROLE PERMISSIONS FROM DATABASE ===" . PHP_EOL;
$doctor = \App\Models\User::find(19);
$rolePerms = $doctor->getPermissionsViaRoles();
foreach ($rolePerms as $perm) {
    if (strpos($perm->name, 'patient_referral') !== false) {
        echo 'Role Perm: ' . $perm->name . PHP_EOL;
    }
}

echo PHP_EOL . "=== DIRECT USER PERMISSIONS ===" . PHP_EOL;
$directPerms = $doctor->getDirectPermissions();
foreach ($directPerms as $perm) {
    if (strpos($perm->name, 'patient_referral') !== false) {
        echo 'Direct Perm: ' . $perm->name . PHP_EOL;
    }
}

echo PHP_EOL . "=== ALL PERMISSIONS (CACHED) ===" . PHP_EOL;
$allPerms = $doctor->getAllPermissions();
foreach ($allPerms as $perm) {
    if (strpos($perm->name, 'patient_referral') !== false) {
        echo 'All Perm: ' . $perm->name . PHP_EOL;
    }
}

echo PHP_EOL . "=== PERMISSION CHECK RESULT ===" . PHP_EOL;
echo 'Can view patient referral: ' . ($doctor->can('view_patient_referral') ? 'YES' : 'NO') . PHP_EOL;

echo PHP_EOL . "=== CACHE STATUS ===" . PHP_EOL;
$cache = app('cache');
$permissionCacheKey = 'spatie.permission.cache';
$cached = $cache->get($permissionCacheKey);
echo 'Permission cache exists: ' . ($cached ? 'YES' : 'NO') . PHP_EOL;
