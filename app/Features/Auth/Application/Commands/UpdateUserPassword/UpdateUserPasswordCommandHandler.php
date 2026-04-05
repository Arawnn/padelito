<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Commands\UpdateUserPassword;

use App\Features\Auth\Domain\Contracts\PasswordHasherInterface;
use App\Features\Auth\Domain\Exceptions\UserNotFoundException;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Domain\ValueObjects\Id;
use App\Features\Auth\Domain\ValueObjects\Password;
use App\Shared\Application\Result;
use App\Shared\Domain\Contracts\EventDispatcherInterface;
use App\Shared\Domain\Exceptions\DomainExceptionInterface;

final readonly class UpdateUserPasswordCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    /**
     * @throws DomainExceptionInterface
     * @return Result<void>
     */
    public function __invoke(UpdateUserPasswordCommand $command): Result
    {
        try {
            $user = $this->userRepository->findById(Id::fromString($command->userId));
            if (! $user) {
                return Result::fail(UserNotFoundException::fromId(Id::fromString($command->userId)));
            }

            $hashedPassword = $this->passwordHasher->hash(Password::fromPlainText($command->password));

            $user->updatePassword($hashedPassword);
            $this->userRepository->update($user);
            $this->eventDispatcher->dispatchEvents($user->pullDomainEvents());

            return Result::void();
        } catch (DomainExceptionInterface $e) {
            return Result::fail($e);
        }
    }
}
