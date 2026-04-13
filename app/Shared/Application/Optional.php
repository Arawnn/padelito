<?php

declare(strict_types=1);

namespace App\Shared\Application;

/**
 * Represents a value that may or may not have been explicitly provided.
 *
 * Used in update commands to distinguish three states:
 *   - absent()   - field not in request, keep current value
 *   - of(null)   - field explicitly set to null, clear value
 *   - of($value) - field explicitly set to a new value
 */
final readonly class Optional
{
    private function __construct(
        private bool $present,
        private mixed $value,
    ) {}

    public static function absent(): self
    {
        return new self(false, null);
    }

    public static function of(mixed $value): self
    {
        return new self(true, $value);
    }

    public function isPresent(): bool
    {
        return $this->present;
    }

    public function value(): mixed
    {
        return $this->value;
    }
}
