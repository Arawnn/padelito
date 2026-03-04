<?php

namespace App\Features\Auth\Domain\ValueObjects;

use App\Features\Auth\Domain\Exceptions\InvalidPasswordException;

final readonly class Password
{
    private function __construct(
        private string $value
    ) {}

    public function value(): string
    {
        return $this->value;
    }

    public static function forVerification(string $value): self
    {
        return new self($value);
    }

    public static function fromPlainText(string $value): self
    {
        self::validate($value);

        return new self($value);
    }

    private static function validate(string $value): void
    {
        $violations = [];

        if (strlen($value) < 12) {
            $violations[] = 'Password must be at least 12 characters long';
        }
        if (!preg_match('/[A-Z]/', $value)) {
            $violations[] = 'Password must contain at least one uppercase letter';
        }
        if (!preg_match('/[a-z]/', $value)) {
            $violations[] = 'Password must contain at least one lowercase letter';
        }
        if (!preg_match('/[0-9]/', $value)) {
            $violations[] = 'Password must contain at least one number';
        }
        if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $value)) {
            $violations[] = 'Password must contain at least one special character';
        }

        if (!empty($violations)) {
            throw InvalidPasswordException::fromViolations($violations);
        }
    }
}
