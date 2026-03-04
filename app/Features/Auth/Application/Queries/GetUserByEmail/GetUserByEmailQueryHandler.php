<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Queries\GetUserByEmail;

use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Domain\Exceptions\UserNotFoundException;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Domain\ValueObjects\Email;

final readonly class GetUserByEmailQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(GetUserByEmailQuery $query): User
    {
        $user = $this->userRepository->findByEmail(Email::fromString($query->email));
        if (!$user) {
            throw UserNotFoundException::fromEmail(Email::fromString($query->email));
        }

        return $user;
    }
}
