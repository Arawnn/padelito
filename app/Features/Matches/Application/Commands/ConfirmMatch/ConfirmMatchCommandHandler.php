<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\Commands\ConfirmMatch;

use App\Features\Matches\Domain\Exceptions\MatchNotFoundException;
use App\Features\Matches\Domain\Repositories\MatchRepositoryInterface;
use App\Features\Matches\Domain\ValueObjects\MatchId;
use App\Features\Matches\Domain\ValueObjects\PlayerId;
use App\Shared\Domain\Contracts\EventDispatcherInterface;

final readonly class ConfirmMatchCommandHandler
{
    public function __construct(
        private MatchRepositoryInterface $matchRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function __invoke(ConfirmMatchCommand $command): void
    {
        $match = $this->matchRepository->findByIdWithLock(MatchId::fromString($command->matchId));
        if ($match === null) {
            throw MatchNotFoundException::create();
        }

        $match->confirm(PlayerId::fromString($command->playerId));

        $domainEvents = $match->pullDomainEvents();

        $this->matchRepository->save($match);

        $this->eventDispatcher->dispatchEvents($domainEvents);
    }
}
