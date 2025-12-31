<?php

namespace Modules\Pharma\database\seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Schema;

class PharmaPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure roles/permissions tables exist
        if (! Schema::hasTable('roles') || ! Schema::hasTable('permissions')) {
            return;
        }

        // Ensure pharma role exists
        $pharma = Role::firstOrCreate(
            ['name' => 'pharma'],
            ['title' => 'Pharma', 'is_fixed' => true]
        );

        $pharmaPermissions = [
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

        // Ensure permissions exist
        foreach ($pharmaPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['is_fixed' => true]
            );
        }

        $pharma->givePermissionTo($pharmaPermissions);
    }
}
