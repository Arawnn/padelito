<?php

declare(strict_types=1);

namespace App\Shared\Domain\Events;

interface DomainEventSubscriberInterface
{
    /** @return list<class-string<DomainEvent>> */
    public static function subscribedTo(): array;
}
