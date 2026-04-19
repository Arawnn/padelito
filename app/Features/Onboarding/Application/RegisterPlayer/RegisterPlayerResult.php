<?php

declare(strict_types=1);

namespace App\Features\Onboarding\Application\RegisterPlayer;

final readonly class RegisterPlayerResult
{
    public function __construct(
        public string $userId,
        public string $name,
        public string $email,
    ) {}
}
