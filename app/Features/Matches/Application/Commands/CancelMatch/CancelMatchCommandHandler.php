<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\Commands\CancelMatch;

use App\Features\Matches\Domain\Exceptions\MatchNotFoundException;
use App\Features\Matches\Domain\Exceptions\UnauthorizedMatchOperationException;
use App\Features\Matches\Domain\Repositories\MatchRepositoryInterface;
use App\Features\Matches\Domain\ValueObjects\MatchId;
use App\Features\Matches\Domain\ValueObjects\PlayerId;
use App\Shared\Application\Transaction\TransactionManagerInterface;
use App\Shared\Domain\Contracts\EventDispatcherInterface;

final readonly class CancelMatchCommandHandler
{
    public function __construct(
        private MatchRepositoryInterface $matchRepository,
        private TransactionManagerInterface $transactionManager,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function __invoke(CancelMatchCommand $command): void
    {
        $matchId = MatchId::fromString($command->matchId);
        $requesterId = PlayerId::fromString($command->requesterId);

        $match = $this->matchRepository->findById($matchId);
        if ($match === null) {
            throw MatchNotFoundException::create();
        }

        if (! $match->isCreator($requesterId)) {
            throw UnauthorizedMatchOperationException::create();
        }

        $match->cancel();

        $domainEvents = $match->pullDomainEvents();

        $this->matchRepository->save($match);

        $this->transactionManager->afterCommit(fn () => $this->eventDispatcher->dispatchEvents($domainEvents));
    }
}
