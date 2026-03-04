<?php

declare(strict_types=1);

namespace App\Features\Auth\Domain\Events;

use App\Features\Auth\Domain\Entities\User;
use App\Shared\Domain\Events\DomainEvent;

final class UserLoggedIn extends DomainEvent
{
    public function __construct(public User $user)
    {
        parent::__construct();
        $this->eventName = 'UserLoggedIn';
        $this->aggregateId = $user->id()->value();
    }
}
