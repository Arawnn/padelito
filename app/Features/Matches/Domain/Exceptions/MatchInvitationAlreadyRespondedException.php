<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class MatchInvitationAlreadyRespondedException extends DomainException
{
    private function __construct()
    {
        parent::__construct('This invitation has already been responded to.', domainCode: 'MATCH_INVITATION_ALREADY_RESPONDED');
    }

    public static function create(): self
    {
        return new self;
    }

    protected function getDefaultCode(): string
    {
        return 'MATCH_INVITATION_ALREADY_RESPONDED';
    }
}
