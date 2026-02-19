<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Commands\ResetUserPassword;

final readonly class ResetUserPasswordCommand {
    public function __construct(
        public string $userId,
        public string $password,
    ) {}
}