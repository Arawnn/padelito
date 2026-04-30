<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class MatchNotFoundException extends DomainException
{
    private function __construct()
    {
        parent::__construct('Match not found.', domainCode: 'MATCH_NOT_FOUND');
    }

    public static function create(): self
    {
        return new self;
    }

    protected function getDefaultCode(): string
    {
        return 'MATCH_NOT_FOUND';
    }
}
