<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api_v1.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\CaptureUserTimezone::class,
            \App\Http\Middleware\CheckInstallation::class,
        ]);
        
        $middleware->alias([
            'password.set'      => \App\Http\Middleware\EnsurePasswordIsSet::class,
            'role'              => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'        => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'auth.api-token'    => \App\Http\Middleware\AuthenticateApiToken::class,
            'super.admin'       => \App\Http\Middleware\SuperAdminMiddleware::class,
            'check.subscription' => \App\Http\Middleware\CheckSubscriptionStatus::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => $e->getMessage() ?: 'Forbidden.'], 403);
            }
        });

        $exceptions->render(function (\InvalidArgumentException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        });

        // Render custom Inertia error pages for web requests (only when debug mode is off)
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            // Skip custom error pages when debug mode is enabled (show detailed errors)
            if (config('app.debug')) {
                return null; // Let Laravel's default error handler show detailed errors
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return null; // Let default JSON handling take over
            }

            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

            $renderableStatuses = [401, 403, 404, 419, 500, 503];

            if (in_array($status, $renderableStatuses)) {
                return \Inertia\Inertia::render('Error', ['status' => $status])
                    ->toResponse($request)
                    ->setStatusCode($status);
            }
        });
    })->create();
