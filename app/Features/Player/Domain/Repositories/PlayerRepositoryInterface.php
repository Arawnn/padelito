<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\Repositories;

use App\Features\Player\Domain\Entities\Profile;
use App\Features\Player\Domain\ValueObjects\Id;
use App\Features\Player\Domain\ValueObjects\Username;

interface PlayerRepositoryInterface
{
    public function findById(Id $id): ?Profile;

    public function findByUsername(Username $username): ?Profile;

    public function save(Profile $profile): void;
}
