<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Repositories;

use App\Features\Matches\Domain\Entities\MatchInvitation;
use App\Features\Matches\Domain\ValueObjects\MatchId;
use App\Features\Matches\Domain\ValueObjects\MatchInvitationId;
use App\Features\Matches\Domain\ValueObjects\PlayerId;

interface MatchInvitationRepositoryInterface
{
    public function findById(MatchInvitationId $id): ?MatchInvitation;

    public function findByMatchAndSlot(MatchId $matchId, string $team, int $position): ?MatchInvitation;

    public function findByMatchAndInvitee(MatchId $matchId, PlayerId $inviteeId): ?MatchInvitation;

    public function save(MatchInvitation $invitation): void;

    /** @return list<MatchInvitation> */
    public function findPendingByInvitee(PlayerId $inviteeId): array;
}
