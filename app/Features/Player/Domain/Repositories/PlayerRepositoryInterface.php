<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\Repositories;

use App\Features\Player\Domain\Entities\Profile;
use App\Features\Player\Domain\ValueObjects\Id;

interface PlayerRepositoryInterface {
    public function findById(Id $id): ?Profile;
    public function save(Profile $profile): void;
}

