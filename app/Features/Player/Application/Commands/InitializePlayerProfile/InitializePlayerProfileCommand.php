<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Commands\InitializePlayerProfile;

final readonly class InitializePlayerProfileCommand
{
    public function __construct(
        public string $userId,
        public string $displayName,
    ) {}
}
