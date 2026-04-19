<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class PlayerAlreadyConfirmedException extends DomainException
{
    private function __construct()
    {
        parent::__construct('Player has already confirmed this match.', domainCode: 'PLAYER_ALREADY_CONFIRMED');
    }

    public static function create(): self
    {
        return new self;
    }

    protected function getDefaultCode(): string
    {
        return 'PLAYER_ALREADY_CONFIRMED';
    }
}
