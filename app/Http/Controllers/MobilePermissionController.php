<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class MobilePermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|demo_admin']);
    }

    /**
     * Mobile permission groups for the UI matrix.
     * Each group maps to a set of mobile_* permission names.
     */
    private function getPermissionGroups(): array
    {
        return [
            'Dashboard' => [
                'mobile_view_dashboard',
            ],
            'Categories' => [
                'mobile_view_categories',
                'mobile_add_categories',
                'mobile_edit_categories',
                'mobile_delete_categories',
            ],
            'Services' => [
                'mobile_view_services',
                'mobile_add_services',
                'mobile_edit_services',
                'mobile_delete_services',
            ],
            'Clinics' => [
                'mobile_view_clinics',
                'mobile_add_clinics',
                'mobile_edit_clinics',
                'mobile_delete_clinics',
            ],
            'Doctors' => [
                'mobile_view_doctors',
                'mobile_add_doctors',
                'mobile_edit_doctors',
                'mobile_delete_doctors',
            ],
            'Patients' => [
                'mobile_view_patients',
                'mobile_add_patients',
                'mobile_edit_patients',
                'mobile_delete_patients',
            ],
            'Receptionists' => [
                'mobile_view_receptionists',
                'mobile_add_receptionists',
                'mobile_edit_receptionists',
                'mobile_delete_receptionists',
            ],
            'Appointments' => [
                'mobile_view_appointment',
                'mobile_add_appointment',
                'mobile_edit_appointment',
                'mobile_delete_appointment',
            ],
            'Encounters' => [
                'mobile_view_encounter',
                'mobile_add_encounter',
                'mobile_edit_encounter',
                'mobile_delete_encounter',
            ],
            'Medical Reports' => [
                'mobile_view_medical_report',
                'mobile_add_medical_report',
                'mobile_edit_medical_report',
                'mobile_delete_medical_report',
            ],
            'Prescriptions' => [
                'mobile_view_prescription',
                'mobile_add_prescription',
                'mobile_edit_prescription',
                'mobile_delete_prescription',
            ],
            'Billing' => [
                'mobile_view_billing',
                'mobile_add_billing',
                'mobile_edit_billing',
                'mobile_delete_billing',
            ],
            'Backups' => [
                'mobile_view_backups',
            ],
        ];
    }

    /**
     * Display the mobile permissions matrix.
     */
    public function index()
    {
        $roles = Role::whereNotIn('name', ['admin', 'demo_admin'])
            ->with('permissions')
            ->get();

        $groups = $this->getPermissionGroups();

        // Ensure all mobile permissions exist in DB
        $allMobilePerms = collect($groups)->flatten()->unique()->values();
        $existingPerms = Permission::where('name', 'like', 'mobile_%')->pluck('name');
        $missingPerms = $allMobilePerms->diff($existingPerms);

        // Stats
        $totalMobilePerms = Permission::where('name', 'like', 'mobile_%')->count();

        return view('backend.mobile-permissions.index', compact(
            'roles', 'groups', 'missingPerms', 'totalMobilePerms'
        ));
    }

    /**
     * Update mobile permissions for a specific role (AJAX).
     */
    public function updateRole(Request $request, Role $role)
    {
        if (env('IS_DEMO')) {
            return response()->json(['success' => false, 'message' => 'Demo mode - changes not allowed'], 403);
        }

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|starts_with:mobile_',
        ]);

        // Get current non-mobile permissions (preserve them)
        $nonMobilePerms = $role->permissions
            ->filter(fn($p) => !str_starts_with($p->name, 'mobile_'))
            ->pluck('name')
            ->toArray();

        // Merge non-mobile + new mobile permissions
        $newMobilePerms = $request->input('permissions', []);
        $allPerms = array_merge($nonMobilePerms, $newMobilePerms);

        $role->syncPermissions($allPerms);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'success' => true,
            'message' => "Mobile permissions updated for {$role->name}",
            'count' => count($newMobilePerms),
        ]);
    }

    /**
     * Add a new mobile permission (AJAX).
     */
    public function store(Request $request)
    {
        if (env('IS_DEMO')) {
            return response()->json(['success' => false, 'message' => 'Demo mode - changes not allowed'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $name = $request->input('name');

        // Ensure mobile_ prefix
        if (!str_starts_with($name, 'mobile_')) {
            $name = 'mobile_' . $name;
        }

        // Sanitize
        $name = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $name));

        if (Permission::where('name', $name)->exists()) {
            return response()->json(['success' => false, 'message' => "Permission '{$name}' already exists"], 422);
        }

        Permission::create(['name' => $name, 'guard_name' => 'web']);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'success' => true,
            'message' => "Permission '{$name}' created successfully",
            'name' => $name,
        ]);
    }

    /**
     * Run the MobileApiPermissionSeeder to sync all permissions (AJAX).
     */
    public function sync()
    {
        if (env('IS_DEMO')) {
            return response()->json(['success' => false, 'message' => 'Demo mode - changes not allowed'], 403);
        }

        try {
            \Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\MobileApiPermissionSeeder', '--force' => true]);
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            $output = \Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Mobile permissions synced successfully',
                'output' => $output,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a mobile permission (AJAX).
     */
    public function destroy(Request $request)
    {
        if (env('IS_DEMO')) {
            return response()->json(['success' => false, 'message' => 'Demo mode - changes not allowed'], 403);
        }

        $request->validate([
            'name' => 'required|string|starts_with:mobile_',
        ]);

        $perm = Permission::where('name', $request->input('name'))->first();
        if (!$perm) {
            return response()->json(['success' => false, 'message' => 'Permission not found'], 404);
        }

        // Revoke from all roles first
        $perm->roles()->detach();
        $perm->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'success' => true,
            'message' => "Permission '{$perm->name}' deleted",
        ]);
    }
}
