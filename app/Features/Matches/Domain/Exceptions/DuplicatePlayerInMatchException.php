<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class DuplicatePlayerInMatchException extends DomainException
{
    private function __construct()
    {
        parent::__construct('Each player can only appear once in a match.', domainCode: 'DUPLICATE_PLAYER_IN_MATCH');
    }

    public static function create(): self
    {
        return new self;
    }

    protected function getDefaultCode(): string
    {
        return 'DUPLICATE_PLAYER_IN_MATCH';
    }
}
