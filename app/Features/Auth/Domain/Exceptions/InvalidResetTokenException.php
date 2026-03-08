<?php

declare(strict_types=1);

namespace App\Features\Auth\Domain\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class InvalidResetTokenException extends DomainException
{
    public static function expiredOrInvalid(): self
    {
        return new self(
            'Password reset token is invalid or has expired.',
            domainCode: 'INVALID_RESET_TOKEN',
        );
    }

    protected function getDefaultCode(): string
    {
        return 'INVALID_RESET_TOKEN';
    }
}
