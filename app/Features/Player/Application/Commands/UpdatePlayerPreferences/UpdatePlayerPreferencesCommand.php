<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Commands\UpdatePlayerPreferences;

use App\Shared\Application\Optional;

final readonly class UpdatePlayerPreferencesCommand
{
    public function __construct(
        public string $userId,
        public Optional $dominantHand,
        public Optional $preferredPosition,
        public Optional $location,
    ) {}
}
