<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\Commands\RespondToMatchInvitation;

use App\Features\Matches\Domain\Events\MatchConfirmationsReset;
use App\Features\Matches\Domain\Exceptions\MatchInvitationNotFoundException;
use App\Features\Matches\Domain\Exceptions\MatchNotFoundException;
use App\Features\Matches\Domain\Exceptions\MatchTeamFullException;
use App\Features\Matches\Domain\Exceptions\UnauthorizedMatchOperationException;
use App\Features\Matches\Domain\Repositories\MatchInvitationRepositoryInterface;
use App\Features\Matches\Domain\Repositories\MatchRepositoryInterface;
use App\Features\Matches\Domain\ValueObjects\MatchInvitationId;
use App\Features\Matches\Domain\ValueObjects\PlayerId;
use App\Shared\Application\Transaction\TransactionManagerInterface;
use App\Shared\Domain\Contracts\EventDispatcherInterface;
use App\Shared\Domain\Events\DomainEventCollection;

final readonly class RespondToMatchInvitationCommandHandler
{
    public function __construct(
        private MatchRepositoryInterface $matchRepository,
        private MatchInvitationRepositoryInterface $invitationRepository,
        private TransactionManagerInterface $transactionManager,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function __invoke(RespondToMatchInvitationCommand $command): void
    {
        $invitationId = MatchInvitationId::fromString($command->invitationId);
        $responderId = PlayerId::fromString($command->responderId);

        $invitation = $this->invitationRepository->findById($invitationId);
        if ($invitation === null) {
            throw MatchInvitationNotFoundException::create();
        }

        if (! $invitation->inviteeId()->equals($responderId)) {
            throw UnauthorizedMatchOperationException::create();
        }

        $match = $this->matchRepository->findById($invitation->matchId());
        if ($match === null) {
            throw MatchNotFoundException::create();
        }

        $events = new DomainEventCollection;
        $wasAccepted = $invitation->status()->isAccepted();
        $wasDeclined = $invitation->status()->isDeclined();

        if ($command->accept) {
            $team = $invitation->type()->toTeam();

            if (! $wasAccepted && $match->isTeamFull($team)) {
                throw MatchTeamFullException::create();
            }

            $invitation->accept();
            if (! $wasAccepted) {
                $match->assignPlayer($responderId, $team);
            }

            $confirmationsReset = false;
            foreach ($match->pullDomainEvents() as $event) {
                $events->add($event);
                if ($event instanceof MatchConfirmationsReset) {
                    $confirmationsReset = true;
                }
            }

            if ($confirmationsReset) {
                $this->matchRepository->deleteConfirmations($invitation->matchId());
            }

            $this->matchRepository->save($match);
        } else {
            $invitation->decline();
            if ($wasAccepted) {
                $match->removePlayer($responderId);

                $confirmationsReset = false;
                foreach ($match->pullDomainEvents() as $event) {
                    $events->add($event);
                    if ($event instanceof MatchConfirmationsReset) {
                        $confirmationsReset = true;
                    }
                }

                if ($confirmationsReset) {
                    $this->matchRepository->deleteConfirmations($invitation->matchId());
                }

                $this->matchRepository->save($match);
            }
        }

        $this->invitationRepository->save($invitation);

        foreach ($invitation->pullDomainEvents() as $event) {
            $events->add($event);
        }

        $this->transactionManager->afterCommit(fn () => $this->eventDispatcher->dispatchEvents($events));
    }
}
