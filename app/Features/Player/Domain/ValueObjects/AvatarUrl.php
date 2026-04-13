<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\ValueObjects;

use App\Features\Player\Domain\Exceptions\InvalidAvatarUrlException;

final readonly class AvatarUrl
{
    private function __construct(private string $value) {}

    public static function fromString(string $value): self
    {
        self::validate($value);

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    private static function validate(string $value): void
    {
        $violations = [];

        if (! filter_var($value, FILTER_VALIDATE_URL)) {
            $violations[] = 'Avatar URL is not a valid URL';
        }

        if (! empty($violations)) {
            throw InvalidAvatarUrlException::fromViolations($violations);
        }
    }
}
