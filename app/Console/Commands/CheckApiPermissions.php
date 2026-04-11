<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Artisan command to check and debug API permission assignments for each role.
 *
 * Usage:
 *   php artisan api:check-permissions              (check all roles)
 *   php artisan api:check-permissions --role=doctor (check specific role)
 *   php artisan api:check-permissions --fix         (auto-fix missing permissions)
 */
class CheckApiPermissions extends Command
{
    protected $signature = 'api:check-permissions {--role= : Check a specific role} {--fix : Auto-fix missing permissions by running MobileApiPermissionSeeder}';
    protected $description = 'Check and debug API permission assignments for mobile app roles';

    /**
     * API permission name => DB permission name(s) mapping.
     * Mirrors the mapping in ApiPermissionMiddleware.
     */
    private array $permissionMap = [
        'view_dashboard'       => ['__allow_authenticated__'],
        'view_categories'      => ['view_clinics_category'],
        'add_categories'       => ['add_clinics_category'],
        'edit_categories'      => ['edit_clinics_category'],
        'delete_categories'    => ['delete_clinics_category'],
        'view_services'        => ['view_clinics_service'],
        'add_services'         => ['add_clinics_service'],
        'edit_services'        => ['edit_clinics_service'],
        'delete_services'      => ['delete_clinics_service'],
        'view_clinics'         => ['view_clinics_center'],
        'add_clinics'          => ['add_clinics_center'],
        'edit_clinics'         => ['edit_clinics_center'],
        'delete_clinics'       => ['delete_clinics_center'],
        'view_appointment'     => ['view_clinic_appointment_list'],
        'add_appointment'      => ['add_clinic_appointment_list'],
        'edit_appointment'     => ['edit_clinic_appointment_list', 'add_clinic_appointment_list'],
        'delete_appointment'   => ['delete_clinic_appointment_list'],
        'view_patients'        => ['view_customer'],
        'add_patients'         => ['add_customer'],
        'edit_patients'        => ['edit_customer'],
        'delete_patients'      => ['delete_customer'],
        'view_receptionists'   => ['view_clinic_receptionist_list'],
        'add_receptionists'    => ['add_clinic_receptionist_list'],
        'edit_receptionists'   => ['edit_clinic_receptionist_list'],
        'delete_receptionists' => ['delete_clinic_receptionist_list'],
        'view_billing'         => ['view_billing_record'],
        'add_billing'          => ['add_billing_record'],
        'edit_billing'         => ['edit_billing_record'],
        'delete_billing'       => ['delete_billing_record'],
        'view_medical_report'  => ['view_encounter'],
        'add_medical_report'   => ['add_encounter', 'edit_encounter'],
        'edit_medical_report'  => ['edit_encounter'],
        'delete_medical_report'=> ['delete_encounter'],
        'view_prescription'    => ['view_prescription', 'view_encounter'],
        'add_prescription'     => ['add_prescription', 'add_encounter'],
        'edit_prescription'    => ['edit_prescription', 'edit_encounter'],
        'delete_prescription'  => ['delete_prescription', 'delete_encounter'],
        'view_backups'         => ['view_backup'],
        'view_doctors'         => ['view_doctors'],
        'add_doctors'          => ['add_doctors'],
        'edit_doctors'         => ['edit_doctors'],
        'delete_doctors'       => ['delete_doctors'],
        'view_encounter'       => ['view_encounter'],
        'add_encounter'        => ['add_encounter'],
        'edit_encounter'       => ['edit_encounter'],
        'delete_encounter'     => ['delete_encounter'],
    ];

