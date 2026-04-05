<?php

declare(strict_types=1);

namespace App\Features\Auth\Domain\Exceptions;

use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Domain\ValueObjects\Id;
use App\Shared\Domain\Exceptions\DomainException;

final class UserAlreadyExistsException extends DomainException
{
    public static function fromEmail(Email $email): self
    {
        return new self(
            'User already exists with email: '.$email->value(),
            domainCode: 'USER_ALREADY_EXISTS'
        );
    }

    public static function fromId(Id $id): self
    {
        return new self(
            'User already exists with id: '.$id->value(),
            domainCode: 'USER_ALREADY_EXISTS'
        );
    }

    protected function getDefaultCode(): string
    {
        return 'USER_ALREADY_EXISTS';
    }
}
