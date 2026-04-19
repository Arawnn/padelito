<?php

declare(strict_types=1);

namespace App\Features\Onboarding\Infrastructure\Providers;

use App\Features\Onboarding\Application\RegisterPlayer\RegisterPlayerCommand;
use App\Features\Onboarding\Application\RegisterPlayer\RegisterPlayerCommandHandler;
use App\Shared\Application\Bus\HandlerMap;
use Illuminate\Support\ServiceProvider;

final class OnboardingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        $map = $this->app->make(HandlerMap::class);
        $map->register(RegisterPlayerCommand::class, RegisterPlayerCommandHandler::class);
    }
}
