<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web([
            \App\Http\Middleware\ResetBranchSessionOutsideInventory::class,
        ])
        ->alias([
            'validate_account' => \App\Http\Middleware\ValidateAccount::class,
            'usertype' => \App\Http\Middleware\UserTypeMiddleware::class,
            'profile_verified' => \App\Http\Middleware\ProfileVerified::class,
        ])->validateCsrfTokens(except: [
            'buyer/ajax/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
