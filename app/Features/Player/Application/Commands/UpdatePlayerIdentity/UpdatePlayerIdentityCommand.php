<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Commands\UpdatePlayerIdentity;

use App\Features\Player\Application\Commands\CreatePlayerProfile\Dto\AvatarInput;
use App\Shared\Application\Optional;

final readonly class UpdatePlayerIdentityCommand
{
    public function __construct(
        public string $userId,
        public Optional $displayName,
        public Optional $bio,
        public ?AvatarInput $avatar,
    ) {}
}
