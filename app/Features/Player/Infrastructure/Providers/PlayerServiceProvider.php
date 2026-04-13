<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Providers;

use App\Features\Player\Application\Commands\ChangeProfileVisibility\ChangeProfileVisibilityCommand;
use App\Features\Player\Application\Commands\ChangeProfileVisibility\ChangeProfileVisibilityCommandHandler;
use App\Features\Player\Application\Commands\CreatePlayerProfile\Contracts\AvatarProvisionerInterface;
use App\Features\Player\Application\Commands\CreatePlayerProfile\CreatePlayerProfileCommand;
use App\Features\Player\Application\Commands\CreatePlayerProfile\CreatePlayerProfileCommandHandler;
use App\Features\Player\Application\Commands\UpdatePlayerIdentity\UpdatePlayerIdentityCommand;
use App\Features\Player\Application\Commands\UpdatePlayerIdentity\UpdatePlayerIdentityCommandHandler;
use App\Features\Player\Application\Commands\UpdatePlayerPreferences\UpdatePlayerPreferencesCommand;
use App\Features\Player\Application\Commands\UpdatePlayerPreferences\UpdatePlayerPreferencesCommandHandler;
use App\Features\Player\Application\Queries\GetPlayerProfile\GetPlayerProfileQuery;
use App\Features\Player\Application\Queries\GetPlayerProfile\GetPlayerProfileQueryHandler;
use App\Features\Player\Application\Queries\GetPublicPlayerProfile\GetPublicPlayerProfileQuery;
use App\Features\Player\Application\Queries\GetPublicPlayerProfile\GetPublicPlayerProfileQueryHandler;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Infrastructure\Persistence\Eloquent\Repositories\EloquentPlayerRepository;
use App\Features\Player\Infrastructure\Services\DefaultAvatarProvisioner;
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
        $map->register(ChangeProfileVisibilityCommand::class, ChangeProfileVisibilityCommandHandler::class);
        $map->register(UpdatePlayerIdentityCommand::class, UpdatePlayerIdentityCommandHandler::class);
        $map->register(UpdatePlayerPreferencesCommand::class, UpdatePlayerPreferencesCommandHandler::class);
        $map->register(GetPlayerProfileQuery::class, GetPlayerProfileQueryHandler::class);
        $map->register(GetPublicPlayerProfileQuery::class, GetPublicPlayerProfileQueryHandler::class);
    }
}
