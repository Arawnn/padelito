<?php

declare(strict_types=1);

namespace Tests\Shared\Mother\Fake;

use App\Shared\Domain\Contracts\EventDispatcherInterface;
use App\Shared\Domain\Events\DomainEvent;
use App\Shared\Domain\Events\DomainEventCollection;

final class SpyEventDispatcher implements EventDispatcherInterface
{
    private array $dispatched = [];

    public function dispatch(DomainEvent $event): void
    {
        $this->dispatched[] = $event;
    }

    public function dispatchEvents(DomainEventCollection $events): void
    {
        foreach ($events as $event) {
            $this->dispatched[] = $event;
        }
    }

    public function dispatched(string $eventClass): bool
    {
        dump($this->dispatched);
        foreach ($this->dispatched as $event) {
            dump($event);
            dump($eventClass);
            if ($event instanceof $eventClass) {
                return true;
            }
        }

        return false;
    }
}
