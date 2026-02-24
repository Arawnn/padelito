<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Commands\LoginUser;

final readonly class LoginUserCommand {
    public function __construct(
        public string $email,
        public string $password,
    ) {}
}