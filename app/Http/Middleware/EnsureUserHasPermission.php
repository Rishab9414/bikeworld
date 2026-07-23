<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    /**
     * Usage: ->middleware('permission:orders') or 'permission:marketing,customers'
     * Passes when the user is a super admin or has any permission in one of the given groups.
     */
    public function handle(Request $request, Closure $next, string ...$groups): Response
    {
        $user = auth()->user();

        if (! $user || ! $user->isAdmin()) {
            abort(403);
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        foreach ($groups as $group) {
            if ($user->hasPermissionGroup($group)) {
                return $next($request);
            }
        }

        abort(403, 'You do not have permission to access this section.');
    }
}
