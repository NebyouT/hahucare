<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Artisan command to check mobile app (mobile_*) permission assignments per role.
 *
 * Usage:
 *   php artisan api:check-permissions              (check all roles)
 *   php artisan api:check-permissions --role=doctor (check specific role)
 *   php artisan api:check-permissions --fix         (auto-fix by running seeder)
 */
class CheckApiPermissions extends Command
{
    protected $signature = 'api:check-permissions {--role= : Check a specific role} {--fix : Auto-fix by running MobileApiPermissionSeeder}';
    protected $description = 'Check mobile app (mobile_*) permission assignments for each role';

    /**
     * Expected mobile permissions per role.
     * These are the mobile_ prefixed permission names that each role SHOULD have.
     * Anything not listed is intentionally denied for that role.
     */
    private array $roleExpectedPermissions = [
        'vendor' => [
            'mobile_view_dashboard',
            'mobile_view_appointment', 'mobile_add_appointment', 'mobile_edit_appointment', 'mobile_delete_appointment',
            'mobile_view_encounter', 'mobile_add_encounter', 'mobile_edit_encounter', 'mobile_delete_encounter',
            'mobile_view_medical_report', 'mobile_add_medical_report', 'mobile_edit_medical_report', 'mobile_delete_medical_report',
            'mobile_view_prescription', 'mobile_add_prescription', 'mobile_edit_prescription', 'mobile_delete_prescription',
            'mobile_view_billing', 'mobile_add_billing', 'mobile_edit_billing', 'mobile_delete_billing',
            'mobile_view_categories', 'mobile_add_categories', 'mobile_edit_categories', 'mobile_delete_categories',
            'mobile_view_services', 'mobile_add_services', 'mobile_edit_services', 'mobile_delete_services',
            'mobile_view_clinics', 'mobile_add_clinics', 'mobile_edit_clinics', 'mobile_delete_clinics',
            'mobile_view_doctors', 'mobile_add_doctors', 'mobile_edit_doctors', 'mobile_delete_doctors',
            'mobile_view_patients', 'mobile_add_patients', 'mobile_edit_patients', 'mobile_delete_patients',
            'mobile_view_receptionists', 'mobile_add_receptionists', 'mobile_edit_receptionists', 'mobile_delete_receptionists',
            'mobile_view_backups',
        ],
        'doctor' => [
            'mobile_view_dashboard',
            'mobile_view_appointment', 'mobile_add_appointment', 'mobile_edit_appointment',
            'mobile_view_encounter', 'mobile_add_encounter', 'mobile_edit_encounter', 'mobile_delete_encounter',
            'mobile_view_medical_report', 'mobile_add_medical_report', 'mobile_edit_medical_report', 'mobile_delete_medical_report',
            'mobile_view_prescription', 'mobile_add_prescription', 'mobile_edit_prescription', 'mobile_delete_prescription',
            'mobile_view_billing', 'mobile_add_billing', 'mobile_edit_billing', 'mobile_delete_billing',
            'mobile_view_categories',
            'mobile_view_services',
            'mobile_view_clinics',
            'mobile_view_doctors',
            'mobile_view_patients', 'mobile_add_patients',
        ],
        'receptionist' => [
            'mobile_view_dashboard',
            'mobile_view_appointment', 'mobile_add_appointment', 'mobile_edit_appointment', 'mobile_delete_appointment',
            'mobile_view_encounter',
            'mobile_view_billing', 'mobile_add_billing',
            'mobile_view_categories',
            'mobile_view_services', 'mobile_add_services', 'mobile_edit_services', 'mobile_delete_services',
            'mobile_view_clinics',
            'mobile_view_doctors', 'mobile_add_doctors', 'mobile_edit_doctors', 'mobile_delete_doctors',
            'mobile_view_patients', 'mobile_add_patients', 'mobile_edit_patients', 'mobile_delete_patients',
            'mobile_view_receptionists',
        ],
        'user' => [
            'mobile_view_dashboard',
            'mobile_view_appointment', 'mobile_add_appointment',
            'mobile_view_categories',
            'mobile_view_services',
            'mobile_view_clinics',
            'mobile_view_doctors',
            'mobile_view_billing',
            'mobile_view_encounter',
            'mobile_view_prescription',
        ],
        'pharma' => [
            'mobile_view_dashboard',
            'mobile_view_prescription', 'mobile_add_prescription', 'mobile_edit_prescription', 'mobile_delete_prescription',
            'mobile_view_billing',
        ],
        'lab_technician' => [
            'mobile_view_dashboard',
            'mobile_view_patients',
            'mobile_view_encounter',
        ],
    ];

