<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\Repositories;

use App\Features\Player\Domain\Entities\Player;
use App\Features\Player\Domain\ValueObjects\Id;
use App\Features\Player\Domain\ValueObjects\Username;

interface PlayerRepositoryInterface
{
    public function findById(Id $id): ?Player;

    /**
     * @param  list<Id>  $ids
     * @return array<string, Player>
     */
    public function findByIds(array $ids): array;

    public function findByUsername(Username $username): ?Player;

    public function save(Player $profile): void;
}
