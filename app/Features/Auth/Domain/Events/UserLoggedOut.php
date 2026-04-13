<?php

declare(strict_types=1);

namespace App\Features\Auth\Domain\Events;

use App\Shared\Domain\Events\DomainEvent;

final class UserLoggedOut extends DomainEvent
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
        return 'auth.user.logged_out';
    }

    public function toPrimitives(): array
    {
        return [
            'userId' => $this->userId,
        ];
    }
}
