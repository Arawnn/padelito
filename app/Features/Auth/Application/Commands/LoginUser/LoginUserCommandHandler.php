<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Commands\LoginUser;

use App\Features\Auth\Domain\Contracts\PasswordHasherInterface;
use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Domain\Exceptions\InvalidPasswordException;
use App\Features\Auth\Domain\Exceptions\UserNotFoundException;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Domain\ValueObjects\Password;
use App\Shared\Application\Result;
use App\Shared\Domain\Contracts\EventDispatcherInterface;

final readonly class LoginUserCommandHandler
{
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
        return Result::try(fn () => Email::fromString($command->email))
            ->flatMap(fn (Email $email) => $this->authenticate($email, $command->password));
    }

    /**
     * @return Result<User>
     */
    private function authenticate(Email $email, string $plainPassword): Result
    {
        $user = $this->userRepository->findByEmail($email);
        if (! $user) {
            return Result::fail(UserNotFoundException::fromEmail($email));
        }

        if (! $this->passwordHasher->verify(Password::forVerification($plainPassword), $user->password())) {
            return Result::fail(InvalidPasswordException::fromViolations(['Invalid password']));
        }

        $user->login();
        $this->eventDispatcher->dispatchEvents($user->pullDomainEvents());

        return Result::ok($user);
    }
}
