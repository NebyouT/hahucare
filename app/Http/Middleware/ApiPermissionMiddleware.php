<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Mobile App API Permission Middleware
 *
 * This middleware uses a SEPARATE permission namespace for the mobile app.
 * All mobile permissions are prefixed with "mobile_" so they are completely
 * independent from the admin panel (web) permissions.
 *
 * Example: api.permission:view_categories → checks for "mobile_view_categories"
 *
 * This means:
 * - Admin panel uses: view_clinics_category, add_clinics_category, etc.
 * - Mobile app uses:  mobile_view_categories, mobile_add_categories, etc.
 * - They can be assigned/revoked independently per role.
 */
class ApiPermissionMiddleware
{
    /**
     * The mobile permission prefix.
     * All mobile app permissions are stored with this prefix in the database.
     */
    private const MOBILE_PREFIX = 'mobile_';

    /**
     * Handle an incoming API request and check mobile permissions.
     *
     * The route defines: api.permission:view_categories
     * This middleware checks: mobile_view_categories
     */
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        // Must be authenticated via Sanctum
        if (!auth('sanctum')->check()) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Please login to access this resource.',
            ], 401);
        }

        $user = auth('sanctum')->user();

        // Admin and demo_admin bypass all mobile permission checks
        if ($user->hasRole(['admin', 'demo_admin'])) {
            return $next($request);
        }

        // If no specific permissions required, allow any authenticated user
        if (empty($permissions)) {
            return $next($request);
        }

        // Convert route permission names to mobile_ prefixed names
        $mobilePermissions = $this->toMobilePermissions($permissions);

        // Check if user has ANY of the required mobile permissions
        try {
            // Filter to only permissions that exist in the DB
            $existingPerms = [];
            foreach ($mobilePermissions as $perm) {
                if (\Spatie\Permission\Models\Permission::where('name', $perm)->exists()) {
                    $existingPerms[] = $perm;
                }
            }

            if (!empty($existingPerms) && $user->hasAnyPermission($existingPerms)) {
                return $next($request);
            }
        } catch (\Exception $e) {
            Log::error('Mobile API Permission Error', [
                'user_id' => $user->id,
                'route'   => $request->path(),
                'error'   => $e->getMessage(),
            ]);
        }

        // Permission denied
        Log::warning('Mobile API Permission: Access denied', [
            'user_id'            => $user->id,
            'user_type'          => $user->user_type ?? 'unknown',
            'roles'              => $user->getRoleNames()->toArray(),
            'route'              => $request->path(),
            'route_permissions'  => $permissions,
            'mobile_permissions' => $mobilePermissions,
        ]);

        return response()->json([
            'status'  => false,
            'message' => 'Access denied. You do not have permission to perform this action.',
        ], 403);
    }

    /**
     * Convert route-level permission names to mobile-prefixed DB names.
     *
     * Example: ['view_categories', 'edit_categories']
     *       → ['mobile_view_categories', 'mobile_edit_categories']
     */
    private function toMobilePermissions(array $routePermissions): array
    {
        return array_map(function (string $perm) {
            // If it already has the prefix, don't double-prefix
            if (str_starts_with($perm, self::MOBILE_PREFIX)) {
                return $perm;
            }
            return self::MOBILE_PREFIX . $perm;
        }, $routePermissions);
    }
}
