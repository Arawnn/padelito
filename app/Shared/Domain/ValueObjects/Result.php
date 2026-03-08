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
     * @param  null|T  $value
     */
    public function __construct(
        public readonly bool $ok,
        public readonly mixed $value,
        public readonly ?DomainExceptionInterface $error,
    ) {}

    /**
     * @template TValue
     *
     * @param  TValue  $value
     * @return self<TValue>
     */
    public static function ok(mixed $value): self
    {
        return new self(true, $value, null);
    }

    /**
     * @return self<never>
     */
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
        return ! $this->ok;
    }

    public function error(): ?DomainExceptionInterface
    {
        return $this->error;
    }

    /**
     * @return null|T
     */
    public function value(): mixed
    {
        return $this->value;
    }
}
