<?php

declare(strict_types=1);

namespace App\Features\Auth\Domain\Exceptions;

use App\Features\Auth\Domain\ValueObjects\Email;

final class UserNotFoundException extends \Exception {
    private function __construct() {}

    public static function fromEmail(Email $email): self
    {
        return new self('User not found with email: ' . $email->value());
    }
}