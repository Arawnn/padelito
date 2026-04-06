<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Providers;

use App\Features\Player\Application\Commands\CreatePlayerProfileCommand;
use App\Features\Player\Application\Commands\CreatePlayerProfileCommandHandler;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Infrastructure\Repositories\EloquentPlayerRepository;
use App\Shared\Application\Bus\HandlerMap;
use Illuminate\Support\ServiceProvider;

final class PlayerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PlayerRepositoryInterface::class, EloquentPlayerRepository::class);
    }

    public function boot(): void
    {
        $map = $this->app->make(HandlerMap::class);
        $map->register(CreatePlayerProfileCommand::class, CreatePlayerProfileCommandHandler::class);
    }
}
