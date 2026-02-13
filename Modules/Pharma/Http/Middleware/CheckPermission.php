<?php

namespace Modules\Pharma\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

class CheckPermission
{
    public function handle($request, Closure $next, $permission)
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = Auth::user();

        if (! $user) {
            abort(403, 'Unauthorized');
        }

        $user = $user->fresh(['roles', 'permissions']);
        Auth::setUser($user); 

        if (! $user->can($permission)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
