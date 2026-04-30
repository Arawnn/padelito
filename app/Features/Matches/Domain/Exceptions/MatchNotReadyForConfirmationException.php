<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class MatchNotReadyForConfirmationException extends DomainException
{
    private function __construct()
    {
        parent::__construct(
            'Match is not ready for confirmation. Score and all required players must be set.',
            domainCode: 'MATCH_NOT_READY_FOR_CONFIRMATION',
        );
    }

    public static function create(): self
    {
        return new self;
    }

    protected function getDefaultCode(): string
    {
        return 'MATCH_NOT_READY_FOR_CONFIRMATION';
    }
}
