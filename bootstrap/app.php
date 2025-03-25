<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up', // Health check route for monitoring tools
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Append Laravel's built-in CORS middleware to allow cross-origin requests
        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);

        // Register your custom CheckRole middleware to enforce role-based access control
/*         $middleware->append(\App\Http\Middleware\CheckRole::class);
 */    })
    ->withExceptions(function (Exceptions $exceptions) {
        // You can customize exception handling here if needed.
        // For example, logging or custom error pages for specific exceptions
    })
    ->create();
