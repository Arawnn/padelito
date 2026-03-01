<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Commands\SendPasswordResetEmail;

use App\Features\Auth\Domain\Repositories\PasswordResetTokenRepositoryInterface;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Shared\Domain\Contracts\MailerInterface;
use App\Shared\Domain\ValueObjects\Result;

final readonly class SendPasswordResetEmailCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordResetTokenRepositoryInterface $tokenRepository,
        private MailerInterface $mailer
    ) {}

    public function __invoke(SendPasswordResetEmailCommand $command): Result
    {
        $email = Email::fromString($command->email);
        $user  = $this->userRepository->findByEmail($email);

        if (!$user) {
            return Result::ok(null);
        }

        $token = $this->tokenRepository->create($email);

       $this->mailer->to($user->email()->value(), $user->name()->value(), $token);

        return Result::ok(null);
    }
}
