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
     * @param T|null $value
     */
    private function __construct(
        private readonly bool $ok,
        private readonly mixed $value,
        private readonly ?DomainExceptionInterface $error,
    ) {}

    /**
     * @template TValue
     * @param TValue $value
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

    public function error(): DomainExceptionInterface
    {
        if ($this->isOk()) {
            throw new \LogicException('Cannot access error on a successful Result.');
        }

        return $this->error;
    }

    /**
     * @return T
     */
    public function value(): mixed
    {
        if ($this->isFail()) {
            throw new \LogicException('Cannot access value on a failed Result.');
        }

        return $this->value;
    }
}