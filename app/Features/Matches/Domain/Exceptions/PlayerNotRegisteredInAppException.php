<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class PlayerNotRegisteredInAppException extends DomainException
{
    private function __construct(string $playerId)
    {
        parent::__construct(
            "Player {$playerId} is not registered in the application.",
            domainCode: 'PLAYER_NOT_REGISTERED',
        );
    }

    public static function forPlayer(string $playerId): self
    {
        return new self($playerId);
    }

    protected function getDefaultCode(): string
    {
        return 'PLAYER_NOT_REGISTERED';
    }
}
