<?php

declare(strict_types=1);

namespace App\Features\Auth\Domain\Exceptions;

final class InvalidPasswordException extends \Exception
{
    private function __construct(
        private readonly array $violations
    ) {
        parent::__construct(implode(', ', $violations));
    }

    public static function fromViolations(array $violations): self
    {
        return new self($violations);
    }

    public function violations(): array
    {
        return $this->violations;
    }
}