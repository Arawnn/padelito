<?php

declare(strict_types=1);

namespace App\Features\Auth\Domain\Events;

use App\Shared\Domain\Events\DomainEvent;

final class UserCreated extends DomainEvent
{
    // maybe create factory to separate domain event when occured and
    // replay from storage
    public function __construct(
        public readonly string $userId,
        public readonly string $name,
        public readonly string $email,
        ?string $eventId = null,
        ?\DateTimeImmutable $occurredOn = null,
    ) {
        parent::__construct(
            aggregateId: $userId,
            eventId: $eventId,
            occurredOn: $occurredOn,
        );
    }

    public static function eventName(): string
    {
        return 'auth.user.created';
    }

    public function toPrimitives(): array
    {
        return [
            'userId' => $this->userId,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }

    // public static function record(): self -> nouvel event sur le point d'etre persisté
    // public static function fromPrimitives -> event deja persisté
}
