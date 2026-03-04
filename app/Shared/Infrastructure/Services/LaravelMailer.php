<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Services;

use App\Features\Auth\Infrastructure\Mail\PasswordResetMail;
use App\Shared\Domain\Contracts\MailerInterface;
use Illuminate\Support\Facades\Mail;

final class LaravelMailer implements MailerInterface
{
    public function to(string $email, string $username, string $token): void
    {
        Mail::to($email)->send(
            new PasswordResetMail($username, $token, $email)
        );
    }
}
