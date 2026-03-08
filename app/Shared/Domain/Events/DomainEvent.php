<?php

declare(strict_types=1);

namespace App\Shared\Domain\Events;

use Ramsey\Uuid\Uuid;

abstract class DomainEvent
{
    public string $eventId;
    public string $aggregateId;
    public \DateTimeImmutable $occurredOn;
    public string $eventName;

    public function __construct()
    {
        $this->eventId = Uuid::uuid4()->toString();
        $this->occurredOn = new \DateTimeImmutable();
    }
}
