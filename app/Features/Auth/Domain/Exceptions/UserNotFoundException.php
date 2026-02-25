<?php

declare(strict_types=1);

namespace App\Features\Auth\Domain\Exceptions;

use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Domain\ValueObjects\Id;
use App\Shared\Domain\Exceptions\DomainException;


final class UserNotFoundException extends DomainException {
    public static function fromEmail(Email $email): self
    {
        return new self(
            'User not found with email: ' . $email->value(),
        domainCode: 'USER_NOT_FOUND'
        );
    }

    public static function fromId(Id $id): self
    {
        return new self(
            'User not found with id: ' . $id->value(),
            domainCode: 'USER_NOT_FOUND'
    );
    }

    protected function getDefaultCode(): string
    {
        return 'USER_NOT_FOUND';
    }
}