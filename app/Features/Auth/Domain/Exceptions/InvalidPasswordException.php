<?php

declare(strict_types=1);

namespace App\Features\Auth\Domain\Exceptions;

final class InvalidPasswordException extends \Exception {
    private function __construct() {}

    public static function fromViolations(array $violations): self
    {
        return new self(implode(', ', $violations));
    }
}