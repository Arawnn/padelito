<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Commands\ResetUserPassword;

use App\Features\Auth\Domain\ValueObjects\Id;
use App\Features\Auth\Domain\ValueObjects\Password;
use App\Features\Auth\Domain\ValueObjects\HashedPassword;
use App\Features\Auth\Domain\Exceptions\UserNotFoundException;
use App\Features\Auth\Domain\Contracts\PasswordHasherInterface;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;

final readonly class ResetUserPasswordCommandHandler {
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
    ) {}

    public function __invoke(ResetUserPasswordCommand $command): void
    {
        $user = $this->userRepository->findById(Id::fromString($command->userId));
        if (!$user) {
            throw UserNotFoundException::fromId(Id::fromString($command->userId));
            return;
        }
        $hashedPassword = $this->passwordHasher->hash(
            Password::fromPlainText($command->password)
        );
        $user->resetPassword($hashedPassword);
        $this->userRepository->update($user);
    }
}