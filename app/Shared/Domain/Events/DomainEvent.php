<?php

declare(strict_types=1);

namespace App\Shared\Domain\Events;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

abstract class DomainEvent
{
    public readonly string $eventId;

    public readonly DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly string $aggregateId,
        ?string $eventId = null,
        ?DateTimeImmutable $occurredOn = null,
    ) {
        $this->eventId = $eventId ?? Uuid::uuid4()->toString();
        $this->occurredOn = $occurredOn ?? new DateTimeImmutable;
    }

    abstract public static function eventName(): string;

    abstract public function toPrimitives(): array;
}
