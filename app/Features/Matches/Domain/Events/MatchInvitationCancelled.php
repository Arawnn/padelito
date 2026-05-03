<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Events;

use App\Shared\Domain\Events\DomainEvent;

final class MatchInvitationCancelled extends DomainEvent
{
    public function __construct(
        public readonly string $matchId,
        public readonly string $playerId,
        public readonly string $invitationId,
        ?string $eventId = null,
        ?\DateTimeImmutable $occurredOn = null,
    ) {
        parent::__construct(aggregateId: $matchId, eventId: $eventId, occurredOn: $occurredOn);
    }

    public static function eventName(): string
    {
        return 'match.invitation_cancelled';
    }

    public function toPrimitives(): array
    {
        return [
            'matchId' => $this->matchId,
            'playerId' => $this->playerId,
            'invitationId' => $this->invitationId,
        ];
    }
}
