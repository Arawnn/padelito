<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Commands\RegisterUser;

use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Domain\ValueObjects\Id;
use App\Features\Auth\Domain\ValueObjects\Name;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Domain\ValueObjects\Password;
use App\Shared\Domain\Contracts\UuidGeneratorInterface;
use App\Shared\Domain\Contracts\EventDispatcherInterface;
use App\Features\Auth\Domain\Exceptions\UserAlreadyExistException;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Application\Commands\RegisterUser\RegisterUserCommand;

final readonly class RegisterUserCommandHandler {
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UuidGeneratorInterface $uuidGenerator,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    public function __invoke(RegisterUserCommand $command): User
    {
        $id = Id::fromString($this->uuidGenerator->generate());

        if ($this->userRepository->findById($id)) {
            throw UserAlreadyExistException::fromId($id);
        }

        if ($this->userRepository->findByEmail(Email::fromString($command->email))) {
            throw UserAlreadyExistException::fromEmail(Email::fromString($command->email));
        }

        $user = User::register(
            id: $id,
            name: Name::fromString($command->name),
            email: Email::fromString($command->email),
            password: Password::fromPlainText($command->password),
        );

        $this->userRepository->create($user);
        $this->eventDispatcher->dispatchEvents($user->pullDomainEvents());

        return $user;
    }
}