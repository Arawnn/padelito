<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Queries\GetPlayerProfile;

final readonly class GetPlayerProfileQuery
{
    public function __construct(
        public string $userId,
    ) {}
}
