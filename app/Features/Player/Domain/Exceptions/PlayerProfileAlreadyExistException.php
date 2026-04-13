<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class PlayerProfileAlreadyExistException extends DomainException
{
    private function __construct()
    {
        parent::__construct(
            'A player profile already exists.',
            domainCode: 'PLAYER_PROFILE_ALREADY_EXIST',
        );
    }

    public static function create(): self
    {
        return new self;
    }

    protected function getDefaultCode(): string
    {
        return 'PLAYER_PROFILE_ALREADY_EXIST';
    }
}
