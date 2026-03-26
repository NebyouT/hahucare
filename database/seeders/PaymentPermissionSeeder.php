<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PaymentPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create payment permissions
        $permissions = [
            'view_payments',
            'add_payments', 
            'edit_payments',
            'delete_payments',
            'update_payment_status',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'is_fixed' => 1]);
        }

        // Assign permissions to roles
        $admin = Role::where('name', 'admin')->first();
        $demo_admin = Role::where('name', 'demo_admin')->first();
        $receptionist = Role::where('name', 'receptionist')->first();

        if ($admin) {
            $admin->givePermissionTo($permissions);
        }

        if ($demo_admin) {
            $demo_admin->givePermissionTo($permissions);
        }

        if ($receptionist) {
            // Receptionist can view and update payment status but not delete
            $receptionist->givePermissionTo(['view_payments', 'update_payment_status']);
        }
    }
}
