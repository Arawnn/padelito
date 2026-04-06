<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Repositories;

use App\Features\Player\Domain\Entities\Player;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Domain\ValueObjects\Id;
use App\Features\Player\Domain\ValueObjects\Username;

class EloquentPlayerRepository implements PlayerRepositoryInterface
{
    public function findById(Id $id): ?Player
    {
        throw new \Exception('Not implemented');
    }

    public function findByUsername(Username $username): ?Player
    {
        throw new \Exception('Not implemented');
    }

    public function save(Player $profile): void
    {
        throw new \Exception('Not implemented');
    }
}
