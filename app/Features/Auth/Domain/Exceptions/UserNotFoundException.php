<?php

declare(strict_types=1);

namespace App\Features\Auth\Domain\Exceptions;

use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Domain\ValueObjects\Id;

final class UserNotFoundException extends \Exception {
    public static function fromEmail(Email $email): self
    {
        return new self('User not found with email: ' . $email->value());
    }

    public static function fromId(Id $id): self
    {
        return new self('User not found with id: ' . $id->value());
    }
}