<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class MatchAlreadyCancelledException extends DomainException
{
    private function __construct()
    {
        parent::__construct('This match has already been cancelled.', domainCode: 'MATCH_ALREADY_CANCELLED');
    }

    public static function create(): self
    {
        return new self;
    }

    protected function getDefaultCode(): string
    {
        return 'MATCH_ALREADY_CANCELLED';
    }
}
