<?php

declare(strict_types=1);

namespace App\Features\Auth\Domain\Events;

use App\Shared\Domain\Events\DomainEvent;

final class UserPasswordUpdated extends DomainEvent
{
    public function __construct(
        public readonly string $userId,
        ?string $eventId = null,
        ?\DateTimeImmutable $occurredOn = null,
    ) {
        parent::__construct(
            aggregateId: $userId,
            eventId: $eventId,
            occurredOn: $occurredOn
        );
    }

    public static function eventName(): string
    {
        return 'auth.user.password_updated';
    }

    public function toPrimitives(): array
    {
        return [
            'userId' => $this->userId,
        ];
    }
}
