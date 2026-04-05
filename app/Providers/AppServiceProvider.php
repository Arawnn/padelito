<?php

namespace App\Providers;

use App\Features\Auth\Infrastructure\Providers\AuthServiceProvider;
use App\Features\Player\Infrastructure\Providers\PlayerServiceProvider;
use App\Shared\Application\Bus\HandlerMap;
use App\Shared\Infrastructure\Providers\SharedServiceProvider;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        // Shared first
        $this->app->singleton(HandlerMap::class, fn () => new HandlerMap);
        $this->app->register(SharedServiceProvider::class);
        // Features second
        $this->app->register(AuthServiceProvider::class);
        $this->app->register(PlayerServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }
}
