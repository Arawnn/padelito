<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\Events;

use App\Shared\Domain\Events\DomainEvent;

final class PlayerIdentityUpdated extends DomainEvent
{
    public function __construct(
        public readonly string $playerId,
        ?string $eventId = null,
        ?\DateTimeImmutable $occurredOn = null,
    ) {
        parent::__construct(
            aggregateId: $playerId,
            eventId: $eventId,
            occurredOn: $occurredOn,
        );
    }

    public static function eventName(): string
    {
        return 'player.identity.updated';
    }

    public function toPrimitives(): array
    {
        return [
            'playerId' => $this->playerId,
        ];
    }
}
