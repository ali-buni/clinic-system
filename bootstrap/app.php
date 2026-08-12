<?php

use App\Http\Middleware\AddCorrelationId;
use App\Http\Middleware\CheckAccess;
use App\Http\Middleware\CheckResourceAccess;
use App\Services\ApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'checkaccess' => CheckAccess::class,
            'resourceAccess' => CheckResourceAccess::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'api/stripe/webhook',
        ]);

        // $middleware->appendToGroup('api', AddCorrelationId::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (UnauthorizedException $e) {
            return ApiResponse::permissionDenied();
        });

        $exceptions->render(function (NotFoundHttpException $e) {
            if (request()->is('admin/*') || request()->is('admin')) {
                return response()->view('admin.errors.404', [], 404);
            }
        });
    })->create();
