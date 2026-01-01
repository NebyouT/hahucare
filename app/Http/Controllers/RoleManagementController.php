<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|demo_admin']);
    }

    /**
     * Display role management interface
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all()->groupBy(function($permission) {
            $parts = explode('_', $permission->name);
            return count($parts) > 1 ? $parts[0] : 'general';
        });
        
        $modules = config('constant.MODULES');
        $menuItems = $this->getMenuItems();
        
        return view('backend.role-management.index', compact('roles', 'permissions', 'modules', 'menuItems'));
    }

    /**
     * Create new role
     */
    public function createRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'title' => 'required|string',
            'description' => 'nullable|string'
        ]);

        $role = Role::create([
            'name' => strtolower(str_replace(' ', '_', $request->name)),
            'title' => $request->title,
            'guard_name' => 'web',
            'is_fixed' => 0
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'role' => $role
        ]);
    }

    /**
     * Update role permissions
     */
    public function updatePermissions(Request $request, Role $role)
    {
        if (env('IS_DEMO')) {
            return response()->json(['error' => 'Demo mode - changes not allowed'], 403);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Get all permissions and revoke them first
        $allPermissions = Permission::all()->pluck('name')->toArray();
        $role->revokePermissionTo($allPermissions);

        // Assign new permissions
        if ($request->has('permissions') && is_array($request->permissions)) {
            foreach ($request->permissions as $permissionName) {
                $permission = Permission::firstOrCreate(['name' => $permissionName]);
                $role->givePermissionTo($permission);
            }
        }

        // Clear cache
        \Artisan::call('cache:clear');
        \Artisan::call('permission:cache-reset');

        return response()->json([
            'success' => true,
            'message' => 'Permissions updated successfully'
        ]);
    }

    /**
     * Toggle role status (enable/disable)
     */
    public function toggleRole(Role $role)
    {
        if ($role->is_fixed) {
            return response()->json(['error' => 'Cannot modify fixed roles'], 403);
        }

        $role->update(['is_active' => !($role->is_active ?? true)]);

        return response()->json([
            'success' => true,
            'message' => 'Role status updated',
            'is_active' => $role->is_active
        ]);
    }

    /**
     * Delete role
     */
    public function deleteRole(Role $role)
    {
        if ($role->is_fixed) {
            return response()->json(['error' => 'Cannot delete fixed roles'], 403);
        }

        if ($role->users()->count() > 0) {
            return response()->json(['error' => 'Cannot delete role with assigned users'], 403);
        }

        $role->permissions()->detach();
        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully'
        ]);
    }

    /**
     * Get role permissions
     */
    public function getPermissions(Role $role)
    {
        return response()->json([
            'permissions' => $role->permissions->pluck('name')->toArray()
        ]);
    }

    /**
     * Get available menu items for permission assignment
     */
    private function getMenuItems()
    {
        return [
            'dashboard' => ['label' => 'Dashboard', 'permissions' => ['view_dashboard']],
            'appointments' => ['label' => 'Appointments', 'permissions' => ['view_clinic_appointment_list', 'add_appointment', 'edit_appointment', 'delete_appointment']],
            'bed_management' => [
                'label' => 'Bed Management',
                'permissions' => ['view_bed_master', 'view_bed_type', 'view_allocations', 'add_bed', 'edit_bed', 'delete_bed']
            ],
            'doctors' => ['label' => 'Doctors', 'permissions' => ['view_doctors', 'add_doctors', 'edit_doctors', 'delete_doctors']],
            'patients' => ['label' => 'Patients', 'permissions' => ['view_customer', 'add_customer', 'edit_customer', 'delete_customer']],
            'clinics' => ['label' => 'Clinics', 'permissions' => ['view_clinics_center', 'add_clinics_center', 'edit_clinics_center', 'delete_clinics_center']],
            'services' => ['label' => 'Services', 'permissions' => ['view_clinics_service', 'add_clinics_service', 'edit_clinics_service', 'delete_clinics_service']],
            'reports' => ['label' => 'Reports', 'permissions' => ['view_daily_bookings', 'view_overall_bookings', 'view_staff_payouts']],
            'settings' => [
                'label' => 'Settings',
                'permissions' => ['view_setting', 'edit_setting', 'view_setting_bussiness', 'view_setting_misc', 'view_setting_customization']
            ],
            'users' => ['label' => 'User Management', 'permissions' => ['view_vendor_list', 'view_clinic_receptionist_list']],
            'finance' => ['label' => 'Finance', 'permissions' => ['view_tax', 'view_earning', 'view_billing_record']],
            'pharma' => ['label' => 'Pharmacy', 'permissions' => ['view_prescription', 'view_medicine', 'view_suppliers', 'view_purchased_order']]
        ];
    }

    /**
     * Get role statistics
     */
    public function getRoleStats()
    {
        $stats = [
            'total_roles' => Role::count(),
            'active_roles' => Role::where('is_active', true)->count(),
            'total_permissions' => Permission::count(),
            'users_by_role' => Role::withCount('users')->get()->pluck('users_count', 'name')
        ];

        return response()->json($stats);
    }
}