    /**
     * Expected API permissions per role.
     * Only these are checked — anything not listed is intentionally denied.
     */
    private array $roleExpectedPermissions = [
        'vendor' => [
            'view_dashboard',
            'view_appointment', 'add_appointment', 'edit_appointment', 'delete_appointment',
            'view_encounter', 'add_encounter', 'edit_encounter', 'delete_encounter',
            'view_medical_report', 'add_medical_report', 'edit_medical_report', 'delete_medical_report',
            'view_prescription', 'add_prescription', 'edit_prescription', 'delete_prescription',
            'view_billing', 'add_billing', 'edit_billing', 'delete_billing',
            'view_categories', 'add_categories', 'edit_categories', 'delete_categories',
            'view_services', 'add_services', 'edit_services', 'delete_services',
            'view_clinics', 'add_clinics', 'edit_clinics', 'delete_clinics',
            'view_doctors', 'add_doctors', 'edit_doctors', 'delete_doctors',
            'view_patients', 'add_patients', 'edit_patients', 'delete_patients',
            'view_receptionists', 'add_receptionists', 'edit_receptionists', 'delete_receptionists',
        ],
        'doctor' => [
            'view_dashboard',
            'view_appointment', 'add_appointment', 'edit_appointment',
            'view_encounter', 'add_encounter', 'edit_encounter', 'delete_encounter',
            'view_medical_report', 'add_medical_report', 'edit_medical_report', 'delete_medical_report',
            'view_prescription', 'add_prescription', 'edit_prescription', 'delete_prescription',
            'view_billing', 'add_billing', 'edit_billing', 'delete_billing',
            'view_services',
            'view_clinics',
            'view_doctors',
            'view_patients', 'add_patients',
        ],
        'receptionist' => [
            'view_dashboard',
            'view_appointment', 'add_appointment', 'delete_appointment',
            'view_encounter',
            'view_billing',
            'view_categories',
            'view_services', 'add_services', 'edit_services', 'delete_services',
            'view_clinics',
            'view_doctors', 'add_doctors', 'edit_doctors', 'delete_doctors',
            'view_patients', 'add_patients', 'edit_patients', 'delete_patients',
            'view_receptionists',
        ],
        'user' => [
            'view_dashboard',
            'view_appointment', 'add_appointment',
            'view_services',
            'view_clinics',
            'view_doctors',
            'view_billing',
            'view_encounter',
            'view_prescription',
        ],
        'pharma' => [
            'view_dashboard',
            'view_prescription', 'add_prescription', 'edit_prescription', 'delete_prescription',
            'view_billing',
        ],
        'lab_technician' => [
            'view_dashboard',
            'view_patients',
            'view_encounter',
        ],
    ];

    public function handle(): int
    {
        $targetRole = $this->option('role');
        $fix = $this->option('fix');

        if ($fix) {
            $this->info('Running MobileApiPermissionSeeder to fix missing permissions...');
            $this->call('db:seed', ['--class' => 'Database\\Seeders\\MobileApiPermissionSeeder']);
            $this->info('Done! Re-running check...');
            $this->newLine();
        }

        $roles = $targetRole
            ? Role::where('name', $targetRole)->get()
            : Role::whereIn('name', ['admin', 'vendor', 'doctor', 'receptionist', 'user', 'pharma', 'lab_technician'])->get();

        if ($roles->isEmpty()) {
            $this->error("Role '{$targetRole}' not found.");
            return 1;
        }

        foreach ($roles as $role) {
            $this->info("═══════════════════════════════════════");
            $this->info("  Role: {$role->name} ({$role->title})");
            $this->info("═══════════════════════════════════════");

            if ($role->name === 'admin' || $role->name === 'demo_admin') {
                $this->line("  ✅ Admin/Demo Admin bypasses all API permission checks.");
                $this->newLine();
                continue;
            }

            $expectedPerms = $this->roleExpectedPermissions[$role->name] ?? [];
            if (empty($expectedPerms)) {
                $this->warn("  ⚠ No expected permissions defined for role '{$role->name}'.");
                $this->newLine();
                continue;
            }

            $rolePermissions = $role->permissions->pluck('name')->toArray();
            $passed = 0;
            $failed = 0;
            $failedList = [];

            foreach ($expectedPerms as $apiPerm) {
                // Check __allow_authenticated__ special case
                $dbPerms = $this->permissionMap[$apiPerm] ?? [];
                if (in_array('__allow_authenticated__', $dbPerms)) {
                    $passed++;
                    continue;
                }

                // Check if role has the API permission name directly
                if (in_array($apiPerm, $rolePermissions)) {
                    $passed++;
                    continue;
                }

                // Check if role has any of the mapped DB permissions
                $hasAny = false;
                foreach ($dbPerms as $dbPerm) {
                    if (in_array($dbPerm, $rolePermissions)) {
                        $hasAny = true;
                        break;
                    }
                }

                if ($hasAny) {
                    $passed++;
                } else {
                    $failed++;
                    $neededPerms = !empty($dbPerms)
                        ? $apiPerm . ' OR ' . implode(' | ', $dbPerms)
                        : $apiPerm;
                    $failedList[] = [
                        'api_perm' => $apiPerm,
                        'needed'   => $neededPerms,
                    ];
                }
            }

            $total = count($expectedPerms);
            if ($failed === 0) {
                $this->line("  ✅ All {$total} expected permissions are granted.");
            } else {
                $this->line("  Permissions: {$passed}/{$total} ✅ granted, {$failed} ❌ MISSING (will cause 403)");
                $this->table(
                    ['API Permission', 'Needs (any of)'],
                    array_map(fn($f) => [$f['api_perm'], $f['needed']], $failedList)
                );
            }

            $this->newLine();
        }

        if (!$fix && $this->confirm('Would you like to auto-fix by running MobileApiPermissionSeeder?')) {
            $this->call('db:seed', ['--class' => 'Database\\Seeders\\MobileApiPermissionSeeder']);
            $this->info('Permissions seeded! Run this command again to verify.');
        }

        return 0;
    }
}
