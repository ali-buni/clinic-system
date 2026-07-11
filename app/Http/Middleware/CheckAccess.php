<?php

namespace App\Http\Middleware;

use App\Services\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccess
{
    public function handle(Request $request, Closure $next, ...$params)
    {
        if (!auth()->check()) {
            return ApiResponse::error('Unauthenticated.', 401);
        }

        // role authorization
        $user = auth()->user();
        $first = $params[0] ?? '';
        $parts = explode(':', $first, 2);
        $type = $parts[0] ?? null;
        $firstValues = $parts[1] ?? '';

        $allValues = array_merge(
            $firstValues !== '' ? explode(',', $firstValues) : [],
            array_slice($params, 1)
        );

        if ($type === 'role' && !$user->hasAnyRole($allValues)) {
            return ApiResponse::permissionDenied();
        }

        else if ($type === 'permission' && !$user->hasAnyPermission($allValues)) {
            return ApiResponse::permissionDenied();
        }

        return $next($request);
    }
}
