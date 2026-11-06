<?php

use App\Http\Middleware\EnsureHasRole;
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
        // Railway (dan PaaS lain) berada di belakang proxy HTTPS — percayai
        // header X-Forwarded-* agar Laravel tahu request aslinya https,
        // supaya login/redirect tidak jatuh ke http.
        $middleware->trustProxies(at: '*');

        // Policy auto-discovery Laravel 11: App\Policies\<Model>Policy
        // dikenali otomatis berdasarkan nama model — tidak perlu
        // registrasi manual di AppServiceProvider.
        $middleware->alias([
            'role' => EnsureHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
