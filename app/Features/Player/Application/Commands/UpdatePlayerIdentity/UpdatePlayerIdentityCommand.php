<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Commands\UpdatePlayerIdentity;

use App\Shared\Application\Optional;

final readonly class UpdatePlayerIdentityCommand
{
    public function __construct(
        public string $userId,
        public Optional $displayName,
        public Optional $bio,
    ) {}
}
