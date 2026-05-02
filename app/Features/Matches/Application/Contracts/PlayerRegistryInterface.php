<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\Contracts;

use App\Features\Matches\Domain\ValueObjects\PlayerId;

interface PlayerRegistryInterface
{
    public function exists(PlayerId $playerId): bool;
}
