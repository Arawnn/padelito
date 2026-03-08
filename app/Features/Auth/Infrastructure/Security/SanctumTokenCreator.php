<?php

declare(strict_types=1);

namespace App\Features\Auth\Infrastructure\Security;

use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Infrastructure\Contracts\TokenCreatorInterface;
use App\Features\Auth\Infrastructure\Mappers\UserMapper;

final readonly class SanctumTokenCreator implements TokenCreatorInterface
{
    public function __construct(private UserMapper $userMapper) {}

    public function createFor(User $user): string
    {
        return $this->userMapper
            ->toModel($user)
            ->createToken('auth_token')
            ->plainTextToken;
    }
}
