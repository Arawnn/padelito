<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Commands\LoginUser;

use App\Features\Auth\Domain\Contracts\PasswordHasherInterface;
use App\Features\Auth\Domain\Exceptions\InvalidPasswordException;
use App\Features\Auth\Domain\Exceptions\UserNotFoundException;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Domain\ValueObjects\Password;
use App\Shared\Domain\Contracts\EventDispatcherInterface;
use App\Shared\Domain\ValueObjects\Result;

final readonly class LoginUserCommandHandler {
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    /**
     * @return Result<User>
     */
    public function __invoke(LoginUserCommand $command): Result
    {
        $user = $this->userRepository->findByEmail(Email::fromString($command->email));
        if (!$user) {
            return Result::fail(UserNotFoundException::fromEmail(Email::fromString($command->email)));
        }

        if (!$this->passwordHasher->verify(Password::forVerification($command->password), $user->password())) {
            return Result::fail(InvalidPasswordException::fromViolations(['Invalid password']));
        }

        $user->login();
        $this->eventDispatcher->dispatchEvents($user->pullDomainEvents());

        return Result::ok($user);
    }
}