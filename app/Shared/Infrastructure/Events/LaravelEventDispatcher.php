<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Events;

use App\Shared\Domain\Events\DomainEvent;
use Illuminate\Contracts\Events\Dispatcher;
use App\Shared\Domain\Contracts\EventDispatcherInterface;

final readonly class LaravelEventDispatcher implements EventDispatcherInterface {
    public function __construct(
        private Dispatcher $laravelDispatcher
    ) {}

    public function dispatch(DomainEvent $event): void
    {
        $this->laravelDispatcher->dispatch($event);
    }
}