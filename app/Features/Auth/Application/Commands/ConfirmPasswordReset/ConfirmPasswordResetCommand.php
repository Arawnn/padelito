<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Commands\ConfirmPasswordReset;

final readonly class ConfirmPasswordResetCommand
{
    public function __construct(
        public string $email,
        public string $token,
        public string $password,
    ) {}
}
