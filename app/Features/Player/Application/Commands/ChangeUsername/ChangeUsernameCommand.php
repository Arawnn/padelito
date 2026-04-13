<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Commands\ChangeUsername;

final readonly class ChangeUsernameCommand
{
    public function __construct(
        public string $userId,
        public string $newUsername,
    ) {}
}
