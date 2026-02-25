<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Commands\UpdateUserPassword;

use App\Features\Auth\Application\Commands\UpdateUserPassword\UpdateUserPasswordCommand;
use App\Features\Auth\Domain\Contracts\PasswordHasherInterface;
use App\Features\Auth\Domain\Exceptions\InvalidPasswordException;
use App\Features\Auth\Domain\Exceptions\UserNotFoundException;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Domain\ValueObjects\Id;
use App\Features\Auth\Domain\ValueObjects\Password;
use App\Shared\Domain\Contracts\EventDispatcherInterface;
use App\Shared\Domain\ValueObjects\Result;

final readonly class UpdateUserPasswordCommandHandler {
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function __invoke(UpdateUserPasswordCommand $command): Result    
    {
        $user = $this->userRepository->findById(Id::fromString($command->userId));
        if (!$user) {
            return Result::fail(UserNotFoundException::fromId(Id::fromString($command->userId)));
        }
        try {
            $hashedPassword = $this->passwordHasher->hash(
                Password::fromPlainText($command->password)
            );
        } catch (InvalidPasswordException $e) {
            return Result::fail(InvalidPasswordException::fromViolations($e->violations()));
        }
        $user->updatePassword($hashedPassword);
        $this->userRepository->update($user);
        $this->eventDispatcher->dispatchEvents($user->pullDomainEvents());

        return Result::ok(null);
    }
}