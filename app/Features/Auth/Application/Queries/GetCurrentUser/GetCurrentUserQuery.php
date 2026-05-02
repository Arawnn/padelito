<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Queries\GetCurrentUser;

final readonly class GetCurrentUserQuery
{
    public function __construct(
        public string $userId,
    ) {}
}
