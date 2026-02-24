<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Commands\LoginUser;

use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Domain\ValueObjects\Password;
use App\Shared\Domain\Contracts\EventDispatcherInterface;
use App\Features\Auth\Domain\Exceptions\UserNotFoundException;
use App\Features\Auth\Domain\Contracts\PasswordHasherInterface;
use App\Features\Auth\Domain\Exceptions\InvalidPasswordException;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;

final readonly class LoginUserCommandHandler {
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    public function __invoke(LoginUserCommand $command): void
    {
        $user = $this->userRepository->findByEmail(Email::fromString($command->email));
        if (!$user) {
            throw UserNotFoundException::fromEmail(Email::fromString($command->email));
        }

        if (!$this->passwordHasher->verify(Password::forVerification($command->password), $user->password())) {
            throw InvalidPasswordException::fromViolations(['Invalid password']);
        }

        $user->login();
        $this->eventDispatcher->dispatchEvents($user->pullDomainEvents());
    }
}