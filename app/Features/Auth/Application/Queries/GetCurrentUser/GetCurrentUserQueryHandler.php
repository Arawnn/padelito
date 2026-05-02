<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Queries\GetCurrentUser;

use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Domain\Exceptions\UserNotFoundException;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Domain\ValueObjects\Id;

final readonly class GetCurrentUserQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(GetCurrentUserQuery $query): User
    {
        $id = Id::fromString($query->userId);

        $user = $this->userRepository->findById($id);
        if (! $user) {
            throw UserNotFoundException::fromId($id);
        }

        return $user;
    }
}
