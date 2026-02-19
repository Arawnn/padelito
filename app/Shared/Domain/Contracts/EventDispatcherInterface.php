<?php

declare(strict_types=1);

namespace App\Shared\Domain\Contracts;

use App\Shared\Domain\Events\DomainEvent;
use App\Shared\Domain\Events\DomainEventCollection;

interface EventDispatcherInterface {
    public function dispatch(DomainEvent $event): void;
    public function dispatchEvents(DomainEventCollection $events): void;
}