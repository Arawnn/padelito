<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Events;

use App\Shared\Domain\Events\DomainEvent;

final class PlayerInvitedToMatch extends DomainEvent
{
    public function __construct(
        public readonly string $matchId,
        public readonly string $invitedPlayerId,
        public readonly string $invitedByPlayerId,
        public readonly string $invitationId,
        ?string $eventId = null,
        ?\DateTimeImmutable $occurredOn = null,
    ) {
        parent::__construct(aggregateId: $matchId, eventId: $eventId, occurredOn: $occurredOn);
    }

    public static function eventName(): string
    {
        return 'match.player_invited';
    }

    public function toPrimitives(): array
    {
        return [
            'matchId' => $this->matchId,
            'invitedPlayerId' => $this->invitedPlayerId,
            'invitedByPlayerId' => $this->invitedByPlayerId,
            'invitationId' => $this->invitationId,
        ];
    }
}
