<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Services;

use App\Features\Matches\Application\Contracts\PlayerRegistryInterface;
use App\Features\Matches\Domain\ValueObjects\PlayerId;
use App\Features\Player\Application\Contracts\PlayerExistenceReader;

final readonly class PlayerRegistryAdapter implements PlayerRegistryInterface
{
    public function __construct(private PlayerExistenceReader $players) {}

    public function exists(PlayerId $playerId): bool
    {
        return $this->players->exists($playerId->value());
    }
}
