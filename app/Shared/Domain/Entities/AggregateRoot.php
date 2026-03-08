<?php

declare(strict_types=1);

namespace App\Shared\Domain\Entities;

use App\Shared\Domain\Events\DomainEvent;
use App\Shared\Domain\Events\DomainEventCollection;

abstract class AggregateRoot
{
    private DomainEventCollection $domainEvents;

    public function __construct()
    {
        $this->domainEvents = new DomainEventCollection;
    }

    public function pullDomainEvents(): DomainEventCollection
    {
        $events = $this->domainEvents;
        $this->domainEvents = new DomainEventCollection;

        return $events;
    }

    protected function recordDomainEvent(DomainEvent $domainEvent): void
    {
        $this->domainEvents->add($domainEvent);
    }
}
