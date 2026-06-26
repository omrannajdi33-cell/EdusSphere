<?php

use App\Http\Middleware\EnsureSessionIsActive;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'guest' => RedirectIfAuthenticated::class,
        ]);

        $middleware->web(append: [
            EnsureSessionIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Session expirée. Recharge la page et réessaie.',
                ], 419);
            }

            $message = 'Session expirée. Recharge la page et réessaie.';

            if ($request->is('login')) {
                return redirect()
                    ->route('login')
                    ->withErrors(['email' => $message]);
            }

            return redirect()
                ->back()
                ->withInput($request->except('password', '_token'))
                ->withErrors(['session' => $message]);
        });
    })->create();
