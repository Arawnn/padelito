<?php

declare(strict_types=1);

namespace App\Features\Auth\Domain\Exceptions;

use App\Features\Auth\Domain\ValueObjects\Id;
use App\Features\Auth\Domain\ValueObjects\Email;

final class UserAlreadyExistException extends \Exception {
    public static function fromEmail(Email $email): self
    {
        return new self('User already exists with email: ' . $email->value());
    }

    public static function fromId(Id $id): self
    {
        return new self('User already exists with id: ' . $id->value());
    }
}