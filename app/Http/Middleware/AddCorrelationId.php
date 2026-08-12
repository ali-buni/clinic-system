<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AddCorrelationId
{
    public function handle($request, Closure $next): Response
    {
        $correlationId = (string) Str::uuid();

        if ($request instanceof Request) {
            $correlationId = $request->header('X-Correlation-ID', $correlationId);
            $request->headers->set('X-Correlation-ID', $correlationId);
        }

        Log::withContext(['correlation_id' => $correlationId]);

        $response = $next($request);

        if (is_object($response) && property_exists($response, 'headers')) {
            $response->headers->set('X-Correlation-ID', $correlationId);
        }

        return $response;
    }
}
