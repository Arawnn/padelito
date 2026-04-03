<?php

declare(strict_types=1);

namespace App\Shared\Application;

use App\Shared\Domain\Exceptions\DomainExceptionInterface;

/**
 * @template T
 */
final class Result
{
    /**
     * @param  null|T  $value
     */
    private function __construct(
        private readonly bool $ok,
        private readonly mixed $value,
        private readonly ?DomainExceptionInterface $error,
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
     * @return self<null>
     */
    public static function void(): self
    {
        return new self(true, null, null);
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

    /**
     * @throws \LogicException if the Result is a failure
     */
    public function error(): DomainExceptionInterface
    {
        if ($this->ok) {
            throw new \LogicException('Cannot access error on a successful Result. Check isFail() first.');
        }

        return $this->error;
    }

    /**
     * @return T
     *
     * @throws \LogicException if the Result is a failure
     */
    public function value(): mixed
    {
        if (! $this->ok) {
            throw new \LogicException('Cannot access value on a failed Result. Check isOk() first.');
        }

        return $this->value;
    }

    /**
     * @template U
     *
     * @param  callable(T): U  $fn
     * @return self<U>
     */
    public function map(callable $fn): self
    {
        if ($this->isFail()) {
            /** @var self<U> */
            return $this;
        }

        return self::ok($fn($this->value));
    }

    /**
     * @template U
     *
     * @param  callable(T): self<U>  $fn
     * @return self<U>
     */
    public function flatMap(callable $fn): self
    {
        if ($this->isFail()) {
            /** @var self<U> */
            return $this;
        }

        return $fn($this->value);
    }

    /**
     * @return T
     *
     * @throws DomainExceptionInterface
     */
    public function getOrThrow(): mixed
    {
        if ($this->isFail()) {
            throw $this->error;
        }

        return $this->value;
    }

    /**
     * @template U
     *
     * @param  U  $default
     * @return T|U
     */
    public function getOrElse(mixed $default): mixed
    {
        return $this->isOk() ? $this->value : $default;
    }

    /**
     * Wraps a callable that may throw a DomainException into a Result.
     *
     * @template TValue
     *
     * @param  callable(): TValue  $fn
     * @return self<TValue>
     */
    public static function try(callable $fn): self
    {
        try {
            return self::ok($fn());
        } catch (DomainExceptionInterface $e) {
            return self::fail($e);
        }
    }
}
