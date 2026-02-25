<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObjects;

use App\Shared\Domain\Exceptions\DomainExceptionInterface;

/**
 * @template T
 */
final class Result
{
    /**
     * @param T|null $value
     */
    public function __construct(
        public readonly bool $ok,
        public readonly mixed $value,
        public readonly ?DomainExceptionInterface $error,
    ) {}

    /**
     * @template T
     * @param T $value
     * @return self<T>
     */
    public static function ok(mixed $value): self
    {
        return new self(true, $value, null);
    }

    public static function fail(DomainExceptionInterface $error): self
    {
        return new self(false, null, $error);
    }

    public function isOk(): bool
    {
        return $this->ok;
    }

    public function isFail(): bool
    {
        return !$this->ok;
    }

    public function error(): ?DomainExceptionInterface
    {
        return $this->error;
    }
    
    /**
     * @return T|null
     */
    public function value(): mixed
    {
        return $this->value;
    }
}