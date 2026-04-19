<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\Commands\RespondToMatchInvitation;

final readonly class RespondToMatchInvitationCommand
{
    public function __construct(
        public string $invitationId,
        public string $responderId,
        public bool $accept,
    ) {}
}
