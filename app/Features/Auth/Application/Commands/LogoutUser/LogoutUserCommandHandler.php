<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Commands\LogoutUser;

use App\Features\Auth\Domain\Exceptions\UserNotFoundException;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Domain\ValueObjects\Id;
use App\Shared\Domain\Contracts\EventDispatcherInterface;

final readonly class LogoutUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    public function __invoke(LogoutUserCommand $command): void
    {
        $id = Id::fromString($command->userId);

        $user = $this->userRepository->findById($id);
        if (! $user) {
            throw UserNotFoundException::fromId($id);
        }

        $user->logout();
        $this->eventDispatcher->dispatchEvents($user->pullDomainEvents());
    }
}
