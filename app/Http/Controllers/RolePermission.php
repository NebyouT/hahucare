<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermission extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        // Page Title
        $this->module_title = 'Permission';

        // module name
        $this->module_name = 'permission';
    }

    public function index()
    {
        $module_title = $this->module_title;
        $module_name = $this->module_name;
        $roles = Role::get();
        $permissions = Permission::get();
        $module_action = 'List';

        // Create a better permission grouping system
        $groupedPermissions = collect();
        
        // Define module groups with their permission patterns
        $moduleGroups = [
            'FAQs' => ['view_faqs', 'add_faqs', 'edit_faqs', 'delete_faqs'],
            'Laboratory' => [
                'view_labs', 'add_labs', 'edit_labs', 'delete_labs',
                'view_lab_tests', 'add_lab_tests', 'edit_lab_tests', 'delete_lab_tests',
                'view_lab_results', 'add_lab_results', 'edit_lab_results', 'delete_lab_results',
                'view_lab_orders', 'add_lab_orders', 'edit_lab_orders', 'delete_lab_orders',
                'view_lab_services', 'add_lab_services', 'edit_lab_services', 'delete_lab_services',
                'view_lab_categories', 'add_lab_categories', 'edit_lab_categories', 'delete_lab_categories',
                'view_lab_equipment', 'add_lab_equipment', 'edit_lab_equipment', 'delete_lab_equipment',
                'order_lab_tests'
            ],
            'Patient Referral' => ['view_patient_referral', 'add_patient_referral', 'edit_patient_referral', 'delete_patient_referral'],
            'Blog' => [
                'view_blogs', 'create_blogs', 'edit_blogs', 'delete_blogs',
                'manage_blog_status', 'feature_blogs', 'manage_blog_media',
                'bulk_delete_blogs', 'bulk_restore_blogs', 'bulk_force_delete_blogs',
                'export_blogs', 'import_blogs', 'view_blog_analytics', 'view_blog_statistics'
            ],
            'Reviews' => ['view_reviews', 'add_reviews', 'edit_reviews', 'delete_reviews'],
            'Payments' => ['view_payments', 'add_payments', 'edit_payments', 'delete_payments', 'update_payment_status'],
            'Doctors' => ['view_doctors', 'add_doctors', 'edit_doctors', 'delete_doctors'],
            'Patients' => ['view_patients', 'add_patients', 'edit_patients', 'delete_patients'],
            'Appointments' => ['view_appointments', 'add_appointments', 'edit_appointments', 'delete_appointments'],
            'Clinics' => ['view_clinics', 'add_clinics', 'edit_clinics', 'delete_clinics'],
            'Settings' => ['view_settings', 'add_settings', 'edit_settings', 'delete_settings'],
            'Roles' => ['view_roles', 'add_roles', 'edit_roles', 'delete_roles'],
            'Permissions' => ['view_permissions', 'add_permissions', 'edit_permissions', 'delete_permissions'],
        ];

        // Group permissions by defined modules
        foreach ($moduleGroups as $moduleName => $permissionNames) {
            $modulePermissions = $permissions->whereIn('name', $permissionNames);
            if ($modulePermissions->count() > 0) {
                $groupedPermissions->put($moduleName, $modulePermissions);
            }
        }

        // Add any remaining permissions to "Other" category
        $usedPermissions = $groupedPermissions->flatten()->pluck('name');
        $remainingPermissions = $permissions->whereNotIn('name', $usedPermissions);
        
        if ($remainingPermissions->count() > 0) {
            // Group remaining permissions by their first part
            $remainingGrouped = $remainingPermissions->groupBy(function($permission) {
                $parts = explode('_', $permission->name);
                if (count($parts) >= 2) {
                    $moduleName = ucfirst($parts[0]);
                    return $moduleName;
                }
                return 'Other';
            });
            
            foreach ($remainingGrouped as $moduleName => $perms) {
                $groupedPermissions->put($moduleName, $perms);
            }
        }

        // Sort the modules alphabetically
        $groupedPermissions = $groupedPermissions->sortKeys();

        return view('permission-role.permissions', compact('roles', 'permissions', 'module_title', 'module_name', 'module_action', 'groupedPermissions'));
    }

    public function store(Request $request, Role $role_id)
    {
        if (env('IS_DEMO')) {
            return redirect()->back()->with('error', __('messages.permission_denied'));
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = Permission::get()->pluck('name')->toArray();
        $role_id->revokePermissionTo($permissions);
        if (isset($request->permission) && is_array($request->permission)) {
            foreach ($request->permission as $permission => $roles) {
                $pr = Permission::findOrCreate($permission);
                $role_id->permissions()->syncWithoutDetaching([$pr->id]);
            }
        }

        \Artisan::call('cache:clear');
        \Artisan::call('permission:cache-reset');

        return redirect()->route('backend.permission-role.list')->withSuccess(__('permission-role.save_form'));
    }

    public function reset_permission($role_id)
    {
        $message = __('messages.reset_form', ['form' => __('page.lbl_role')]);
        try {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            $role = Role::find($role_id);

            $permissions = Permission::get()->pluck('name')->toArray();

            if ($role) {
                $role->permissions()->detach();
            }

            \Artisan::call('cache:clear');
        } catch (\Exception $th) {
        }

        return response()->json(['status' => true, 'message' => $message]);
    }
}
