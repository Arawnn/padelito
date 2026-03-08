<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Queries\GetUserByEmail;

final readonly class GetUserByEmailQuery
{
    public function __construct(
        public string $email,
    ) {}
}
