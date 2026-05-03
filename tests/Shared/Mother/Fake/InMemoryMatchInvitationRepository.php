<?php

declare(strict_types=1);

namespace Tests\Shared\Mother\Fake;

use App\Features\Matches\Domain\Entities\MatchInvitation;
use App\Features\Matches\Domain\Repositories\MatchInvitationRepositoryInterface;
use App\Features\Matches\Domain\ValueObjects\MatchId;
use App\Features\Matches\Domain\ValueObjects\MatchInvitationId;
use App\Features\Matches\Domain\ValueObjects\PlayerId;

final class InMemoryMatchInvitationRepository implements MatchInvitationRepositoryInterface
{
    /** @var array<string, MatchInvitation> */
    private array $store = [];

    public function findById(MatchInvitationId $id): ?MatchInvitation
    {
        return $this->store[$id->value()] ?? null;
    }

    public function findByMatchAndInvitee(MatchId $matchId, PlayerId $inviteeId): ?MatchInvitation
    {
        foreach ($this->store as $invitation) {
            if ($invitation->matchId()->value() === $matchId->value()
                && $invitation->inviteeId()->value() === $inviteeId->value()
            ) {
                return $invitation;
            }
        }

        return null;
    }

    public function save(MatchInvitation $invitation): void
    {
        $this->store[$invitation->id()->value()] = $invitation;
    }

    /** @return list<MatchInvitation> */
    public function findPendingByInvitee(PlayerId $inviteeId): array
    {
        return array_values(array_filter($this->store, fn (MatchInvitation $i) => $i->inviteeId()->value() === $inviteeId->value() && $i->status()->isPending()));
    }

    /** @return list<MatchInvitation> */
    public function findCancellableByMatchId(MatchId $matchId): array
    {
        return array_values(array_filter($this->store, fn (MatchInvitation $i) => $i->matchId()->value() === $matchId->value() && ($i->status()->isPending() || $i->status()->isAccepted())));
    }
}
