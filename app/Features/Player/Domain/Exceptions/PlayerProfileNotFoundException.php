<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class PlayerProfileNotFoundException extends DomainException
{
    private function __construct()
    {
        parent::__construct(
            'Player profile not found.',
            domainCode: 'PLAYER_PROFILE_NOT_FOUND',
        );
    }

    public static function create(): self
    {
        return new self;
    }

    protected function getDefaultCode(): string
    {
        return 'PLAYER_PROFILE_NOT_FOUND';
    }
}
