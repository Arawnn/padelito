<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\ValueObjects;

use App\Features\Player\Domain\Exceptions\InvalidBioException;

final readonly class Bio
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

        if (strlen($value) > 255) {
            $violations[] = 'Bio must be less than 256 characters long';
        }
        if (! empty($violations)) {
            throw InvalidBioException::fromViolations($violations);
        }
    }
}
