<?php

namespace App\Http\Middleware;

use App\Services\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $type, string $values)
    {
        if (!auth()->check()) {
            return ApiResponse::error('Unauthenticated.', 401);
        }

        // $user = auth()->user();
        // $hasAccess = false;

        // switch ($type) {
        //     case 'role':
        //         $hasAccess = $user->hasAnyRole(explode(',', $values));
        //         break;
        //     case 'permission':
        //         $hasAccess = $user->hasAnyPermission(explode(',', $values));
        //         break;
        //     case 'role_or_permission':
        //         // Format: "role1,role2|perm1,perm2"
        //         $parts = explode('|', $values, 2);
        //         $roles = explode(',', $parts[0]);
        //         $perms = isset($parts[1]) ? explode(',', $parts[1]) : [];
        //         $hasAccess = $user->hasAnyRole($roles) || $user->hasAnyPermission($perms);
        //         break;
        // }

        // if (!$hasAccess) {
        //     return ApiResponse::permissionDenied();
        // }

        return $next($request);
    }
}
