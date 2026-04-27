<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class MatchTeamFullException extends DomainException
{
    private function __construct()
    {
        parent::__construct('This team is already full.', domainCode: 'MATCH_TEAM_FULL');
    }

    public static function create(): self
    {
        return new self;
    }

    protected function getDefaultCode(): string
    {
        return 'MATCH_TEAM_FULL';
    }
}