    public function handle(): int
    {
        $targetRole = $this->option('role');
        $fix = $this->option('fix');

        if ($fix) {
            $this->info('Running MobileApiPermissionSeeder...');
            $this->call('db:seed', ['--class' => 'Database\\Seeders\\MobileApiPermissionSeeder']);
            $this->newLine();
            $this->info('Re-checking permissions:');
            $this->newLine();
        }

        $roles = $targetRole
            ? Role::where('name', $targetRole)->get()
            : Role::whereIn('name', ['admin', 'vendor', 'doctor', 'receptionist', 'user', 'pharma', 'lab_technician'])->get();

        if ($roles->isEmpty()) {
            $this->error("Role '{$targetRole}' not found.");
            return 1;
        }

        // Check if any mobile permissions exist in DB at all
        $mobilePermCount = Permission::where('name', 'like', 'mobile_%')->count();
        if ($mobilePermCount === 0) {
            $this->warn('⚠ No mobile_* permissions found in the database!');
            $this->warn('  Run: php artisan db:seed --class=MobileApiPermissionSeeder');
            $this->newLine();
        } else {
            $this->info("Found {$mobilePermCount} mobile permissions in database.");
            $this->newLine();
        }

        foreach ($roles as $role) {
            $this->info("═══════════════════════════════════════");
            $this->info("  Role: {$role->name}");
            $this->info("═══════════════════════════════════════");

            if (in_array($role->name, ['admin', 'demo_admin'])) {
                $this->line("  ✅ Bypasses all mobile permission checks in middleware.");
                $this->newLine();
                continue;
            }

            $expectedPerms = $this->roleExpectedPermissions[$role->name] ?? [];
            if (empty($expectedPerms)) {
                $this->warn("  ⚠ No expected permissions defined for '{$role->name}'.");
                $this->newLine();
                continue;
            }

            $rolePermissions = $role->permissions->pluck('name')->toArray();
            $passed = 0;
            $failed = 0;
            $failedList = [];

            foreach ($expectedPerms as $mobilePerm) {
                if (in_array($mobilePerm, $rolePermissions)) {
                    $passed++;
                } else {
                    $failed++;
                    // Check if the permission even exists in DB
                    $existsInDb = Permission::where('name', $mobilePerm)->exists();
                    $failedList[] = [
                        'perm'   => $mobilePerm,
                        'reason' => $existsInDb ? 'Not assigned to role' : 'Does not exist in DB',
                    ];
                }
            }

            $total = count($expectedPerms);
            if ($failed === 0) {
                $this->line("  ✅ All {$total} mobile permissions granted.");
            } else {
                $this->line("  {$passed}/{$total} ✅ granted, {$failed} ❌ MISSING (will cause 403)");
                $this->table(
                    ['Mobile Permission', 'Issue'],
                    array_map(fn($f) => [$f['perm'], $f['reason']], $failedList)
                );
            }

            $this->newLine();
        }

        if (!$fix && $this->confirm('Run MobileApiPermissionSeeder to fix missing permissions?')) {
            $this->call('db:seed', ['--class' => 'Database\\Seeders\\MobileApiPermissionSeeder']);
            $this->info('Done! Run this command again to verify.');
        }

        return 0;
    }
}
