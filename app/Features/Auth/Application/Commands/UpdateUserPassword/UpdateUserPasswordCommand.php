<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Commands\UpdateUserPassword;

final readonly class UpdateUserPasswordCommand {
    public function __construct(
        public string $userId,
        public string $password,
    ) {}
}