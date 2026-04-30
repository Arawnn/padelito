<?php

use App\Shared\Domain\Exceptions\DomainException;
use App\Shared\Domain\Exceptions\DomainExceptionInterface;
use App\Shared\Infrastructure\Exceptions\InfrastructureException;
use App\Shared\Infrastructure\Http\Exceptions\ApiExceptionMapper;
use App\Shared\Infrastructure\Http\Exceptions\DomainExceptionRendererInterface;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
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

        $exceptions->dontReport([DomainException::class]);

        $exceptions->renderable(function (InfrastructureException $e) {
            \Illuminate\Support\Facades\Log::error('Infrastructure failure', ['message' => $e->getMessage(), 'exception' => $e]);

            return response()->json([
                'error' => ['code' => 'INFRASTRUCTURE_ERROR', 'message' => 'An unexpected error occurred.'],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        });

        $exceptions->renderable(function (DomainExceptionInterface $e) {
            /** @var DomainExceptionRendererInterface $renderer */
            foreach (app()->tagged('domain_exception_renderers') as $renderer) {
                if ($renderer->handles($e)) {
                    $response = $renderer->render($e);
                    if ($response->getStatusCode() >= 500 && app()->bound('sentry')) {
                        \Sentry\captureException($e);
                    }

                    return $response;
                }
            }

            if (app()->bound('sentry')) {
                \Sentry\captureException($e);
            }

            return ApiExceptionMapper::toResponse($e, Response::HTTP_INTERNAL_SERVER_ERROR);
        });
    })->create();
