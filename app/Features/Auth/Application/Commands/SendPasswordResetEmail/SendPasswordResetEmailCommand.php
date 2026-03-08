<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Commands\SendPasswordResetEmail;

final readonly class SendPasswordResetEmailCommand
{
    public function __construct(
        public string $email,
    ) {}
}
