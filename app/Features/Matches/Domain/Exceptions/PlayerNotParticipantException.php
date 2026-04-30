<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class PlayerNotParticipantException extends DomainException
{
    private function __construct()
    {
        parent::__construct('Player is not a participant in this match.', domainCode: 'PLAYER_NOT_PARTICIPANT');
    }

    public static function create(): self
    {
        return new self;
    }

    protected function getDefaultCode(): string
    {
        return 'PLAYER_NOT_PARTICIPANT';
    }
}
