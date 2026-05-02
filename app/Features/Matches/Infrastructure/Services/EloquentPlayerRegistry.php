<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Services;

use App\Features\Matches\Application\Contracts\PlayerRegistryInterface;
use App\Features\Matches\Domain\ValueObjects\PlayerId;
use App\Features\Player\Infrastructure\Persistence\Eloquent\Models\Player as EloquentPlayer;

final readonly class EloquentPlayerRegistry implements PlayerRegistryInterface
{
    public function exists(PlayerId $playerId): bool
    {
        return EloquentPlayer::query()->whereKey($playerId->value())->exists();
    }
}
