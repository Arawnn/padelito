<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Commands\RegisterUser;

use App\Features\Auth\Domain\Contracts\PasswordHasherInterface;
use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Domain\Exceptions\UserAlreadyExistsException;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Domain\ValueObjects\Id;
use App\Features\Auth\Domain\ValueObjects\Name;
use App\Features\Auth\Domain\ValueObjects\Password;
use App\Shared\Domain\Contracts\EventDispatcherInterface;
use App\Shared\Domain\Contracts\UuidGeneratorInterface;

final readonly class RegisterUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
        private UuidGeneratorInterface $uuidGenerator,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    /**
     * La transaction est ouverte par le command bus appelant.
     */
    public function __invoke(RegisterUserCommand $command): User
    {
        $email = Email::fromString($command->email);

        if ($this->userRepository->findByEmail($email) !== null) {
            throw UserAlreadyExistsException::fromEmail($email);
        }

        $user = User::register(
            id: Id::fromString($this->uuidGenerator->generate()),
            name: Name::fromString($command->name),
            email: $email,
            password: $this->passwordHasher->hash(
                Password::fromPlainText($command->password)
            ),
        );

        $this->userRepository->save($user);

        $domainEvents = $user->pullDomainEvents();
        $this->eventDispatcher->dispatchEvents($domainEvents);

        return $user;
    }
}
