<?php

namespace App\Features\Auth\Infrastructure\Providers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use App\Features\Auth\Domain\Contracts\PasswordHasherInterface;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Infrastructure\Security\SanctumTokenCreator;
use App\Features\Auth\Infrastructure\Security\LaravelPasswordHasher;
use App\Features\Auth\Infrastructure\Contracts\TokenCreatorInterface;
use App\Features\Auth\Infrastructure\Exceptions\AuthExceptionHandler;
use App\Features\Auth\Infrastructure\Repositories\EloquentUserRepository;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminate\Foundation\Exceptions\Handler;

final class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);

        $this->app->bind(PasswordHasherInterface::class, LaravelPasswordHasher::class);
        $this->app->bind(TokenCreatorInterface::class, SanctumTokenCreator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::middleware('api')
            ->prefix('api/v1')
            ->group(function () {
                $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        });

        /** @var Handler $handler */
        $handler = $this->app->make(ExceptionHandlerContract::class);
        $this->app->make(AuthExceptionHandler::class)->register($handler);
    }


    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        // RateLimiter::for('two-factor', function (Request $request) {
        //     return Limit::perMinute(5)->by($request->session()->get('login.id'));
        // });

        // RateLimiter::for('login', function (Request $request) {
        //     $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

        //     return Limit::perMinute(5)->by($throttleKey);
        // });
    }
}
