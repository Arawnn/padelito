<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\Queries\GetMyMatchInvitations;

final readonly class GetMyMatchInvitationsQuery
{
    public function __construct(
        public string $playerId,
    ) {}
}
