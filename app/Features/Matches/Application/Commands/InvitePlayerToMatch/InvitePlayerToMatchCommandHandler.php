<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\Commands\InvitePlayerToMatch;

use App\Features\Matches\Domain\Entities\MatchInvitation;
use App\Features\Matches\Domain\Exceptions\DuplicatePlayerInMatchException;
use App\Features\Matches\Domain\Exceptions\MatchAlreadyCancelledException;
use App\Features\Matches\Domain\Exceptions\MatchTeamFullException;
use App\Features\Matches\Domain\Exceptions\MatchAlreadyValidatedException;
use App\Features\Matches\Domain\Exceptions\MatchNotFoundException;
use App\Features\Matches\Domain\Exceptions\PlayerNotRegisteredInAppException;
use App\Features\Matches\Domain\Exceptions\UnauthorizedMatchOperationException;
use App\Features\Matches\Domain\Repositories\MatchInvitationRepositoryInterface;
use App\Features\Matches\Domain\Repositories\MatchRepositoryInterface;
use App\Features\Matches\Domain\ValueObjects\InvitationType;
use App\Features\Matches\Domain\ValueObjects\MatchId;
use App\Features\Matches\Domain\ValueObjects\MatchInvitationId;
use App\Features\Matches\Domain\ValueObjects\PlayerId;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Domain\ValueObjects\Id;
use App\Shared\Application\Transaction\TransactionManagerInterface;
use App\Shared\Domain\Contracts\EventDispatcherInterface;

final readonly class InvitePlayerToMatchCommandHandler
{
    public function __construct(
        private MatchRepositoryInterface $matchRepository,
        private MatchInvitationRepositoryInterface $invitationRepository,
        private PlayerRepositoryInterface $playerRepository,
        private TransactionManagerInterface $transactionManager,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function __invoke(InvitePlayerToMatchCommand $command): MatchInvitation
    {
        $matchId = MatchId::fromString($command->matchId);
        $inviterId = PlayerId::fromString($command->inviterId);
        $inviteeId = PlayerId::fromString($command->inviteeId);
        $type = InvitationType::fromString($command->type);

        $match = $this->matchRepository->findById($matchId);
        if ($match === null) {
            throw MatchNotFoundException::create();
        }

        if (! $match->isCreator($inviterId)) {
            throw UnauthorizedMatchOperationException::create();
        }

        if ($match->status()->isValidated()) {
            throw MatchAlreadyValidatedException::create();
        }

        if ($match->status()->isCancelled()) {
            throw MatchAlreadyCancelledException::create();
        }

        if (! $this->playerRepository->findById(Id::fromString($command->inviteeId))) {
            throw PlayerNotRegisteredInAppException::forPlayer($command->inviteeId);
        }

        foreach ($match->participantIds() as $participant) {
            if ($participant->equals($inviteeId)) {
                throw DuplicatePlayerInMatchException::create();
            }
        }

        $existing = $this->invitationRepository->findByMatchAndInvitee($matchId, $inviteeId);
        if ($existing !== null && $existing->status()->isPending()) {
            throw DuplicatePlayerInMatchException::create();
        }

        if ($type->isPartner() && $match->isTeamFull($type->toTeam())) {
            throw MatchTeamFullException::create();
        }

        $invitation = MatchInvitation::invite(
            id: MatchInvitationId::generate(),
            matchId: $matchId,
            inviteeId: $inviteeId,
            invitedByPlayerId: $inviterId,
            type: $type,
        );

        $this->invitationRepository->save($invitation);

        $events = $invitation->pullDomainEvents();
        $this->transactionManager->afterCommit(fn () => $this->eventDispatcher->dispatchEvents($events));

        return $invitation;
    }
}
