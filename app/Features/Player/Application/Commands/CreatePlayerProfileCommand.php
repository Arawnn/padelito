<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Commands;

final readonly class CreatePlayerProfileCommand
{
    public function __construct(
        public string $userId,
        public string $username,
        public ?string $displayName,
        public ?string $avatarUrl,
        public ?string $bio,
        public ?string $location,
        public ?string $dominantHand,
        public ?string $preferredPosition,
    ) {}
}
