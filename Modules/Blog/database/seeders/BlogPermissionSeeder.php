<?php

namespace Modules\Blog\database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BlogPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions for Blog module
        $permissions = [
            // Blog CRUD
            'view_blogs',
            'create_blogs',
            'edit_blogs',
            'delete_blogs',
            
            // Blog Status Management
            'manage_blog_status',
            'feature_blogs',
            
            // Blog Media Management
            'manage_blog_media',
            
            // Blog Bulk Actions
            'bulk_delete_blogs',
            'bulk_restore_blogs',
            'bulk_force_delete_blogs',
            
            // Blog Export/Import
            'export_blogs',
            'import_blogs',
            
            // Blog Analytics
            'view_blog_analytics',
            'view_blog_statistics',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'is_fixed' => true]);
        }

        // Assign permissions to roles - use firstOrCreate to handle existing roles
        $admin = Role::firstOrCreate(['name' => 'admin'], ['title' => 'Admin']);
        $demo_admin = Role::firstOrCreate(['name' => 'demo_admin'], ['title' => 'Demo Admin']);

        // Give all permissions to admin and demo_admin
        if ($admin) {
            $admin->givePermissionTo($permissions);
        }
        
        if ($demo_admin) {
            $demo_admin->givePermissionTo($permissions);
        }

        // Give limited permissions to other roles
        $vendor = Role::firstOrCreate(['name' => 'vendor'], ['title' => 'Vendor']);
        $doctor = Role::firstOrCreate(['name' => 'doctor'], ['title' => 'Doctor']);
        $receptionist = Role::firstOrCreate(['name' => 'receptionist'], ['title' => 'Receptionist']);
        $patient = Role::firstOrCreate(['name' => 'patient'], ['title' => 'Patient']);

        $vendor_permissions = ['view_blogs', 'create_blogs', 'edit_blogs', 'delete_blogs', 'manage_blog_media', 'manage_blog_status'];
        $doctor_permissions = ['view_blogs', 'create_blogs', 'edit_blogs', 'manage_blog_media'];
        $receptionist_permissions = ['view_blogs'];
        $patient_permissions = ['view_blogs'];

        if ($vendor) {
            $vendor->givePermissionTo($vendor_permissions);
        }
        
        if ($doctor) {
            $doctor->givePermissionTo($doctor_permissions);
        }

        if ($receptionist) {
            $receptionist->givePermissionTo($receptionist_permissions);
        }

        if ($patient) {
            $patient->givePermissionTo($patient_permissions);
        }
    }
}
