<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\Events;

use App\Shared\Domain\Events\DomainEvent;

final class PlayerVisibilityChanged extends DomainEvent
{
    public function __construct(
        public readonly string $playerId,
        public readonly bool $isPublic,
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
        return 'player.visibility.changed';
    }

    public function toPrimitives(): array
    {
        return [
            'playerId' => $this->playerId,
            'isPublic' => $this->isPublic,
        ];
    }
}
