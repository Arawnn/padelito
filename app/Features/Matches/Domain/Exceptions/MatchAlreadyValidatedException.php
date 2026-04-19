<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class MatchAlreadyValidatedException extends DomainException
{
    private function __construct()
    {
        parent::__construct('This match has already been validated.', domainCode: 'MATCH_ALREADY_VALIDATED');
    }

    public static function create(): self
    {
        return new self;
    }

    protected function getDefaultCode(): string
    {
        return 'MATCH_ALREADY_VALIDATED';
    }
}
