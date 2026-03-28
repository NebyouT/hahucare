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
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ], ['is_fixed' => true]);
        }

        // Assign permissions to roles
        $roles = [
            'admin' => $permissions,
            'demo_admin' => $permissions,
            'vendor' => [
                'view_blogs',
                'create_blogs',
                'edit_blogs',
                'delete_blogs',
                'manage_blog_media',
            ],
            'doctor' => [
                'view_blogs',
                'create_blogs',
                'edit_blogs',
                'delete_blogs',
                'manage_blog_media',
            ],
            'receptionist' => [
                'view_blogs',
            ],
            'patient' => [
                'view_blogs',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                foreach ($rolePermissions as $permission) {
                    if (!$role->hasPermissionTo($permission)) {
                        $role->givePermissionTo($permission);
                    }
                }
            }
        }
    }
}
