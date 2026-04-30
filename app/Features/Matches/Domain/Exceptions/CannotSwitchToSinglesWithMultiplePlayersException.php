<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class CannotSwitchToSinglesWithMultiplePlayersException extends DomainException
{
    private function __construct()
    {
        parent::__construct(
            'Cannot switch to singles format when 3 or more players are already assigned.',
            domainCode: 'CANNOT_SWITCH_TO_SINGLES',
        );
    }

    public static function create(): self
    {
        return new self;
    }

    protected function getDefaultCode(): string
    {
        return 'CANNOT_SWITCH_TO_SINGLES';
    }
}
