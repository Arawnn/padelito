<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class UsernameAlreadyTakenException extends DomainException
{
    private function __construct()
    {
        parent::__construct(
            'This username is already taken.',
            domainCode: 'USERNAME_ALREADY_TAKEN',
        );
    }

    public static function create(): self
    {
        return new self;
    }

    protected function getDefaultCode(): string
    {
        return 'USERNAME_ALREADY_TAKEN';
    }
}
