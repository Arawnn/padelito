<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Commands\UpdatePlayerPreferences;

final readonly class UpdatePlayerPreferencesCommand
{
    public function __construct(
        public string $userId,
        public ?string $dominantHand,
        public ?string $preferredPosition,
        public ?string $location,
    ) {}
}
