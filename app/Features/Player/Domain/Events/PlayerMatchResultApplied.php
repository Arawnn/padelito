<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\Events;

use App\Shared\Domain\Events\DomainEvent;

final class PlayerMatchResultApplied extends DomainEvent
{
    public function __construct(
        public readonly string $playerId,
        public readonly string $matchId,
        public readonly bool $won,
        public readonly int $eloChange,
        ?string $eventId = null,
        ?\DateTimeImmutable $occurredOn = null,
    ) {
        parent::__construct(aggregateId: $playerId, eventId: $eventId, occurredOn: $occurredOn);
    }

    public static function eventName(): string
    {
        return 'player.match_result_applied';
    }

    public function toPrimitives(): array
    {
        return [
            'playerId' => $this->playerId,
            'matchId' => $this->matchId,
            'won' => $this->won,
            'eloChange' => $this->eloChange,
        ];
    }
}
