<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Services;

use App\Features\Player\Application\Contracts\PlayerExistenceReader;
use App\Features\Player\Infrastructure\Persistence\Eloquent\Models\Player as EloquentPlayer;

final readonly class EloquentPlayerExistenceReader implements PlayerExistenceReader
{
    public function exists(string $playerId): bool
    {
        return EloquentPlayer::query()->whereKey($playerId)->exists();
    }
}
