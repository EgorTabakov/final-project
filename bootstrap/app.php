<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\Localization;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        health: '/up',
        then: function ($router) {
            Route::prefix('api/v1')
                ->middleware('api')
                ->name('api.v1.')
                ->group(base_path('routes/apiv1.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(Localization::class);
        $middleware->alias(['role' => \Spatie\Permission\Middleware\RoleMiddleware::class]);
        $middleware->alias(['permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class]);
        $middleware->alias(['role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e) {
            return response()->json([
                'success' => false,
                'message' => __($e->getMessage()),
                'data' => (object) ['error' => __($e->getMessage())]
            ], 401);
        });
    })->create();
