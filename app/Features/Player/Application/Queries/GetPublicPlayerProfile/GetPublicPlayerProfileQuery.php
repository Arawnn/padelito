<?php

declare(strict_types=1);

namespace App\Features\Player\Application\Queries\GetPublicPlayerProfile;

final readonly class GetPublicPlayerProfileQuery
{
    public function __construct(
        public string $targetUsername,
    ) {}
}
