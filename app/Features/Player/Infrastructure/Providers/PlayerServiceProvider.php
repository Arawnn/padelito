<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Providers;

use App\Features\Player\Application\Commands\CreatePlayerProfile\CreatePlayerProfileCommand;
use App\Features\Player\Application\Commands\CreatePlayerProfile\CreatePlayerProfileCommandHandler;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Infrastructure\Services\DefaultAvatarProvisioner;
use App\Features\Player\Application\Commands\CreatePlayerProfile\Contracts\AvatarProvisionerInterface;
use App\Features\Player\Infrastructure\Persistence\Eloquent\Repositories\EloquentPlayerRepository;
use App\Shared\Application\Bus\HandlerMap;
use Illuminate\Support\ServiceProvider;

final class PlayerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PlayerRepositoryInterface::class, EloquentPlayerRepository::class);
        $this->app->bind(AvatarProvisionerInterface::class, DefaultAvatarProvisioner::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        $map = $this->app->make(HandlerMap::class);
        $map->register(CreatePlayerProfileCommand::class, CreatePlayerProfileCommandHandler::class);
    }
}
