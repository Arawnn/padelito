<?php

namespace App\Features\Auth\Infrastructure\Contracts;

use App\Features\Auth\Domain\Entities\User;

interface TokenCreatorInterface
{
    public function createFor(User $user): string;
}
