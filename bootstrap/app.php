<?php

use App\Http\Middleware\EnsureUserIsActive;
use App\Providers\AuthServiceProvider;                  // ✅ importa tu provider
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\SubstituteBindings;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',               // ✅ API habilitada (opcional)
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        AuthServiceProvider::class,                      // ✅ registra policies aquí
    ])
    ->withMiddleware(function (Middleware $middleware) {
        // Aliases personalizados
        $middleware->alias([
            'role'        => RoleMiddleware::class,
            'permission'  => PermissionMiddleware::class,
            'active'      => EnsureUserIsActive::class,
            // Si usas Sanctum en endpoints API de sesión:
            'stateful'    => EnsureFrontendRequestsAreStateful::class,
            // Otros que ya tenías importados si los necesitas:
            'throttle'    => ThrottleRequests::class,
            'bindings'    => SubstituteBindings::class,
        ]);

        // Aplica el middleware de usuario activo a todo el grupo web
        $middleware->appendToGroup('web', EnsureUserIsActive::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
