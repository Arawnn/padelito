<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Commands\LogoutUser;

use App\Features\Auth\Domain\Exceptions\UserNotFoundException;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Domain\ValueObjects\Id;
use App\Shared\Application\Result;
use App\Shared\Domain\Contracts\EventDispatcherInterface;
use App\Shared\Domain\Exceptions\DomainExceptionInterface;

final readonly class LogoutUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    /**
     * @return Result<void>
     *
     * @throws DomainExceptionInterface
     */
    public function __invoke(LogoutUserCommand $command): Result
    {
        try {
            $user = $this->userRepository->findById(Id::fromString($command->userId));
            if (! $user) {
                return Result::fail(UserNotFoundException::fromId(Id::fromString($command->userId)));
            }

            $user->logout();
            $this->eventDispatcher->dispatchEvents($user->pullDomainEvents());

            return Result::void();
        } catch (DomainExceptionInterface $e) {
            return Result::fail($e);
        }
    }
}
