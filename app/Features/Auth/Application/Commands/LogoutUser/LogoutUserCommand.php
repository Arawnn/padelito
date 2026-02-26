<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Commands\LogoutUser;

final readonly class LogoutUserCommand {
    public function __construct(
        public string $userId,
    ) {}
}