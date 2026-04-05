<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Commands\SendPasswordResetEmail;

use App\Features\Auth\Domain\Repositories\PasswordResetTokenRepositoryInterface;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Shared\Application\Result;
use App\Shared\Domain\Contracts\MailerInterface;
use App\Shared\Domain\Exceptions\DomainExceptionInterface;

final readonly class SendPasswordResetEmailCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordResetTokenRepositoryInterface $tokenRepository,
        private MailerInterface $mailer
    ) {}

    /**
     * @throws DomainExceptionInterface
     * @return Result<void>
     */
    public function __invoke(SendPasswordResetEmailCommand $command): Result
    {
        try {
            $email = Email::fromString($command->email);

            $user = $this->userRepository->findByEmail($email);
            if (! $user) {
                return Result::void();
            }

            $token = $this->tokenRepository->create($email);

            // I choose to send the mail in this handler to keep thing simpler for now but later on
            // A domain event should be published and a subscriber should handle the emailing in reaction of this event
            $this->mailer->to($user->email()->value(), $user->name()->value(), $token);

            return Result::void();
        } catch (DomainExceptionInterface $e) {
            return Result::fail($e);
        }
    }
}
