<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Providers;

use App\Features\Matches\Application\Commands\CancelMatch\CancelMatchCommand;
use App\Features\Matches\Application\Commands\CancelMatch\CancelMatchCommandHandler;
use App\Features\Matches\Application\Commands\ConfirmMatch\ConfirmMatchCommand;
use App\Features\Matches\Application\Commands\ConfirmMatch\ConfirmMatchCommandHandler;
use App\Features\Matches\Application\Commands\CreateMatch\CreateMatchCommand;
use App\Features\Matches\Application\Commands\CreateMatch\CreateMatchCommandHandler;
use App\Features\Matches\Application\Commands\InvitePlayerToMatch\InvitePlayerToMatchCommand;
use App\Features\Matches\Application\Commands\InvitePlayerToMatch\InvitePlayerToMatchCommandHandler;
use App\Features\Matches\Application\Commands\RespondToMatchInvitation\RespondToMatchInvitationCommand;
use App\Features\Matches\Application\Commands\RespondToMatchInvitation\RespondToMatchInvitationCommandHandler;
use App\Features\Matches\Application\Commands\UpdateMatch\UpdateMatchCommand;
use App\Features\Matches\Application\Commands\UpdateMatch\UpdateMatchCommandHandler;
use App\Features\Matches\Application\Contracts\PlayerRegistryInterface;
use App\Features\Matches\Application\Events\CancelActiveInvitationsWhenMatchCancelled;
use App\Features\Matches\Application\Queries\GetMatch\GetMatchQuery;
use App\Features\Matches\Application\Queries\GetMatch\GetMatchQueryHandler;
use App\Features\Matches\Application\Queries\GetMyMatches\GetMyMatchesQuery;
use App\Features\Matches\Application\Queries\GetMyMatches\GetMyMatchesQueryHandler;
use App\Features\Matches\Application\Queries\GetMyMatchInvitations\GetMyMatchInvitationsQuery;
use App\Features\Matches\Application\Queries\GetMyMatchInvitations\GetMyMatchInvitationsQueryHandler;
use App\Features\Matches\Domain\Repositories\MatchInvitationRepositoryInterface;
use App\Features\Matches\Domain\Repositories\MatchRepositoryInterface;
use App\Features\Matches\Infrastructure\Http\v1\Exceptions\MatchExceptionMapper;
use App\Features\Matches\Infrastructure\Persistence\Eloquent\Repositories\EloquentMatchInvitationRepository;
use App\Features\Matches\Infrastructure\Persistence\Eloquent\Repositories\EloquentMatchRepository;
use App\Features\Matches\Infrastructure\Services\EloquentPlayerRegistry;
use App\Shared\Application\Bus\HandlerMap;
use Illuminate\Support\ServiceProvider;

final class MatchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MatchRepositoryInterface::class, EloquentMatchRepository::class);
        $this->app->bind(MatchInvitationRepositoryInterface::class, EloquentMatchInvitationRepository::class);
        $this->app->bind(PlayerRegistryInterface::class, EloquentPlayerRegistry::class);

        $this->app->bind(MatchExceptionMapper::class);
        $this->app->tag([MatchExceptionMapper::class], 'domain_exception_renderers');
        $this->app->tag([CancelActiveInvitationsWhenMatchCancelled::class], 'domain_event_subscribers');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        $map = $this->app->make(HandlerMap::class);

        $map->register(CreateMatchCommand::class, CreateMatchCommandHandler::class);
        $map->register(UpdateMatchCommand::class, UpdateMatchCommandHandler::class);
        $map->register(CancelMatchCommand::class, CancelMatchCommandHandler::class);
        $map->register(InvitePlayerToMatchCommand::class, InvitePlayerToMatchCommandHandler::class);
        $map->register(RespondToMatchInvitationCommand::class, RespondToMatchInvitationCommandHandler::class);
        $map->register(ConfirmMatchCommand::class, ConfirmMatchCommandHandler::class);

        $map->register(GetMatchQuery::class, GetMatchQueryHandler::class);
        $map->register(GetMyMatchesQuery::class, GetMyMatchesQueryHandler::class);
        $map->register(GetMyMatchInvitationsQuery::class, GetMyMatchInvitationsQueryHandler::class);
    }
}
