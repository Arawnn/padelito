<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Commands\RegisterUser;

class RegisterUserCommand
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}
}
