<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Commands\ConfirmPasswordReset;

use App\Features\Auth\Application\Commands\UpdateUserPassword\UpdateUserPasswordCommand;
use App\Features\Auth\Domain\Exceptions\InvalidEmailException;
use App\Features\Auth\Domain\Exceptions\InvalidResetTokenException;
use App\Features\Auth\Domain\Exceptions\UserNotFoundException;
use App\Features\Auth\Domain\Repositories\PasswordResetTokenRepositoryInterface;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Domain\ValueObjects\Result;

final readonly class ConfirmPasswordResetCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordResetTokenRepositoryInterface $tokenRepository,
        private CommandBusInterface $commandBus,
    ) {}

    /**
     * @return Result<null>
     */
    public function __invoke(ConfirmPasswordResetCommand $command): Result
    {
        try {
            $email = Email::fromString($command->email);
        } catch (InvalidEmailException $e) {
            return Result::fail(InvalidEmailException::fromViolations($e->violations()));
        }

        $user = $this->userRepository->findByEmail($email);
        if (! $user) {
            return Result::fail(UserNotFoundException::fromEmail($email));
        }

        if (! $this->tokenRepository->isValid($email, $command->token)) {
            return Result::fail(InvalidResetTokenException::expiredOrInvalid());
        }

        // Better to generate a domain event a subscriber should handle to dispatch this command ?
        $result = $this->commandBus->dispatch(new UpdateUserPasswordCommand(
            userId: $user->id()->value(),
            password: $command->password,
        ));

        if (! $result->isOk()) {
            return $result;
        }

        $this->tokenRepository->delete($email);

        return Result::ok(null);
    }
}
