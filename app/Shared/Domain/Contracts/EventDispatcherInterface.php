<?php

declare(strict_types=1);

namespace App\Shared\Domain\Contracts;

use App\Shared\Domain\Events\DomainEvent;

interface EventDispatcherInterface {
    public function dispatch(DomainEvent $event): void;
}