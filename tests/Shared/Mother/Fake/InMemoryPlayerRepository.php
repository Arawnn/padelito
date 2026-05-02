<?php

declare(strict_types=1);

namespace Tests\Shared\Mother\Fake;

use App\Features\Matches\Application\Contracts\PlayerRegistryInterface;
use App\Features\Matches\Domain\ValueObjects\PlayerId;
use App\Features\Player\Domain\Entities\Player;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Domain\ValueObjects\Id;
use App\Features\Player\Domain\ValueObjects\Username;

final class InMemoryPlayerRepository implements PlayerRegistryInterface, PlayerRepositoryInterface
{
    /** @var array<string, Player> */
    private array $store = [];

    public function findById(Id $id): ?Player
    {
        return $this->store[$id->value()] ?? null;
    }

    public function findByIds(array $ids): array
    {
        $players = [];
        foreach ($ids as $id) {
            $player = $this->findById($id);
            if ($player !== null) {
                $players[$id->value()] = $player;
            }
        }

        return $players;
    }

    public function exists(PlayerId $playerId): bool
    {
        return isset($this->store[$playerId->value()]);
    }

    public function findByUsername(Username $username): ?Player
    {
        foreach ($this->store as $player) {
            if ($player->username()->value() === $username->value()) {
                return $player;
            }
        }

        return null;
    }

    public function save(Player $player): void
    {
        $this->store[$player->id()->value()] = $player;
    }
}
