<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Matches\Infrastructure\Services;

use App\Features\Matches\Domain\ValueObjects\PlayerId;
use App\Features\Matches\Infrastructure\Services\PlayerRegistryAdapter;
use App\Features\Player\Application\Contracts\PlayerExistenceReader;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class PlayerRegistryAdapterTest extends TestCase
{
    public function test_it_delegates_player_existence_to_player_context(): void
    {
        $reader = new class implements PlayerExistenceReader
        {
            public ?string $receivedPlayerId = null;

            public function exists(string $playerId): bool
            {
                $this->receivedPlayerId = $playerId;

                return true;
            }
        };

        $adapter = new PlayerRegistryAdapter($reader);

        $exists = $adapter->exists(PlayerId::fromString('00000000-0000-0000-0000-000000000001'));

        $this->assertTrue($exists);
        $this->assertSame('00000000-0000-0000-0000-000000000001', $reader->receivedPlayerId);
    }
}
