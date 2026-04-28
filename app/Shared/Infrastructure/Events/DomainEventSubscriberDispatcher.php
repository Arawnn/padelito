<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Events;

use App\Shared\Domain\Contracts\EventDispatcherInterface;
use App\Shared\Domain\Events\DomainEvent;
use App\Shared\Domain\Events\DomainEventCollection;
use App\Shared\Domain\Events\DomainEventSubscriberInterface;

final readonly class DomainEventSubscriberDispatcher implements EventDispatcherInterface
{
    /** @param iterable<DomainEventSubscriberInterface> $subscribers */
    public function __construct(
        private iterable $subscribers,
    ) {}

    public function dispatch(DomainEvent $event): void
    {
        foreach ($this->subscribers as $subscriber) {
            foreach ($subscriber::subscribedTo() as $eventClass) {
                if ($event instanceof $eventClass) {
                    $subscriber($event);
                }
            }
        }
    }

    public function dispatchEvents(DomainEventCollection $events): void
    {
        foreach ($events as $event) {
            $this->dispatch($event);
        }
    }
}
