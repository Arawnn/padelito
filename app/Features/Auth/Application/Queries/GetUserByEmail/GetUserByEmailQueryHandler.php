<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Queries\GetUserByEmail;

use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Domain\Exceptions\UserNotFoundException;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Shared\Application\Result;

final readonly class GetUserByEmailQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    /**
     * @return Result<User>
     */
    public function __invoke(GetUserByEmailQuery $query): Result
    {
        return Result::try(fn () => Email::fromString($query->email))
            ->flatMap(fn (Email $email) => $this->findUser($email));
    }

    /**
     * @return Result<User>
     */
    private function findUser(Email $email): Result
    {
        $user = $this->userRepository->findByEmail($email);
        if (! $user) {
            return Result::fail(UserNotFoundException::fromEmail($email));
        }

        return Result::ok($user);
    }
}
