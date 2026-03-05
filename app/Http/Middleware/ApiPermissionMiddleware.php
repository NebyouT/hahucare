<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiPermissionMiddleware
{
    /**
     * Handle an incoming API request and check permissions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|array  $permission
     * @return mixed
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

        // Log permission check for debugging
        Log::info('API Permission Check', [
            'user_id' => $user->id,
            'email' => $user->email,
            'user_type' => $user->user_type,
            'route' => $request->path(),
            'method' => $request->method(),
            'required_permissions' => $permissions,
        ]);

        // Admin and demo_admin bypass all permission checks
        if ($user->hasRole(['admin', 'demo_admin'])) {
            Log::info('API Permission: Admin access granted', [
                'user_id' => $user->id,
                'route' => $request->path(),
            ]);
            return $next($request);
        }

        // If no specific permissions required, just check authentication
        if (empty($permissions)) {
            return $next($request);
        }

        // Check if user has any of the required permissions
        try {
            if ($user->hasAnyPermission($permissions)) {
                Log::info('API Permission: Access granted', [
                    'user_id' => $user->id,
                    'route' => $request->path(),
                    'matched_permission' => $this->getMatchedPermission($user, $permissions),
                ]);
                return $next($request);
            }
        } catch (\Exception $e) {
            Log::error('API Permission Check Error', [
                'user_id' => $user->id,
                'route' => $request->path(),
                'error' => $e->getMessage(),
            ]);
        }

        // Permission denied
        Log::warning('API Permission: Access denied', [
            'user_id' => $user->id,
            'email' => $user->email,
            'user_type' => $user->user_type,
            'route' => $request->path(),
            'required_permissions' => $permissions,
            'user_permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
        ]);

        return response()->json([
            'status' => false,
            'message' => 'Access denied. You do not have permission to perform this action.',
            'required_permissions' => $permissions,
        ], 403);
    }

    /**
     * Get the first matched permission from the list
     *
     * @param  \App\Models\User  $user
     * @param  array  $permissions
     * @return string|null
     */
    private function getMatchedPermission($user, array $permissions)
    {
        foreach ($permissions as $permission) {
            if ($user->hasPermissionTo($permission)) {
                return $permission;
            }
        }
        return null;
    }
}
