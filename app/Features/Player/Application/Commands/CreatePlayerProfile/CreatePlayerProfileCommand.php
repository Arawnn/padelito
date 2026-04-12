<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Commands\CreatePlayerProfile;

use App\Features\Player\Application\Commands\CreatePlayerProfile\Dto\AvatarInput;

final readonly class CreatePlayerProfileCommand
{
    public function __construct(
        public string $userId,
        public string $username,
        public string $level,
        public ?string $displayName,
        public ?AvatarInput $avatar,
        public ?string $bio,
        public ?string $location,
        public ?string $dominantHand,
        public ?string $preferredPosition,
    ) {}
}
