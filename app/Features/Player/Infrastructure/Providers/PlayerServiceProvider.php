<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Providers;

use App\Features\Player\Application\Commands\ChangeProfileVisibility\ChangeProfileVisibilityCommand;
use App\Features\Player\Application\Commands\ChangeProfileVisibility\ChangeProfileVisibilityCommandHandler;
use App\Features\Player\Application\Commands\ChangeUsername\ChangeUsernameCommand;
use App\Features\Player\Application\Commands\ChangeUsername\ChangeUsernameCommandHandler;
use App\Features\Player\Application\Commands\CreatePlayerProfile\CreatePlayerProfileCommand;
use App\Features\Player\Application\Commands\CreatePlayerProfile\CreatePlayerProfileCommandHandler;
use App\Features\Player\Application\Commands\InitializePlayerProfile\InitializePlayerProfileCommand;
use App\Features\Player\Application\Commands\InitializePlayerProfile\InitializePlayerProfileCommandHandler;
use App\Features\Player\Application\Commands\UpdatePlayerIdentity\UpdatePlayerIdentityCommand;
use App\Features\Player\Application\Commands\UpdatePlayerIdentity\UpdatePlayerIdentityCommandHandler;
use App\Features\Player\Application\Commands\UpdatePlayerPreferences\UpdatePlayerPreferencesCommand;
use App\Features\Player\Application\Commands\UpdatePlayerPreferences\UpdatePlayerPreferencesCommandHandler;
use App\Features\Player\Application\Commands\UploadPlayerAvatar\UploadPlayerAvatarCommand;
use App\Features\Player\Application\Commands\UploadPlayerAvatar\UploadPlayerAvatarCommandHandler;
use App\Features\Player\Application\Contracts\AvatarProvisionerInterface;
use App\Features\Player\Application\Events\UpdatePlayerStats\UpdatePlayerStatsWhenMatchValidated;
use App\Features\Player\Application\Queries\GetPlayerProfile\GetPlayerProfileQuery;
use App\Features\Player\Application\Queries\GetPlayerProfile\GetPlayerProfileQueryHandler;
use App\Features\Player\Application\Queries\GetPublicPlayerProfile\GetPublicPlayerProfileQuery;
use App\Features\Player\Application\Queries\GetPublicPlayerProfile\GetPublicPlayerProfileQueryHandler;
use App\Features\Player\Domain\Repositories\EloHistoryRepositoryInterface;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Infrastructure\Http\v1\Exceptions\PlayerExceptionMapper;
use App\Features\Player\Infrastructure\Persistence\Eloquent\Repositories\EloquentEloHistoryRepository;
use App\Features\Player\Infrastructure\Persistence\Eloquent\Repositories\EloquentPlayerRepository;
use App\Features\Player\Infrastructure\Services\DefaultAvatarProvisioner;
use App\Shared\Application\Bus\HandlerMap;
use Illuminate\Support\ServiceProvider;

final class PlayerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PlayerRepositoryInterface::class, EloquentPlayerRepository::class);
        $this->app->bind(EloHistoryRepositoryInterface::class, EloquentEloHistoryRepository::class);
        $this->app->bind(AvatarProvisionerInterface::class, DefaultAvatarProvisioner::class);

        $this->app->bind(PlayerExceptionMapper::class);
        $this->app->tag([PlayerExceptionMapper::class], 'domain_exception_renderers');
        $this->app->tag([UpdatePlayerStatsWhenMatchValidated::class], 'domain_event_subscribers');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        $map = $this->app->make(HandlerMap::class);
        $map->register(CreatePlayerProfileCommand::class, CreatePlayerProfileCommandHandler::class);
        $map->register(InitializePlayerProfileCommand::class, InitializePlayerProfileCommandHandler::class);
        $map->register(UploadPlayerAvatarCommand::class, UploadPlayerAvatarCommandHandler::class);
        $map->register(ChangeProfileVisibilityCommand::class, ChangeProfileVisibilityCommandHandler::class);
        $map->register(ChangeUsernameCommand::class, ChangeUsernameCommandHandler::class);
        $map->register(UpdatePlayerIdentityCommand::class, UpdatePlayerIdentityCommandHandler::class);
        $map->register(UpdatePlayerPreferencesCommand::class, UpdatePlayerPreferencesCommandHandler::class);
        $map->register(GetPlayerProfileQuery::class, GetPlayerProfileQueryHandler::class);
        $map->register(GetPublicPlayerProfileQuery::class, GetPublicPlayerProfileQueryHandler::class);
    }
}
