<?php

declare(strict_types=1);

namespace Tests\Shared\Mother\Fake;

use App\Features\Matches\Application\Contracts\PlayerRegistryInterface;
use App\Features\Matches\Domain\ValueObjects\PlayerId;

final class InMemoryPlayerRegistry implements PlayerRegistryInterface
{
    /** @var array<string, true> */
    private array $registeredPlayerIds = [];

    public function register(string $playerId): void
    {
        $this->registeredPlayerIds[$playerId] = true;
    }

    public function exists(PlayerId $playerId): bool
    {
        return isset($this->registeredPlayerIds[$playerId->value()]);
    }
}
