<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Commands\ConfirmPasswordReset;

use App\Features\Auth\Application\Commands\UpdateUserPassword\UpdateUserPasswordCommand;
use App\Features\Auth\Domain\Exceptions\InvalidResetTokenException;
use App\Features\Auth\Domain\Exceptions\UserNotFoundException;
use App\Features\Auth\Domain\Repositories\PasswordResetTokenRepositoryInterface;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Shared\Application\Bus\CommandBusInterface;

final readonly class ConfirmPasswordResetCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordResetTokenRepositoryInterface $tokenRepository,
        private CommandBusInterface $commandBus,
    ) {}

    public function __invoke(ConfirmPasswordResetCommand $command): void
    {
        $email = Email::fromString($command->email);

        $user = $this->userRepository->findByEmail($email);
        if (! $user) {
            throw UserNotFoundException::fromEmail($email);
        }

        if (! $this->tokenRepository->isValid($email, $command->token)) {
            throw InvalidResetTokenException::expiredOrInvalid();
        }

        $this->commandBus->dispatch(new UpdateUserPasswordCommand(
            userId: $user->id()->value(),
            password: $command->password,
        ));

        $this->tokenRepository->delete($email);
    }
}
