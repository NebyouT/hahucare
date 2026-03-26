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

        // Group permissions by module automatically
        $groupedPermissions = $permissions->groupBy(function($permission) {
            $name = $permission->name;
            
            // Handle special cases first
            if (str_starts_with($name, 'view_') || str_starts_with($name, 'add_') || 
                str_starts_with($name, 'edit_') || str_starts_with($name, 'delete_')) {
                
                $parts = explode('_', $name);
                if (count($parts) >= 2) {
                    // Remove the action (view, add, edit, delete) and get the module name
                    $action = array_shift($parts);
                    $moduleName = implode('_', $parts);
                    
                    // Clean up common module name patterns
                    $moduleName = str_replace(['_list', '_management', '_settings'], '', $moduleName);
                    $moduleName = ucfirst(str_replace('_', ' ', $moduleName));
                    
                    return $moduleName;
                }
            }
            
            // Handle permission names with module prefix
            if (preg_match('/^([a-z_]+)_/', $name, $matches)) {
                $modulePart = $matches[1];
                
                // Map common module prefixes to readable names
                $moduleMap = [
                    'lab' => 'Laboratory',
                    'faq' => 'FAQs',
                    'patient' => 'Patient',
                    'doctor' => 'Doctor',
                    'clinic' => 'Clinic',
                    'appointment' => 'Appointment',
                    'billing' => 'Billing',
                    'encounter' => 'Encounter',
                    'prescription' => 'Prescription',
                    'medicine' => 'Medicine',
                    'pharma' => 'Pharmacy',
                    'earning' => 'Earning',
                    'wallet' => 'Wallet',
                    'setting' => 'Settings',
                    'backup' => 'Backup',
                    'notification' => 'Notification',
                    'report' => 'Reports',
                    'role' => 'Role Management',
                    'permission' => 'Permission Management',
                    'user' => 'User Management',
                    'vendor' => 'Vendor',
                    'customer' => 'Customer',
                    'review' => 'Reviews',
                    'tax' => 'Tax',
                    'commission' => 'Commission',
                    'log' => 'Logs',
                ];
                
                if (isset($moduleMap[$modulePart])) {
                    return $moduleMap[$modulePart];
                }
                
                return ucfirst(str_replace('_', ' ', $modulePart));
            }
            
            // Fallback - group by first word or create "Other" category
            $firstWord = explode('_', $name)[0];
            return ucfirst($firstWord);
        })->sortKeys();

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
