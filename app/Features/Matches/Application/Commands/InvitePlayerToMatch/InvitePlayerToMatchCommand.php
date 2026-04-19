<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\Commands\InvitePlayerToMatch;

final readonly class InvitePlayerToMatchCommand
{
    public function __construct(
        public string $matchId,
        public string $inviterId,
        public string $inviteeId,
        public string $team,
        public int $position,
    ) {}
}
