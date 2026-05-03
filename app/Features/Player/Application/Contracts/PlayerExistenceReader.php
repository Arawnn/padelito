<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Contracts;

interface PlayerExistenceReader
{
    public function exists(string $playerId): bool;
}
