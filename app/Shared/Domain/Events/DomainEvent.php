<?php

declare(strict_types=1);

namespace App\Shared\Domain\Events;


abstract class DomainEvent {
    public string $eventId;
    public string $aggregateId;
    public \DateTimeImmutable $occurredOn;
    public string $eventName;

    public function __construct()
    {
        $this->eventId = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $this->occurredOn = new \DateTimeImmutable();
    }


}
    