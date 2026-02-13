<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $pharma_permissions = [
            'view_prescription',
            'add_prescription',
            'edit_prescription',
            'delete_prescription',
            'view_medicine',
            'add_medicine',
            'edit_medicine',
            'delete_medicine',
            'view_pharma_payout',
            'add_pharma_payout',
            'edit_pharma_payout',
            'delete_pharma_payout',
            'view_expired_medicine',
            'view_suppliers',
            'add_suppliers',
            'edit_suppliers',
            'delete_suppliers',
            'view_purchased_order',
            'add_purchased_order',
            'edit_purchased_order',
            'delete_purchased_order',
            'view_tax',
            'add_tax',
            'edit_tax',
            'delete_tax',
            'view_setting',
            'view_notification',
            'view_pharma_billing_record',
        ];

        // Roles
        $allPermissionsRoles = Role::whereIn('name', ['admin', 'vendor', 'demo_admin'])->get();
        $pharmaRole = Role::where('name', 'pharma')->first();
        $doctorRole = Role::where('name', 'doctor')->first();

        // Assign all permissions module-wise to admin, vendor, demo_admin
        $allModules = [
            'Pharma',
            'Prescription',
            'Medicine',
            'Expired Medicine',
            'Suppliers',
            'Purchased order',
            'Pharma Billing record',
            'Pharma Payout',
        ]; // or wherever you store modules
        foreach ($allModules as $module) {
            $moduleName = strtolower(str_replace(' ', '_', $module));
            $actions = ['view', 'add', 'edit', 'delete'];

            // Add any custom permissions for the module
            if (isset($module['more_permission']) && is_array($module['more_permission'])) {
                $actions = array_merge($actions, $module['more_permission']);
            }

            foreach ($actions as $action) {
                $permName = is_numeric($action) ? $moduleName . '_' . $action : $action . '_' . $moduleName;
                $permission = Permission::firstOrCreate(['name' => $permName], ['is_fixed' => true]);

                foreach ($allPermissionsRoles as $role) {
                    if (!$role->hasPermissionTo($permName)) {
                        $role->givePermissionTo($permName);
                    }
                }
            }
        }

        // Assign only pharma permissions to pharma role
        if ($pharmaRole) {
            foreach ($pharma_permissions as $permName) {
                $permission = Permission::firstOrCreate(['name' => $permName], ['is_fixed' => true]);
                if (!$pharmaRole->hasPermissionTo($permName)) {
                    $pharmaRole->givePermissionTo($permName);
                }
            }
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $allRoles = Role::whereIn('name', ['admin', 'vendor', 'demo_admin'])->get();

        foreach ($allRoles as $role) {
            foreach ($allModules as $module) {
                $moduleName = strtolower(str_replace(' ', '_', $module['module_name']));
                $actions = ['view', 'add', 'edit', 'delete'];
                if (isset($module['more_permission']) && is_array($module['more_permission'])) {
                    $actions = array_merge($actions, $module['more_permission']);
                }

                foreach ($actions as $action) {
                    $permName = is_numeric($action) ? $moduleName . '_' . $action : $action . '_' . $moduleName;
                    $permission = Permission::where('name', $permName)->first();
                    if ($permission) {
                        $role->revokePermissionTo($permName);
                        // Optionally delete permission if no role uses it
                        if ($permission->roles()->count() === 0) {
                            $permission->delete();
                        }
                    }
                }
            }
        }

    }
};
