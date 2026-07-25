<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Hostinger / shared hosting sits behind a reverse proxy (SSL termination).
        $middleware->trustProxies(at: '*');

        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
        ]);

        // Keep admin + webhooks reachable while the storefront is in maintenance.
        $middleware->preventRequestsDuringMaintenance([
            'admin',
            'admin/*',
            'webhooks/*',
            'up',
            'bikeworld-admin-bypass',
        ]);

        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'admin.guest' => \App\Http\Middleware\RedirectIfAdminAuthenticated::class,
            'permission' => \App\Http\Middleware\EnsureUserHasPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Session expired. Please refresh and try again.'], 419);
            }

            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'Your session expired. Please submit the form again.');
        });
    })->create();
