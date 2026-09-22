<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'prevent-academic-password-change' => \App\Http\Middleware\PreventAcademicPasswordChange::class,
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'not-suspended' => \App\Http\Middleware\EnsureUserIsNotSuspended::class,
        ]);

        $middleware->appendToGroup('web', [
            \App\Http\Middleware\EnsureUserIsNotSuspended::class,
        ]);

        $middleware->appendToGroup('api', [
            \App\Http\Middleware\EnsureUserIsNotSuspended::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
