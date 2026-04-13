<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Commands\UpdatePlayerIdentity;

use App\Features\Player\Application\Commands\CreatePlayerProfile\Dto\AvatarInput;

final readonly class UpdatePlayerIdentityCommand
{
    public function __construct(
        public string $userId,
        public ?string $displayName,
        public ?string $bio,
        public ?AvatarInput $avatar,
    ) {}
}
