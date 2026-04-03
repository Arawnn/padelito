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

final readonly class UpdateUserPasswordCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    /**
     * @return Result<null>
     */
    public function __invoke(UpdateUserPasswordCommand $command): Result
    {
        $user = $this->userRepository->findById(Id::fromString($command->userId));
        if (! $user) {
            return Result::fail(UserNotFoundException::fromId(Id::fromString($command->userId)));
        }

        return Result::try(fn () => $this->passwordHasher->hash(Password::fromPlainText($command->password)))
            ->map(function ($hashedPassword) use ($user) {
                $user->updatePassword($hashedPassword);
                $this->userRepository->update($user);
                $this->eventDispatcher->dispatchEvents($user->pullDomainEvents());
            })
            ->flatMap(fn () => Result::void());
    }
}
