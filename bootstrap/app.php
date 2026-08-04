<?php

declare(strict_types=1);

use App\Http\Controllers\HomeController;
use App\Http\Middleware\EnsureSecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Middleware\CreateFreshApiToken;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        apiPrefix: '',
        then: function ($router): void {
            Route::get('/', HomeController::class)
                ->middleware('web')
                ->name('api.home');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(EnsureSecurityHeaders::class);

        $middleware->web(append: [
            CreateFreshApiToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn (Request $request, Throwable $e): bool => true);
    })->create();
