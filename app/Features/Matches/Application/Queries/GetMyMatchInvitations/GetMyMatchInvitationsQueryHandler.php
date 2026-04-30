<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\Queries\GetMyMatchInvitations;

use App\Features\Matches\Domain\Repositories\MatchInvitationRepositoryInterface;
use App\Features\Matches\Domain\ValueObjects\PlayerId;

final readonly class GetMyMatchInvitationsQueryHandler
{
    public function __construct(
        private MatchInvitationRepositoryInterface $invitationRepository,
    ) {}

    /** @return list<\App\Features\Matches\Domain\Entities\MatchInvitation> */
    public function __invoke(GetMyMatchInvitationsQuery $query): array
    {
        return $this->invitationRepository->findPendingByInvitee(
            PlayerId::fromString($query->playerId),
        );
    }
}
