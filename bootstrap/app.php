<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Exceptions\ExternalServiceException;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckPermission;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')->prefix('api')->group(base_path('routes/admin.php'));
            Route::middleware('api')->prefix('api')->group(base_path('routes/officer.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn (Request $request) => null);
        $middleware->alias([
            'role'       => CheckRole::class,
            'permission' => CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // Invalid or expired token → 401
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'data'    => null,
                    'message' => 'Unauthenticated. Please log in again.',
                    'success' => false,
                ], 401);
            }
        });

        // Model not found → 404
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'data'    => null,
                    'message' => 'Resource not found.',
                    'success' => false,
                ], 404);
            }
        });

        // Validation errors → 422 with field-level detail
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'data'    => null,
                    'message' => $e->getMessage(),
                    'errors'  => $e->errors(),
                    'success' => false,
                ], 422);
            }
        });

        // Third-party service unreachable → 503
        $exceptions->render(function (ExternalServiceException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'data'    => null,
                    'message' => $e->getMessage(),
                    'success' => false,
                ], 503);
            }
        });
    })->create();
