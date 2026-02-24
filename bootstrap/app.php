<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Request;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Features\Auth\Domain\Exceptions\UserNotFoundException;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use App\Features\Auth\Domain\Exceptions\InvalidPasswordException;
use App\Features\Auth\Domain\Exceptions\UserAlreadyExistException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        app()->make(\App\Features\Auth\Infrastructure\Exceptions\AuthExceptionHandler::class)->register($exceptions);
    })->create();
