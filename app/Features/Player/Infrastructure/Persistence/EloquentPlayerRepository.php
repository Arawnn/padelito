<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Persistence;

use App\Features\Player\Domain\Entities\Profile;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Domain\ValueObjects\Id;
use App\Features\Player\Domain\ValueObjects\Username;

class EloquentPlayerRepository implements PlayerRepositoryInterface
{
    public function findById(Id $id): ?Profile
    {
        throw new \Exception('Not implemented');
    }

    public function findByUsername(Username $username): ?Profile
    {
        throw new \Exception('Not implemented');
    }

    public function save(Profile $profile): void
    {
        throw new \Exception('Not implemented');
    }
}