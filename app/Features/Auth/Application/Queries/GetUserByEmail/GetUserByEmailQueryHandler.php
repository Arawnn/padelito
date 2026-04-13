<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Queries\GetUserByEmail;

use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Domain\Exceptions\UserNotFoundException;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Shared\Application\Result;
use App\Shared\Domain\Exceptions\DomainExceptionInterface;

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
        try {
            $email = Email::fromString($query->email);

            $user = $this->userRepository->findByEmail($email);
            if (! $user) {
                return Result::fail(UserNotFoundException::fromEmail($email));
            }

            return Result::ok($user);
        } catch (DomainExceptionInterface $e) {
            return Result::fail($e);
        }
    }
}
