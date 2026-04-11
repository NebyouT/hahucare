<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiPermissionMiddleware
{
    /**
     * Map of API permission names to their equivalent database permission names.
     * The API routes use simplified names, but the database has different names
     * from the PermissionRoleTableSeeder (based on config/constant.php MODULES).
     *
     * Format: 'api_permission_name' => ['db_permission_1', 'db_permission_2', ...]
     * The user only needs ONE of the mapped permissions to pass.
     */
    private array $permissionMap = [
        // ─── Dashboard (accessible to all authenticated roles) ───
        'view_dashboard' => ['__allow_authenticated__'],

        // ─── Categories ───
        'view_categories'   => ['view_clinics_category'],
        'add_categories'    => ['add_clinics_category'],
        'edit_categories'   => ['edit_clinics_category'],
        'delete_categories' => ['delete_clinics_category'],

        // ─── Services ───
        'view_services'   => ['view_clinics_service'],
        'add_services'    => ['add_clinics_service'],
        'edit_services'   => ['edit_clinics_service'],
        'delete_services' => ['delete_clinics_service'],

        // ─── Clinics ───
        'view_clinics'   => ['view_clinics_center'],
        'add_clinics'    => ['add_clinics_center'],
        'edit_clinics'   => ['edit_clinics_center'],
        'delete_clinics' => ['delete_clinics_center'],

        // ─── Appointments ───
        'view_appointment'   => ['view_clinic_appointment_list'],
        'add_appointment'    => ['add_clinic_appointment_list'],
        'edit_appointment'   => ['edit_clinic_appointment_list', 'add_clinic_appointment_list'],
        'delete_appointment' => ['delete_clinic_appointment_list'],

        // ─── Patients ───
        'view_patients'   => ['view_customer'],
        'add_patients'    => ['add_customer'],
        'edit_patients'   => ['edit_customer'],
        'delete_patients' => ['delete_customer'],

        // ─── Receptionists ───
        'view_receptionists'   => ['view_clinic_receptionist_list'],
        'add_receptionists'    => ['add_clinic_receptionist_list'],
        'edit_receptionists'   => ['edit_clinic_receptionist_list'],
        'delete_receptionists' => ['delete_clinic_receptionist_list'],

        // ─── Billing ───
        'view_billing'   => ['view_billing_record'],
        'add_billing'    => ['add_billing_record'],
        'edit_billing'   => ['edit_billing_record'],
        'delete_billing' => ['delete_billing_record'],

        // ─── Medical Reports (map to encounter permissions) ───
        'view_medical_report'   => ['view_encounter'],
        'add_medical_report'    => ['add_encounter', 'edit_encounter'],
        'edit_medical_report'   => ['edit_encounter'],
        'delete_medical_report' => ['delete_encounter'],

        // ─── Prescriptions (exist in DB for pharma; doctors also need access) ───
        'view_prescription'   => ['view_prescription', 'view_encounter'],
        'add_prescription'    => ['add_prescription', 'add_encounter'],
        'edit_prescription'   => ['edit_prescription', 'edit_encounter'],
        'delete_prescription' => ['delete_prescription', 'delete_encounter'],

        // ─── Backups ───
        'view_backups' => ['view_backup'],

        // ─── Doctors (already match in DB) ───
        'view_doctors'   => ['view_doctors'],
        'add_doctors'    => ['add_doctors'],
        'edit_doctors'   => ['edit_doctors'],
        'delete_doctors' => ['delete_doctors'],

        // ─── Encounters (already match in DB) ───
        'view_encounter'   => ['view_encounter'],
        'add_encounter'    => ['add_encounter'],
        'edit_encounter'   => ['edit_encounter'],
        'delete_encounter' => ['delete_encounter'],
    ];

    /**
     * Roles that are allowed to bypass specific permission groups.
     * This handles cases where a role should have API access to a feature
     * even if the specific permission name doesn't exist in the DB.
     */
    private array $roleOverrides = [
        'view_dashboard' => ['vendor', 'doctor', 'receptionist', 'pharma', 'user', 'lab_technician'],
    ];

    /**
     * Handle an incoming API request and check permissions.
     */
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        // Check if user is authenticated
        if (!auth('sanctum')->check()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated. Please login to access this resource.',
            ], 401);
        }

        $user = auth('sanctum')->user();

        // Admin and demo_admin bypass all permission checks
        if ($user->hasRole(['admin', 'demo_admin'])) {
            return $next($request);
        }

        // If no specific permissions required, just check authentication
        if (empty($permissions)) {
            return $next($request);
        }

        // Resolve all API permission names to their DB equivalents
        $resolvedPermissions = $this->resolvePermissions($permissions);

        // Check for special __allow_authenticated__ flag
        if (in_array('__allow_authenticated__', $resolvedPermissions)) {
            return $next($request);
        }

        // Check for role-based overrides
        foreach ($permissions as $apiPermission) {
            if (isset($this->roleOverrides[$apiPermission])) {
                if ($user->hasRole($this->roleOverrides[$apiPermission])) {
                    return $next($request);
                }
            }
        }

        // Check if user has any of the resolved permissions
        try {
            // Filter out permissions that don't exist in DB to avoid exceptions
            $existingPermissions = [];
            foreach ($resolvedPermissions as $perm) {
                try {
                    $permModel = \Spatie\Permission\Models\Permission::findByName($perm, $user->getDefaultGuardName());
                    $existingPermissions[] = $perm;
                } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
                    // Permission doesn't exist in DB, skip it
                    continue;
                }
            }

            if (!empty($existingPermissions) && $user->hasAnyPermission($existingPermissions)) {
                return $next($request);
            }
        } catch (\Exception $e) {
            Log::error('API Permission Check Error', [
                'user_id' => $user->id,
                'route' => $request->path(),
                'error' => $e->getMessage(),
            ]);
        }

        // Permission denied - log details for debugging
        Log::warning('API Permission: Access denied', [
            'user_id' => $user->id,
            'user_type' => $user->user_type ?? 'unknown',
            'roles' => $user->getRoleNames()->toArray(),
            'route' => $request->path(),
            'api_permissions' => $permissions,
            'resolved_permissions' => $resolvedPermissions,
        ]);

        return response()->json([
            'status' => false,
            'message' => 'Access denied. You do not have permission to perform this action.',
            'required_permissions' => $permissions,
        ], 403);
    }

    /**
     * Resolve API permission names to their database equivalents.
     * Checks the mapping table and also includes the original permission name.
     */
    private function resolvePermissions(array $apiPermissions): array
    {
        $resolved = [];

        foreach ($apiPermissions as $apiPerm) {
            // Always include the original API permission name (in case it exists in DB)
            $resolved[] = $apiPerm;

            // Also add all mapped DB permission names
            if (isset($this->permissionMap[$apiPerm])) {
                foreach ($this->permissionMap[$apiPerm] as $dbPerm) {
                    $resolved[] = $dbPerm;
                }
            }
        }

        return array_unique($resolved);
    }
}
