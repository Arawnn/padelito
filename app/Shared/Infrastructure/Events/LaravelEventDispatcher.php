<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Events;

use App\Shared\Domain\Contracts\EventDispatcherInterface;
use App\Shared\Domain\Events\DomainEvent;
use App\Shared\Domain\Events\DomainEventCollection;
use Illuminate\Contracts\Events\Dispatcher;

final readonly class LaravelEventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private Dispatcher $laravelDispatcher
    ) {}

    public function dispatch(DomainEvent $event): void
    {
        $this->laravelDispatcher->dispatch($event);
    }

    public function dispatchEvents(DomainEventCollection $events): void
    {
        foreach ($events as $event) {
            $this->dispatch($event);
        }
    }
}
