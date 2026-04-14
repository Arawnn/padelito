<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Commands\UpdateUserPassword;

use App\Features\Auth\Domain\Contracts\PasswordHasherInterface;
use App\Features\Auth\Domain\Exceptions\UserNotFoundException;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Domain\ValueObjects\Id;
use App\Features\Auth\Domain\ValueObjects\Password;
use App\Shared\Domain\Contracts\EventDispatcherInterface;

final readonly class UpdateUserPasswordCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function __invoke(UpdateUserPasswordCommand $command): void
    {
        $id = Id::fromString($command->userId);

        $user = $this->userRepository->findById($id);
        if (! $user) {
            throw UserNotFoundException::fromId($id);
        }

        $hashedPassword = $this->passwordHasher->hash(Password::fromPlainText($command->password));

        $user->updatePassword($hashedPassword);
        $this->userRepository->save($user);
        $this->eventDispatcher->dispatchEvents($user->pullDomainEvents());
    }
}
