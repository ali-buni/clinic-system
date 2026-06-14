<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{

    public function handle(Request $request, Closure $next, ...$types): Response
    {

        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthenticateddd.'], 401);
        }

        $currentUser = auth()->user();

        foreach ($types as $type) {
            
            $relationName = $type . 'Profile';

            if (method_exists($currentUser, $relationName) && $currentUser->{$relationName}()->exists()) {
                return $next($request); 
            }
        }

        return response()->json([
            'message' => 'Unauthorized. This section requires a specific account type.'
        ], 403);
    }
}